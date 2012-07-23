<?php

/**
 *   
 * @package CommandAction\Species\SpeciesMaxent
 * 
 * Wrapper for Maxent.jar and assocoiated projections
 * 
 * 
 */
class SpeciesMaxent extends CommandAction {
    
    
    
    public function __construct() {
        parent::__construct();
        $this->CommandName(__CLASS__);
        
        $this->FinderName("SpeciesFinder");
     
        $this->isServerRun(false);
        
    }

    public function __destruct() {
        parent::__destruct();
    }

    
    /**
     * This is run from the web server side 
     * - so NO processing here 
     * - check to see if this has already been run / outputs or other items have already been processed 
     * 
     * @param src array 
     *  species   => space delimited string of values
        scenario  => space delimited string of values
        model     => space delimited string of values
        time      => space delimited string of values
     * 
     */
    public function initialise($src = null) 
    {
        
        if (is_null($src)) return null;
        
        $this->SpeciesCombinations(null);
        $this->ResultsComplete(false);
        $this->ScriptsToRun();
        
        
                 $this->SpeciesIDs(array_util::Value($src,'species',null,true));
        $this->EmissionScenarioIDs(array_util::Value($src,'scenario',null,true));
            $this->ClimateModelIDs(array_util::Value($src,'model',null,true));
                    $this->TimeIDs(array_util::Value($src,'time',null,true));
                    
        // check here to see if we are missing some data
        
        
        $this->buildCombinations(); 

        $this->initialised(true);
        
        if (!$this->isServerRun())
            $this->GetResults();  // then populates the combinations with anything that has already been done
        
        if ($this->ResultsComplete())
        {
            $this->ExecutionFlag(CommandAction::$EXECUTION_FLAG_COMPLETE);      // if we already have the dat - no reason to go to the server
            $this->Queue();
            return $this->Result();   // we are done 
        }
        
        return true;
        
    }
    
    private function Queue()
    {
        DBO::LogError(__METHOD__."(".__LINE__.")"," Updated queue for  ".$this->ID()." .. ".$this->Status());
        DatabaseCommands::CommandActionQueue($this);
    }
    
    
    /**
     * 
     */
    private function buildCombinations()
    {
        $species   = explode(" ",$this->SpeciesIDs());
        $scenarios = explode(" ",$this->EmissionScenarioIDs());
        $models    = explode(" ",$this->ClimateModelIDs());
        $times     = explode(" ",$this->TimeIDs());
        
        $full_count =1;
        $result = array();
        foreach ($species as $speciesID)
        {
            $result[$speciesID] = array();
            foreach ($scenarios as $scenarioID)
                foreach ($models  as $modelID)
                    foreach ($times as $timeID)
                    {
                        $result[$speciesID]["{$scenarioID}_{$modelID}_{$timeID}"] = null;
                        $full_count++;
                    }
                        
                        
                        
        }

        $this->SpeciesCombinations($result); // sets up the default empty result set
        
        $this->ResultsFullCountString($full_count - 1);
        
        
    }
    
    
    
