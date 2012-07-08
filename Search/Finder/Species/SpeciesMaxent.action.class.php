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
     */
    public function initialise($_post = null) 
    {
        
        if (is_null($_post)) return null;
        
        $this->SpeciesIDs($_post['species']);
        $this->EmissionScenarioIDs($_post['scenario']);
        $this->ClimateModelIDs($_post['model']);
        $this->TimeIDs($_post['time']);
        
        $this->SpeciesIDs(trim($this->SpeciesIDs()));
        $this->EmissionScenarioIDs(trim($this->EmissionScenarioIDs()));
        $this->ClimateModelIDs(trim($this->ClimateModelIDs()));
        $this->TimeIDs(trim($this->TimeIDs()));
        
        $this->initialised(true);
        
        // check to see if we really need to go to the GRID
        $this->SpeciesCombinations($this->buildCombinations());
        
        $this->Result($this->progessResults());
        $alreadyCompleted = $this->progressResultsComplete();

        // if we already have the dat - no reason to go to the server
        if ($alreadyCompleted)
        {
            $this->ExecutionFlag(CommandAction::$EXECUTION_FLAG_COMPLETE);    
        }
        
        
        return print_r($this->SpeciesCombinations(),true);
        
        
    }

    private function buildCombinations()
    {
        $species   = explode(" ",$this->SpeciesIDs());
        $scenarios = explode(" ",$this->EmissionScenarioIDs());
        $models    = explode(" ",$this->ClimateModelIDs());
        $times     = explode(" ",$this->TimeIDs());

        $os_path = configuration::osPathDelimiter();
        $os_dot = configuration::osExtensionDelimiter();
        
        
        $result = array();
        foreach ($species as $speciesID)
        {
            $speciesID =  str_replace("_", " ", $speciesID);
            
            $result[$speciesID] = array();
            foreach ($scenarios as $scenarioID)
                foreach ($models  as $modelID)
                    foreach ($times as $timeID)
                    {
                
                        $combo = "{$scenarioID}_{$modelID}_{$timeID}";
                        $future_projection_output = "{$speciesID}{$os_path}".configuration::Maxent_Species_Data_Output_Subfolder()."{$os_path}{$combo}{$os_dot}asc";
                        $result[$speciesID][$combo] = $future_projection_output;
                        
                    }
                        
        }

        return $result;
        
    }
    
    
    
    /**
     * Runnning on GRID 
     * - Look at initialiaseation variables 
     * - get Species occurances for any species that we don't have
     * - get current progress, if other process have already done parts of this call.
     * - launch other jobs thru QSUB that will be able to run paralell jobs for maxent i.e. Future Projections
     * 
     * @return type 
     */
    public function Execute()
    {
        
        $this->SpeciesCombinations($this->buildCombinations());
        
        $this->getOccurances();
        
        $this->Result($this->progessResults());   // this will check to see if the outputs exists  
        CommandUtil::PutCommand($this); // post any results we already have

        //  make sure we have the occurance data for species   $this->SpeciesCombinations  gives use the list of species to look for
        
        
        // has this alredy been done ?
        $alreadyCompleted = $this->progressResultsComplete();
        
        if ($alreadyCompleted) 
        {            
            $this->Status("ALL DONE");
            $this->ExecutionFlag(CommandAction::$EXECUTION_FLAG_FINALISE);
            CommandUtil::PutCommand($this); // update the status and interim results            
            return $this->Result();   // we are done 
        }
        
        // check to see if we have the data they asked for ??
        
        CommandUtil::QueueUpdateStatus($this, "Started" ); 
        
        $this->QsubCollectionID(substr(uniqid(),0,13)); // setup unique ID so we can track all jobs asscoiated with this call
        
        $jobsCount = $this->ComputeSelectedSpecies();
        
        if ($jobsCount > 0)        
        {
            sleep(10);

            $jobList = $this->qsubJobs();

            $this->QsubJobCount(count($jobList));

            while (count($jobList) > 0)
            {
                // check the status of each job and stop if they are all C 

                // with this we can also set the resuklt to 
                // the list of acs files we have just generated

                // progessResults()

                $this->Status("We have ".count($jobList)."of ".$this->QsubJobCount()." sub jobs left ");
                $this->Result($this->progessResults());
                CommandUtil::PutCommand($this); // update the status and interim results

                sleep(5);
                $jobList = $this->qsubJobs();

            }
            
            
        }
        
        
        $this->Result($this->progessResults());
        CommandUtil::PutCommand($this); // update the status and interim results

        return $this->Result();

    }
    
    
    /**
     * Read database for this species and create species folder for the required species
     * and retrive Occur.csv and place in species data folder if it does not exist.
     * 
     */
    private function getOccurances()
    {
        
        $os_path = configuration::osPathDelimiter();        
        
        foreach (array_keys($this->SpeciesCombinations()) as $speciesID) 
        {
            
            if (! (is_null($speciesID) || $speciesID == ""))
            {
                file::mkdir_safe(configuration::Maxent_Species_Data_folder().$speciesID);                
                $occurFilename =  configuration::Maxent_Species_Data_folder()."{$speciesID}{$os_path}".configuration::Maxent_Species_Data_Occurance_Filename();
                
                if (!file_exists($occurFilename))  // check that this exists if not then - get data from Database 
                    $this->getOccurancesRecordsFor($speciesID,$occurFilename);
            }
            
        }
        
        // at this point all Occurrances files will exist (ie for each species)
        
    }

    
    
    /**
     *
     * @param type $speciesID - Species Scientific Name
     * @param type $occurFilename - Where to write datafrom database
     * @return boolean 
     */
    private function getOccurancesRecordsFor($speciesID,$occurFilename)
    {
        
        CommandUtil::QueueUpdateStatus($this, "Getting Species Occourance data from Datasource for ".  urldecode($speciesID)); 
        
        $ok = true;
        if (!file_exists($occurFilename))
        {
            $ok = SpeciesData::SpeciesOccuranceToFile($speciesID, $occurFilename); // get occuances from database
            
            if (!$ok) return FALSE; // something  wrong
            
            if (!file_exists($occurFilename)) return FALSE; // make sure file exists
        }

        
        // sanity check agains the occurance file - probably has atleast 3 rows - ie header and data row and empty last row ?   
        $lineCount = file::lineCount($occurFilename);
        if ($lineCount < self::$OCCURANCE_MINIMUM_LINES) return FALSE;;
        
    }
    
    
    
    /**
     * At any one time this will have the current results available 
     * - it will be a list of ASC grid filenames that can be displayed on the web server
     * 
     * @return type 
     */
    private function progessResults()
    {
        
        $result = array();
        foreach ($this->SpeciesCombinations() as $speciesID => $combinations) 
        {
            
            // check for this species that the HTML file is greater than 0
            // if not then 
            //$result[$speciesID]["current"] = ""

            $htmlFile = $speciesID.configuration::osPathDelimiter().configuration::Maxent_Species_Data_Output_Subfolder().configuration::osPathDelimiter().$speciesID.".html";
            
            $htmlFromMaxentLocal = configuration::Maxent_Species_Data_folder().$htmlFile;
            
            if (!file_exists($htmlFromMaxentLocal))
            {
                $result[$speciesID]['Current Condition'] = "Waiting to start computing ";
            }
            else
            {
                if (filesize($htmlFromMaxentLocal) == 0)
                {
                    $result[$speciesID]['Current Condition'] = "Computing ..... ";
                }
                else
                {
                    $result[$speciesID]['Current Condition'] = configuration::osPathDelimiter()."{$htmlFile}";
                }
                
            }
            
            
            foreach ($combinations as $combination  => $outputFilename) 
            {
                // check to see if the file for this species combination exists
                // on hpc check to see if the file exists 
        
                // $outputfile only points to the path that is the same on both sides
                
                $result[$speciesID][$combination] = ""; // empty mean it does not exist
                if (file_exists(configuration::Maxent_Species_Data_folder().$outputFilename))
                {                    
                    $result[$speciesID][$combination] = configuration::osPathDelimiter().$outputFilename;
                }
            }
            
        }
        
        return $result;
        
    }
    

    /** 
     * Test to see if we have a full complement ie all data we requested exists now.
     * @return bool 
     */
    private function progressResultsComplete()
    {
        
        foreach ($this->SpeciesCombinations() as $speciesID => $combinations) 
        {
            
            if (!$this->maxentLogDone($speciesID)) return false;
            
            foreach ($combinations as $comboFilename) 
            {
                if (!file_exists(configuration::Maxent_Species_Data_folder().$comboFilename))  // $localComboFilename
                    return false;
            
            }
            
        }
            
        return true;
        
    }
    
    
    /**
     * Find maxent Log and check that the Last line states "Ending"
     * then we know that this species was processed correctly
     * 
     * @param type $speciesID
     * @return boolean 
     */
    private function maxentLogDone($speciesID)
    {
        
        $os_path = configuration::osPathDelimiter();
        
        $output_subfolder = configuration::Maxent_Species_Data_Output_Subfolder();
        
        $maxentLog = configuration::Maxent_Species_Data_folder()."{$speciesID}{$os_path}{$output_subfolder}{$os_path}maxent.log";
        
        
        if (!file_exists($maxentLog)) return false;

        $lastLogLine = exec("tail -n1 '$maxentLog'");

        if (util::contains($lastLogLine, "Ending"))  return true; // Maxent finished OK
        
        return false; // Maxtent did not finish OK
        
    }
    
    
    /**
     * 
     * @return array QSTAT output for all jobs with the same CollectionID 
     * 
     */
    private function qsubJobs()
    {
        $lines = array();
        exec("qstat | grep {$this->QsubCollectionID()}",$lines);
        return $lines;
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
        
        $jobCount = 0;
        
        $scriptsToRun = $this->writeMaxentSpeciesProjectionScriptFile(); 
        
        if (count($scriptsToRun) > 0)
        {
            CommandUtil::QueueUpdateStatus($this, "Executing All Jobs");
            foreach ($scriptsToRun as $scriptname) 
                exec("qsub -N{$this->QsubCollectionID()} {$scriptname}");  
            
            $jobCount = $scriptsToRun;
            
        }
        else
            CommandUtil::QueueUpdateStatus($this, "Nothing to do - data already exists");    
        
        return $jobCount;
        
    }

    
    
    /*
     * Script file used to run maxent.jar and all projectections state in combinations
     * 
     */
    private function writeMaxentSpeciesProjectionScriptFile()
    {
        
        // for each species we need to run all the combinations (or check to see if thay have been run)
        $scripts = array();
        foreach ($this->SpeciesCombinations() as $speciesID => $combinations)
        {
            file::mkdir_safe(configuration::CommandScriptsFolder().$speciesID);
            
            $singleScript = self::writeMaxentSingleSpeciesProjectionScriptFile($speciesID,$combinations); // all combinations for a single species
            
            if ($singleScript != "") 
                $scripts[$speciesID] = $singleScript;
        }

        return $scripts;

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

        $scriptFilename = configuration::CommandScriptsFolder().$speciesID.configuration::osPathDelimiter().$speciesID.'_root'.configuration::CommandScriptsSuffix();
        
        $maxent = configuration::MaxentJar();
        
        $train = configuration::Maxent_Taining_Data_folder();
        $project = configuration::Maxent_Future_Projection_Data_folder();
        $data_folder = configuration::Maxent_Species_Data_folder();

        $output_subfolder = configuration::Maxent_Species_Data_Output_Subfolder();
        $occur = configuration::Maxent_Species_Data_Occurance_Filename();

        $os_path = configuration::osPathDelimiter();

        $species_folder = "{$data_folder}{$speciesID}{$os_path}";
        $output_folder  = "{$data_folder}{$speciesID}{$os_path}{$output_subfolder}";

        $occur =  "{$data_folder}{$speciesID}{$os_path}{$occur}";
        
        
$maxent_script = <<<AAA
#!/bin/tcsh
# speciesID = {$speciesID}
# Scenarios = {$this->EmissionScenarioIDs()}
# Models    = {$this->ClimateModelIDs()} 
# Times     = {$this->TimeIDs()}
#        
if (! -e "{$species_folder}" ) then  
  echo "Folder for {$speciesID} does not exist  ({$species_folder})"
  exit
endif

#define file locations
set MAXENT={$maxent}
set TRAINCLIMATE={$train}
set PROJECTCLIMATE={$project}
set OCCUR={$occur}

#load the java module for the HPC
module load java

#make an output directory
echo Make output folder {$output_folder}
if (! -e "{$output_folder}" ) then
  mkdir {$output_folder}
endif

echo execute model for {$speciesID}

#model the species distribution
java -mx2048m -jar {$maxent} environmentallayers={$train} samplesfile={$occur} outputdirectory={$output_folder} -J -P -x -z redoifexists autorun

echo execute future projections


AAA;


        // create combinration script files here

        $future_projection_scripts = "";

        echo "loop thru combos to create for $speciesID\n";

        
        foreach ($combinations as $combination => $comboFilename)
        {
            
            echo "COMBO   $combination = $comboFilename\n";
            
            // here we need to check to see if the output already exists
            // if it does we don't need to add this to the JOB
            
            
            $localComboFilename = configuration::Maxent_Species_Data_folder().$comboFilename;
            
            echo "does output exist localComboFilename  ".$localComboFilename."\n";
            
            if (!file_exists($localComboFilename))  // only create script if we don't have the file 
            {

                 echo "NO output exist  ".$localComboFilename."\n";
                
                $ss = $this->singleCombinationScript($speciesID,$combination,$localComboFilename); // script that will be used to execute just a single combo

                if ($ss != "")
                {
                    $ssFN = file::random_filename(configuration::CommandScriptsFolder()."/{$speciesID}/").configuration::CommandScriptsSuffix() ;
                    $ssFN = str_replace("//","/",$ssFN);

                    file_put_contents($ssFN, $ss);

                    exec("chmod u+x '$ssFN'");            

                    $subJobName  = "{$this->QsubCollectionID()}s";

                    $future_projection_scripts .= "qsub -N{$subJobName} $ssFN\n";

                }
                
            }
            
        }

        
        $includeMaxent = true;
        
        // check to see if we need to run "Maxent"
        $maxentLog = "{$output_folder}{$os_path}maxent.log";
            
        
        echo "maxentLog = $maxentLog\n";
        
        if (file_exists($maxentLog))
        {
            $lastLogLine = exec("tail -n1 '$maxentLog'");
            
            echo "lastLogLine = $lastLogLine\n";
            
            if (util::contains($lastLogLine, "Ending")) 
                    $includeMaxent = false;
        }


        $script = "";
        if ($includeMaxent) $script .= $maxent_script;
        if ($future_projection_scripts != "")  $script .= $future_projection_scripts;  // here check to see if the future predectiosn are there as well.

        
        $script = trim($script);
        if ($script != "")
        {
            file_put_contents($scriptFilename, $script);
            // exec("chmod u+x '{$scriptFilename}'");
            
            return $scriptFilename;   // we have something to RUN so return the filename
            
        }

        
        return null;
    }


    /**
     * For a single species for a single combination create script that can be submited to QSUB
     * 
     * @param type $speciesID
     * @param type $combination
     * @param type $localComboFilename
     * @return string 
     */
    private function singleCombinationScript($speciesID,$combination,$localComboFilename)
    {

        $os_path = configuration::osPathDelimiter();
        $os_dot = configuration::osExtensionDelimiter();
        
        $maxent = configuration::MaxentJar();
        
        $project = configuration::Maxent_Future_Projection_Data_folder();
        $data_folder = configuration::Maxent_Species_Data_folder();

        $output_subfolder = configuration::Maxent_Species_Data_Output_Subfolder();
        $occur = configuration::Maxent_Species_Data_Occurance_Filename();


        $output_folder  = "{$data_folder}{$speciesID}{$os_path}{$output_subfolder}";
        $occur          = "{$data_folder}{$speciesID}{$os_path}{$occur}";

        
        // $future_projection_output = "{$output_folder}{$os_path}$combination{$os_dot}asc";

        $future_projection_output = $localComboFilename;
        
        echo "D we need a script \n";
        echo "Checkig to see if {$future_projection_output} exists\n";
        
        
        $script = "";
        // if we already have the file then we don't need this script
        if (!file_exists($future_projection_output))
        {

            $script_folder = configuration::CommandScriptsFolder();
            $lambdas = "{$output_folder}{$os_path}{$speciesID}.lambdas";

            $script_folder = configuration::CommandScriptsFolder();

            
            $script .= "#!/bin/tcsh\n";
            $script .= "module load java\n";
            $script .= "cd {$script_folder}\n";

            $proj = "{$project}{$os_path}{$combination}";

                        //java -mx2048m -cp $MAXENT   density.Project output/${SPP}.lambdas $PROJ output/`basename $PROJ`.asc fadebyclamping nowriteclampgrid nowritemess -x        
            $script .= "java -mx2048m -cp {$maxent} density.Project {$lambdas} {$proj} {$future_projection_output} fadebyclamping nowriteclampgrid nowritemess -x\n";    
            $script .= "\n";
            
        }
        
        
        return $script;
        
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
    
    

    public function AttachedCommand() 
    {
        return $this;
    }
    
}


?>
