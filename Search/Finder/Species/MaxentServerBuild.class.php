<?php


class MaxentMainServerBuild extends Object {
    

    public static function Run($speciesID)
    {
        if (is_null($speciesID)) return null;
        
        if ($speciesID == "ALL")  return self::RunAll();
        
        echo "MaxentMainServerBuild for $speciesID\n";
        
        $MM = new MaxentMainServerBuild($speciesID);
        $MM->GenerateOnly(false);
        return $MM->Execute();
        
    }

    private  static function RunAll()
    {
        
        $species_ids = DBO::Unique("species_occurence", "species_id", "count > 1", true);
        

        foreach ($species_ids as $species_row) 
        {
            $id = $species_row['species_id'];
            //echo "species_id = {$id} \n";
            
            $MM = new MaxentMainServerBuild($id);
            $MM->GenerateOnly(false);
            $MM->Execute();
        }
        
    }
    
    
    
    public function __construct($speciesID) {
        parent::__construct();
        
        $this->SpeciesID($speciesID);
        
        $this->ParalellFuturePredictions(false);
    
        $this->GenerateOnly(false);
    
            
        
    }

    public function __destruct() {
        parent::__destruct();
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
        
        if (file_exists($this->maxent_single_species_script_filename())) 
        {
            echo "Already Exists {$this->maxent_single_species_script_filename()}\n ";
            return;
        }
        
        
        file::mkdir_safe($this->species_scripts_folder());
        file::mkdir_safe($this->species_data_folder());
        file::mkdir_safe($this->species_output_folder());
        
        
        
        $this->QsubCollectionID("tdh_".$this->SpeciesID());
        
        if (is_null($this->getOccurances()))  return null;
        
        $combinations = array();
        foreach (DatabaseClimate::GetScenarios() as $scenarioID)
            foreach (DatabaseClimate::GetModels()  as $modelID)
                foreach (DatabaseClimate::GetTimes() as $timeID)
                    $combinations["{$scenarioID}_{$modelID}_{$timeID}"] = null;

        $this->SpeciesCombinations($combinations); // sets up the default empty result set

        
        
        $scriptname = $this->writeMaxentSingleSpeciesProjectionScriptFile(); // all combinations for a single species
        if ($scriptname == "") return null;
        
        if (!file_exists($scriptname)) return null;
        
        
        if ($this->GenerateOnly())
        {
            echo "Create script ready for execution \n$scriptname \n ";
        }
        else
        {
            $dir = dirname($scriptname);
            
            echo "Executing to QSUB  $scriptname \n ";
            $qsub_result     = exec("cd {$dir}; qsub -N{$this->QsubCollectionID()} '{$scriptname}'");  /// QSUB JOBS Submitted here
            echo "QSUB  qsub_result = $qsub_result \n ";
            
        }

    }
    
    
    /**
     * Create script file that will submitted to QSUB to run maxent for Current Projection and all future projections requested
     * 
     * @param type $speciesID
     * @param type $combinations
     * @return string|null 
     */
    private function writeMaxentSingleSpeciesProjectionScriptFile()
    {
        
        $script = $this->maxentScript();
        
        foreach (array_keys($this->SpeciesCombinations()) as $combination)
        {
            
            $singleFutureScript = $this->singleCombinationScript($combination);   // script that will be used to execute just a single combo
            
            if (is_null($singleFutureScript)) continue;


            //
            // If we are running bigger Future jobs then these may be in paralell 
            // - here we add either the script itself or a call to execute the script 
            //
            if ($this->ParalellFuturePredictions())
            {
                echo "Adding as Paralell {$combination} ";
                
                $script .= "\nqsub -N{$this->QsubCollectionID()} '{$singleFutureScript}'";              
            }
            else
            {
                // echo "Adding as Serial {$combination} ";
                $script .= $singleFutureScript;
            }
            
            
        }
        
        $script = trim($script);
        
        $maxent_single_species_script_filename = $this->maxent_single_species_script_filename();
        
        // add lines to remove - script once it's complete
        $script .=  "\n";
        $script .=  "\n# rm {$maxent_single_species_script_filename}\n"; // remove itself after execution
        $script .=  "\n";
        
        
        file_put_contents($maxent_single_species_script_filename, $script);

        if (!file_exists($maxent_single_species_script_filename))
        {
            DBO::LogError(__METHOD__."(".__LINE__.")","Failed to Write Script for Species = {$this->SpeciesID()} maxent_single_species_script_filename = $maxent_single_species_script_filename \n");
            return null;
        }
        
        
        echo "maxent_single_species_script_filename = $maxent_single_species_script_filename\n";
        
        // echo "\n\n$script\n\n";
        
        return $maxent_single_species_script_filename;   // we have something to RUN so return the filename
        
    }

    
    private function maxent_single_species_script_filename() {
        
        $result  = 
                            configuration::CommandScriptsFolder().
                            $this->SpeciesID().
                            configuration::osPathDelimiter().
                            $this->SpeciesID().'_root'.
                            configuration::CommandScriptsSuffix();
        
        return $result ;
        
    }