    /**
     * Runnning on GRID 
     * - Look at inititalisation variables 
     * - get Species occurances for any species that we don't have
     * - get current progress, if other process have already done parts of this call.
     * - launch other jobs thru QSUB that will be able to run paralell jobs for maxent i.e. Future Projections
     * 
     * @return type 
     */
    public function Execute()
    {
        
        $this->QsubCollectionID(substr(uniqid(),0,10));
        
        // rebuild combos i we don't have them
        if (is_null($this->SpeciesCombinations()) ||  count($this->SpeciesCombinations()) == 0  ) 
        {
            $this->buildCombinations();
            DBO::LogError(__METHOD__."(".__LINE__.")","Has to rebuild Combinations as thery were not build - Odd ??\n");
        }
        
        
        // if we have data in the file system but not in database load it in - Just NMain root results for each spcecies
        // but only load the data if we are running as "Large Server Process"
        // we don't want to inflict this on the user
        if ($this->isServerRun())
            foreach (array_keys($this->SpeciesCombinations()) as $speciesID) 
                DatabaseMaxent::InsertMainMaxentResults($speciesID);  // this will load for all 

        
        
        $this->GetResults();
        
        
        
        if ($this->ResultsComplete())  // check now to see if we have to  call the GRID
        {            
            $this->Status("ALL DONE");
            $this->ExecutionFlag(CommandAction::$EXECUTION_FLAG_FINALISE);
            $this->Queue();
            return $this->Result();   // we are done 
        }

        //
        // We have to call the grid because at least one outyput is missing
        //
        
        $this->Status("Started Model Execution on GRID");
        $this->Queue();
        
        $occurences_ok = $this->getOccurances();
        if (!$occurences_ok) 
        {
            DBO::LogError(__METHOD__."(".__LINE__.")","Failed to get Occurences \n".print_r($this->SpeciesCombinations(),true));

            $this->Status("An Error Occured ".__METHOD__."(".__LINE__.")"." - 1");
            $this->Result(null);
            $this->Queue();
            return null;
        }
        
        
        
        $compute_ok = $this->ComputeSelectedSpecies();
        if (!$compute_ok) 
        {
            DBO::LogError(__METHOD__."(".__LINE__.")","Failed to compute Scripts \n".print_r($this->SpeciesCombinations(),true));
            
            $this->Status("An Error Occured ".__METHOD__."(".__LINE__.")"." - 2");
            $this->Result(null);
            $this->Queue();
            return null;
        }
        
        
        if (count($this->ScriptsToRun()) == 0)
        {
            $this->ExecutionFlag(CommandAction::$EXECUTION_FLAG_FINALISE);
            $this->GetResults();
            $this->Queue();
            return $this->Result();
        }
        
        sleep(10); // wait for Qstat to catch up - before we start poling it

        
        $jobList = $this->qsubJobs();      // get current number of qsub jobs actually running now
        
        while (count($jobList) > 0)
        {

            $this->GetResults();
            $this->Queue();
            
            sleep(10);
            $jobList = $this->qsubJobs();
            
        }
        
        $this->Status("All GRID jobs have been completed ");
        $this->ExecutionFlag(CommandAction::$EXECUTION_FLAG_COMPLETE);
        $this->GetResults();
        $this->Queue();

    }
    
    
    /**
     * 
     * @return array QSTAT output for all jobs with the same CollectionID 
     * 
     */
    private function qsubJobs()
    {
        $lines = array();
        exec("qstat | grep '{$this->QsubCollectionID()}'",$lines);
        return $lines;
    }

    
    
    
    /**
     * Read database for this species and create species folder for the required species
     * and retrive Occur.csv and place in species data folder if it does not exist.
     * 
     */
    private function getOccurances()
    {
        
        $speciesIDs = array_keys($this->SpeciesCombinations());
        
        foreach ($speciesIDs as $speciesID) 
        {
            if (is_null($speciesID)) continue;
            if ($speciesID == "" ) continue;
            
            file::mkdir_safe($this->speciesDataFolder($speciesID));
            
            $getOccurResult = $this->getOccurancesRecordsFor($speciesID);
            
            
            
            if (is_null($getOccurResult))
            {
                DBO::LogError(__METHOD__."(".__LINE__.")","Error getting Occurence data for ".$speciesID."\n");
                return null;
            }
            
        }
        
        // at this point all Occurrances files will exist (ie for each species)        
        return true;
        
    }
    
    
    
