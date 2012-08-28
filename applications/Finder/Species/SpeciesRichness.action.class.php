<?php

/**
 * 
 * 
 */
class SpeciesRichness extends CommandAction {
    
    
    public function __construct() {
        parent::__construct();
        
        $this->scenario(null);
        $this->model('ALL');
        $this->time(null);
     
        $this->LoadAscii(false);
        $this->LoadQuickLook(true);
        $this->Recalculate(false);
        $this->ValidateExistenceOnly(false);
        
        $this->MinimumOccurance(10);
        
    }

    public function __destruct() {
        parent::__destruct();
    }
    
    public function initialise()
    {
        
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
        
        $this->Error(array());
        $this->Missing(array());
        
        $this->AsciiGrids(array());
        $this->QuickLooks(array());
        
        $this->ASCIIGRID_FileUniqueIDs(array());
        $this->QuickLook_FileUniqueIDs(array());

        $this->Result("");
        
        $this->ProgressPercent(1);
        
        $this->getParameters();
        
        $this->UpdateStatus("Parameters set");
        
        $this->SpeciesList($this->getSpeciesList());
        
        $this->ProgressPercent(5);
        
        $this->UpdateStatus("Species Count: " + count($this->SpeciesList()));
        
        $this->getThresholds(array_keys($this->SpeciesList()));
        
        $toProcess = $this->getValidMedians();
        
        $this->deleteForRecalculate($toProcess); // delete richness entries and files - only if requested
        
        $this->calculateRichness($toProcess);  //updates    $this->Missing();  $this->Invalid();  $this->Error();
        
        $this->returnResults();
        $this->ProgressPercent(100);
        
        $this->UpdateStatus("Richness Calculations complete");
        
        
    }

    private function returnResults()
    {
        
        $lines = array();
        foreach ($this->QuickLook_FileUniqueIDs() as $key => $value) 
        {
            $lines[] = "{$key}={$value}";
        }
        
        $str = implode("~", $lines);
        
        $this->Result($str);
        
        
    }
    
    
    private function getThresholds($species_ids)
    {
        
        $result = array();
                         
        foreach ($species_ids as $species_id) 
        {
            
            $threshold_result = DatabaseMaxent::GetMaxentThresholdForSpecies($species_id);
            if ($threshold_result instanceof ErrorMessage)
            {
                $this->addError($threshold_result);
                continue;
            }
                
            if (!is_null($threshold_result))
                $result[$species_id] = $threshold_result;
            
        }
    
        $this->ProgressPercentIncrement();
        
        $this->MaxentThresholds($result);
        
        
    }

    
    
    private function calculateRichness($toProcess)
    {
        
        $model = "ALL";
    
        foreach ($toProcess as $scenario => $times) 
        {
            
            foreach ($times as $time => $species_files) 
            {
                
                if (is_null($species_files))
                {
                    $this->addAsciiGridCombination($scenario,$model,$time,null);  // add empty combination
                }
                else
                {
                    
                    $single_richness_filename = $this->process_richness_for_set_of_species_median_files
                                (
                                    $scenario, 
                                    $model, 
                                    $time, 
                                    $species_files
                                );


                    $this->addAsciiGridResult($single_richness_filename,$scenario,$model,$time);
                    
                }

                
            }
            
            $this->ProgressPercentIncrement();
            $this->processRichnessQuicklooks($scenario); // updates  $this->Error();
            $this->ProgressPercentIncrement();
            
            
        }
        
        
    }
    
    
    /**
     * Added the acsi grid that was create into a memory array
     * 
     * @param ErrorMessage $gridFilename
     * @param type $scenario
     * @param type $model
     * @param type $time
     * @return null 
     */
    private function addAsciiGridResult($gridFilename,$scenario,$model,$time)
    {

        if ($gridFilename instanceof ErrorMessage)
        {
            $this->UpdateStatus("FAILED to create richness for {$this->genus()} {$scenario} {$model} {$time}");
            $this->addError($gridFilename);
            return null;
        }
        
        // we could not create richness
        if (is_null($gridFilename) || !file_exists($gridFilename)) 
        {
            $this->UpdateStatus("Error creating richness for {$this->genus()} {$scenario} {$model} {$time}");
            $err = $this->AsciiError();
            $err["{$scenario}_{$model}_{$time}"] = new ErrorMessage(__METHOD__, __LINE__,"Error creating richness for {$this->genus()} {$scenario} {$model} {$time}", true) ;
            $this->AsciiError($err);
            return null;
        }
        
        $this->UpdateStatus("Created richness for {$this->genus()} {$scenario} {$model} {$time}");
        
        $this->addAsciiGridCombination($scenario,$model,$time,$gridFilename);
        
        
    }