    private function maxentScript()
    {
        
        if ($this->MaxentLogDone()) return ""; // if we already have the maxent data then don't run this part 
        
        $species_folder = $this->species_data_folder();
        
         $maxent = configuration::MaxentJar();
          $train = configuration::Maxent_Taining_Data_folder();
        $project = configuration::Maxent_Future_Projection_Data_folder();
        
        $output_folder  =  $this->species_output_folder(); 
        

        $MaxentResultsInsert_php  = configuration::ApplicationFolder()."Search/MaxentResultsInsert.php";
        
        $speciesInfo = SpeciesData::SpeciesQuickInformation($this->SpeciesID());
        
        
$maxent_script = <<<AAA
#!/bin/tcsh
#
# execute maxent for  ({$this->SpeciesID()})
# ==================================================================================
#
# speciesID      = {$this->SpeciesID()} 
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
# OCCUR          = {$this->OccurenceFilename()}

set MAXENT={$maxent}
set TRAINCLIMATE={$train}
set PROJECTCLIMATE={$project}
set OCCUR={$this->OccurenceFilename()}

module load java

#model the species distribution
java -mx2048m -jar {$maxent} environmentallayers={$train} samplesfile={$this->OccurenceFilename()} outputdirectory={$output_folder} -J -P -x -z redoifexists autorun
php -q '{$MaxentResultsInsert_php}' {$this->SpeciesID()}

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
    private function singleCombinationScript($combination)
    {
        
        $script_folder =  $this->species_scripts_folder();
        
        $maxent = configuration::MaxentJar();
        

        $lambdas =  configuration::Maxent_Species_Data_folder().
                    $this->SpeciesID().
                    configuration::osPathDelimiter().
                    configuration::Maxent_Species_Data_Output_Subfolder().
                    configuration::osPathDelimiter().
                    $this->SpeciesID().
                    ".lambdas";
        
        
        $proj =     configuration::Maxent_Future_Projection_Data_folder().configuration::osPathDelimiter().$combination;

        $future_projection_output = configuration::Maxent_Species_Data_folder().
                                    $this->SpeciesID().
                                    configuration::osPathDelimiter().
                                    configuration::Maxent_Species_Data_Output_Subfolder().
                                    configuration::osPathDelimiter().
                                    $combination.
                                    ".asc";

        
        $scriptFilename =   configuration::CommandScriptsFolder().
                            $this->SpeciesID().
                            configuration::osPathDelimiter().
                            $combination.
                            configuration::CommandScriptsSuffix();
        
        if (file_exists($scriptFilename)) return $scriptFilename;
        
        $script  = ""; 
        
        // don't have to do this as the main script alreayd has the data  if ParalellFuturePredictions  === FALSE
        if ($this->ParalellFuturePredictions())
        {
            $script .= "#!/bin/tcsh"; 
        
            $script .= "\n# combination              = {$combination}";
            $script .= "\n# speciesID                = {$this->SpeciesID()}";
            $script .= "\n# speciesInfo              = ".SpeciesData::SpeciesQuickInformation($this->SpeciesID());
            $script .= "\n# script_folder            = {$script_folder}";
            $script .= "\n# maxent                   = {$maxent}";
            $script .= "\n# lambdas                  = {$lambdas}";
            $script .= "\n# proj                     = {$proj}";
            $script .= "\n# future_projection_output = {$future_projection_output}";
            $script .= "\n# scriptFilename           = {$scriptFilename}";
            $script .= "\n#";

            $script .= "\nmodule load java";            
            $script .= "\ncd {$script_folder}";

        } else {
            
            $script .= "\n# speciesID .. combination = {$this->SpeciesID()} .. {$combination}";
        }


                    //java -mx2048m -cp $MAXENT   density.Project output/${SPP}.lambdas $PROJ output/`basename $PROJ`.asc fadebyclamping nowriteclampgrid nowritemess -x        
        $script .= "\njava -mx2048m -cp {$maxent} density.Project {$lambdas} {$proj} {$future_projection_output} fadebyclamping nowriteclampgrid nowritemess -x";   
        $script .= "\nphp -q ".configuration::ApplicationFolder()."Search/MaxentQuickLookInsert.php {$this->SpeciesID()} '$future_projection_output'";
        
        if ($this->ParalellFuturePredictions())        
        {
            $script .= "\nrm $scriptFilename\n";
        }
        
        $script .= "\n";
            
        
        
        // it may be usefull to have different script for each future model 
        // // as we can run then on hpc as different jobs - this will be useful wehn the resol;ution of the data is higher
        // 
        // 
        // for now return the script  and we will add it to the end of the major script 
        
        
        
        // NOT running paralell 
        if (!$this->ParalellFuturePredictions()) return $script;   // - return the actual script
            
        
        
        
        // --------------------------------------------------------------
        // create new script as we are going to run in paralell
        // --------------------------------------------------------------
        file_put_contents($scriptFilename, $script);

        if (!file_exists($scriptFilename))
        {
            DBO::LogError(__METHOD__."(".__LINE__.")","Failed to Write Script for Species = {$this->SpeciesID()}  combination = $combination scriptFilename = $scriptFilename \n");
            return null;
        }


        //echo "singleCombinationScript for {$combination} scriptFilename = $scriptFilename;\n";

        return $scriptFilename;
        
        
    }
        
    
    
    /**
     * Read database for this species and create species folder for the required species
     * and retrive Occur.csv and place in species data folder if it does not exist.
     * 
     */
    private function getOccurances()
    {
        
        $species_data_folder =  configuration::Maxent_Species_Data_folder().
                                $this->SpeciesID().
                                configuration::osPathDelimiter();

        
        file::mkdir_safe($species_data_folder);
        
        $this->OccurenceFilename($species_data_folder.configuration::Maxent_Species_Data_Occurance_Filename());
        
        
        echo "OccurenceFilename = {$this->OccurenceFilename()}\n";
        
        if (file_exists($this->OccurenceFilename())) return true;
        
        $file_written = SpeciesData::SpeciesOccuranceToFile($this->SpeciesID(),$this->OccurenceFilename()); // get occuances from database
        if (!$file_written) 
        {
            DBO::LogError(__METHOD__."(".__LINE__.")","Failed to get Occurences \n".print_r($this->SpeciesCombinations(),true));
            return null;
        }
        
        echo "OccurenceFilename Written to {$this->OccurenceFilename()}  Line Count = ".file::lineCount($this->OccurenceFilename())."\n";
        
        return true;
        
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

    
    /**
     *
     * JUst create scripts to be run and don't execute them,
     * - this will download the occurence files and create the folders but will nmot run the scripts
     * 
     * @return type 
     */
    public function GenerateOnly() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }
    
    
    
    /**
     *
     * Set to stru to have each future prediction as a sepearte QSUB job
     * 
     * @return type 
     */
    public function ParalellFuturePredictions() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }
    
    
    
