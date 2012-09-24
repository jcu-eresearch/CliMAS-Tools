<?php

/**
 * 
 * 
 */
class SpeciesRichness extends CommandAction {
    
    
    public function __construct() {
        parent::__construct();
        
        $this->Error(array());
        $this->AsciiGrids(array());
     
        $this->ValidateExistenceOnly(false);
        $this->MinimumOccurance(10);
        $this->Result('');
        
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

        $this->UpdateStatus("Started richness calculations");
        
        $this->ProgressPercent(0);
        
        
        $this->getParameters();
        
        $this->getSpeciesList();
        
        $this->getThresholds();
        
        
        $this->calculateRichness();  

        
        $this->getResults();
        
        $this->ProgressPercent(100);
        
        $this->UpdateStatus("Richness Calculations complete");
        
        
    }

    private function outputFolder()
    {
        
        $result = configuration::Maxent_Species_Data_folder().
                  'richness'.
                  configuration::osPathDelimiter().
                  $this->ID().
                  configuration::osPathDelimiter()
                  ;
     
        file::mkdir_safe($result);
        
        return $result;
        
    }
    
    
    
    private function getThresholds()
    {
        
        $result = array();
                         
        foreach ($this->SpeciesList() as $species_id) 
        {
            
            $threshold_result = DatabaseMaxent::GetMaxentThresholdForSpecies($species_id);
            if ($threshold_result instanceof ErrorMessage)
            {
                $this->addError($threshold_result);
                continue;
            }
            
            // record threshold for the species_id
            if (!is_null($threshold_result) && is_numeric($threshold_result)) $result[$species_id] = $threshold_result;
            
        }
    
        $this->ProgressPercentIncrement();
        
        $this->MaxentThresholds($result);
        
        
    }

    
    
    private function calculateRichness()
    {
        
        $toProcess = $this->getValidMedians();        
    
        foreach ($this->scenarios() as $scenario) 
        {
            
            foreach ($this->times() as $time ) 
            {
                
                $combination = "{$scenario}_{$time}";
                
                $species_files = $toProcess[$combination];
                
                ErrorMessage::Marker("combination = {$combination}  species_files = ".count($species_files)." \n".print_r($species_files,true));
                
                if (is_null($species_files) || count($species_files) == 0)
                {
                    $this->addAsciiGridResult($combination);  // add empty combination
                    continue;
                }

                // process files into a mrichness
                $this->process_richness_for_set_of_species_median_files($combination,$species_files);
                    
                $this->ProgressPercentIncrement();
                
            }
            
            
        }

        
        //  $toProcess[scenario][time] => array( Species medians) 
        
        $this->processRichnessQuicklooks(); //  create quick looks for all files for this richness calc.
        
        $this->ProgressPercentIncrement();
        
        
    }
    
    

    
    
    /**
     * All quicklooks for a single scenario  have to have the same number of Species in the min max range
     * ie all the times slices 
     * @param type $scenario
     * @param type $time
     * @param type $scenario_time_richness_filename 
     */
    private function processRichnessQuicklooks()
    {
        
        $this->UpdateStatus("Create QuickLooks");
        
        $richness_min = null;
        $richness_max = null;
        
        $this->getStatisticsForAllRichnessFiles(&$richness_min,&$richness_max);
        
        if (is_null($richness_min) || is_null($richness_max))
        {
            ErrorMessage::Marker("Unable to get valid min and max to create quick looks richness_min = $richness_min   richness_max = $richness_max");
            
            $this->addError(new ErrorMessage(__METHOD__, __LINE__, "Unable to get valid min and max to create quick looks richness_min = $richness_min   richness_max = $richness_max"));
            $richness_min = null;
            $richness_max = null;
            return null;
        }

        
        foreach ($this->AsciiGrids() as $combination => $richness_asciigrid_filename)
        {
            ErrorMessage::Marker("processRichnessQuicklooks combination = $combination   richness_asciigrid_filename = $richness_asciigrid_filename");
            
            $this->createQuicklookForAscii($richness_asciigrid_filename,$combination,$richness_min,$richness_max,true);
        }

        
    }
    