    private function addAsciiGridCombination($scenario,$model,$time,$gridFilename)
    {
        $ag = $this->AsciiGrids();
        $ag["{$scenario}_{$model}_{$time}"] = $gridFilename;
        $this->AsciiGrids($ag);
        
    }


    private function addASCIIGRID_FileUniqueID($scenario,$model,$time,$file_unique_id)
    {
        $ag = $this->ASCIIGRID_FileUniqueIDs();
        $ag["{$scenario}_{$model}_{$time}"] = $file_unique_id;
        $this->ASCIIGRID_FileUniqueIDs($ag);
    }

    private function addQuickLook_FileUniqueID($scenario,$model,$time,$file_unique_id)
    {
        $ag = $this->QuickLook_FileUniqueIDs();
        $ag["{$scenario}_{$model}_{$time}"] = $file_unique_id;
        $this->QuickLook_FileUniqueIDs($ag);
    }
    

    

    private function addError($src)
    {
        
        $err = $this->Error();
        
        if (is_array($src))
            foreach ($src as $value) 
                $err[] = $value;    
        else
            $err[] = $src;    
        
        
        $this->Error($err);
        
    }
    

    
    
    /**
     * All quicklooks for a single scenario  have to have the same number of Species in the min max range
     * ie all the times slices 
     * @param type $scenario
     * @param type $model
     * @param type $time
     * @param type $scenario_time_richness_filename 
     */
    private function processRichnessQuicklooks($scenario_requested)
    {
        
        $this->UpdateStatus("Create QuickLooks for {$scenario_requested} ");
        
        $model  = "ALL";
        
        $scenario_files = $this->getScenarioRichnessFilenames($scenario_requested);
        
        
        $scenario_min = null;
        $scenario_max = null;
        $this->getStatisticsForScenario($scenario_requested,&$scenario_min,&$scenario_max);
        if (is_null($scenario_min) || is_null($scenario_max))
        {
            $scenario_min = null;
            $scenario_max = null;
            $this->addError(new ErrorMessage(__METHOD__, __LINE__, "Unable to get valid min and max to create quick look"));            
        }

        
        
        foreach ($scenario_files as $combination => $richness_asciigrid_filename)
        {
            
            list($scenario,$model,$time) = explode("_",$combination);
            
            $this->createQuicklookForAscii($richness_asciigrid_filename,$scenario, $model, $time,$scenario_min,$scenario_max,true);
            

        }

        
    }
    
