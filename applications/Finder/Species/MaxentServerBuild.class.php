<?php
class MaxentMainServerBuild extends Object {
    

    public static function Run($array)
    {
        
        $help = util::CommandScriptsFoldermandLineOptionValue($array, 'help');
        if (!is_null($help)) return self::Usage($array);

        $check_species = util::CommandScriptsFoldermandLineOptionValue($array, 'check_species');
        if (!is_null($check_species)) return self::check_species($array,$check_species);
        
        
        $species = util::CommandScriptsFoldermandLineOptionValue($array, 'species');
        if (!is_null($species)) return self::run_species($array,$species);

        $clazz = util::CommandScriptsFoldermandLineOptionValue($array, 'clazz');
        if (!is_null($clazz)) return self::run_clazz($array,$clazz);

        $folder = util::CommandScriptsFoldermandLineOptionValue($array, 'folder');
        if (!is_null($folder)) return self::RunFolder($array,$folder);
        
        self::Usage($array);
        
    }

    private  static function Usage($array)
    {
        
        echo "php $array[0] --species=n    where n = species id\n";
        echo "php $array[0] --clazz=name   where name is a species clazz (taxa) name\n";
        echo "php $array[0] --clazz=LIST   display a list of clazz's\n";
        echo "php $array[0] --folder=true  process all spcies in current data folder\n";

        echo "other parameters \n";
        echo "\n";
        echo "\n";
        echo "       --ProjectFutures=[true|false]  default: true    true = Generate Future projections\n";
        echo "       --Project1975=[true|false]     default: true    true = Create layers for 1975 condition\n";
        echo "       --Project1990=[true|false]     default: true    true = Create layers for 1990 condition\n";
        echo "       --JobPrefix=JobName            default: tdh     Used in QSTAT / PBS to identify job in QSTAT";
        echo "\n";
        echo "       --LoadMediansOnly=[true|false] default: true    true = load median data ONLY\n";
        echo "       --LoadQuickLooks=[true|false]  default: true    true = load QuickLook image into database\n";
        echo "       --LoadASCII=[true|false]       default: false   true = load ASCII grid data into database (VERY SLOW & LARGE)n";
        echo "\n";
        echo "       --GenerateOnly=[true|false]    default: false   true = generate scripts but don't execute them\n";
        echo "       --MaxentOnly=[true|false]      default: false   true = run maxent.jar (don't process future climate projections)\n";
        echo "       --UseQSUB=[true|false]         default: true    true = Submit scripts to PBS queue to be executed on GRID\n";
        echo "       --UpdateDatabase=[true|false]  default: true    true = Insert results in to database to reading by other systems\n";
        echo "\n";
        echo "       --scenarios=a,b,c              default: all     scenarios  comma delimited list of scenarios names\n";
        echo "       --models=a,b,c                 default: all     models     comma delimited list of model     names\n";
        echo "       --times=yyyy,yyyy,yyyy         default: all     times      comma delimited list of years\n";
        echo "       --loadScenario=a,b,c           default: all     scenarios  names to load into database\n";
        echo "       --loadModel=a,b,c              default: all     models     names to load into database MODEL=ALL is special this is median of all Climate Models \n";
        echo "       --loadTimes=yyyy,yyyy,yyyy     default: all     times      times to load into database\n";
        echo "\n";
        echo "       --NoExecute=[true|false]       default: false   true = Only initialise Object and then print_r\n";
        echo "\n\n";
        
    }

    
    private  static function RunFolder($array,$folder_passed)
    {
        
        ErrorMessage::Marker("Run for Data already in Folder\n");
        
        
        $folders = file::folder_folders(configuration::Maxent_Species_Data_folder());
        
        
        foreach ($folders as $folder) 
        {   
            $species_id = basename($folder);            
        
            if (self::IsJobRunning($array,$species_id)) continue;
            
            ErrorMessage::Marker("From Folder species_id = {$species_id} \n");
            
            $MM = new MaxentMainServerBuild($species_id);
            self::setCommandLineParameters($array, $MM);
            
            
            $NoExecute = util::CommandScriptsFoldermandLineOptionValue($array, 'NoExecute',false);
            if ($NoExecute == false) 
                $MM->Execute();
            else
                print_r($MM);
            
            unset($MM);

            
        }
        
    }
    
    private  static function IsJobRunning($array,$species_id)
    {
     
        $jobPrefix = util::CommandScriptsFoldermandLineOptionValue($array, 'JobPrefix','tdh_');
        
        ErrorMessage::Marker("jobPrefix  = $jobPrefix");
        
        $running_species = DatabaseFile::RunningJobs($jobPrefix);
        
        //print_r($running_species);
        
        
        if (array_key_exists($jobPrefix.$species_id, $running_species))  
        {
            ErrorMessage::Marker("Job {$jobPrefix}{$species_id} is already running");
            return true;
        }
                
        ErrorMessage::Marker("{$jobPrefix}{$species_id} not running ");
        
        
        return false;
        
    }
    