    public function SpeciesID() {
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

    
    public function OccurenceFilename() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }

    
    
    
    
    private function species_scripts_folder()
    {
        return configuration::CommandScriptsFolder().$this->SpeciesID();
        
    }
    

    private function species_data_folder()
    {
        return configuration::Maxent_Species_Data_folder().$this->SpeciesID();
    }
    

    private function species_output_folder()
    {
        $output_folder  =   configuration::Maxent_Species_Data_folder().
                            $this->SpeciesID().
                            configuration::osPathDelimiter().
                            configuration::Maxent_Species_Data_Output_Subfolder();

        return $output_folder;
        
    }

    
    
    
    
    /**
     * Find maxent Log and check that the Last line states "Ending"
     * then we know that this species was processed correctly
     * 
     * @param type $speciesID
     * @return boolean 
     */
    private function MaxentLogDone()
    {
        
        $maxentLog =    configuration::Maxent_Species_Data_folder().
                        $this->SpeciesID().
                        configuration::osPathDelimiter().
                        configuration::Maxent_Species_Data_Output_Subfolder().
                        configuration::osPathDelimiter().
                        "maxent.log";
        
        echo "Checking on maxentLog = $maxentLog \n";
        
        
        if (!file_exists($maxentLog)) return false;   // if we don't have the file then it's not done yet

        $lastLogLine = exec("tail -n1 '$maxentLog'");

        if (util::contains($lastLogLine, "Ending"))  return true; // Maxent finished OK
        
        return false; // Maxtent did not finish OK
        
    }
    
    
    
}


?>