    private function createQuicklookForAscii($richness_asciigrid_filename,$scenario, $model, $time,$scenario_min,$scenario_max,$reuse = false) 
    {
        
        if (!file_exists($richness_asciigrid_filename)) 
        {
            $this->addQuickError(new ErrorMessage(__METHOD__, __LINE__, "Can't find richness_asciigrid_filename {$richness_asciigrid_filename}"));
            return null;
        }


        $richness_quicklook_filename = str_replace(".asc", ".png", $richness_asciigrid_filename);
        
        if (!file_exists($richness_quicklook_filename))
        {
            $this->UpdateStatus("Create QuickLook Image for ".basename($richness_asciigrid_filename));    
            
            $richness_quicklook_filename = GenusData::CreateImage($this->genus(), $richness_asciigrid_filename,$scenario_min,$scenario_max,$richness_quicklook_filename); // create quickLook from ascii
            if ($richness_quicklook_filename instanceof ErrorMessage)
            {
                $this->addQuickError($richness_quicklook_filename);
                return null;
            }
            
            
            $this->UpdateStatus("Created richness QuickLook for {$this->genus()} {$scenario} {$model} {$time}");
        }        
        else
        {
            $this->UpdateStatus("Richness QuickLook for ALREADY EXISTS for {$this->genus()} {$scenario} {$model} {$time}");
        }
        
        $this->addQuickLook("{$scenario}_{$model}_{$time}",$richness_quicklook_filename); // keep track of file that was created
        
        
        if ($this->LoadQuickLook())  // if we want to load data to database then 
        {
         
            $quick_look_file_id = null;
            
            if ($reuse)
            {

                $data = GenusData::GetProjectedFiles($this->genus(), 'QUICK_LOOK', $scenario, $model, $time);
                if ($data instanceof ErrorMessage) 
                {
                    $this->addError($data);
                }
                else
                {
                    if (count($data) > 0 )
                    {
                        $quick_look_file_id = util::first_key($data);
                        $this->UpdateStatus("QuickLook read from DB  for $scenario, $model, $time   {$quick_look_file_id}");
                    }
                }
                
                
            }

            
            
            if (is_null($quick_look_file_id))
            {
                
                $dbresult = GenusData::InsertProjectedFile($this->genus(),$richness_quicklook_filename,'QUICK_LOOK',$scenario, $model, $time,true);
                if ($dbresult instanceof ErrorMessage)
                {
                    $this->addQuickError($dbresult);
                    return null;
                }

                $quick_look_file_id = $dbresult;
                
            }

            
            if (!is_null($quick_look_file_id))
                $this->addQuickLook_FileUniqueID($scenario, $model, $time, $quick_look_file_id);
            
            
        }
        
        
        
        
    }
    
    private function addQuickLook($combination, $src)
    {
        $ql = $this->QuickLooks();
        $ql[$combination] = $src;
        $this->QuickLooks($ql);
        
    }
    
    
    