    /**
     *
     * @param type $speciesID - Species Scientific Name
     * @param type $occurFilename - Where to write datafrom database
     * @return boolean 
     */
    private function getOccurancesRecordsFor($speciesID)
    {
     
        $occurFilename = $this->species_occurence_filename($speciesID);
        file::Delete($occurFilename);
        
        $file_written = SpeciesData::SpeciesOccuranceToFile($speciesID,$occurFilename); // get occuances from database
        if (!$file_written) return null;
        
        return true;
        
    }
    
    
    /**
     * At any one time this will have the current results available 
     * - it will be a list of ASC grid filenames that can be displayed on the web server
     * 
     * @return type 
     */
    private function GetResults()
    {
        
        $result = array();
        $complete = true;
        
        $done_count = 0;
        
        foreach ($this->SpeciesCombinations() as $speciesID => $combinations) 
        {

            DBO::LogError(__METHOD__."(".__LINE__.")","Checking on results for {$speciesID}");
            
            
            $speciesID = trim($speciesID);
            if ($speciesID == "") continue;
            
            if (!$this->MaxentLogDone($speciesID)) 
            {
                $complete = false;
                
                // since the maxent log does not exiosts we want to  just copy the current results over                
                foreach ($combinations as $combination => $file_id) 
                    $result[$speciesID][$combination] = $file_id;
                
                continue;  // Next SpeciesID don't check outputs when we don't have a complete maxent log
            }
            
            
            
            foreach ($combinations as $combination => $file_id) 
            {
                
                $result[$speciesID][$combination] = $file_id; // copy current values into new result - will copy over null values as well
                
                
                DBO::LogError(__METHOD__."(".__LINE__.")","Checking on results for {$speciesID} ... {$combination}");
                
                // the current file_id for this combinartion is not set - go get it / check for it
                if (is_null($file_id))
                {
                    
                    list($scenario, $model, $time) = explode("_",$combination);    
                                                                                             
                    $file_id = $this->GetModelledData($speciesID, $scenario, $model, $time); // we don't know if we have database data for this comnination so go acheck
                    
                    if (is_null($file_id))
                    {
                        $result[$speciesID][$combination] = null;  // we check for the file / db row and we did not find it so set this combinatrion to null
                        $complete = false; // we still don't have all results requested
                    }
                    else
                    {
                        $result[$speciesID][$combination] = $file_id;    
                    }
                    
                }
                
                if (strlen($file_id) > 0) $done_count++; // count what ahs been done
                
            }
                        
        }
        
        $this->SpeciesCombinations($result);

        $this->Result($result);
        
        $this->ResultsComplete($complete);
        
        $this->ResultsDoneCountString($done_count);
        
        $this->Status("Items processed ".$this->ResultsDoneCountString()." of ".$this->ResultsFullCountString() );
        
    }
    
    
    /**
     * Find maxent Log and check that the Last line states "Ending"
     * then we know that this species was processed correctly
     * 
     * @param type $speciesID
     * @return boolean 
     */
    private function MaxentLogDone($speciesID)
    {
        
        $maxentLog =    configuration::Maxent_Species_Data_folder().
                        $speciesID.
                        configuration::osPathDelimiter().
                        configuration::Maxent_Species_Data_Output_Subfolder().
                        configuration::osPathDelimiter().
                        "maxent.log";
        
        
        if (!file_exists($maxentLog)) return false;   // if we don't have the file then it's not done yet

        $lastLogLine = exec("tail -n1 '$maxentLog'");

        if (util::contains($lastLogLine, "Ending"))  return true; // Maxent finished OK
        
        return false; // Maxtent did not finish OK
        
    }

    
    