    private  static function check_species($array,$species_id)
    {
        
        $result = SpeciesData::CurrentInfo2File($species_id);
        if ($result instanceof ErrorMessage) 
        {
            echo $result;
            exit(1);
        }

        echo "file created:: [{$result}]\n";

    }
    
    
    private  static function run_species($array,$species_id)
    {

        if (self::IsJobRunning($array,$species_id)) return;
        
        $MM = new MaxentMainServerBuild($species_id);
        self::setCommandLineParameters($array, $MM);
        
        $NoExecute = util::CommandScriptsFoldermandLineOptionValue($array, 'NoExecute',false);
        if ($NoExecute == false) 
            $MM->Execute();
        else
            print_r($MM);

                    
        unset($MM);
        
    }

    
    
    private  static function run_clazz($array,$clazz)
    {
                
        if ($clazz == "LIST") 
        {
            print_r(SpeciesData::Clazz());
            return null;
        }

        
        ErrorMessage::Marker("Find all species that are inside this clazz (Taxa)  [{$clazz}]\n");
        
        $speciesTaxas = SpeciesData::TaxaForClazzWithOccurances($clazz);
        
        
        
        foreach ($speciesTaxas as $row) 
        {
            $species_id = $row['species_id'];
            
            if (self::IsJobRunning($array,$species_id)) continue;
            
            
            $MM = new MaxentMainServerBuild($species_id);
            self::setCommandLineParameters($array, $MM);
            
            $NoExecute = util::CommandScriptsFoldermandLineOptionValue($array, 'NoExecute',false);
            if ($NoExecute == false) 
                $MM->Execute();
            else
                print_r($MM);
            
            unset($MM);
            
        }
        
        
    }
    
    
    private static function setCommandLineParameters($array, MaxentMainServerBuild $MM)
    {
        
        $MM->GenerateOnly(util::CommandScriptsFoldermandLineOptionValue($array, 'GenerateOnly',false));
        
        $MM->MaxentOnly(util::CommandScriptsFoldermandLineOptionValue($array, 'MaxentOnly',false));

        $MM->UseQSUB(util::CommandScriptsFoldermandLineOptionValue($array, 'UseQSUB',true));
        
        $MM->UpdateDatabase(util::CommandScriptsFoldermandLineOptionValue($array, 'UpdateDatabase',true));

        $MM->LoadMediansOnly(util::CommandScriptsFoldermandLineOptionValue($array, 'LoadMediansOnly',true));

        
        $MM->LoadASCII(util::CommandScriptsFoldermandLineOptionValue($array, 'LoadASCII',false));
        $MM->LoadQuickLooks(util::CommandScriptsFoldermandLineOptionValue($array, 'LoadQuickLooks',true));

        $MM->Project1975(util::CommandScriptsFoldermandLineOptionValue($array, 'Project1975',true));
        $MM->Project1990(util::CommandScriptsFoldermandLineOptionValue($array, 'Project1990',true));
        $MM->ProjectFutures(util::CommandScriptsFoldermandLineOptionValue($array, 'ProjectFutures',true));
        
        
        
        $MM->scenarios(util::CommandScriptsFoldermandLineOptionValue($array, 'scenarios',null));
        $MM->models(util::CommandScriptsFoldermandLineOptionValue($array, 'models',null));
        $MM->times(util::CommandScriptsFoldermandLineOptionValue($array, 'times',null));

        $MM->loadScenarios(util::CommandScriptsFoldermandLineOptionValue($array, 'loadScenarios',null));
        $MM->loadModels(util::CommandScriptsFoldermandLineOptionValue($array, 'loadModels',null));
        $MM->loadTimes(util::CommandScriptsFoldermandLineOptionValue($array, 'loadTimes',null));
        $MM->JobPrefix(util::CommandScriptsFoldermandLineOptionValue($array, 'JobPrefix','tdh_'));
        
        
        
    }
    
    
    