    private function createQuicklookForAscii($richness_asciigrid_filename,$combination,$richness_min,$richness_max,$reuse = false) 
    {
        
        $richness_asciigrid_pathname = $this->outputFolder().basename($richness_asciigrid_filename);

        ErrorMessage::Marker("createQuicklookForAscii combination = $combination   richness_asciigrid_pathname = $richness_asciigrid_pathname");
        
        
        if (!file_exists($richness_asciigrid_pathname)) 
        {
            $this->addError(new ErrorMessage(__METHOD__, __LINE__, "Can't find richness_asciigrid_filename {$richness_asciigrid_pathname}"));
            return null;
        }
        

        $richness_quicklook_pathname = str_replace(".asc", ".png", $richness_asciigrid_pathname);
        
        // remove old version of file.
        if (file_exists($richness_quicklook_pathname) && $reuse == false) file::Delete($richness_quicklook_pathname);
        
        
        if (!file_exists($richness_quicklook_pathname))
        {
            $this->UpdateStatus("Create QuickLook Image for {$combination}");    
            
            
            // #### HERE WE NEED TO CREATE A CROSS SPECIES / GENUS / FAMILY / TAX RICHNESS
            
            $richness_quicklook_pathname = $this->CreateQuickLookImage(
                                     $this->job_description()
                                    ,$richness_asciigrid_pathname
                                    ,$richness_min
                                    ,$richness_max
                                    ,$richness_quicklook_pathname
                                    ); // create quickLook from ascii
            
            
            
            if ($richness_quicklook_pathname instanceof ErrorMessage)
            {
                $this->addError($richness_quicklook_pathname);
                return null;
            }
            
            $this->UpdateStatus("Created richness QuickLook for {$this->job_description()} {$combination}");
        }        

        
        $this->addQuickLook($combination,$richness_quicklook_pathname); // keep track of file that was created

        
        
    }
    
    
    /**
     * get the min and max across an array of ascii grids
     * 
     * @param type $richness_min
     * @param type $richness_max
     * @return type 
     */
    private function getStatisticsForAllRichnessFiles(&$richness_min,&$richness_max)
    {
        
        ErrorMessage::Marker("getStatisticsForAllRichnessFiles AsciiGrids()");
        
        print_r($this->AsciiGrids());
        
        $full_paths = array();
        foreach ($this->AsciiGrids() as $combination => $filename) 
        {
            $full_paths[$combination] = $this->outputFolder().basename($filename);
        }
        
        ErrorMessage::Marker("getStatisticsForAllRichnessFiles Full Paths");
        
        print_r($full_paths);
        
        
        $richness_stats = spatial_util::ArrayRasterStatistics($full_paths);

        
        ErrorMessage::Marker("getStatisticsForAllRichnessFiles richness_stats");
        print_r($richness_stats);
        
        
        if (is_null($richness_stats) || $richness_stats instanceof ErrorException || count($richness_stats) == 0)
        {
            $richness_min = null;
            $richness_max = null;
            ErrorMessage::Marker("getStatisticsForAllRichnessFiles richness_stats error \n".print_r($richness_stats,true));
            return;
        }            
        
        
        
        $mins = matrix::Column($richness_stats, spatial_util::$STAT_MINIMUM);
        $maxs = matrix::Column($richness_stats, spatial_util::$STAT_MAXIMUM);
        
        
        if (is_null($mins) || is_null($maxs) )
        {
            $richness_min = null;
            $richness_max = null;
            ErrorMessage::Marker("getStatisticsForAllRichnessFiles richness_stats error BOTH min and max are null ");
            return;
        }
        
        if (count($mins) == 0 || count($maxs) == 0)
        {
            $richness_min = null;
            $richness_max = null;
            ErrorMessage::Marker("getStatisticsForAllRichnessFiles richness_stats error BOTH min and max count 0");
            return;
        }

        
        if (count($mins) == 1 )
            $richness_min = util::first_element($mins);
        else
            $richness_min = min( $mins);
        
        
        
        if (count($maxs) == 1 )
            $richness_max = util::first_element($maxs);
        else
            $richness_max = max( $maxs);
        
        
        
        $this->UpdateStatus("richness minimum = $richness_min  maximum = $richness_max");
        
        
        ErrorMessage::Marker("getStatisticsForAllRichnessFiles richness_stats richness minimum = $richness_min  maximum = $richness_max");
        
        
    }

    
    /**
     * Create Species Richness for a Scenario / Time 
     * 
     * @param type $combination
     * @param type $species_files
     * @return string|null|\ErrorMessage 
     */
    private function process_richness_for_set_of_species_median_files($combination,$species_files)
    {
        
        $this->UpdateStatus("start richness calculations {$combination}");
        
        $richness_filename = $this->outputFolder()."{$combination}.asc";  // here we need the full filename so we kknow where to put it
        
        
        ErrorMessage::Marker("process_richness_for_set_of_species_median_files ... richness_filename = {$richness_filename}");
        
        // check to see if we already have the Richness file 
        if (file_exists($richness_filename)) return $richness_filename; // doubtfull as this is per user 
        

        $this->UpdateStatus("{$combination} Species Count = ".count($species_files));

        
        // ##################################################
        // ### HERE is where we do the RICHNESS CALC
        // ##################################################
        
        $richness_filename = $this->CalculateSpeciesRichness($species_files,$richness_filename,null);
        
        
        if ($richness_filename instanceof ErrorMessage)
        {
            $this->addError($richness_filename);
            $richness_filename = null;
        }
        
        
        if (!file_exists($richness_filename))
        {
            $this->addError("After process richness file not found {$richness_filename}");
            $richness_filename = null;
        }

        
        $this->addAsciiGridResult($combination,$richness_filename);   // we can't return complete file name just the basename
                                                                      // as we will be reading this froma  different system


    }
    


