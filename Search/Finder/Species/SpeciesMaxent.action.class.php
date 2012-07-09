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
            
            $result[str_replace(" ", "_", $speciesID)] = array();
            foreach ($scenarios as $scenarioID)
                foreach ($models  as $modelID)
                    foreach ($times as $timeID)
                    {
                
                        $combo = "{$scenarioID}_{$modelID}_{$timeID}";
                        $future_projection_output = str_replace(" ", "_", $speciesID)."{$os_path}".configuration::Maxent_Species_Data_Output_Subfolder()."{$os_path}{$combo}{$os_dot}asc";
                        $result[str_replace(" ", "_", $speciesID)][$combo] = $future_projection_output;
                        
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
        
        // rebuild combos i we don't have them
        if (count($this->SpeciesCombinations()) == 0 ) $this->SpeciesCombinations($this->buildCombinations());
        
        $this->getOccurances();
        
        $this->Result($this->progessResults());   // this will check to see if the outputs exists  
        
        pgdb::CommandActionQueue($this);

        //  make sure we have the occurance data for species   $this->SpeciesCombinations  gives use the list of species to look for
        
        // has this alredy been done ?
        $alreadyCompleted = $this->progressResultsComplete();
        
        if ($alreadyCompleted) 
        {            
            $this->Status("ALL DONE");
            $this->ExecutionFlag(CommandAction::$EXECUTION_FLAG_FINALISE);
            pgdb::CommandActionQueue($this);; // update the status and interim results            
            return $this->Result();   // we are done 
        }
        
        // check to see if we have the data they asked for ??
        
        
        $this->Status('Started');
        pgdb::CommandActionQueue($this);
        
        $this->QsubCollectionID(substr(uniqid(),0,13)); // setup unique ID so we can track all jobs asscoiated with this call
        
        $jobsCount = $this->ComputeSelectedSpecies(); // will start QSUB jobs if any 
        
        if ($jobsCount == 0)
        {
            $this->Status("All data has already been computed");
            $this->Result($this->progessResults());
            pgdb::CommandActionQueue($this);   // update the status and last results
            return $this->Result();
            
        }
        
        sleep(10); // wait for Qstat to catch up - before we start poling it

        $jobList = $this->qsubJobs(); // get current number of qsub jobs actually running now

        $this->QsubJobCount(count($jobList));

        while (count($jobList) > 0)
        {
            // check on jobs and get their progress

            $this->Status("Processing data on grid, waiting ".count($jobList)." jobs");
            $this->Result($this->progessResults());
            pgdb::CommandActionQueue($this); // update the status and interim results

            sleep(10);
            $jobList = $this->qsubJobs();
            $this->QsubJobCount(count($jobList));
            
        }

        $this->Result($this->progessResults());
        pgdb::CommandActionQueue($this); // update the status and interim results

    }
    
    
    /**
     * Read database for this species and create species folder for the required species
     * and retrive Occur.csv and place in species data folder if it does not exist.
     * 
     */
    private function getOccurances()
    {
        
        foreach (array_keys($this->SpeciesCombinations()) as $speciesID) 
        {
            if (is_null($speciesID)) continue;
            if ($speciesID == "" ) continue;
            
            $species_data_folder = file::mkdir_safe(configuration::Maxent_Species_Data_folder().str_replace(" ", "_", $speciesID));
            
            $occurFilename =  $species_data_folder.configuration::osPathDelimiter().configuration::Maxent_Species_Data_Occurance_Filename();

            if (!file_exists($occurFilename))  // check that this exists if not then - get data from Database 
                $this->getOccurancesRecordsFor($speciesID,$occurFilename);
            
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
        
        if (file_exists($occurFilename))
        {
            $line_count = file::lineCount($occurFilename);
            if ($line_count <= self::$OCCURANCE_MINIMUM_LINES) file::Delete($occurFilename); // not big enough
        }
        
        
        $ok = true;
        if (!file_exists($occurFilename))
        {
                                                     // reverese the space replacement
            $ok = $this->SpeciesOccuranceToFile($speciesID, $occurFilename); // get occuances from database
            
            if (is_null($ok)) return null; // something  wrong
            
            if (!file_exists($occurFilename)) return null; // make sure file exists
        }
        
        // sanity check agains the occurance file - probably has atleast 3 rows - ie header and data row and empty last row ?   
        $lineCount = file::lineCount($occurFilename);
        if ($lineCount < self::$OCCURANCE_MINIMUM_LINES) return FALSE;;
        
    }
    
    
    public function SpeciesOccuranceToFile($speciesID,$filename) 
    {
        
        $result = SpeciesData::SpeciesOccurance(str_replace("_", " ", $speciesID));
        
        if (count($result) <= self::$OCCURANCE_MINIMUM_LINES )
        {
            echo "SpeciesOccuranceToFile: too Few Results\n";
            return null;
        }
        
        $file = '"SPPCODE","LATDEC","LONGDEC"'."\n";  // headers specific to Maxent JAR
        foreach ($result as $row) 
            $file .= str_replace(" ","_",$speciesID).",".$row['latitude'].",".$row['longitude']."\n";
        
        
        file_put_contents($filename, $file);
        
        if (!file_exists($filename)) return null;
        
        
        $lineCount = file::lineCount($filename);
        if ($lineCount < self::$OCCURANCE_MINIMUM_LINES) return null;

        echo "SpeciesOccuranceToFile: row count in $filename  ($lineCount)\n";

        
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
    private function progessResults()
    {
        
        $result = array();
        foreach ($this->SpeciesCombinations() as $speciesID => $combinations) 
        {
            
            // check for this species that the HTML file is greater than 0
            // if not then 
            //$result[$speciesID]["current"] = ""

            $htmlFile = str_replace(" ","_",$speciesID).configuration::osPathDelimiter().configuration::Maxent_Species_Data_Output_Subfolder().configuration::osPathDelimiter().str_replace(" ","_",$speciesID).".html";
            
            $htmlFromMaxentLocal = configuration::Maxent_Species_Data_folder().$htmlFile;
            
            if (!file_exists($htmlFromMaxentLocal))
            {
                $result[$speciesID]['Current Condition'] = "Waiting to start computing ";
            }
            else
            {
                if (filesize($htmlFromMaxentLocal) == 0)
                {
                    $result[str_replace(" ","_",$speciesID)]['Current Condition'] = "Computing ..... ";
                }
                else
                {
                    $result[str_replace(" ","_",$speciesID)]['Current Condition'] = configuration::osPathDelimiter()."{$htmlFile}";
                }
                
            }
            
            
            foreach ($combinations as $combination  => $outputFilename) 
            {
                // check to see if the file for this species combination exists
                // on hpc check to see if the file exists 
        
                // $outputfile only points to the path that is the same on both sides
                
                $result[str_replace(" ","_",$speciesID)][$combination] = ""; // empty mean it does not exist
                if (file_exists(configuration::Maxent_Species_Data_folder().$outputFilename))
                {                    
                    $result[str_replace(" ","_",$speciesID)][$combination] = configuration::osPathDelimiter().$outputFilename;
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
            
            if (!$this->maxentLogDone(str_replace(" ","_",$speciesID))) return false;
            
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
        
        $maxentLog = configuration::Maxent_Species_Data_folder().str_replace(" ","_",$speciesID)."{$os_path}{$output_subfolder}{$os_path}maxent.log";
        
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
            foreach ($scriptsToRun as $scriptname) 
                exec("qsub -N{$this->QsubCollectionID()} '{$scriptname}'");  
            
            $jobCount = $scriptsToRun;
        }
        
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
            file::mkdir_safe(configuration::CommandScriptsFolder().str_replace(" ", "_", $speciesID));
            
            $singleScript = self::writeMaxentSingleSpeciesProjectionScriptFile($speciesID,$combinations); // all combinations for a single species
            
            if ($singleScript != "") 
                $scripts[str_replace(" ","_",$speciesID)] = $singleScript;
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

        $scriptFilename = configuration::CommandScriptsFolder().str_replace(" ", "_", $speciesID).configuration::osPathDelimiter().str_replace(" ", "_", $speciesID).'_root'.configuration::CommandScriptsSuffix();
        
        $maxent = configuration::MaxentJar();
        
        $train = configuration::Maxent_Taining_Data_folder();
        $project = configuration::Maxent_Future_Projection_Data_folder();
        $data_folder = configuration::Maxent_Species_Data_folder();

        $output_subfolder = configuration::Maxent_Species_Data_Output_Subfolder();
        $occur = configuration::Maxent_Species_Data_Occurance_Filename();

        $os_path = configuration::osPathDelimiter();

        $species_folder = "{$data_folder}".str_replace(" ", "_", $speciesID)."{$os_path}";
        $output_folder  = "{$data_folder}".str_replace(" ", "_", $speciesID)."{$os_path}{$output_subfolder}";

        $occur =  "{$data_folder}".str_replace(" ", "_", $speciesID)."{$os_path}{$occur}";
        
        
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

mkdir '{$output_folder}'

echo execute model for {$speciesID}

#model the species distribution
java -mx2048m -jar {$maxent} environmentallayers={$train} samplesfile={$occur} outputdirectory={$output_folder} -J -P -x -z redoifexists autorun

echo execute future projections

AAA;

        // create combinration script files here

        $future_projection_scripts = "";
        
        foreach ($combinations as $combination => $comboFilename)
        {
            
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
                    $ssFN = file::random_filename(configuration::CommandScriptsFolder()."/".str_replace(" ", "_", $speciesID)."/").configuration::CommandScriptsSuffix() ;
                    $ssFN = str_replace("//","/",$ssFN);

                    file_put_contents($ssFN, $ss);

                    exec("chmod u+x '$ssFN'");            

                    $subJobName  = "{$this->QsubCollectionID()}s";

                    $future_projection_scripts .= "qsub -N{$subJobName} '$ssFN'\n";

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
        
        $maxent = configuration::MaxentJar();
        
        $project = configuration::Maxent_Future_Projection_Data_folder();
        $data_folder = configuration::Maxent_Species_Data_folder();

        $output_subfolder = configuration::Maxent_Species_Data_Output_Subfolder();
        $occur = configuration::Maxent_Species_Data_Occurance_Filename();


        $output_folder  = "{$data_folder}".str_replace(" ", "_", $speciesID)."{$os_path}{$output_subfolder}";
        $occur          = "{$data_folder}".str_replace(" ", "_", $speciesID)."{$os_path}{$occur}";

        
        // $future_projection_output = "{$output_folder}{$os_path}$combination{$os_dot}asc";

        $future_projection_output = $localComboFilename;
        
        echo "Do we need a script  .. Checkig to see if {$future_projection_output} exists\n";
        
        $script = "";
        // if we already have the file then we don't need this script
        if (!file_exists($future_projection_output))
        {

            $script_folder = configuration::CommandScriptsFolder();
            $lambdas = "{$output_folder}{$os_path}".str_replace(" ", "_", $speciesID).".lambdas";

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

    
    
    /**
    *
    * @param type $src_grid_filename      - Maxent ASC Grid with filename in format of      (Scenario)_(model)_(time).asc
    * @param type $output_image_filename  - Where you want to output mage to end up 
    * @param type $low_threshold          - display value start from   - to be read from maxentResults.csv - "Equate entropy of thresholded and original distributions logistic threshold"
    * @param type $transparency           - transparency of all colors 
    * @param type $background_colour      - background colour   use 0 0 0 255 = Full balck   0 0 0 0 = Full Transparent
    * @return null|String                 - Output filename 
    */
    public static function CreateQuickLookImage($src_grid_filename,$output_image_filename = null ,$low_threshold = null,$transparency = 255,$background_colour = "0 0 0 255")
    {

        list($scenario, $model, $time) =  explode("_",str_replace('.asc','',basename($src_grid_filename)));    
        $stats = spatial_util::RasterStatisticsPrecision($src_grid_filename);    
        $ramp = RGB::Ramp($stats['Minimum'], $stats['Maximum'], 10, RGB::GradientYellowOrangeRed());    


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
        if (is_null($low_threshold)) $low_threshold = $indexes[1]; // if they did not pass low end threshold then just show everything above lowest value

        //echo "\nlow_threshold = $low_threshold\n";


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


        return $output_image_filename; // filename of png that can be used - is in temp folder

    }


    
    
    
    
}


?>