    public function __construct($speciesID) {
        parent::__construct();
        
        $this->SpeciesID($speciesID);
        $this->ParalellFuturePredictions(false);
        
        $this->GenerateOnly(false);
        $this->MaxentOnly(false);
        $this->UseQSUB(true);
        $this->UpdateDatabase(true);
        
        $this->LoadMediansOnly(true);
        
        $this->LoadASCII(false);
        $this->LoadQuickLooks(true);
        
        
        $this->SpeciesInfo(SpeciesData::SpeciesQuickInformation($this->SpeciesID()));
        
        $this->scenarios(null);
        $this->models(null);
        $this->times(null);

        $this->JobPrefix("tdh_");
        
        $this->Project1975(true);
        $this->Project1990(true);
        $this->ProjectFutures(true);
        
        
        $this->loadScenarios(null);
        $this->loadModels(null);
        $this->loadTimes(null);
        
        ErrorMessage::Marker("Initialise ".  $this->SpeciesInfo()."\n");
       
        
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
     
        print_r($this);
        
        $this->MaxentDone(false);
        
        file::Delete($this->maxent_single_species_script_filename());
        
        
        file::mkdir_safe($this->species_scripts_folder());
        if (!is_dir($this->species_scripts_folder()))
            return ErrorMessage::Marker("Can't create scripts folder ".$this->species_scripts_folder());
        
        
        file::mkdir_safe($this->species_data_folder());
        if (!is_dir($this->species_data_folder()))
            return ErrorMessage::Marker("Can't create species_data_folder folder ".$this->species_data_folder());
        
        
        file::mkdir_safe($this->species_output_folder());
        if (!is_dir($this->species_output_folder()))
            return ErrorMessage::Marker("Can't create species_output_folder folder ".$this->species_output_folder());
        
        
        $this->QsubCollectionID($this->JobPrefix().$this->SpeciesID());
        
        if ($this->UpdateDatabase())
        {
            ErrorMessage::Marker("Numbers from Database\n");
            $this->DataAlreadyLoadedASCII(SpeciesData::GetAllModelledData($this->SpeciesID(),'ASCII_GRID' ));        
            $this->DataAlreadyLoadedQL(SpeciesData::GetAllModelledData($this->SpeciesID(),'ASCII_GRID' ));                    
        }
        else
        {
            // find out how many from counting in filesystem
            // becasue we not updating database at the moment so read file system for counts
            ErrorMessage::Marker("Numbers from Filesystem\n");
            
            $this->DataAlreadyLoadedASCII(file::LS($this->species_output_folder(). "/*.asc", '-1', true));        
            $this->DataAlreadyLoadedQL   (file::LS($this->species_output_folder(). "/*.png", '-1', true));        
        }
        
        
        ErrorMessage::Marker("DataAlreadyLoadedASCII = ".count($this->DataAlreadyLoadedASCII()));
        ErrorMessage::Marker($this->DataAlreadyLoadedASCII());
        
        
        ErrorMessage::Marker("DataAlreadyLoadedQL    = ".count($this->DataAlreadyLoadedQL()));
        ErrorMessage::Marker($this->DataAlreadyLoadedQL());
        
        
        $this->ExecutionCompleted(Count($this->DataAlreadyLoadedASCII()));

        ErrorMessage::Marker("Generating for {$this->SpeciesInfo()} \n");

        
        $occurs = $this->getOccurances();
        if ($occurs instanceof ErrorMessage)
        {
            ErrorMessage::Marker("Get occurance failed \n");
            return;
        }
        
        if (!$occurs) return;
        
        
        if ($this->MaxentOnly())
        {
            ErrorMessage::Marker("Maxent Only\n");
            
            if ($this->MaxentLogDone())
            {
                ErrorMessage::Marker("Maxent Already calculated for {$this->SpeciesID()}\n Exiting .....\n");
                exit();
            }
            
        }
        
        $scriptname = $this->writeMaxentSingleSpeciesProjectionScriptFile(); // all combinations for a single species
        if ($scriptname == "") return null;        
        if (!file_exists($scriptname)) return null;
        
        if (!$this->MaxentOnly())
        {
            $this->ExecutionTotal($this->ExecutionCount() + $this->ExecutionCompleted());                    
            
            ErrorMessage::Marker("Already executed {$this->ExecutionCompleted()}\n");
            ErrorMessage::Marker("Left  to execute {$this->ExecutionCount()}\n");
            ErrorMessage::Marker("Total to execute {$this->ExecutionTotal()}\n");
            
        }
        
        if (file_exists($scriptname))
        {
            exec("chmod u+x '{$scriptname}'");
        }
        
        
        
        if ($this->GenerateOnly())
        {
            $dir = dirname($scriptname);
            ErrorMessage::Marker("Create script ready for execution \n Scripts Folder= {$dir} \n$scriptname \n ");
        }
        else
        {
            $dir = dirname($scriptname);
            exec("rm {$dir}/tdh_*");
            
            if ($this->UseQSUB())
            {
                ErrorMessage::Marker("Executing to QSUB  $scriptname \n ");
                $qsub_result     = exec("cd {$dir}; qsub -N{$this->QsubCollectionID()} '{$scriptname}'");  /// QSUB JOBS Submitted here
                ErrorMessage::Marker("QSUB  qsub_result = $qsub_result \n ");
            }
            else
            {
                ErrorMessage::Marker("Executing here  $scriptname \n ");
                ErrorMessage::Marker("[$scriptname]\n ");
                
            }
            
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
        $script  = "#!/bin/tcsh\n"; 
        $script .= "module load java\n";
        
        $script .= $this->maxentScript();
        
        
        // only add current  projections if we want them 
        // todp need to allow these scrpts to runn in paralell as well as some stag - create their own script 
        if (!$this->MaxentOnly()) 
        {
            if ($this->Project1975()) $script .= $this->ScriptProject1975();
            if ($this->Project1990()) $script .= $this->ScriptProject1990();
        }
        
        
        ErrorMessage::Marker("Controll what order the generation is done, we want to have a all climates for on scenario at least \n");
        ErrorMessage::Marker("Testing validity of Data Files");
        
        
        if (!$this->MaxentOnly()) // only add future projections if we want them 
        {
    
            
            if ($this->ProjectFutures())
            {
            
                ErrorMessage::Marker("Building Scripts for future projections\n");

                foreach (DatabaseClimate::GetScenarios() as $scenarioID)
                {
                    if (is_null($scenarioID)) continue;
                    $scenarioID = trim($scenarioID);
                    if ($scenarioID == "") continue;

                    // if we request a scpecifi scenario
                    if (!is_null($this->scenarios()))
                        if (!util::contains($this->scenarios(), $scenarioID)) continue;    


                    ErrorMessage::Marker("Building Scripts for scenarioID = $scenarioID\n");

                    foreach (DatabaseClimate::GetTimes() as $timeID)
                    {

                        if (is_null($timeID)) continue;
                        $timeID = trim($timeID);
                        if ($timeID == "") continue;


                        // if we request a scpecifi time
                        if (!is_null($this->times()))
                            if (!util::contains($this->times(), $timeID)) continue;    


                        ErrorMessage::Marker("Building Scripts for scenarioID = $scenarioID  timeID = $timeID\n");

                        foreach (DatabaseClimate::GetModels()  as $modelID)
                        {


                            if (is_null($modelID)) continue;
                            $modelID = trim($modelID);
                            if ($modelID == "") continue;

                            // if we request a scpecifi model
                            if (!is_null($this->models()))
                                if (!util::contains($this->models(), $modelID)) continue;    

                            ErrorMessage::Marker("Building Scripts for scenarioID = $scenarioID  timeID = $timeID modelID=[$modelID] \n");
                            //

                            $singleFutureScript = $this->singleCombinationScript("{$scenarioID}_{$modelID}_{$timeID}");   // script that will be used to execute just a single combo
                            if (is_null($singleFutureScript)) 
                            {
                                ErrorMessage::Marker("Seems to be no reason to create script for {$this->SpeciesID()} {$combination} ");
                                continue;
                            }


                            // If we are running bigger Future jobs then these may be in paralell  - here we add either the script itself or a call to execute the script 
                            if ($this->ParalellFuturePredictions())
                            {
                                ErrorMessage::Marker("Adding as Paralell {$combination} ");
                                $script .= "\nqsub -N{$this->QsubCollectionID()} '{$singleFutureScript}'";              
                            }
                            else
                            {
                                $script .= $singleFutureScript;
                            }



                        }


                        // not parallel we know then all data must be created before we make it here
                        // so we can create median for all data for a single scenario
                        if (!$this->ParalellFuturePredictions())            
                        {
                            // this is after all Climates models for this scenario and time have been run

                            $loadAscii      =  util::boolean2string($this->LoadASCII());
                            $loadQuicklooks =  util::boolean2string($this->LoadQuickLooks());

                            $SpeciesMedian_php = configuration::ApplicationFolder()."applications/SpeciesMedian.php";
                            $script .= "php {$SpeciesMedian_php} {$this->SpeciesID()} --scenario={$scenarioID} --time={$timeID}  --LoadASCII={$loadAscii}  --LoadQuickLooks={$loadQuicklooks}  \n";

                        }



                    }



                }

            } // if ($this->ProjectFutures)

            
            
        }    
        
        
        $script = trim($script);
        
        $maxent_single_species_script_filename = $this->maxent_single_species_script_filename();
        
        // add lines to remove - script once it's complete
        
        $script .=  "\n";
        $script .=  "\n# rm {$maxent_single_species_script_filename}\n"; // remove itself after execution
        $script .=  "\n";
        
        
        file_put_contents($maxent_single_species_script_filename, $script);

        if (!file_exists($maxent_single_species_script_filename))
            return new ErrorMessage(__METHOD__, __LINE__, "failed to write maxent_single_species_script_filename = $maxent_single_species_script_filename\n", true);

        
        ErrorMessage::Marker("maxent_single_species_script_filename = $maxent_single_species_script_filename\n");
        
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
        
        $maxent_script = "#!/bin/tcsh\n";
        
        if (!$this->MaxentLogDone()) 
        {    
            
            ErrorMessage::Marker("CReating Main Maxent script for {$this->SpeciesID()}\n");    
            
            $species_folder = $this->species_data_folder();
        
            $maxent = configuration::MaxentJar();
            $train = configuration::Maxent_Taining_Data_folder();
            $project = configuration::Maxent_Future_Projection_Data_folder();
        
            $output_folder  =  $this->species_output_folder(); 
            
        
$maxent_script .= <<<AAA
#
# execute maxent for  ({$this->SpeciesID()})
# ==================================================================================
#
# speciesID      = {$this->SpeciesID()} 
# Species Info   = {$this->SpeciesInfo()}
# Scenarios      = ALL
# Models         = ALL 
# Times          = ALL
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

AAA;

        }
        else
        {
            ErrorMessage::Marker("Maxent already done for {$this->SpeciesID()}\n");    
        }
        
        if ($this->UpdateDatabase())
        {
            ErrorMessage::Marker("Setting up to load Maxent data into DB {$this->SpeciesID()}\n");    

            $MaxentResultsInsert_php  = configuration::ApplicationFolder()."applications/MaxentResultsInsert.php";               
            $maxent_script  .= "\nphp -q '{$MaxentResultsInsert_php}' {$this->SpeciesID()}\n";
        }
            
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

        
        
        
        $script  = ""; 
        
        // don't have to do this as the main script alreayd has the data  if ParalellFuturePredictions  === FALSE
        if ($this->ParalellFuturePredictions())
        {
         
            $scriptFilename =   configuration::CommandScriptsFolder().
                                $this->SpeciesID().
                                configuration::osPathDelimiter().
                                $combination.
                                configuration::CommandScriptsSuffix();

            file::Delete($scriptFilename);
            
            
            ErrorMessage::Marker("singleCombinationScript for {$this->SpeciesID()} .. {$combination}");
            ErrorMessage::Marker("script_folder            = $script_folder");
            ErrorMessage::Marker("maxent                   = $maxent");
            ErrorMessage::Marker("lambdas                  = $lambdas");
            ErrorMessage::Marker("proj                     = $proj");
            ErrorMessage::Marker("future_projection_output = $future_projection_output");
            
            
            $script .= "#!/bin/tcsh\n"; 
        
            $script .= "# combination              = {$combination}\n";
            $script .= "# speciesID                = {$this->SpeciesID()}\n";
            $script .= "# speciesInfo              = ".SpeciesData::SpeciesQuickInformation($this->SpeciesID())."\n";
            $script .= "# script_folder            = {$script_folder}\n";
            $script .= "# maxent                   = {$maxent}\n";
            $script .= "# lambdas                  = {$lambdas}\n";
            $script .= "# proj                     = {$proj}\n";
            $script .= "# future_projection_output = {$future_projection_output}\n";
            $script .= "# scriptFilename           = {$scriptFilename}\n";
            $script .= "#\n";

            $script .= "module load java\n";
            $script .= "cd {$script_folder}\n";

        } else {
            
            if (!file_exists($future_projection_output))
            {
                $script .= "# speciesID .. combination = {$this->SpeciesID()} .. {$combination}\n";
            }
            
        }
        
        $count = file::lineCount($future_projection_output); // check  line count of $future_projection_output
        if ($count < 697) 
        {
            ErrorMessage::Marker("$future_projection_output is does not have enough lines = $future_projection_output line count = ".$count);
            file::Delete($future_projection_output);
        }
            
        
        // if already calc then don't do again
        if (!file_exists($future_projection_output))
        {
            
            
            
            ErrorMessage::Marker("$future_projection_output Added for generation");
            
                        //java -mx2048m -cp $MAXENT   density.Project output/${SPP}.lambdas $PROJ output/`basename $PROJ`.asc fadebyclamping nowriteclampgrid nowritemess -x        
            $script .= "java -mx2048m -cp {$maxent} density.Project {$lambdas} {$proj} {$future_projection_output} fadebyclamping nowriteclampgrid nowritemess -x\n";   
            $this->ExecutionCount($this->ExecutionCount() + 1);      
            
            // create quicklook
            $script .= "php -q ".configuration::ApplicationFolder()."applications/CreateQuickLookFromAscii.php {$this->SpeciesID()} {$future_projection_output}\n";
            
            $qlfn = str_replace("asc", "png", $future_projection_output);
            
            if ($this->UpdateDatabase())
            {
                // need to check if this is something that we ware going to load
                
                if ($this->shouldLoadData($combination)) 
                {

                    if ($this->LoadASCII())
                        $script .= "php -q ".configuration::ApplicationFolder()."applications/MaxentInsertAsciiOnly.php {$this->SpeciesID()} '{$future_projection_output}'\n";  
                    
                    if ($this->LoadQuickLooks())    
                        $script .= "php -q ".configuration::ApplicationFolder()."applications/MaxentInsertQuickLookOnly.php {$this->SpeciesID()} '{$qlfn}'\n";                    
                    
                }
            }
                
        }
        else
        {

            // ErrorMessage::Marker("$future_projection_output already exists ");
            
            
            if ($this->UpdateDatabase())  
            {
                if (!array_key_exists($combination, $this->DataAlreadyLoadedASCII()))    
                {
                    
                    if ($this->shouldLoadData($combination)) 
                    {
                        
                        if ($this->LoadASCII())
                        {
                            ErrorMessage::Marker("Added Load into database $future_projection_output  ");
                            $script .= "php -q ".configuration::ApplicationFolder()."applications/MaxentInsertAsciiOnly.php {$this->SpeciesID()} '{$future_projection_output}' \n";      
                        }
                            
                    }
                    
                }                
                else
                {
                    ErrorMessage::Marker("$future_projection_output already in Database");
                }
            }
            
            
            
            $qlfn = str_replace("asc", "png", $future_projection_output);
            if (!file_exists($qlfn))  // check to see if quick look exists 
            {
                
                ErrorMessage::Marker("Added to create quick look from  $future_projection_output");
                $script .= "php -q ".configuration::ApplicationFolder()."applications/CreateQuickLookFromAscii.php {$this->SpeciesID()} '{$future_projection_output}'\n";
                
                
                if ($this->UpdateDatabase())  // lets see if we need to update db only
                {
                    if ($this->shouldLoadData($combination))
                    {
                        if ($this->LoadQuickLooks())    
                        {
                            ErrorMessage::Marker("Added to loaded quick look iinto DB from  $qlfn  ");
                            $script .= "php -q ".configuration::ApplicationFolder()."applications/MaxentInsertQuickLookOnly.php {$this->SpeciesID()} '{$qlfn}'\n";                                                    
                        }

                    }

                }
                
                
            }
            else
            {
                

                if ($this->UpdateDatabase())  // lets see if we need to update db only
                {
                    if (!array_key_exists($combination, $this->DataAlreadyLoadedQL()))    
                    {
                        if ($this->shouldLoadData($combination))
                        {
                            if ($this->LoadQuickLooks())    
                            {
                                ErrorMessage::Marker("Added to loaded quick look iinto DB from  $qlfn  ");
                                $script .= "php -q ".configuration::ApplicationFolder()."applications/MaxentInsertQuickLookOnly.php {$this->SpeciesID()} '{$qlfn}'\n";                                                        
                            }

                        }
                    }
                    else
                    {
                        ErrorMessage::Marker("quick look $qlfn  already in DB ");
                    }

                }
                
            }
        }
        
        
        if ($this->ParalellFuturePredictions())     
        {
            $script .= "rm $scriptFilename\n";
        }
            
        
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
            ErrorMessage::Marker(__METHOD__."(".__LINE__.")","Failed to Write Script for Species = {$this->SpeciesID()}  combination = $combination scriptFilename = $scriptFilename \n");
            
            return null;
        }
        

        return $scriptFilename;
        
        
    }
        
    
    