    /**
     *
     * Create script file to run Maxent.jar and retrive status and outputs 
     *
     * @return int JobCount - (Number of species to compute) x (Number of Combinations)
     * 
     * 
     */
    private  function ComputeSelectedSpecies()
    {
        
        $scripts_written_ok = $this->writeScriptFiles();  // array of script path names
        
        if (!$scripts_written_ok)
        {
            DBO::LogError(__METHOD__."(".__LINE__.")","Failed to create scripts \n");
            return null;
        }
        
        if (is_null($this->ScriptsToRun())) return null;  // no scripts to run this might be just fune
        
        foreach ($this->ScriptsToRun() as $scriptname) 
        {
            if (is_null($scriptname) || trim($scriptname) == "" ) continue;
            $cmd = "qsub -N{$this->QsubCollectionID()} '{$scriptname}'";
            exec($cmd);  /// QSUB JOBS Submitted here
        }
        
        return true;
        
    }
    
    
    /*
     * Script file used to run maxent.jar and all projectections state in combinations
     * 
     */
    private function writeScriptFiles()
    {
        
        // for each species we need to run all the combinations
        
        $scripts = array();
        foreach ($this->SpeciesCombinations() as $speciesID => $combinations)
        {
            $singleScriptName = $this->writeMaxentSingleSpeciesProjectionScriptFile($speciesID,$combinations); // all combinations for a single species
            
            
            if (is_null($singleScriptName))
            {
                DBO::LogError(__METHOD__."(".__LINE__.")","Failed to create script for  {$speciesID}\n");
                return null;
            }
            
            
            if ($singleScriptName != "")   $scripts[$speciesID] = $singleScriptName; // add script if we have the name
            
        }

        $this->ScriptsToRun($scripts); // update list of scriupts that need to run

        return true;
        
    }


    /**
     * Create script file that will submitted to QSUB to run maxent for Current Projection and all future projections requested
     * 
     * @param type $speciesID
     * @param type $combinations
     * @return string|null 
     */
    private function writeMaxentSingleSpeciesProjectionScriptFile($speciesID,$combinations)
    {
        
        file::mkdir_safe($this->species_scripts_folder($speciesID));
        file::mkdir_safe($this->species_data_folder($speciesID));
        file::mkdir_safe($this->species_output_folder($speciesID));
        
        $maxent_single_species_script_filename = $this->maxent_single_species_script_filename($speciesID);
        
        
        // create combinration script files here - will contains lines to run MaxEnt and Qsub calls to run future projects  in paralell
        
        $script = "";
        $script .= $this->maxentScript($speciesID);
        

        foreach ($combinations as $combination => $file_id)
        {
            
            if (!is_null($file_id)) continue;  // we already have this combination
            
            // script that will be used to execute just a single combo
            $singleFutureScriptFilename = $this->singleCombinationScript($speciesID,$combination); 

            if (is_null($singleFutureScriptFilename))
            {
                DBO::LogError(__METHOD__."(".__LINE__.")","Call to singleCombinationScript failed SpeciesID = $speciesID  combination = $combination  \n");
                return null;
            }
            
            
            // add this script name to the Main "root" script to be run
            $script .= "\nqsub -N{$this->QsubCollectionID()} '{$singleFutureScriptFilename}'";          
            
        }
        
            
        
        $script = trim($script);
        if ($script == "") return "";  // if there is nothing to run return empty_string

        
        // add lines to remove - script once it's complete
        $script .=  "\n";
        $script .=  "\nrm {$maxent_single_species_script_filename}\n"; // remove itself after execution
        $script .=  "\n";
        
        
        file_put_contents($maxent_single_species_script_filename, $script);

        if (!file_exists($maxent_single_species_script_filename))
        {
            DBO::LogError(__METHOD__."(".__LINE__.")","Failed to Write Script for Species = {$speciesID} maxent_single_species_script_filename = $maxent_single_species_script_filename \n");
            return null;
        }
        
        return $maxent_single_species_script_filename;   // we have something to RUN so return the filename
        
    }

    
    private function species_scripts_folder($speciesID)
    {
        $scripts_folder =  configuration::CommandScriptsFolder().
                           $speciesID.
                           configuration::osPathDelimiter();

        return $scripts_folder;
        
    }
    

    private function species_data_folder($speciesID)
    {
        $data_folder    =   configuration::Maxent_Species_Data_folder().
                            $speciesID.
                            configuration::osPathDelimiter();

        return $data_folder;
        
    }
    

    private function species_output_folder($speciesID)
    {
        $output_folder  =   configuration::Maxent_Species_Data_folder().
                            $speciesID.
                            configuration::osPathDelimiter().
                            configuration::Maxent_Species_Data_Output_Subfolder();

        return $output_folder;
        
    }