    /**
     *
     * @return type 
     */
    private function getValidMedians()
    {

        $result = array();
        
        foreach ($this->scenarios() as $scenario) 
        {
            
            foreach ($this->times() as $time)     
            {
                $this->ProgressPercentIncrement();
                
                ErrorMessage::Marker("Validate Medians for {$scenario}_{$time}");
                
                $result["{$scenario}_{$time}"] =  $this->validateMediansFor($scenario,$time);   // could be null                 
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
        
        // we have to have a median file for all species in this selection for this $scenario $time
     
        $ok = true;
        
        $result = array();
        
        // find all the files for this species List for a single  - $scenario $time
        
        $pattern = "{$scenario}_ALL_{$time}.asc"; // filename to be found as this is create from ScenarioTimeMediansForSpecies
        
        
        foreach ($this->SpeciesList() as $species_id ) 
        {
            
            $filename = SpeciesData::species_data_folder($species_id).$pattern;

            ErrorMessage::Marker("Validate Medians filename = {$filename}");
            
            // look for the median file for this species 
            if (!file_exists($filename))
            {
                
                ErrorMessage::Marker("Validate Medians creating median files for species {$species_id}");
                
                // if we can't find the median of all climate models for this species then try create it.
                $this->UpdateStatus("creating median files for species species_id = {$species_id}");
                
                $result = SpeciesData::ScenarioTimeMediansForSpecies($species_id,$scenario,$time,false,false);
                
                if ($result instanceof ErrorMessage)
                {
                    $this->addError(new ErrorMessage(__METHOD__, __LINE__, "FAILED to create Median file for species_id = $species_id ".print_r($result,true)));
                    $ok = false;
                    continue;
                }
                
                // check for median file again
                if (!file_exists($filename))
                {
                    $ok = false;
                    continue;
                }
                
                
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
        if ($ok == false) return  null;
        
        
        return $result;
    }
    
    
    
    



    /**
     * for one Scenario / One Time point for all species in the selection 
     * calculate the richness
     * 
     * @param type $species_files
     * @param type $output_filename
     * @param type $null_value
     * @return \Exception 
     */
    private function CalculateSpeciesRichness($species_files,$output_filename, $null_value = null)
    {
        
        ErrorMessage::Marker(__METHOD__);
        
         // $species_files  species id to filename 
        
        $species_ids = array_keys($species_files);
        
        $thresholds = $this->MaxentThresholds();
        
        
        ErrorMessage::Marker("thresholds");
        ErrorMessage::Marker($thresholds);
        // assume all files are standard and all align want to build check for this soon
        
        // try to find the null data from the first file
        if (is_null($null_value)) $null_value = spatial_util::asciigrid_nodata_value(util::first_element($species_files));

        
        $first_species_id = util::first_element($species_ids); // use this a controller for the other files
        
        
        $CountValues = array();
        $CountTotal = array();
        
        $handles = array();

        $lineCounts = file::lineCounts($species_files,true);  // we can use this check if any file is not right
        $lineCountFirst = util::first_element($lineCounts) ;
        
        
        // open all files
        foreach ($species_files as  $species_id => $filename)  
        {
            if (!file_exists($filename))  
                return new ErrorMessage(__METHOD__,__LINE__,"Could not open for ".__METHOD__." {$filename}");
                
            $handles[$species_id] = fopen($filename, "rb");
            
            $CountValues[$species_id] = 0;
            $CountTotal[$species_id] = 0;
            
            
        }
            
        
        ErrorMessage::Marker("handles count = ".count($handles));
        ErrorMessage::Marker($handles);
        
        
        
        // ASCII files have 6 rowsa of "metadata" skip those
        for ($index = 0; $index < 6; $index++) {
            foreach ($handles as  $species_id => $handle)  
                fgets($handle); // read lines from all files     -  skipping lines
        }
        
        
        
        $result = array();
        // we can only read one line at a time from each file 
        // as we don't have enough memory to to read all files in memory at one time
        for ($lineNum = 6; $lineNum < $lineCountFirst ; $lineNum++) {
        
            
            // ErrorMessage::Marker("lineNum = $lineNum");
            
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
                    
                    $CountTotal[$species_id]++;
                    
                    $species_cell_value = $cells[$species_id][$xIndex];
                    
                    if ($species_cell_value == $null_value) continue;
                    if ($species_cell_value < $thresholds[$species_id]) continue;
                    
                    
                    $CountValues[$species_id]++;
                    
                    $result_row[$xIndex]++; // result here is a species count for this gird cell
                    
                }
                
                if ($result_row[$xIndex] == 0) $result_row[$xIndex] = $null_value;
                
            }
            
            $result[] = implode(" ", $result_row); // grid cells across this line with a count of species 
            
            unset($cells);
            
        }
        
        foreach ($handles as $handle) fclose($handle);  //close all files


        ErrorMessage::Marker("CountTotal count = ".count($CountTotal));
        ErrorMessage::Marker($CountTotal);
            
            
        ErrorMessage::Marker("CountValues count = ".count($CountValues));
        ErrorMessage::Marker($CountValues);
        
                                      // use the Metadata of the first file as the header for the output
        $file_result =   implode("\n",file::Head(util::first_element($species_files), 6))."\n"   
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
        
        if (is_null($this->scenarios())) 
            $this->scenarios(DatabaseClimate::GetScenarios());
        else
        {
            if (!is_array($this->scenarios()))
                $this->scenarios(explode(",",$this->scenarios()));
        }
        
        
        $scenarios = array();
        foreach ($this->scenarios() as $scenario) 
            if ($scenario != "CURRENT") $scenarios[$scenario] = $scenario;
            
        $this->scenarios($scenarios);
        
        ErrorMessage::Marker("Scenarios " .print_r($this->scenarios(),true));
       
        
        ErrorMessage::Marker("Before Times " .print_r($this->times(),true));
        
        
        if (is_null($this->times())) 
            $this->times(DatabaseClimate::GetTimes());
        else
        {
            if (!is_array($this->times()))
                $this->times(explode(",",$this->times()));
        }
        
        
        
        $times = array();
        foreach ($this->times() as $time) 
            if ($time >= 2000) $times[$time] = $time;
        
        $this->times($times);
        
        ErrorMessage::Marker("After Times " .print_r($this->times(),true));

        $this->UpdateStatus("Parameters set");
        
        
    }
    
    private function getSpeciesList()
    {
     
        // for each Clazz, Family, Genus and Species get the Species ID and Name
        // and add to the list
        
        $species_ids = array();
        $this->ProgressPercentIncrement();        
        if (!is_null($this->clazz() ) )
        {
            if (is_array($this->clazz()))
                foreach ($this->clazz() as $clazz_name) 
                    foreach (SpeciesData::TaxaForClazzWithOccurances($clazz_name) as $species_id => $row) 
                        $species_ids[$species_id] = $species_id;
            else
                foreach (SpeciesData::TaxaForClazzWithOccurances($this->clazz()) as $species_id => $row) 
                    $species_ids[$species_id] = $species_id;
            
        }

        $this->ProgressPercentIncrement();        
        if (!is_null($this->family() ) )
        {
            if (is_array($this->family()))
                foreach ($this->family() as $family_name) 
                    foreach (SpeciesData::TaxaForfamilyWithOccurances($family_name) as $species_id => $row) 
                        $species_ids[$species_id] = $species_id;
            else
                foreach (SpeciesData::TaxaForfamilyWithOccurances($this->family()) as $species_id => $row) 
                    $species_ids[$species_id] = $species_id;
            
        }
        
        
        $this->ProgressPercentIncrement();
        if (!is_null($this->genus() ) )
        {
            if (is_array($this->genus()))
                foreach ($this->genus() as $genus_name) 
                    foreach (SpeciesData::TaxaForgenusWithOccurances($genus_name) as $species_id => $row) 
                        $species_ids[$species_id] = $species_id;
            else
                foreach (SpeciesData::TaxaForgenusWithOccurances($this->genus()) as $species_id => $row) 
                    $species_ids[$species_id] = $species_id;
            
        }

        
        $this->ProgressPercentIncrement();
        if (!is_null($this->species() ) )
        {
            
            if (is_array($this->species()))
                foreach ($this->species() as $species_id) 
                    $species_ids[$species_id] = $species_id;
            else
                $species_ids[$this->species()] = $this->species();
            
            
        }
        
        
        
        
        // get species name for $species_ids
        $this->ProgressPercentIncrement();
        
        
        $this->SpeciesList($species_ids);
        
        $this->UpdateStatus("Species Count: " . count($this->SpeciesList()));
        
        print_r($species_ids);
        
        
        
    }
    
    public function scenarios() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }
    
    public function times() {
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
    
    public function species() {
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


    
    
    private function QuickLooks() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }
    
    private function AsciiGrids() {
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


    
    
    
    public function ProgressPercent() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }

    public function job_description() {
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
    
    
    protected function UpdateStatus($msg,$execution_flag = null)
    {
        
        if (!is_null($execution_flag))  $this->ExecutionFlag($execution_flag);
        
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
    
    protected function UpdateExecutionFlag($execution_flag)
    {
        
        $this->ExecutionFlag($execution_flag);
        
        if (php_sapi_name() == "cli")
        {
            ErrorMessage::Marker("Execution Flag changed to: {$execution_flag}");
            self::Queue($this);    
        }
        else 
        {
            self::Queue($this);    
        }
        
        
        
    }
    
    
    
    /**
     * Added the ascii grid that was create into a memory array
     * 
     * @param ErrorMessage $gridFilename
     * @return null 
     */
    private function addAsciiGridResult($combination,$single_richness_filename = null)
    {
        
        ErrorMessage::Marker("addAsciiGridResult ... single_richness_filename = $single_richness_filename");
        
        
        if ($single_richness_filename instanceof ErrorMessage)
        {
            $this->UpdateStatus("FAILED to create richness for {$combination}");
            $this->addError($single_richness_filename);
        }
        
        // we could not create richness
        if (is_null($single_richness_filename) || !file_exists($single_richness_filename)) 
        {
            $this->UpdateStatus("Error creating richness for {$combination}");
            $this->addError(new ErrorMessage(__METHOD__, __LINE__,"Error creating richness for {$this->job_description()} {$combination}", true) );            
        }
        
        if (!is_null($single_richness_filename))
            $this->UpdateStatus("Created richness for {$combination}");
        
        
        $this->addAsciiGridCombination($combination,$single_richness_filename);
        
        
    }

    private function addAsciiGridCombination($combination,$single_richness_filename = null)
    {
        $ag = $this->AsciiGrids();
        $ag[$combination] = $this->ID().configuration::osPathDelimiter().basename($single_richness_filename);
        $this->AsciiGrids($ag);
        
    }

    
    private function addQuickLook($combination, $src)
    {
        $ql = $this->QuickLooks();
        $ql[$combination] = $this->ID().configuration::osPathDelimiter().basename($src);
        $this->QuickLooks($ql);
        
    }

    

    private function addError($src)
    {
        
        $err = $this->Error();
        
        if (is_array($src))
            foreach ($src as $value)  
                $err[] = $value;
        else
            $err[] = $src;    
        
        ErrorMessage::Marker($err);
        
        $this->Error($err);
        
    }
    
    
    private function getResults()
    {
        
        $lines = array();
        foreach ($this->AsciiGrids() as $combination => $filename) 
            $lines[] = "{$combination}={$filename}";

            
        $str = implode("~", $lines);
        
        $this->Result($str);
        
        
    }
    
 
    /**
    *
    * @param type $src_grid_filename      - Maxent ASC Grid with filename in format of      (Scenario)_(time).asc
    * @param type $output_image_filename  - Where you want to output mage to end up 
    * @param type $transparency           - transparency of all colors 
    * @param type $background_colour      - background colour   use 0 0 0 255 = Full balck   0 0 0 0 = Full Transparent
    * @return null|String                 - Output filename 
    */
    public function CreateQuickLookImage($name,$src_grid_filename,$min = null,$max = null,$output_image_filename = null ,$transparency = 255,$background_colour = "0 0 0 255")
    {
    
        
        if (!file_exists($src_grid_filename)) 
            new ErrorMessage(__METHOD__, __LINE__, "$src_grid_filename File Does not exist \n");
        
        
        if (is_null($output_image_filename)) $output_image_filename = str_replace("asc","png",$src_grid_filename);
        
        if (file_exists($output_image_filename)) return $output_image_filename;
        
        
        if (is_null($min) || is_null($max))
        {
            $stats = spatial_util::RasterStatisticsBasic($src_grid_filename);
            
            if (is_null($stats) || $stats instanceof ErrorMessage)
            {
                $min = null;
                $max = null;
            }
            else
            {
                if (is_null($min)) $min = $stats[spatial_util::$STAT_MINIMUM];
                if (is_null($max)) $max = $stats[spatial_util::$STAT_MAXIMUM];
            }
            
        }
        
        if (is_null($min) ||  is_null($max) )
        {            
            $min = null;
            $max = null;
        }

        
        list($scenario, $time) =  explode("_",str_replace('.asc','',basename($src_grid_filename)));    
        
        $histogram_buckets = $max + 1;
        if ($histogram_buckets > 100) $histogram_buckets = 100;
        
        $gradient = RGB::ReverseGradient(RGB::GradientYellowOrangeRed());
        
        $ramp = null;
        if (!is_null($min) &&  !is_null($max) )
        {
            if ($max - $min > 0)
                // only create ramp if we have a valid min and max.
                $ramp = RGB::Ramp($min, $max, $histogram_buckets,$gradient);     
            else
            {
                $ramp = array();
                $ramp[$min] = util::first_element($gradient);
                $ramp[$max] = util::last_element($gradient);
            }
                
        }
        

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

        $header_png = file::random_filename().".png"; // legend  image together
        file::Delete($header_png);
        
        
        if (is_null($output_image_filename))
            $output_image_filename = file::random_filename().".png"; // return image filename
        

                
        // create colour "lookup table"    

        $color_table = "nv 0 0 0 0\n";  // no value
        
        if (!is_null($ramp))
        {
            // we have a ramp so create percentage colour gradient.
            $pcent = 0;
            $step = round( 100 / count($ramp),0);
            foreach ($ramp as $index => $rgb) 
            {
                $rgb instanceof RGB;
                $color_table .= $pcent."% ".$rgb->Red()." ".$rgb->Green()." ".$rgb->Blue()." {$transparency}\n";        
                $pcent += $step;
            }
            
        }

        // save the colour lookup table 
        $fpc = file_put_contents($colour_txt, $color_table);
        if ($fpc === false) return new ErrorMessage(__METHOD__,__LINE__,"Failed to write to colour_txt {$colour_txt}");
            
        
        $cmd = "gdaldem  color-relief {$src_grid_filename} {$colour_txt} -nearest_color_entry -alpha -of PNG {$colour_png}";
        exec($cmd);  // generate a coloured image using colour lookup 

        
        
        // create backgound to put coloured image on top of
        $fpc = file_put_contents($colour_zero_txt, "nv 0 0 0 0\n0% {$background_colour}\n100% {$background_colour}\n"); // default is ALL Values = $background_colour  & No Value  = transparent  
        if ($fpc === false) return new ErrorMessage(__METHOD__,__LINE__,"Failed to create backgound to put coloured image on {$colour_zero_txt}");
        
        
        $cmd = "gdaldem  color-relief {$src_grid_filename} $colour_zero_txt -nearest_color_entry -alpha -of PNG {$colour_background_png}";
        exec($cmd);


        // order here is important first is lowest
        $cmd = "convert {$colour_background_png} {$colour_png} -layers flatten {$colour_combined_png}";
        exec($cmd);
        
        
        list($width, $height, $type, $attr) = getimagesize($colour_png);     
        
        // - add parameters  to the image  $scenario, $time

$header = <<<HEADER
convert \
-size  {$width}x60 xc:white -font DejaVu-Sans-Book -fill black \
-draw 'text  10,20 "{$name}"' \
-draw 'text  10,40 "Species Richness"' \
-draw 'text 200,20 "Scenario: {$scenario}"' \
-draw 'text 400,20 "Time: {$time}"' \
{$header_png};
HEADER;

        exec($header);


        if (!is_null($ramp))
        {
            // create a legend image
            // # rectangle left,top right,bottom" \
            $swatch_height = 20;
            $swatch_width = 20;
            $swatch_width_padding = 10;
            $text_align_up = -2;


            $height = count($ramp) * $swatch_height + (2 * $swatch_height);  // heioght of the legend is a cal of the number of legend items + 2 for top and bittom padding
            $box_top = 10;
            $box_left = 20;

            $legend  = "convert -size  {$width}x{$height} xc:white ";
            $legend .= "-font DejaVu-Sans-Book ";

            foreach (array_reverse($ramp, true) as $index => $rgb) 
            {
                $rgb instanceof RGB;

                $box_right = $box_left + $swatch_width;
                $box_bottom = $box_top + $swatch_height;

                $text_left = $box_left + $swatch_width + $swatch_width_padding;
                $text_top  = $box_top + $swatch_height + $text_align_up;

                $text = sprintf("%01.2f", $index);

                $legend .= "-fill '#{$rgb->asHex()}' -draw 'rectangle {$box_left},{$box_top} {$box_right},{$box_bottom}' ";
                $legend .= "-fill black -draw 'text {$text_left},{$text_top} \"{$text}\"' ";

                $box_top += $swatch_height;

            }

            $legend .= " {$colour_legend_png}";

            exec($legend); // create legend

            
        }
        

        if (file_exists($colour_legend_png))
            $cmd = "convert {$header_png} {$colour_combined_png} {$colour_legend_png} -append {$output_image_filename}";    
        else 
            $cmd = "convert {$header_png} {$colour_combined_png} -append {$output_image_filename}";    
        
        
        exec($cmd);

        // might be better here to convert to a tmp image
        // and then copy back to the $output_image_filename
        

        file::Delete($colour_txt);
        file::Delete($colour_png);
        file::Delete($colour_zero_txt);
        file::Delete($colour_background_png);
        file::Delete($colour_combined_png);
        file::Delete($colour_legend_png);

        if (!file_exists($output_image_filename))
            return new ErrorMessage(__METHOD__,__LINE__,"Failed to write/ create  outputfile as {$output_image_filename}"); 
            
        
        return $output_image_filename; // filename of png that can be used - 

    }    
    
    
    
}


?>