    private function ScriptProject1975()
    {        
        $proj = configuration::Maxent_Taining_Data_folder();
        
        ErrorMessage::Marker("Create Projection for 1975 using {$proj}");
        
        return $this->ProjectUsingFolder($proj,1975);
    }
    
    private function ScriptProject1990()
    {
        $proj =  configuration::Maxent_Future_Projection_Data_folder().configuration::osPathDelimiter()."1990";
        
        ErrorMessage::Marker("Create Projection for 1990 using {$proj}");
        
        return $this->ProjectUsingFolder($proj,1990);
    }

    
    /**
     * Create a script to project a certain folder 
     * 
     * mainly used to get current conditions 1975 & 1990
     * 
     * @return null|string 
     */
    private function ProjectUsingFolder($proj,$year)
    {
        
        $combination = "CURRENT_CURRENT_{$year}";
        
        $script_folder =  $this->species_scripts_folder();
        
        $maxent = configuration::MaxentJar();
        

        $lambdas =  configuration::Maxent_Species_Data_folder().
                    $this->SpeciesID().
                    configuration::osPathDelimiter().
                    configuration::Maxent_Species_Data_Output_Subfolder().
                    configuration::osPathDelimiter().
                    $this->SpeciesID().
                    ".lambdas";
        

        $future_projection_output = configuration::Maxent_Species_Data_folder().
                                    $this->SpeciesID().
                                    configuration::osPathDelimiter().
                                    configuration::Maxent_Species_Data_Output_Subfolder().
                                    configuration::osPathDelimiter().
                                    $combination.
                                    ".asc";
        
        
        $script  = ""; 
        
        // don't have to do this as the main script already has the data  if ParalellFuturePredictions  === FALSE
        if ($this->ParalellFuturePredictions())
        {
         
            $scriptFilename =   configuration::CommandScriptsFolder().
                                $this->SpeciesID().
                                configuration::osPathDelimiter().
                                $combination.
                                configuration::CommandScriptsSuffix();

            file::Delete($scriptFilename); // only delete script file if we are in paralell mode
            
            
            ErrorMessage::Marker("singleCombinationScript for {$this->SpeciesID()} .. {$combination}");
            ErrorMessage::Marker("script_folder            = $script_folder");
            ErrorMessage::Marker("maxent                   = $maxent");
            ErrorMessage::Marker("lambdas                  = $lambdas");
            ErrorMessage::Marker("proj                     = $proj");
            ErrorMessage::Marker("future_projection_output = $future_projection_output");
            
            
            $script .= "#!/bin/tcsh\n"; 
        
            $script .= "# combination              = {$combination}\n";
            $script .= "# speciesID                = {$this->SpeciesID()}\n";
            $script .= "# speciesInfo              = ".SpeciesData::SpeciesQuickInformation($this->SpeciesID())."\n";
            $script .= "# script_folder            = {$script_folder}\n";
            $script .= "# maxent                   = {$maxent}\n";
            $script .= "# lambdas                  = {$lambdas}\n";
            $script .= "# proj                     = {$proj}\n";
            $script .= "# future_projection_output = {$future_projection_output}\n";
            $script .= "# scriptFilename           = {$scriptFilename}\n";
            $script .= "#\n";

            $script .= "module load java\n";
            $script .= "cd {$script_folder}\n";

        } else {
            
            if (!file_exists($future_projection_output))
            {
                $script .= "# speciesID .. {$this->SpeciesID()} combination =  .. {$combination}\n";
            }
            
        }
        
        
        if (file_exists($future_projection_output))
        {
            $count = file::lineCount($future_projection_output); // check  line count of $future_projection_output
            if ($count < 697) 
            {
                ErrorMessage::Marker("$future_projection_output is does not have enough lines = $future_projection_output line count = ".$count);
                file::Delete($future_projection_output);
            }            
        }
            
        
        // if already calc then don't do again
        if (!file_exists($future_projection_output))
        {
            
            
            ErrorMessage::Marker("$future_projection_output Added for generation");
            
                        //java -mx2048m -cp $MAXENT   density.Project output/${SPP}.lambdas $PROJ output/`basename $PROJ`.asc fadebyclamping nowriteclampgrid nowritemess -x        
            $script .= "java -mx2048m -cp {$maxent} density.Project {$lambdas} {$proj} {$future_projection_output} fadebyclamping nowriteclampgrid nowritemess -x\n";   
            $this->ExecutionCount($this->ExecutionCount() + 1);      
            
            // create quicklook
            $script .= "php -q ".configuration::ApplicationFolder()."applications/CreateQuickLookFromAscii.php {$this->SpeciesID()} {$future_projection_output}\n";
            
            $qlfn = str_replace("asc", "png", $future_projection_output);
            
            if ($this->UpdateDatabase())
            {
                // need to check if this is something that we ware going to load
                
                if ($this->shouldLoadData($combination)) 
                {

                    if ($this->LoadASCII())
                        $script .= "php -q ".configuration::ApplicationFolder()."applications/MaxentInsertAsciiOnly.php {$this->SpeciesID()} '{$future_projection_output}'\n";  
                    
                    if ($this->LoadQuickLooks())    
                        $script .= "php -q ".configuration::ApplicationFolder()."applications/MaxentInsertQuickLookOnly.php {$this->SpeciesID()} '{$qlfn}'\n";                    
                    
                }
            }
                
        }
        else
        {

            // ErrorMessage::Marker("$future_projection_output already exists ");
            
            
            if ($this->UpdateDatabase())  
            {
                if (!array_key_exists($combination, $this->DataAlreadyLoadedASCII()))    
                {
                    
                    if ($this->shouldLoadData($combination)) 
                    {
                        
                        if ($this->LoadASCII())
                        {
                            ErrorMessage::Marker("Added Load into database $future_projection_output  ");
                            $script .= "php -q ".configuration::ApplicationFolder()."applications/MaxentInsertAsciiOnly.php {$this->SpeciesID()} '{$future_projection_output}' \n";      
                        }
                            
                    }
                    
                }                
                else
                {
                    ErrorMessage::Marker("$future_projection_output already in Database");
                }
            }
            
            
            
            $qlfn = str_replace("asc", "png", $future_projection_output);
            if (!file_exists($qlfn))  // check to see if quick look exists 
            {
                
                ErrorMessage::Marker("Added to create quick look from  $future_projection_output");
                $script .= "php -q ".configuration::ApplicationFolder()."applications/CreateQuickLookFromAscii.php {$this->SpeciesID()} '{$future_projection_output}'\n";
                
                
                if ($this->UpdateDatabase())  // lets see if we need to update db only
                {
                    if ($this->shouldLoadData($combination))
                    {
                        if ($this->LoadQuickLooks())    
                        {
                            ErrorMessage::Marker("Added to loaded quick look iinto DB from  $qlfn  ");
                            $script .= "php -q ".configuration::ApplicationFolder()."applications/MaxentInsertQuickLookOnly.php {$this->SpeciesID()} '{$qlfn}'\n";                                                    
                        }

                    }

                }
                
                
            }
            else
            {
                

                if ($this->UpdateDatabase())  // lets see if we need to update db only
                {
                    if (!array_key_exists($combination, $this->DataAlreadyLoadedQL()))    
                    {
                        if ($this->shouldLoadData($combination))
                        {
                            if ($this->LoadQuickLooks())    
                            {
                                ErrorMessage::Marker("Added to loaded quick look iinto DB from  $qlfn  ");
                                $script .= "php -q ".configuration::ApplicationFolder()."applications/MaxentInsertQuickLookOnly.php {$this->SpeciesID()} '{$qlfn}'\n";                                                        
                            }

                        }
                    }
                    else
                    {
                        ErrorMessage::Marker("quick look $qlfn  already in DB ");
                    }

                }
                
            }
        }
        
        
        if ($this->ParalellFuturePredictions())     
        {
            $script .= "rm $scriptFilename\n";
        }
            
        
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
        

        return $scriptFilename;
        
        
    }
            
    
    
    
    
