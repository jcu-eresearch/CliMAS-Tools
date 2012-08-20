<?php

/**
 * 
 * 
 */
class SpeciesRichness extends CommandAction{
    
    
    public function __construct() {
        parent::__construct();
        
        $this->scenario(null);
        $this->model('ALL');
        $this->time(null);
     
        $this->LoadAscii(false);
        $this->LoadQuickLook(true);
        $this->Recalculate(false);
        $this->ValidateExistenceOnly(false);
        
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
        
        $this->getParameters();
        
        $this->SpeciesList($this->getSpeciesList());
        
        $thresholds = $this->getThresholds(array_keys($this->SpeciesList()),$this->Error());
        
        $this->MaxentThresholds($thresholds);
        
        $richness_results = $this->calculateRichness();  //updates    $this->Missing();  $this->Invalid();  $this->Error();
        
        
        ErrorMessage::Marker(" ========================= ERRORs ========================= ");
        print_r($this->Error());
        ErrorMessage::Marker(" ========================= ERRORs ========================= ");

        ErrorMessage::Marker(" ========================= MISSING ========================= ");
        print_r($this->Missing());
        ErrorMessage::Marker(" ========================= MISSING ========================= ");
        
        
        
    }

    
    private function calculateRichness()
    {
        
        $toProcess = $this->getValidMedians();
        
        $this->deleteForRecalculate($toProcess); // delete richness entries and files - only if requested
        
        $model = "ALL";
        
        $error = $this->Error();
        $missing = $this->Missing();
        $invalid = $this->Invalid();
        
        
        $richness_results = array();
        
        foreach ($toProcess as $scenario => $times) 
        {
            
            foreach ($times as $time => $species_files) 
            {
                
                /*                
                * [scenario1]
                *         [time1]
                *              ['complete']               use this list of files to produce species richness
                *              ['missing']                if this list has a count > 0 then this result is valid as we are missing inputs
                *              ['species_stats_invalid']  inavlid files that need t be recalculated
                *         [time2]
                *              ['complete']
                *              ['missing']
                *              ['species_stats_invalid']
                */              

                
                ErrorMessage::Marker("{$scenario}  {$time}  pre process_richness_for_set_of_species_median_files ");
                
                
                
               $single_richness_filename = 
                $this->process_richness_for_set_of_species_median_files
                        (
                            $scenario, 
                            $model, 
                            $time, 
                            $species_files,
                            &$error,
                            &$missing,
                            &$invalid
                        );
               
                // These array  will be updated 
                // $error,
                // $missing_files,
                // $invalid_files
               
               
               if (!is_null($single_richness_filename)) 
               {
                   ErrorMessage::Marker("{$scenario}  {$time}  single_richness_filename =  {$single_richness_filename}");
                   $richness_results[$scenario][$time] = $single_richness_filename;
               }
               else
               {
                   if (count($this->Error()) > 0) print_r($this->Error());
                   
               }
               
            }
            
            
            
            $this->processRichnessQuicklooks($richness_results,$error); // updates  $this->Error();
            
            
            
        }

        
        $this->Error($error);
        $this->Missing($missing);
        $this->Invalid($invalid);
        
        return $richness_results;
        
        
    }
    
    
    /**
     * All quicklooks for a single scenario  have to have the same number of Species in the min max range
     * ie all the times slices 
     * @param type $scenario
     * @param type $model
     * @param type $time
     * @param type $scenario_time_richness_filename 
     */
    private function processRichnessQuicklooks($richness_results,&$error = null)
    {
        
        if (!is_null($error)) $error = array();
        
        $model  = "ALL";
        
        $result = array();
        
        foreach ($richness_results as $scenario => $times) 
        {
            
            $times_stats = spatial_util::ArrayRasterStatistics($times);
            
            $scenario_min = min(matrix::Column($times_stats, spatial_util::$STAT_MINIMUM));
            $scenario_max = max(matrix::Column($times_stats, spatial_util::$STAT_MAXIMUM));
            
            print_r($times_stats);

            ErrorMessage::Marker("scenario_min = {$scenario_min}");
            ErrorMessage::Marker("scenario_max = {$scenario_max}");
            
            
            foreach ($times as $time => $richness_asciigrid_filename)
            {
                if (!file_exists($richness_asciigrid_filename)) 
                {
                    ErrorMessage::Marker("Can't fimd {$richness_asciigrid_filename}");
                    continue;
                }
                
                
                ErrorMessage::Marker("Create Image for {$richness_asciigrid_filename}");
                
                $image_filename = GenusData::CreateImage($this->genus(), $richness_asciigrid_filename,$scenario_min,$scenario_max); // create quickLook from ascii
                if ($image_filename instanceof ErrorMessage)
                {
                    $error[] = $image_filename;
                    continue;
                }

                ErrorMessage::Marker("Created {$image_filename}");
                
                $qlResult = $this->loadSingleQuickLook($image_filename,$scenario, $model, $time);
                if ($qlResult instanceof ErrorMessage)
                {
                    $error[] = $qlResult;
                    continue;
                }
                
                $result[$scenario][$time] = $qlResult;
                
                
            }
            
        }
        

        return $result;
        
    }
    
    
    private function loadSingleQuickLook($image_filename,$scenario, $model, $time) 
    {
        
        if (!$this->LoadQuickLook()) return;
        
        
        ErrorMessage::Marker("image_filename = $image_filename");                

        $remove_current_db_files = GenusData::RemoveProjectedFiles($this->genus(),'QUICK_LOOK',$scenario, $model, $time);
        if ($remove_current_db_files instanceof ErrorMessage) return $remove_current_db_files;
        
        
        ErrorMessage::Marker("Quick Look Removed Result {$remove_current_db_files}");


        $dbresult = GenusData::InsertProjectedFile($this->genus(),$image_filename,'QUICK_LOOK',$scenario, $model, $time,true);
        if ($dbresult instanceof ErrorMessage) return $dbresult;

        ErrorMessage::Marker("QUICKLOOK dbresult = $dbresult");

        
        return $dbresult;
        
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
                            $species_files,
                            &$error,
                            &$missing_files,
                            &$invalid_files
                      )
        {
        

        $combination = "{$scenario}_{$model}_{$time}";
        
        
        $scenario_time_richness_filename = GenusData::data_folder($this->genus())."{$combination}.asc";
        
        ErrorMessage::Marker("scenario_time_richness_filename =  {$scenario_time_richness_filename} ");
        
        
        // check to see if we already have the Richness file 
        if (file_exists($scenario_time_richness_filename))
        {
            ErrorMessage::Marker("Already exists {$scenario_time_richness_filename} ");
            $dbResult = $this->loadAsciiGrid($scenario, $model, $time,$scenario_time_richness_filename);                    
            if ($dbResult instanceof ErrorMessage)
            {
                $error[] = "~$combination~ File Already exists and trying to load asciigrid into DB !".print_r($dbResult,true);  
                return null;
            }
            
            
            return $scenario_time_richness_filename;
            
        }
        
        
        
        //
        // FILE does not exists so we need to check on the data going tin to computing it
        //
        
        if (count($species_files['missing']) > 0)  // check for missing 
        {
            array_util::Merge($species_files['missing'], $missing_files);
            $error[] = "~{$combination}~ has ".count($species_files['missing'])." missing Files !".print_r($species_files['missing'],true);  
            return null;
        }

        if (count($species_files['species_stats_invalid']) > 0)  // check for invaid
        {
            array_util::Merge($species_files['species_stats_invalid'], $invalid_files);
            $error[] = "~{$combination}~ has ".count($species_files['species_stats_invalid'])." invalid Files !".print_r($species_files['species_stats_invalid'],true);  
            return null;
        }


        $scenario_time_species_files = $species_files['complete'];
        

        ErrorMessage::Marker(__METHOD__."Process richness for {$combination} file count = ".count($scenario_time_species_files)." out to {$scenario_time_richness_filename}");


        // ### HERE is where we do the RICHNESS CALC
        $scenario_time_richness_filename = $this->CalculateSpeciesRichness($scenario_time_species_files,$scenario_time_richness_filename,null,&$error); 
        
        
        if ($scenario_time_richness_filename instanceof ErrorMessage)
        {
            $error[] = "~$combination~ Failed to create Richness {$scenario_time_richness_filename} !".print_r($scenario_time_richness_filename,true); 
            return null;
        }

        if (!file_exists($scenario_time_richness_filename))
        {
            $error[] = "~$combination~ Richness file does not exist {$scenario_time_richness_filename}"; 
            return null;
        }
        
        
        
        ErrorMessage::Marker("Load ASCII grid after Richness {$scenario_time_richness_filename} ");
        
        $dbResult = $this->loadAsciiGrid($scenario, $model, $time,$scenario_time_richness_filename);                    
        if ($dbResult instanceof ErrorMessage)
        {
            $error[] = "~$combination~ New richness created and Failed to load asciigrid into DB !".print_r($dbResult,true);  
            return null;
        }


        return $scenario_time_richness_filename;


    }
    