    private function addQuickError($src)
    {
        $err = $this->QuickError();
        $err[] = $src;
        $this->QuickError($err);
        
    }
    
    
    private function getScenarioRichnessFilenames($scenario_requested)
    {
        
        
        $ag = $this->AsciiGrids();   // key =>   "{$scenario}_{$model}_{$time}"  value => acsigid filename
        
        $scenario_files = array();
        foreach ($ag as $combination => $richness_filename) 
        {
            
            list($scenario,$model,$time) = explode("_",$combination);
            
            if ($scenario == $scenario_requested)
                if (!is_null($richness_filename))
                    $scenario_files[$combination] = $richness_filename;
                
        }
        
        return $scenario_files;

        
    }
    
    
    private function getStatisticsForScenario($scenario_requested,&$scenario_min,&$scenario_max)
    {
        
        $scenario_files = $this->getScenarioRichnessFilenames($scenario_requested);
        
        $scenario_stats = spatial_util::ArrayRasterStatistics($scenario_files);

        if (is_null($scenario_stats))
        {
            $scenario_min = null;
            $scenario_max = null;
            return;
        }            
        
        
        $mins = matrix::Column($scenario_stats, spatial_util::$STAT_MINIMUM);
        $maxs = matrix::Column($scenario_stats, spatial_util::$STAT_MAXIMUM);
        
        
        if (is_null($mins) || is_null($maxs) )
        {
            $scenario_min = null;
            $scenario_max = null;
            return;
        }
        
        if (count($mins) == 0 || count($maxs) == 0)
        {
            $scenario_min = null;
            $scenario_max = null;
            return;
        }

        
        if (count($mins) == 1 )
        {
            $scenario_min = util::first_element($mins);
        }
        else
        {
            $scenario_min = min( $mins);
        }
        
        if (count($maxs) == 1 )
        {
            $scenario_max = util::first_element($maxs);
        }
        else
        {
            $scenario_max = max( $maxs);
        }
        
        
        $this->UpdateStatus("scenario_min = $scenario_min");
        $this->UpdateStatus("scenario_max = $scenario_max");
        
        
    }
    

    
    /**
     * 
     *  Create Species Richness for a Scenario / Time 
     * 
     * @param type $scenario
     * @param type $model
     * @param type $time
     * @param type $species_files
     * @param type $error
     * @param type $missing_files
     * @param type $invalid_files
     * @return null 
     */
    private function process_richness_for_set_of_species_median_files(
                            $scenario, 
                            $model, 
                            $time, 
                            $species_files
                      )
    {
    
        
        
        $this->UpdateStatus("start richness calculations {$scenario} {$model} {$time}");

        $combination = "{$scenario}_{$model}_{$time}";
        
        
        $scenario_time_richness_filename = GenusData::data_folder($this->genus())."{$combination}.asc";
        
        
        // check to see if we already have the Richness file 
        if (file_exists($scenario_time_richness_filename))
        {
            
            $dbResult = $this->loadAsciiGrid($scenario, $model, $time,$scenario_time_richness_filename);                    
            if ($dbResult instanceof ErrorMessage)
            {
                $this->AsciiError($dbResult);
                return null;
            }
            
            // no error so return filename that has be generated beofre
            return $scenario_time_richness_filename;
            
        }
        


        $scenario_time_species_files = $species_files;
        

        $this->UpdateStatus("{$combination} Species Count = ".count($scenario_time_species_files));


        // ### HERE is where we do the RICHNESS CALC
        $scenario_time_richness_filename = $this->CalculateSpeciesRichness($scenario_time_species_files,$scenario_time_richness_filename,null,&$error);         
        if ($scenario_time_richness_filename instanceof ErrorMessage)
        {
            $this->addError($scenario_time_richness_filename);
            return null;
        }
        
        
        if (!file_exists($scenario_time_richness_filename))
        {
            $this->addError("After process richness file not found {$scenario_time_richness_filename}");
            return null;
        }
        
        
        $loadResult = $this->loadAsciiGrid($scenario, $model, $time,$scenario_time_richness_filename,true);                    
        if ($loadResult instanceof ErrorMessage)
        {
            $this->addError($loadResult);
            return null;
        }


        return $scenario_time_richness_filename;


    }
    
    
    /**
     * LOad the ascii grid create ino the Database
     * 
     * @param type $scenario
     * @param type $model
     * @param type $time
     * @param type $scenario_time_richness_filename
     * @return null|\ErrorMessage 
     */
    private function loadAsciiGrid($scenario, $model, $time,$scenario_time_richness_filename,$reuse = true)
    {
        
        if (!$this->LoadAscii()) return;
        
        //$combination = "{$scenario}_{$model}_{$time}";
        
        $this->UpdateStatus("Load ASCII Grid ".basename($scenario_time_richness_filename));

        
        if ($reuse)
        {
            
            $data = GenusData::GetProjectedFiles($this->genus(), 'ASCII_GRID', $scenario, $model, $time);
            if ($data instanceof ErrorMessage) 
            {
                $this->addError($data);
                return null;            
            }
            
            if (count($data) > 0 )
            {
                $first = util::first_key($data);
                return $first;
            }
                
        }
            
        
        $dbresult = GenusData::InsertProjectedFile($this->genus(),$scenario_time_richness_filename,'ASCII_GRID',$scenario, $model, $time,true);
        if ($dbresult instanceof ErrorMessage) 
        {
            $this->addError($dbresult);
            return null;            
        }
        
        $this->addASCIIGRID_FileUniqueID($scenario, $model, $time, $dbresult);
        
        return $dbresult;
        
    }

    
    