    private function species_output_projection_filename($speciesID, $scenario, $model, $time)
    {
        $output_file    =   configuration::Maxent_Species_Data_folder().
                            $speciesID.
                            configuration::osPathDelimiter().
                            configuration::Maxent_Species_Data_Output_Subfolder().
                            configuration::osPathDelimiter().
                            "{$scenario}_{$model}_{$time}".'.asc';

        return $output_file;
        
    }
    
    
    private function maxent_single_species_script_filename($speciesID)
    {
        $scriptFilename =   configuration::CommandScriptsFolder().
                            $speciesID.
                            configuration::osPathDelimiter().
                            $speciesID.'_root'.
                            configuration::CommandScriptsSuffix();

        return $scriptFilename;
        
    }
    
    
    private function maxentScript($speciesID)
    {
        
        if ($this->MaxentLogDone($speciesID)) return ""; // if we already have the maxent data then don't run this part 
        
        $species_folder = $this->species_data_folder($speciesID);
        
         $maxent = configuration::MaxentJar();
          $train = configuration::Maxent_Taining_Data_folder();
        $project = configuration::Maxent_Future_Projection_Data_folder();
          $occur = configuration::Maxent_Species_Data_Occurance_Filename();
        
        $output_folder  =  $this->species_output_folder($speciesID);
        
        $occur =  $this->species_occurence_filename($speciesID);

        $MaxentResultsInsert_php  = configuration::ApplicationFolder()."Search/MaxentResultsInsert.php";
        
        $speciesInfo = SpeciesData::SpeciesQuickInformation($speciesID);
        
        
$maxent_script = <<<AAA
#!/bin/tcsh
#
# execute maxent for  ({$speciesID})
# ==================================================================================
#
# speciesID      = {$speciesID} 
# Species Info   = {$speciesInfo}
# Scenarios      = {$this->EmissionScenarioIDs()}
# Models         = {$this->ClimateModelIDs()} 
# Times          = {$this->TimeIDs()}
#
# file locations
# Species Folder = {$species_folder}
# MAXENT         = {$maxent}
# TRAINCLIMATE   = {$train}
# PROJECTCLIMATE = {$project}
s OCCUR          = {$occur}

set MAXENT={$maxent}
set TRAINCLIMATE={$train}
set PROJECTCLIMATE={$project}
set OCCUR={$occur}

module load java

#model the species distribution
java -mx2048m -jar {$maxent} environmentallayers={$train} samplesfile={$occur} outputdirectory={$output_folder} -J -P -x -z redoifexists autorun
php -q '{$MaxentResultsInsert_php}' $speciesID

AAA;
            return $maxent_script;


    }
    
    
    