    private function loadAsciiGrid($scenario, $model, $time,$scenario_time_richness_filename)
    {
        
        if (!$this->LoadAscii()) return;

        $combination = "{$scenario}_{$model}_{$time}";
        
        ErrorMessage::Marker("Load ASCII Grid {$scenario_time_richness_filename} ");


        ErrorMessage::Marker("Make sure we have no data for this {$combination}");
        
        $remove_current_db_files = GenusData::RemoveProjectedFiles($this->genus(),'ASCII_GRID',$scenario, $model, $time,false);
        if ($remove_current_db_files instanceof ErrorMessage) 
            return $remove_current_db_files;


        ErrorMessage::Marker("remove_current_db_files = $remove_current_db_files ");

        $dbresult = GenusData::InsertProjectedFile($this->genus(),$scenario_time_richness_filename,'ASCII_GRID',$scenario, $model, $time,true);
        if ($dbresult instanceof ErrorMessage) return $dbresult;

        ErrorMessage::Marker("ASCII dbresult = $dbresult");                    
        
        
        return $dbresult;
        
        
    }

    private function deleteForRecalculate($toProcess)
    {

        if (!$this->Recalculate()) return;
        
        $model = "ALL";
        
        foreach ($toProcess as $scenario => $times) 
        {
            
            foreach ($times as $time => $species_files) 
            {
             
                $combination = "{$scenario}_{$model}_{$time}";
                ErrorMessage::Marker(__METHOD__." {$combination} \n");


                $output_filename = GenusData::data_folder($this->genus())."{$combination}.asc";

                ErrorMessage::Marker("Removeing Files and DB entries");

                file::Delete($output_filename);
                file::Delete(str_replace("asc", "png", $output_filename));

                $remove_result = GenusData::RemoveProjectedFiles($this->genus(), null, $scenario, $model, $time);
                if ($remove_result instanceof ErrorMessage)
                {
                    $error[$combination] = $remove_result; 
                    continue;
                }
                
            }
            
        }
        
    }
    
    