    private function deleteForRecalculate($toProcess)
    {

        if (!$this->Recalculate()) return;
        
        $model = "ALL";
        
        $this->ProgressPercentIncrement();
        
        foreach ($toProcess as $scenario => $times) 
        {
            
            foreach ($times as $time => $species_files) 
            {
             
                $combination = "{$scenario}_{$model}_{$time}";

                $output_filename = GenusData::data_folder($this->genus())."{$combination}.asc";

                $remove_result = GenusData::RemoveProjectedFiles($this->genus(), null, $scenario, $model, $time);
                if (is_array($remove_result) ||  $remove_result instanceof ErrorMessage )
                {
                    $this->addError($remove_result);
                    continue;
                }

                $asc = $output_filename;
                $png = str_replace("asc", "png", $output_filename);
                
                if ($remove_result > 0 || file_exists($asc) || file_exists($png))
                {
                    file::Delete($asc);
                    file::Delete($png);
                    $this->UpdateStatus("remove for recalculate for {$combination}");    
                }
                
            }
            
        }
        
        
        $this->ProgressPercentIncrement();
        
    }
    
    


    /**
     *
     * $result structure 
     * 
     * [scenario1]
     *         [time1]
     *         [time2]
     * 
     * [scenario2]
     *         [time1]
     *         [time2]
     * 
     * 
     * @return type 
     */
    private function getValidMedians()
    {

        $result = array();
        
        foreach ($this->scenarios() as $scenario) 
        {
            $this->ProgressPercentIncrement();
            
            foreach ($this->times() as $time)     
            {
                $this->ProgressPercentIncrement();
                $result[$scenario][$time] =  $this->validateMediansFor($scenario,$time);   // could be null                 
            }
            
        }
        
        return $result;
        
    }
    
    /**
     *
     * 
     * @param type $scenario
     * @param type $time
     * @return array        list of file paths that go to making this Genus / Scenario Time 
     */
    private function validateMediansFor($scenario,$time)
    {
        
        // we have to have a m,edian file for all species in this genus for this $scenario $time
     
        $ok = true;
        
        $result = array();
        
        // find all the files for this species List for a single  - $scenario,$time
        
        $pattern = "{$scenario}_ALL_{$time}_median.asc";
        
        foreach ($this->SpeciesList() as $species_id => $row ) 
        {
            
            $filename = SpeciesData::species_data_folder($species_id).$pattern;
            
            if (!file_exists($filename))
            {
                $this->addError(new ErrorMessage(__METHOD__, __LINE__, "Inputfile to creatr richness for  $scenario $time  does not exist  {$filename} ", true));
                $ok = false;
                continue;
            }
            

            if (!$this->ValidateExistenceOnly())
            {
                
                $stats = spatial_util::RasterStatisticsBasic($filename);
                if ($stats instanceof Exception) 
                {
                    $this->addError(new ErrorMessage(__METHOD__, __LINE__, "Inputfile statsistics are invalid for   $scenario $time  [{$filename}] \n".$stats->getMessage(), true));
                    $ok = false;
                    continue;                    
                }
                if (is_null($stats)) 
                {
                    $this->addError(new ErrorMessage(__METHOD__, __LINE__, "Inputfile statsistics are invalid for   $scenario $time  [{$filename}] ", true));
                    $ok = false;
                    continue;                    
                }
                                 
            }
            
            $result[$species_id] = $filename;  // only add files that exist and are valid
            
            
        }

        // but if any files were not valid then return null; as this whole scenario time is invalid
        if (!$ok) return  null;
        
        
        return $result;
    }
    
    
    
    