    /**
     * For a single species for a single combination create script that can be submited to QSUB
     * 
     * @param type $speciesID
     * @param type $combination
     * @param type $localComboFilename
     * @return string 
     */
    private function singleCombinationScript($speciesID,$combination)
    {
        
        $script_folder =    $this->species_scripts_folder($speciesID);
        
        $maxent = configuration::MaxentJar();
        
        $lambdas =  $this->species_lambdas($speciesID);

        $proj =     configuration::Maxent_Future_Projection_Data_folder().
                    configuration::osPathDelimiter().
                    $combination;
        

        $future_projection_output = configuration::Maxent_Species_Data_folder().
                                    $speciesID.
                                    configuration::osPathDelimiter().
                                    configuration::Maxent_Species_Data_Output_Subfolder().
                                    configuration::osPathDelimiter().
                                    $combination.
                                    ".asc";

        
        $scriptFilename =   configuration::CommandScriptsFolder().
                            $speciesID.
                            configuration::osPathDelimiter().
                            $this->ID().'_'.$combination.
                            configuration::CommandScriptsSuffix();
        
        
        $script  = "#!/bin/tcsh"; 
        $script .= "\n# combination              = {$combination}";
        $script .= "\n# speciesID                = {$speciesID}";
        $script .= "\n# speciesInfo              = ".SpeciesData::SpeciesQuickInformation($speciesID);
        $script .= "\n# script_folder            = {$script_folder}";
        $script .= "\n# maxent                   = {$maxent}";
        $script .= "\n# lambdas                  = {$lambdas}";
        $script .= "\n# proj                     = {$proj}";
        $script .= "\n# future_projection_output = {$future_projection_output}";
        $script .= "\n# scriptFilename           = {$scriptFilename}";
        $script .= "\n#";
        
        $script .= "\nmodule load java";
        $script .= "\ncd {$script_folder}";

                    //java -mx2048m -cp $MAXENT   density.Project output/${SPP}.lambdas $PROJ output/`basename $PROJ`.asc fadebyclamping nowriteclampgrid nowritemess -x        
        $script .= "\njava -mx2048m -cp {$maxent} density.Project {$lambdas} {$proj} {$future_projection_output} fadebyclamping nowriteclampgrid nowritemess -x";   
        $script .= "\nphp -q ".configuration::ApplicationFolder()."Search/MaxentQuickLookInsert.php $speciesID '$future_projection_output'";
        $script .= "\nrm $scriptFilename";
        $script .= "\n";
            
        file_put_contents($scriptFilename, $script);
        
        if (!file_exists($scriptFilename))
        {
            DBO::LogError(__METHOD__."(".__LINE__.")","Failed to Write Script for Species = {$speciesID}  combination = $combination scriptFilename = $scriptFilename \n");
            return null;
        }
        
        
        return $scriptFilename;
        
    }
    
    
    private function species_lambdas($speciesID) {
        
        $lambdas =  configuration::Maxent_Species_Data_folder().
                    $speciesID.
                    configuration::osPathDelimiter().
                    configuration::Maxent_Species_Data_Output_Subfolder().
                    configuration::osPathDelimiter().
                    $speciesID.
                    ".lambdas";
        
        return $lambdas;
        
    }
    
    
    
    public function SpeciesIDs() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }


    public function EmissionScenarioIDs() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }

    public function ClimateModelIDs() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }

    public function TimeIDs() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }

    public function FinderName() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }

    public function QsubCollectionID() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }
    
    public function QsubJobCount() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }

    
    public function ScriptsToRun() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }
    
    
    /**
     * Keep the array of [species][combintations]
     * here so we can look it up and get the outputs as they come
     * 
     * @return type 
     */
    public function SpeciesCombinations() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }

    
    public function isServerRun() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }
    

    public function ResultsComplete() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }
    

    public function ResultsDoneCountString() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }


    public function ResultsFullCountString() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }
    
    
    public function AttachedCommand() 
    {
        return $this;
    }

    
    private function species_occurence_filename($speciesID)
    {
        
        $occurFilename =    configuration::Maxent_Species_Data_folder().
                            $speciesID.
                            configuration::osPathDelimiter().
                            configuration::Maxent_Species_Data_Occurance_Filename();
        
        return $occurFilename;
        
    }

    
    
    private function speciesDataFolder($speciesID)
    {
        
        $species_data_folder =  configuration::Maxent_Species_Data_folder().
                                $speciesID.
                                configuration::osPathDelimiter();
        
        return $species_data_folder;
        
    }
    
    
    
    
    /**
     * Make sure all outputs for a model run exist
     * 
     * @param type $species
     * @param type $scenario
     * @param type $model
     * @param type $time
     * @return null 
     */
    public function GetModelledData($species,$scenario, $model, $time)
    {
        
        $asc_file_id        = SpeciesData::GetModelledData($species, $scenario, $model, $time,'ASCII_GRID');
        $quickLook_file_id  = SpeciesData::GetModelledData($species, $scenario, $model, $time,'QUICK_LOOK');
        
        if (is_null($asc_file_id) || is_null($quickLook_file_id) )return null;
        
        return $asc_file_id;
    }
    
}


?>