    /**
     *
     * $result structure 
     * 
     * [scenario1]
     *         [time1]
     *              ['complete']               use this list of files to produce species richness
     *              ['missing']                if this list has a count > 0 then this result is valid as we are missing inputs
     *              ['species_stats_invalid']  inavlid files that need t be recalculated
     *         [time2]
     *              ['complete']
     *              ['missing']
     *              ['species_stats_invalid']
     * 
     * [scenario2]
     *         [time1]
     *              ['complete']
     *              ['missing']
     *              ['species_stats_invalid']
     *         [time2]
     *              ['complete']
     *              ['missing']
     *              ['species_stats_invalid']
     * 
     * 
     * 
     * 
     * @return type 
     */
    private function getValidMedians()
    {

        $result = array();
        
        foreach ($this->scenarios() as $scenario) 
            foreach ($this->times() as $time) 
                $result[$scenario][$time] =  $this->validateMediansFor($scenario,$time);

        
        return $result;
        
    }
    
    /**
     *
     * 
     * @param type $scenario
     * @param type $time
     * @return array 
     *     $result['missing']                = array();  list of file paths that that are missing Genus / Scenario Time 
     *     $result['complete']               = array();  list of file paths that go to making this Genus / Scenario Time 
     *     $result['species_stats_invalid']  = array() list of species id's that were ignore because we have no occurance data 
     */
    private function validateMediansFor($scenario,$time)
    {
        
        // we have to have a m,edian file for all species in this genus for this $scenario $time
     
        $result = array();
        $result['species_stats_invalid']  = array();
        $result['missing']  = array();
        $result['complete'] = array();        
        
        // find all the files for this species List for a single  - $scenario,$time
        
        $pattern = "{$scenario}_ALL_{$time}_median.asc";
        
        foreach ($this->SpeciesList() as $species_id => $row ) 
        {
            
            $filename = SpeciesData::species_data_folder($species_id).$pattern;
            
            if (!file_exists($filename))
            {
                $result['missing'][$species_id] = $filename;
                ErrorMessage::Marker("NOT FOUND - {$filename}");
                continue;
            }
            
            

            if (!$this->ValidateExistenceOnly())
            {
                ErrorMessage::Marker("Get Stats for {$filename}");
                $stats = spatial_util::RasterStatisticsBasic($filename);
                if ($stats instanceof Exception) throw $stats;

                if (is_null($stats))
                {
                    $result['species_stats_invalid'][$species_id] = $filename;
                    ErrorMessage::Marker(" {$species_id} - Stats invalid for {$filename}");
                    continue;
                }                
            }

            
            
            $result['complete'][$species_id] = $filename;
            
            
        }
        
        
        
        return $result;
    }
    
    
    
    
    private function getThresholds($species_ids,&$errors)
    {
        
        $result = array();
                 
        foreach ($species_ids as $species_id) 
        {

            $threshold_result = DatabaseMaxent::GetMaxentThreshold($species_id);
            if (is_null($threshold_result) ||  $threshold_result instanceof ErrorMessage)
            {
                $errors[] = "Species id {$species_id} has no threshold value not in database ";
                
                
                $maxentResultsFilename = SpeciesData::species_data_folder($species_id)."maxentResults.csv";
                $maxentResult = matrix::Load($maxentResultsFilename);
                
                ErrorMessage::Marker("NOT in DB Get from file  {$species_id} - {$maxentResultsFilename}");
                
                $first = util::first_element($maxentResult);
                
                $name = "Equate entropy of thresholded and original distributions logistic threshold";
                
                $threshold_from_file = $first[$name];
                
                $result[$species_id] = $threshold_from_file;
                
                continue;
            }
            else
            {
                $result[$species_id] = $threshold_result['threshold'];    
            }
            
            
            
        }
    
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
    private function CalculateSpeciesRichness($scenario_time_species_files,$output_filename, $null_value = null,&$error = null)
    {

        if (is_null($error)) $error = array();
        
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
        
        
        
        ErrorMessage::Marker($scenario_time_species_files);        
        ErrorMessage::Marker($species_ids);
        
        
        
        // open all files
        foreach ($scenario_time_species_files as  $species_id => $filename)  
        {
            if (!file_exists($filename))  return new Exception("Could not open for ".__METHOD__." {$filename}");
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
        
        
        ErrorMessage::Marker("Going to write ".count(explode("\n",$file_result)) ." lines to {$output_filename}");
        
        file_put_contents($output_filename, $file_result);

        if (!file_exists($output_filename)) 
            return new Exception("Failed to Write file for Median {$output_filename}");

            
        $outputFileLineCount  = file::lineCount($output_filename);

        // check to see if output file line count = $lineCountFirst
        if ($lineCountFirst != $outputFileLineCount)
            return new Exception("Failed to create Median number of input and output lines don't match  $lineCountFirst != $outputFileLineCount ");
        
        
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
        
        if (is_null($this->model())) 
            $this->models(DatabaseClimate::GetModels());
        else
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
        
        
        // convert query to species ID / species name list
        $species_list = array();
        foreach ($species_list_query_result as $row) 
            $species_list[$row['species_id']] = $row['species'];
        
        
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

    private function models() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }
    
    private function scenarios() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }
    
    private function times() {
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
    

    
    
    public function SpeciesList() {
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

    
    
    
    
    
    
}


?>