    /**
     * For OIne Scenario for One Time point for all species in Genus
     * 
     * 
     * @param type $species_files
     * @param type $output_filename
     * @param type $null_value
     * @return \Exception 
     */
    private function CalculateSpeciesRichness($scenario_time_species_files,$output_filename, $null_value = null)
    {
        
        
         // $species_files  species id to filename 
        
        $species_ids = array_keys($scenario_time_species_files);
        
        $thresholds = $this->MaxentThresholds();
        
        
        // assume all files are standard and all align
        // make want to build check for this soon
        
        // try to find the null data from the first file
        if (is_null($null_value))
            $null_value = spatial_util::asciigrid_nodata_value(util::first_element($scenario_time_species_files));

        
        
        $first_species_id = util::first_element($species_ids); // use this a controller for th other files

        
        
        $handles = array();

        $lineCounts = file::lineCounts($scenario_time_species_files,true);  // we can use this check if any file is not right
        $lineCountFirst = util::first_element($lineCounts) ;
        
        
        // open all files
        foreach ($scenario_time_species_files as  $species_id => $filename)  
        {
            if (!file_exists($filename))  
                return new ErrorMessage(__METHOD__,__LINE__,"Could not open for ".__METHOD__." {$filename}");
                
            $handles[$species_id] = fopen($filename, "rb");
        }
            
        
        
        // ASCII files have 6 rowsa of "metadata" skip those
        for ($index = 0; $index < 6; $index++) {
            foreach ($handles as  $species_id => $handle)  
                fgets($handle); // read lines from all files     -  skipping lines
        }
        
        
        
        $result = array();
        // we can only read one line at a time from each file 
        // as we don't have enough memory to to read all files in memory at one time
        for ($lineNum = 6; $lineNum < $lineCountFirst ; $lineNum++) {
        
            
            // create row (of cells) for each file open
            $cells = array();
            foreach ($handles as  $species_id => $handle) 
                $cells[$species_id] = explode(" ",fgets($handle));  // load a line from each file - and convert to cells

            
            // process a line for each file 
            // species ID =>  array() or values fro that file 
            
            // process across  line
            $result_row = array();
            foreach (array_keys($cells[$first_species_id]) as $xIndex ) 
            {
            
                // what we have to do is look at each cell and if the cell value for the species is below threshold then 
                // the count for this cell is NOT increased
                // otherwise the count for cell is increased
                
                $result_row[$xIndex] = 0; // assume ZERo species already here
                
                foreach ($species_ids as $species_id) 
                {
                    $species_cell_value = $cells[$species_id][$xIndex];
                    
                    if ($species_cell_value == $null_value) continue;
                    if ($species_cell_value < $thresholds[$species_id]) continue;
                    
                    
                    $result_row[$xIndex]++; // result here is a species count for this gird cell
                    
                }
                
                if ($result_row[$xIndex] == 0) $result_row[$xIndex] = $null_value;
                
            }
            
            $result[] = implode(" ", $result_row); // grid cells across this line with a count of species 
            
            unset($cells);
            
        }
        
        foreach ($handles as $handle) fclose($handle);  //close all files

                                      // use the Metadata of the first file as the header for the output
        $file_result =   implode("\n",file::Head(util::first_element($scenario_time_species_files), 6))."\n"   
                        .implode("\n",$result).
                        "\n";
        
        
        // $this->UpdateStatus("Going to write ".count(explode("\n",$file_result)) ." lines to {$output_filename}");
        
        file_put_contents($output_filename, $file_result);

        if (!file_exists($output_filename)) 
            return new ErrorMessage(__METHOD__,__LINE__,"Failed to Write file for Median {$output_filename}");

            
        $outputFileLineCount  = file::lineCount($output_filename);

        // check to see if output file line count = $lineCountFirst
        if ($lineCountFirst != $outputFileLineCount)
            return new ErrorMessage(__METHOD__,__LINE__,"Failed to create Median number of input and output lines don't match  $lineCountFirst != $outputFileLineCount ");
        
        
        return $output_filename;
        
    }
    
    

    
    private function getParameters()
    {
        if (is_null($this->LoadAscii()))     $this->LoadAscii(false);
        if (is_null($this->LoadQuickLook())) $this->LoadQuickLook(true);
        if (is_null($this->Recalculate()))   $this->Recalculate(false);

        
        if (is_null($this->scenario())) 
            $this->scenarios(DatabaseClimate::GetScenarios());
        else
            $this->scenarios(explode(",",$this->scenario()));
        
//        if (is_null($this->model())) 
//            $this->models(DatabaseClimate::GetModels());
//        else

        
        // Hstsots onlky works with medians at the moment
        $this->models(explode(",",'ALL'));  // default to the ALL model
        
        
        if (is_null($this->time())) 
            $this->times(DatabaseClimate::GetTimes());
        else
            $this->times(explode(",",$this->time()));
        
        
        
        $scenarios = array();
        foreach ($this->scenarios() as $scenario) 
            if ($scenario != "CURRENT") $scenarios[$scenario] = $scenario;
        
        $this->scenarios($scenarios);
        
        
        $models = array();
        foreach ($this->models() as $model) 
            if ($model != "CURRENT") $models[$model] = $model;
        
        $this->models($models);
        
        
        $times = array();
        foreach ($this->times() as $time) 
            if ($time >= 2000) $times[$time] = $time;
        
        $this->times($times);
        
        

        
        
    }
    
