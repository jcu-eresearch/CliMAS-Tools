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
    
    public static $OCCURANCE_MINIMUM_LINES = 3;

    public static $DisplayThresholdFieldName = "Equate entropy of thresholded and original distributions logistic threshold";
    
    
    public function __construct() {
        parent::__construct();
        $this->CommandName(__CLASS__);
        
        $this->FinderName("SpeciesFinder");
        
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
        
        $this->initialised(true);
        
        $this->buildCombinations(); 
        
        $this->GetResults();  // then populates the combinations with anything that has already been done

        $this->loadFilesystemMaxentResults();
        
        
        if ($this->ResultsComplete())
        {
            $this->ExecutionFlag(CommandAction::$EXECUTION_FLAG_COMPLETE);      // if we already have the dat - no reason to go to the server
            pgdb::CommandActionQueue($this);
            return $this->Result();   // we are done 
        }
        
        return true;
        
    }

    private function loadFilesystemMaxentResults()
    {
        
        $result = array();
        
        foreach ($this->SpeciesCombinations() as $speciesID => $combinations) 
        {
            
            $result[$speciesID] = array();
            
            // get data into datbase if we already have it - manin maxent data has been compiled            
            $db = new PGDB();        
            $db->RemoveAllMaxentResults($speciesID,true);  // Remove this line later
            $db->InsertAllMaxentResults($speciesID);       
            unset($db);            
            
            foreach ($combinations as $combination => $file_id) 
            {
                $result[$speciesID][$combination] = $file_id;
                if (is_null($file_id))
                {
                    $ascii_file_id = $this->insertMaxentProjectedFiles($speciesID, $scenario, $model, $time);    
                    
                    if (!is_null($ascii_file_id))
                        $result[$speciesID][$combination] = $new_file_id;
                    
                }
                
            }
            
            
        }
        
        $this->SpeciesCombinations($result); // 

        
        
    }
    
    
    private function msg($from,$str)
    {
        
        $logStr = "";
        
        if (is_array($str) || is_object($str))
        {
            $logStr .= datetimeutil::NowDateTime()." ".$from."\n".print_r($str,true)."\n";    
        }
        else
            $logStr .= datetimeutil::NowDateTime()." ".$from." ".$str."\n";
        
        $logStr .= str_repeat("-", 50)."\n";
        
        
        echo $logStr;
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
            $result[$this->clean_species_name($speciesID)] = array();
            foreach ($scenarios as $scenarioID)
                foreach ($models  as $modelID)
                    foreach ($times as $timeID)
                    {
                        $result[$this->clean_species_name($speciesID)]["{$scenarioID}_{$modelID}_{$timeID}"] = null;
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
        if (is_null($this->SpeciesCombinations()) ||  count($this->SpeciesCombinations()) == 0  )  $this->buildCombinations();
        
        $this->GetResults();

        $this->msg(__METHOD__,"Check after GetResults()");
        $this->msg(__METHOD__,$this->SpeciesCombinations());
        
        
        /**
         * check now to see if we have to  call the GRID
         */
        if ($this->ResultsComplete()) 
        {            
            
            $this->msg(__METHOD__,"we already have all so finish quick");
            
            $this->Status("ALL DONE");
            $this->ExecutionFlag(CommandAction::$EXECUTION_FLAG_FINALISE);
            pgdb::CommandActionQueue($this);; // update the status and interim results 
            return $this->Result();   // we are done 
        }

        //
        // We have to call the grid because at least one outyput is missing
        //

        $this->msg(__METHOD__,"Started Model Execution on GRID");
        
        $this->Status("Started Model Execution on GRID");
        pgdb::CommandActionQueue($this);
        
        
        $this->msg(__METHOD__,"pre getOccurances()");
        
        $this->getOccurances();

        $this->msg(__METHOD__,"post getOccurances()");
        
        $this->msg(__METHOD__,"pre ComputeSelectedSpecies");
        $this->ComputeSelectedSpecies();
        $this->msg(__METHOD__,"post ComputeSelectedSpecies");
        
        $this->msg(__METHOD__,"Scripts to run");
        $this->msg(__METHOD__,$this->ScriptsToRun());
        
        
        
        if (count($this->ScriptsToRun()) == 0)
        {
            $this->msg(__METHOD__,"No Scripts to run  shpuld be complete");
            
            $this->Status("Completed");  // or error ?
            $this->ExecutionFlag(CommandAction::$EXECUTION_FLAG_FINALISE);
            $this->GetResults();
            
            $this->msg(__METHOD__,"Current Result");
            $this->msg(__METHOD__,$this->Result());
            
            pgdb::CommandActionQueue($this);   // update the status and last results
            return $this->Result();
        }
        
        sleep(10); // wait for Qstat to catch up - before we start poling it

        
        $this->msg(__METHOD__,"Current QSTAT job list");
        $jobList = $this->qsubJobs(); // get current number of qsub jobs actually running now

        $this->msg(__METHOD__,$jobList);
        
        while (count($jobList) > 0)
        {
            // check on jobs and get their progress

             $this->msg(__METHOD__,"Job list count = ".count($jobList));
            
            $this->Status("Processing data on grid, waiting ".count($jobList)." jobs");
            $this->GetResults();
            pgdb::CommandActionQueue($this); // update the status and interim results

            
            sleep(10);
            $jobList = $this->qsubJobs();
            
        }

        $this->Status("All GRID jobs have been completed ");
        $this->ExecutionFlag(CommandAction::$EXECUTION_FLAG_COMPLETE);
        $this->GetResults();
        pgdb::CommandActionQueue($this); // update the status and final results

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
        
        $this->msg(__METHOD__,"qsubJobs");
        $this->msg(__METHOD__,$lines);
        
        return $lines;
    }

    
    
    
    /**
     * Read database for this species and create species folder for the required species
     * and retrive Occur.csv and place in species data folder if it does not exist.
     * 
     */
    private function getOccurances()
    {
        
        $this->msg(__METHOD__,"start - keys for species are ");
        
        $speciesIDs = array_keys($this->SpeciesCombinations());

        $this->msg(__METHOD__,"marker 1");

        
        $this->msg(__METHOD__,$speciesIDs);
        
        foreach ($speciesIDs as $speciesID) 
        {
            if (is_null($speciesID)) continue;
            
            if ($speciesID == "" ) continue;
            
            $this->msg(__METHOD__,"species data folder = ".$this->speciesDataFolder($speciesID));
            
            file::mkdir_safe($this->speciesDataFolder($speciesID));
            
            $getOccurResult = $this->getOccurancesRecordsFor($speciesID);
            
            $this->msg(__METHOD__,"getOccurResult");
            $this->msg(__METHOD__,$getOccurResult);
            
            
            if (is_null($getOccurResult)) return new Exception ("Error getting Occurence data for ".$this->clean_species_name($speciesID)."\n");
            
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
     
        $this->msg(__METHOD__,"occurFilename = $occurFilename");
        
        
        // check the validity of the file - a "good" number of lines
        if (file_exists($occurFilename))
        {
            $line_count = file::lineCount($occurFilename);
            
            $this->msg(__METHOD__,"Occur file exists line_count = $line_count");
            
            if ($line_count <= self::$OCCURANCE_MINIMUM_LINES) 
                file::Delete($occurFilename); // not big enough
        }
        
        
        if (!file_exists($occurFilename))
        {
                                      
            $this->msg(__METHOD__,"Occur file does not exists  pre get SpeciesOccuranceToFile");
            
            $file_result = $this->SpeciesOccuranceToFile($speciesID); // get occuances from database
            
            $this->msg(__METHOD__,"post SpeciesOccuranceToFile   file_result = $file_result");

            
            if (is_null($file_result)) return null; // something  wrong
            
            if (!file_exists($occurFilename)) return null; // make sure file exists


            // check the validity of the file - a "good" number of lines
            $lineCount = file::lineCount($occurFilename);
            if ($lineCount < self::$OCCURANCE_MINIMUM_LINES) 
            {
                file::Delete($occurFilename);
                return null;
            }
            
            
            $this->msg(__METHOD__," after gettoing amd checking line count lineCount = $lineCount");
            
            
        }
        
        // sanity check again the occurance file - probably has atleast N rows - ie header and data row and empty last row ?   
        if (!file_exists($occurFilename)) return null; // make sure file exists
        
        
        return true;
        
    }
    
    
    public function SpeciesOccuranceToFile($speciesID) 
    {
        
        $occurFilename = $this->species_occurence_filename($speciesID);
        
        $this->msg(__METHOD__," occurFilename = $occurFilename");
        
        $result = pgdb::SpeciesOccurance(str_replace("_", " ", $speciesID));
        
        $this->msg(__METHOD__,"post pgdb::SpeciesOccurance");
        //$this->msg(__METHOD__,$result);
        
        if (count($result) < self::$OCCURANCE_MINIMUM_LINES) return null;
        
        
        // this will create occurance file wqhere the "name" of the species will be the Species ID from database
        // thise seems to have to match the Lambdas filename
        
        $file = '"SPPCODE","LATDEC","LONGDEC"'."\n";  // headers specific to Maxent JAR
        foreach ($result as $row) 
            $file .= $this->clean_species_name($speciesID).",".$row['latitude'].",".$row['longitude']."\n";
        
        
        file_put_contents($occurFilename, $file);
        
        if (!file_exists($occurFilename)) return null;
        
        if (file::lineCount($occurFilename) < self::$OCCURANCE_MINIMUM_LINES) return null;

        $this->msg(__METHOD__,"Check contents of occurFilename = $occurFilename");
        
        
        unset($result);
        unset($file);
        
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
            
            $speciesID = trim($speciesID);
            if ($speciesID == "") continue;
            
            if (!$this->MaxentLogDone($speciesID)) 
            {
                $complete = false;
                
                // since the maxent log does not exiosts we want to 
                // just copy the current results over
                foreach ($combinations as $combination => $file_id) 
                    $result[$this->clean_species_name($speciesID)][$combination] = $file_id;
                
                continue;      
                // don't check outputs when we don't have a complete maxent log
            }
            
            
            foreach ($combinations as $combination => $file_id) 
            {
                
                $result[$this->clean_species_name($speciesID)][$combination] = $file_id; // copy current value
                
                if (is_null($file_id))
                {
                    list($scenario, $model, $time) = explode("_",$combination);    
                    
                    // we don't know if we have database data for this comnination so go acheck
                    $file_id = $this->GetModelledData($speciesID, $scenario, $model, $time);
                    
                    
                    if (is_null($file_id))
                    {
                        // if Id is still NULL check the filesystem for results
                        // check filesystem to see if we have that file already - if so add this to database and set file_id
                        
                        
                    }
                    
                    $result[$this->clean_species_name($speciesID)][$combination] = $file_id;
                    
                    if (is_null($file_id)) $complete = false;
                    
                }
                
                if (!is_null($file_id)) $done_count++; // count what ahs been done
                
            }
                        
        }
        
        $this->SpeciesCombinations($result);

        $this->Result($result);
        
        $this->ResultsComplete($complete);
        
        $this->ResultsDoneCountString($done_count);
        
        $this->Status("Items processed ".$this->ResultsDoneCountString()." of ".$this->ResultsFullCountString() );
        
    }
    

    private function insertMaxentProjectedFiles($speciesID, $scenario, $model, $time)
    {
        
        $pfn = $this->species_output_projection_filename($speciesID, $scenario, $model, $time);

        echo "checking for pfn $pfn\n"; // - ascii grid output                        

        $combination = "{$scenario}_{$model}_{$time}";
        
        $file_id = null;
        
        if (file_exists($pfn))
        {
            
            $file_id = $this->GetModelledData($speciesID, $scenario, $model, $time);
            
            $db = new PGDB();

            echo "pushing ascii grid into database $pfn\n";                            
            $file_id = $db->InsertSingleMaxentOutput($speciesID, $pfn, "Grid_".$combination, $scenario, $model, $time);
            unset($db);
        }
        
        $quicklook_image_filename = self::CreateQuickLookImage($species_id,$pfn);
        
        if (file_exists($quicklook_image_filename))
        {
            echo "pushing quick look into database $quicklook_image_filename\n";                            
            
            $ql_file_id = $db->InsertSingleMaxentOutput($species_id,$quicklook_image_filename,"QuickLook_".$combination,$scenario, $model, $time);    
        }
        
        return $file_id; // nly need to retuyrn ascii grid file id
        
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
        
        $maxentLog = $this->maxentLogFilename($speciesID);
        
        if (!file_exists($maxentLog)) return false;

        $lastLogLine = exec("tail -n1 '$maxentLog'");

        if (util::contains($lastLogLine, "Ending"))  return true; // Maxent finished OK
        
        return false; // Maxtent did not finish OK
        
    }

    private function maxentLogFilename($speciesID)
    {
        
        $maxentLog =    configuration::Maxent_Species_Data_folder().
                        $this->clean_species_name($speciesID).
                        configuration::osPathDelimiter().
                        configuration::Maxent_Species_Data_Output_Subfolder().
                        configuration::osPathDelimiter().
                        "maxent.log";
        
        return $maxentLog;
        
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
        
        $this->msg(__METHOD__,"Pre ");
        
        
        $this->writeScriptFiles();  // array of script path names
        
        if (is_null($this->ScriptsToRun())) return null;
        
        $this->msg(__METHOD__,"ScriptsToRun()");
        $this->msg(__METHOD__,$this->ScriptsToRun());
        
        
        foreach ($this->ScriptsToRun() as $scriptname) 
        {
            if (!is_null($scriptname))
            {
                $cmd = "qsub -N{$this->QsubCollectionID()} '{$scriptname}'";
                
                $this->msg(__METHOD__,"exec $cmd");
                
                exec($cmd);  /// QSUB JOBS Submitted here
            }
        }
        
        $this->msg(__METHOD__,"post ");
        
    }
    
    
    /*
     * Script file used to run maxent.jar and all projectections state in combinations
     * 
     */
    private function writeScriptFiles()
    {
        
        // for each species we need to run all the combinations (or check to see if thay have been run)
        $this->msg(__METHOD__,"pre ");
        
        $scripts = array();
        foreach ($this->SpeciesCombinations() as $speciesID => $combinations)
        {
            $singleScript = $this->writeMaxentSingleSpeciesProjectionScriptFile($speciesID,$combinations); // all combinations for a single species
            
            $this->msg(__METHOD__,"singleScript = [$singleScript] ");
            
            if ($singleScript != "") 
            {
                
                $scripts[$speciesID] = $singleScript;
            }
                
            
        }

        $this->ScriptsToRun($scripts);
        
        $this->msg(__METHOD__,"post ");

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
        
        $this->msg(__METHOD__,"pre");
        
        
        file::mkdir_safe($this->species_scripts_folder($speciesID));

        file::mkdir_safe($this->species_data_folder($speciesID));

        file::mkdir_safe($this->species_output_folder($speciesID));
        
        $this->msg(__METHOD__,"this->species_scripts_folder(speciesID) ".$this->species_scripts_folder($speciesID));
        $this->msg(__METHOD__,"this->species_data_folder(speciesID) ".$this->species_data_folder($speciesID));
        $this->msg(__METHOD__,"this->species_output_folder(speciesID) ".$this->species_output_folder($speciesID));
        
        $maxent_single_species_script_filename = $this->maxent_single_species_script_filename($speciesID);
        
        $this->msg(__METHOD__,"$maxent_single_species_script_filename ".$maxent_single_species_script_filename);
        

        // create combinration script files here
        
        $script = "";
        $script .= $this->maxentScript($speciesID);
        
        foreach ($this->futureProjectionScripts($speciesID,$combinations) as $singleFutureScriptFilename) 
        {
            $script .= "\nqsub -N{$this->QsubCollectionID()} '{$singleFutureScriptFilename}'";          // this is the line need to qsub this script
        }
        
        $script = trim($script);
        if ($script == "") null;
        
        // TODO for testing uncomment this line
        $script .=  "\n";
        $script .= "\n#rm {$maxent_single_species_script_filename}\n"; // remove itself after execution
        $script .=  "\n";
        
        
        $this->msg(__METHOD__,"Maxent Script is");
        $this->msg(__METHOD__,$script);
        
        
        file_put_contents($maxent_single_species_script_filename, $script);
        
        if (!file_exists($maxent_single_species_script_filename))  return null;
        
        $this->msg(__METHOD__,"return {$maxent_single_species_script_filename}");
        
        return $maxent_single_species_script_filename;   // we have something to RUN so return the filename
        
    }

    
    private function species_scripts_folder($speciesID)
    {
        $scripts_folder =  configuration::CommandScriptsFolder().
                           $this->clean_species_name($speciesID).
                           configuration::osPathDelimiter();

        return $scripts_folder;
        
    }
    

    private function species_data_folder($speciesID)
    {
        $data_folder    =   configuration::Maxent_Species_Data_folder().
                            $this->clean_species_name($speciesID).
                            configuration::osPathDelimiter();

        return $data_folder;
        
    }
    

    private function species_output_folder($speciesID)
    {
        $output_folder  =   configuration::Maxent_Species_Data_folder().
                            $this->clean_species_name($speciesID).
                            configuration::osPathDelimiter().
                            configuration::Maxent_Species_Data_Output_Subfolder();

        return $output_folder;
        
    }

    private function species_output_projection_filename($speciesID, $scenario, $model, $time)
    {
        $output_file    =   configuration::Maxent_Species_Data_folder().
                            $this->clean_species_name($speciesID).
                            configuration::osPathDelimiter().
                            configuration::Maxent_Species_Data_Output_Subfolder().
                            configuration::osPathDelimiter().
                            "{$scenario}_{$model}_{$time}".'.asc';

        return $output_file;
        
    }
    
    
    private function maxent_single_species_script_filename($speciesID)
    {
        $scriptFilename =   configuration::CommandScriptsFolder().
                            $this->clean_species_name($speciesID).
                            configuration::osPathDelimiter().
                            $this->clean_species_name($speciesID).'_root'.
                            configuration::CommandScriptsSuffix();

        return $scriptFilename;
        
    }
    
    
    private function maxentScript($speciesID)
    {
        
        if ($this->MaxentLogDone($speciesID)) return ""; // if we already have the maxent data then don't run this part 
        
        $clean_species_name = str_replace(" ", "_", $speciesID);        
        
        $species_folder = $this->species_data_folder($speciesID);
        
        
         $maxent = configuration::MaxentJar();
          $train = configuration::Maxent_Taining_Data_folder();
        $project = configuration::Maxent_Future_Projection_Data_folder();
          $occur = configuration::Maxent_Species_Data_Occurance_Filename();
        
        $output_folder  =  $this->species_output_folder($speciesID);
        
        $occur =  $this->species_occurence_filename($speciesID);

        $MaxentResultsInsert_php  = configuration::ApplicationFolder()."MaxentResultsInsert_php.php";
        
$maxent_script = <<<AAA
#!/bin/tcsh
#
# execute maxent for  ({$clean_species_name})
# ==================================================================================
#
# speciesID      = {$speciesID} 
# Scenarios      = {$this->EmissionScenarioIDs()}
# Models         = {$this->ClimateModelIDs()} 
# Times          = {$this->TimeIDs()}
# Species Folder = {$species_folder}
# file locations
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
php -q '{$MaxentResultsInsert_php}' '$clean_species_name'

AAA;
            return $maxent_script;


    }
    
    
    private function futureProjectionScripts($speciesID,$combinations)
    {
        
        //$this->GetResults(); // make sure we only create for what we don't have
        
        $future_projection_scripts = array();
        foreach ($combinations as $combination => $file_id)
        {
            if (!is_null($file_id)) continue;  // we already have this combination

            $this->msg(__METHOD__," getting future_projection_scripts[$combination]");
            
            $future_projection_scripts[$combination] = $this->singleCombinationScript($speciesID,$combination); // script that will be used to execute just a single combo

        }

            
        return $future_projection_scripts;
            
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

        $clean_species_name = str_replace(" ", "_", $speciesID);
        
        $script_folder =    $this->species_scripts_folder($speciesID);
        
        $maxent = configuration::MaxentJar();
        
        $lambdas =  $this->species_lambdas($speciesID);

        $proj =     configuration::Maxent_Future_Projection_Data_folder().
                    configuration::osPathDelimiter().
                    $combination;
        

        $future_projection_output = configuration::Maxent_Species_Data_folder().
                                    $clean_species_name.
                                    configuration::osPathDelimiter().
                                    configuration::Maxent_Species_Data_Output_Subfolder().
                                    configuration::osPathDelimiter().
                                    $combination.
                                    ".asc";

        
        $scriptFilename =   configuration::CommandScriptsFolder().
                            $clean_species_name.
                            configuration::osPathDelimiter().
                            $this->ID().'_'.$combination.
                            configuration::CommandScriptsSuffix();
        
        $script  = "#!/bin/tcsh"; 
        $script .= "\n#combination              = {$combination}";
        $script .= "\n#clean_species_name       = {$clean_species_name}";
        $script .= "\n#script_folder            = {$script_folder}";
        $script .= "\n#maxent                   = {$maxent}";
        $script .= "\n#lambdas                  = {$lambdas}";
        $script .= "\n#proj                     = {$proj}";
        $script .= "\n#future_projection_output = {$future_projection_output}";
        $script .= "\n#scriptFilename           = {$scriptFilename}";
        $script .= "\n#";
        
        $script .= "\nmodule load java";
        $script .= "\ncd {$script_folder}";

                    //java -mx2048m -cp $MAXENT   density.Project output/${SPP}.lambdas $PROJ output/`basename $PROJ`.asc fadebyclamping nowriteclampgrid nowritemess -x        
        $script .= "\njava -mx2048m -cp {$maxent} density.Project {$lambdas} {$proj} {$future_projection_output} fadebyclamping nowriteclampgrid nowritemess -x";   
        $script .= "\nphp -q ".configuration::ApplicationFolder()."Search/MaxentQuickLookInsert.php '$clean_species_name' '$future_projection_output'";
        $script .= "\n";
        
        
        // here uncomment to make script  remove itself after running
        // $script .= "\nrm $scriptFilename";
        
            
        file_put_contents($scriptFilename, $script);
        
        
        return $scriptFilename;
        
    }
    
    
    private function species_lambdas($speciesID) {
        
        $lambdas =  configuration::Maxent_Species_Data_folder().
                    $this->clean_species_name($speciesID).
                    configuration::osPathDelimiter().
                    configuration::Maxent_Species_Data_Output_Subfolder().
                    configuration::osPathDelimiter().
                    $this->clean_species_name($speciesID).
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
                            $this->clean_species_name($speciesID).
                            configuration::osPathDelimiter().
                            configuration::Maxent_Species_Data_Occurance_Filename();
        
        return $occurFilename;
        
    }

    
    
    private function speciesDataFolder($speciesID)
    {
        
        $species_data_folder =  configuration::Maxent_Species_Data_folder().
                                $this->clean_species_name($speciesID).
                                configuration::osPathDelimiter();
        
        return $species_data_folder;
        
    }

    private function clean_species_name($speciesID)
    {
        return str_replace(" ", "_", $speciesID);
    }
    
    
    
    /**
    *
    * @param type $src_grid_filename      - Maxent ASC Grid with filename in format of      (Scenario)_(model)_(time).asc
    * @param type $output_image_filename  - Where you want to output mage to end up 
    * @param type $low_threshold          - display value start from   - to be read from maxentResults.csv - "Equate entropy of thresholded and original distributions logistic threshold"
    * @param type $transparency           - transparency of all colors 
    * @param type $background_colour      - background colour   use 0 0 0 255 = Full balck   0 0 0 0 = Full Transparent
    * @return null|String                 - Output filename 
    */
    public static function CreateQuickLookImage($species_id,$src_grid_filename,$output_image_filename = null ,$low_threshold = null,$transparency = 255,$background_colour = "0 0 0 255")
    {
        
        if (is_null($output_image_filename)) $output_image_filename = str_replace("asc","png",$output_image_filename);
        
        if (file_exists($output_image_filename)) return $output_image_filename;
        
        list($scenario, $model, $time) =  explode("_",str_replace('.asc','',basename($src_grid_filename)));    
        
        $stats = spatial_util::RasterStatisticsPrecision($src_grid_filename);    
        
        $ramp = RGB::Ramp(0, 1, 10, RGB::GradientYellowOrangeRed());    

        $colour_txt = file::random_filename().".txt"; // list of colours to use - will bne generated
        file::Delete($colour_txt);

        $colour_png = file::random_filename().".png"; // colourized ASC file as a png
        file::Delete($colour_png);

        $colour_zero_txt = file::random_filename().".txt"; // used to create black background
        file::Delete($colour_zero_txt);

        $colour_background_png = file::random_filename().".png"; // background image
        file::Delete($colour_background_png);

        $colour_combined_png = file::random_filename().".png"; // background + coloured image 
        file::Delete($colour_combined_png);

        $colour_legend_png = file::random_filename().".png"; // legend  image together
        file::Delete($colour_legend_png);

        if (is_null($output_image_filename))
            $output_image_filename = file::random_filename().".png"; // return image filename
        
        

    //    echo "\ncolour_txt = ".$colour_txt ;
    //    echo "\ncolour_png = ".$colour_png ;
    //    echo "\ncolour_zero_txt = ".$colour_zero_txt ;
    //    echo "\ncolour_background_png = ".$colour_background_png ;
    //    echo "\ncolour_combined_png = ".$colour_combined_png ;
    //    echo "\ncolour_legend_png = ".$colour_legend_png ;


        $indexes = array_keys($ramp);
        if (is_null($low_threshold)) 
        {
            // try to get value from  Maxent Results self::$DisplayThresholdFieldName
            
            $low_threshold = $indexes[1]; // if they did not pass low end threshold then just show everything above lowest value    
            
            $db = new PGDB();
            $maxent_threshold = $db->GetMaxentResult($species_id,self::$DisplayThresholdFieldName);
            
            echo "\nmaxent_threshold = $maxent_threshold\n";
            
            exit();
            
            if (!is_null($maxent_threshold)) 
            {
                echo "\nlow_threshold set by lookup to maxent results = $low_threshold\n";
                $low_threshold = $maxent_threshold;
            }
                
            unset($db);
            
        }
            

        echo "\nlow_threshold = $low_threshold\n";


        // create colour "lookup table"    
        $value_count = 0; // used later to know how tall the legend will be

        $color_table = "nv 0 0 0 0\n";  // no value
        $count = 0;
        foreach ($ramp as $index => $rgb) 
        {
            $rgb instanceof RGB;
            if ($index < $low_threshold)
            {
                $color_table .= $count."% 0 0 0 0\n";
            }
            else
            {
                $color_table .= $count."% ".$rgb->Red()." ".$rgb->Green()." ".$rgb->Blue()." {$transparency}\n";    
                $value_count++;
            }
            $count++;
        }

        //echo "\nramp\n";
        //print_r($ramp);


        // save the colour lookup table 
        file_put_contents($colour_txt, $color_table);
        $cmd = "gdaldem  color-relief {$src_grid_filename} {$colour_txt} -nearest_color_entry -alpha -of PNG {$colour_png}";

        //echo "$cmd\n";

        exec($cmd);  // generate a coloured image using colour lookup 



        // create backgound to put coloured image on top of
        file_put_contents($colour_zero_txt, "nv 0 0 0 0\n0% {$background_colour}\n100% {$background_colour}\n"); // default is ALL Values = $background_colour  & No Value  = transparent  

        $cmd = "gdaldem  color-relief {$src_grid_filename} $colour_zero_txt -nearest_color_entry -alpha -of PNG {$colour_background_png}";

        //echo "$cmd\n";

        exec($cmd);


        // order here is important first is lowest
        $cmd = "convert {$colour_background_png} {$colour_png} -layers flatten {$colour_combined_png}";
        //echo "$cmd\n";
        exec($cmd);


        // create a legend image
        // # rectangle left,top right,bottom" \


        $swatch_height = 20;
        $swatch_width = 20;

        $swatch_width_padding = 10;

        $text_align_up = -2;

        list($width, $height, $type, $attr) = getimagesize($colour_png);     

        $height = $value_count * $swatch_height + (2 * $swatch_height);  // heioght of the legend is a cal of the number of legend items


        $box_top = 20;
        $box_left = 20;


        $legend  = "convert -size  {$width}x{$height} xc:white ";
        $legend .= "-font DejaVu-Sans-Book ";

        foreach (array_reverse($ramp, true) as $index => $rgb) 
        {
            $rgb instanceof RGB;

            if ($index >= $low_threshold)
            {
                $box_right = $box_left + $swatch_width;
                $box_bottom = $box_top + $swatch_height;

                $text_left = $box_left + $swatch_width + $swatch_width_padding;
                $text_top  = $box_top + $swatch_height + $text_align_up;

                $text = $index;

                $legend .= "-fill '#{$rgb->asHex()}' -draw 'rectangle {$box_left},{$box_top} {$box_right},{$box_bottom}' ";
                $legend .= "-fill black -draw 'text {$text_left},{$text_top} \"{$text}\"' ";

                $box_top += $swatch_height;
            }

        }


        // - add parameters  to the image  $scenario, $model, $time

        $param_text_left = $width / 2;
        $param_text_top = 20; 

        $legend .= "-fill black -draw 'text {$param_text_left},{$param_text_top} \"Scenario: {$scenario}\"' ";

        $param_text_top += 20;
        $legend .= "-fill black -draw 'text {$param_text_left},{$param_text_top} \"Model: {$model}\"' ";

        $param_text_top += 20;
        $legend .= "-fill black -draw 'text {$param_text_left},{$param_text_top} \"Time: {$time}\"' ";

        $legend .= " {$colour_legend_png}";


        //echo "$legend\n";

        exec($legend); // create legend


        $cmd = "convert {$colour_combined_png} {$colour_legend_png} -append {$output_image_filename}";
        //echo "$cmd\n";
        exec($cmd);

        // might be better here to convert to a tmp image
        // and then copy back to the $output_image_filename
        

        file::Delete($colour_txt);
        file::Delete($colour_png);
        file::Delete($colour_zero_txt);
        file::Delete($colour_background_png);
        file::Delete($colour_combined_png);
        file::Delete($colour_legend_png);

        if (!file_exists($output_image_filename)) return null;

        return $output_image_filename; // filename of png that can be used - 

    }

    
    public function GetModelledData($species,$scenario, $model, $time)
    {
        // $scenario, $model, $time
        $db = new PGDB();
        $file_id = $db->GetModelledData($species, $scenario, $model, $time);
        unset($db);
        
        return $file_id;
    }
    
}


?>