    /**
     * Check to see if this combination si to be loaded
     * 
     * 
     * @param type $combination
     * @return boolean 
     */
    private function shouldLoadData($combination)
    {
        
        list($scenario,$model,$time) = explode("_",$combination);
        
        if ($this->LoadMediansOnly())
            if ($model != "ALL") return false; // we only want medians not Model ALL so don't load
            
            
        
        // all null so load data
        if (is_null($this->loadModels()) &&  is_null($this->loadScenarios())  && is_null($this->loadTimes())) return true;
        
        
        
        // check to see if the scenario they sent is in the loadable scenarios
        if (!is_null($this->loadScenarios()))
            if (util::contains($this->loadScenarios(), $scenario)) return true;
        
        
        // check to see if the model they sent is in the loadable models
        if (!is_null($this->loadModels()))
            if (util::contains($this->loadModels(), $model)) return true;

            
        // check to see if the model they sent is in the loadable models
        if (!is_null($this->loadTimes()))
            if (util::contains($this->loadTimes(), $time)) return true;
            
        
        return false;
        
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
        
        ErrorMessage::Marker("OccurenceFilename = {$this->OccurenceFilename()}\n");

        
        if (file_exists($this->OccurenceFilename())) return true;
        
        $file_written = SpeciesData::SpeciesOccuranceToFile($this->SpeciesID(),$this->OccurenceFilename()); // get occuances from database
        if ($file_written instanceof ErrorMessage) 
            return ErrorMessage::Stacked(__METHOD__, __LINE__, "", true, $file_written);
        
        if (!$file_written) 
        {
            ErrorMessage::Marker("OccurenceFilename Not enoguh entries to Model {$this->SpeciesID()}\n");            
            return false;
        }
        
        ErrorMessage::Marker("OccurenceFilename Written to {$this->OccurenceFilename()}  Line Count = ".file::lineCount($this->OccurenceFilename())."\n");
        
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


    
    public function ExecutionCount() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }

    
    public function ExecutionCompleted() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }
    
    
    public function DataAlreadyLoadedASCII() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }
    
    public function DataAlreadyLoadedQL() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }

    
    public function ExecutionTotal() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
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


    public function scenarios() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }

    public function models() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }

    public function times() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }

    public function loadScenarios() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }

    public function loadModels() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }

    public function loadTimes() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }

    public function JobPrefix() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }

    
    public function ProjectFutures() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }
    
    
    public function Project1990() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }
    
    public function Project1975() {
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
    

    public function MaxentDone() {
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

    
    public function MaxentOnly() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }
    
    public function UseQSUB() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }

    public function UpdateDatabase() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }

    public function LoadMediansOnly() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }

    public function LoadQuickLooks() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }
    

    public function LoadASCII() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }
    
    
    public function SpeciesInfo() {
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
        
        ErrorMessage::Marker("Checking on maxentLog = $maxentLog \n");
        
        
        if (!file_exists($maxentLog)) return false;   // if we don't have the file then it's not done yet

        $lastLogLine = exec("tail -n1 '$maxentLog'");

        if (util::contains($lastLogLine, "Ending"))  
        {
            $this->MaxentDone(false);
            return true; // Maxent finished OK
            
        }
                
        $this->MaxentDone(false);
        return false; // Maxtent did not finish OK
        
    }
    
    
    
}


?>