    private function getSpeciesList()
    {
        $species_list_query_result = null;
        
        if (!is_null($this->clazz() ) )
        {
            $species_list_query_result =  
                SpeciesData::TaxaForGenusWithOccurances($this->clazz(), $this->MinimumOccurance());
            
        }
            
        
        if (!is_null($this->family()) ) 
        {
            $species_list_query_result =  
                SpeciesData::TaxaForGenusWithOccurances($this->family(),$this->MinimumOccurance());
            
        }            
        
        
        if (!is_null($this->genus() ) ) 
        {
            $species_list_query_result =  
                SpeciesData::TaxaForGenusWithOccurances($this->genus(), $this->MinimumOccurance());             
            
        }
        
        $this->ProgressPercentIncrement();
        
        // convert query to species ID / species name list
        $species_list = array();
        foreach ($species_list_query_result as $row) 
            $species_list[$row['species_id']] = $row['species'];
        
        $this->ProgressPercentIncrement();
        
        
        return $species_list;
        
    }
        
    
    
    
    
    public function model() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }
    
    public function scenario() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }
    
    public function time() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }

    protected function models() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }
    
    protected function scenarios() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }
    
    protected function times() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }
    
    protected function bioclims() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }
    
    
    public function clazz() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }
    

    public function family() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }

    
    public function genus() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }
    

    
    
    private function SpeciesList() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }

    public function MaxentThresholds() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }
    

    
    public function LoadAscii() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }

    public function LoadQuickLook() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }
    

    public function Recalculate() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }

    public function MinimumOccurance() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }
    
    public function ValidateExistenceOnly() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }

    public function DisplaySummary() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }
    
    
    private function QuickLooks() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }

    private function QuickError() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }
    
    
    private function AsciiGrids() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }
    
    private function AsciiError() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }

    
    /**
     * OUTPUT
     * @return type 
     */
    public function Invalid() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }

    
    /**
     * OUTPUT
     * @return type 
     */
    public function Missing() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }

    /**
     * OUTPUT
     * @return type 
     */
    public function Error() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }


    public function QuickLook_FileUniqueIDs() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }
    
    
    public function ASCIIGRID_FileUniqueIDs() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }
    
    public function ProgressPercent() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }
    
    
    private function ProgressPercentIncrement( $size = 1) 
    {
        $curr = $this->ProgressPercent();    
        
        if ($curr >= 100 ) $curr = $curr - 5;
        
        $curr += $size;
        $this->ProgressPercent($curr);
    }
    
    

    
    
    
    protected function UpdateStatus($msg)
    {
        
        $this->Status($msg);
        
        if (php_sapi_name() == "cli")
        {
            ErrorMessage::Marker($msg);
            self::Queue($this);    
        }
        else 
        {
            self::Queue($this);    
        }
        
        
        
    }
    
    
    
    
    
}


?>
