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
        
        $this->SpeciesCounts(SpeciesData::SpeciesWithOccuranceData());
     
        $this->LoadAscii(false);
        $this->LoadQuickLook(true);
        $this->Recalculate(false);
        
        
        
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
            $this->scenarios(explode(",",$this->time()));
        
        
        if (is_null($this->LoadAscii()))     $this->LoadAscii(false);
        if (is_null($this->LoadQuickLook())) $this->LoadQuickLook(true);
        if (is_null($this->Recalculate()))   $this->Recalculate(false);
        
        
        if (!is_null($this->clazz()))   return $this->calculateClazzRichness();
        if (!is_null($this->family()))  return $this->calculateFamilyRichness();
        if (!is_null($this->genus()))   return $this->calculateGenusRichness();
        
        
    }
    
    private function calculateClazzRichness()
    {
        ErrorMessage::Marker("Clazz requested");
        $this->ClazzList(SpeciesData::Clazz());

        $this->SpeciesList(SpeciesData::SpeciesIDsForClazz($this->clazz()));
    }

    private function calculateFamilyRichness()
    {
        ErrorMessage::Marker("Family requested");
        $this->FamilyList(SpeciesData::Family());
        // $this->SpeciesList(SpeciesData::SpeciesIDsForFamily($this->family()));
        
        $families = SpeciesData::GenusForFamily($this->family());
        
        foreach ($families as $famly) 
        {
            print_r($famly);
            
        }
        
    }
    
    
    private function calculateGenusRichness()
    {
        ErrorMessage::Marker("genus requested");
        
        $this->GenusList(SpeciesData::Genus());
        $this->SpeciesList(SpeciesData::SpeciesIDsForGenus($this->genus()));
        
        $this->validateGenusMedian();
        
        if ($this->SpeciesMissingCount() > 0) 
            return new ErrorMessage(__METHOD__, __LINE__, "Missing count greater than Zero "); 

        $model = "ALL";

        ErrorMessage::Marker("Species List count = ".count($this->SpeciesList()));
        print_r($this->SpeciesList());

        
        
        $error = array();
        foreach ($this->SpeciesFilenamesToSum() as $scenario => $times) 
        {
            
            if ($scenario != "RCP3PD") continue; // for testiong 
        
            $scenario_outputs = array();
            
            
            
            foreach ($times as $time => $species_files) 
            {
                
                // if ($time != "2015") continue;   // for testiong 
                
                $combination = "{$scenario}_{$model}_{$time}";
                
                ErrorMessage::Marker(__METHOD__." {$combination} \n");
                
                $output_filename = SpeciesData::genus_data_folder($this->genus())."{$combination}.asc";
                
                
                if ($this->Recalculate())
                {
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
                
                
                if (!file_exists($output_filename))
                {
                    // ### HERE is where we doo the calc
                    $output_result = $this->CalculateSpeciesRichness($species_files,$output_filename);
                    
                    if ($output_result instanceof ErrorMessage)
                    {
                        $error[$combination] = $output_result; 
                        ErrorMessage::Marker($output_result);
                        continue;
                    }
                    
                    ErrorMessage::Marker("output_result = $output_result");

                    if ($this->LoadAscii())
                    {
                    
                        ErrorMessage::Marker("Load ASCII Grid {$output_result} ");
                        
                        
                        ErrorMessage::Marker("Make sure we have no data for this $combination");
                        $remove_current_db_files = GenusData::RemoveProjectedFiles($this->genus(),'ASCII_GRID',$scenario, $model, $time);
                        if ($remove_current_db_files instanceof ErrorMessage)
                        {
                            $error[$combination] = $remove_current_db_files;  
                            ErrorMessage::Marker($remove_current_db_files);
                            continue;
                        }
                        
                        ErrorMessage::Marker("remove_current_db_files = $remove_current_db_files ");
                        
                        
                        $dbresult = GenusData::InsertProjectedFile($this->genus(),$output_result,'ASCII_GRID',$scenario, $model, $time,true);
                        if ($dbresult instanceof ErrorMessage)
                        {
                            $error[$combination] = $dbresult;                     
                            ErrorMessage::Marker($dbresult);
                            continue;
                        }

                        
                        ErrorMessage::Marker("ASCII dbresult = $dbresult");                    

                    }
                    
                    
                    $scenario_outputs[$time] = $output_result; //ascii grid for this time point
                    
                    
                }
                else
                {
                    
                    ErrorMessage::Marker("Already exists {$output_filename} ");
                    
                    // file exists then load into db
                    if ($this->LoadAscii())
                    {
                    
                        ErrorMessage::Marker("Load already created ASCII Grid {$output_result} ");
                        
                        ErrorMessage::Marker("Remove any current ones as we want to replace with this new one");
                        $remove_current_db_files = GenusData::RemoveProjectedFiles($this->genus(),'ASCII_GRID',$scenario, $model, $time);
                        if ($remove_current_db_files instanceof ErrorMessage)
                        {
                            $error[$combination] = $remove_current_db_files;  
                            ErrorMessage::Marker($remove_current_db_files);
                            continue;
                        }
                        
                        
                        $dbresult = GenusData::InsertProjectedFile($this->genus(),$output_result,'ASCII_GRID',$scenario, $model, $time,true);
                        if ($dbresult instanceof ErrorMessage)
                        {
                            $error[$combination] = $dbresult;                     
                            ErrorMessage::Marker($dbresult);
                            continue;
                        }

                        
                        ErrorMessage::Marker("ASCII dbresult = $dbresult");                    

                    }
                    
                    $scenario_outputs[$time] = $output_filename; //ascii grid for this time point

                }
                
                
            }
            
            // outside TIMES loop 
            
            
            // for all times for this scenario we want to create quick looks with same min and max
            $min = array();
            $max = array();
            foreach ($scenario_outputs as $time => $richness_asciigrid_filename)
            {
                ErrorMessage::Marker("Get stats for {$richness_asciigrid_filename}");
                
                $stats = spatial_util::RasterStatisticsBasic($richness_asciigrid_filename);
                if (is_null($stats)) continue;
                
                $min[$time] = $stats[spatial_util::$STAT_MINIMUM];
                $max[$time] = $stats[spatial_util::$STAT_MAXIMUM];    
                
                //print_r($stats);
            }

            if (count($min) <= 0)
            {
                $error[] = new ErrorMessage(__METHOD__, __LINE__, "NO stats for minimum for time [{$time}]");
                continue;
            }
            
            if (count($max) <= 0)
            {
                $error[] = new ErrorMessage(__METHOD__, __LINE__, "NO stats for minimum for time [{$time}]");
                continue;
            }
            
            
            $scenario_min = min($min);
            $scenario_max = min($max);
            
            ErrorMessage::Marker("scenario_min = $scenario_min scenario_max = $scenario_max");
            
            foreach ($scenario_outputs as $time => $richness_asciigrid_filename)
            {
                
                ErrorMessage::Marker("Create Image for {$richness_asciigrid_filename}");
                
                $image_filename = GenusData::CreateImage($this->genus(), $richness_asciigrid_filename,$scenario_min,$scenario_max); // create quickLook from ascii
                if ($image_filename instanceof ErrorMessage)
                {
                    $error[$combination] = $image_filename;
                    ErrorMessage::Marker($image_filename);
                    continue;
                }
                
                ErrorMessage::Marker("Created {$image_filename}");
                
                
                if ($this->LoadQuickLook())
                {
                    
                    ErrorMessage::Marker("image_filename = $image_filename");                
                    
                    $remove_current_db_files = GenusData::RemoveProjectedFiles($this->genus(),'QUICK_LOOK',$scenario, $model, $time);
                    if ($remove_current_db_files instanceof ErrorMessage)
                    {
                        $error[$combination] = $remove_current_db_files;  
                        ErrorMessage::Marker($remove_current_db_files);
                        continue;
                    }
                    
                    ErrorMessage::Marker("Quick Look Removed Result {$remove_current_db_files}");
                    
                    
                    $dbresult = GenusData::InsertProjectedFile($this->genus(),$image_filename,'QUICK_LOOK',$scenario, $model, $time,true);
                    if ($dbresult instanceof ErrorMessage)
                    {
                        $error[$combination] = new ErrorMessage(__METHOD__, __LINE__, "Failed to Calculate Species Richness \n".$output_result->getMessage());                     
                        continue;
                    }

                    ErrorMessage::Marker("QUICKLOOK dbresult = $dbresult");                    

                }

            }
            
            
            
        }
        
        
    }
    




    private function validateGenusMedian()
    {

        $result = array();
        
        foreach ($this->scenarios() as $scenario) 
        {
            $scenario = trim($scenario);
            
            if ($scenario == "CURRENT") continue;
            
            $result[$scenario] = array();
            
            foreach ($this->times() as $time) 
            {
                if ($time < 2000) continue;
                
                $result[$scenario][$time] = $this->validateGenusMedianFor($scenario,$time);
                
            }
            
            
        }

        $total_missing = 0;
        $total_ignored = 0;
        $total_count = 0;
        $filenames = array();

        $missing = array();
        foreach ($result as $result_scenario => $result_times) 
        {
            foreach ($result_times as $result_time => $result_types) 
            {
                $total_missing = $total_missing + count($result_types['missing']);
                $total_ignored = $total_ignored + count($result_types['species_ignored']);
                $filenames[$result_scenario][$result_time] =  $result_types['complete'];
                $total_count += $result_types['count'];      
                
                
                if (count($result_types['missing']) > 0)
                {
                    foreach ($result_types['missing'] as $missing_species_id => $missing_filename) 
                    {
                        $missing[$missing_filename] = " --species={$missing_species_id} --scenarios={$result_scenario} --times={$result_time} --models=ALL ";
                    }
                    
                }
                
                
            }
        }

        $this->CombinationsMissing($missing);
        
        $this->SpeciesIgnoredCount($total_ignored);
        $this->SpeciesMissingCount($total_missing);
        $this->SpeciesTotalCount($total_count);
        $this->SpeciesFilenamesToSum($filenames);
        
        ErrorMessage::Marker("total_missing = $total_missing");
        ErrorMessage::Marker("total_ignored = $total_ignored");
        ErrorMessage::Marker("total_count   = {$this->SpeciesTotalCount()}");
        
        
        
    }
    
    /**
     *
     * 
     * @param type $scenario
     * @param type $time
     * @return array 
     *     $result['missing']  = array();  list of file paths that that are missing Genus / Scenario Time 
     *     $result['paths']    = array();  list of file paths that go to making this Genus / Scenario Time 
     *     $result['species_ignored']  = array() list of species id's that were ignore because we have no occurance data 
     *     $result['count']            = total count of items
     */
    private function validateGenusMedianFor($scenario,$time)
    {
        
        // we have to have a m,edian file for all species in this genus
     
        $result = array();
        $result['species_ignored']  = array();
        $result['missing']  = array();
        $result['complete'] = array();
        $result['count'] = 0;
        // find all the files for this Genus - $scenario,$time
        
        $pattern = "{$scenario}_ALL_{$time}_median.asc";
        
        foreach ($this->SpeciesList() as $species_id => $row ) 
        {
            $result['count']++;            
            
            // check the occurace for this species  - if have no occurance but they are in taxa then return ignored count
            if (!array_key_exists($species_id, $this->SpeciesCounts()))
            {
                $result['species_ignored'][$species_id] = $species_id;
                //ErrorMessage::Marker("Ignored {$species_id} - NO Occurance Counts");
                continue;
            }
            
            $species_count = $this->SpeciesCounts();
            
            // check the occurace for this species  - if have no occurance but they are in taxa then return ignored count
            if ($species_count[$species_id]['species_count'] < 10)
            {
                
                $result['species_ignored'][$species_id] = $species_id;
                //ErrorMessage::Marker("Ignored {$species_id} - NO Occurance Counts");
                continue;
            }
            
            // get the stats of the species timew scenario  - check see if it's valid
            
            
            $filename = SpeciesData::species_data_folder($species_id).$pattern;

            //ErrorMessage::Marker("check for $pattern in  {$row['species_id']}  {$filename}");
            
            if (file_exists($filename))
            {
                ErrorMessage::Marker("Get Stats for {$filename}");
                
                $stats = spatial_util::RasterStatisticsBasic($filename);
                
                if ($stats instanceof Exception) throw $stats;
                
                if (is_null($stats))
                {
                    $result['species_ignored'][$species_id] = $filename;                        
                    ErrorMessage::Marker("Ignored {$species_id} - Stats invalid");
                }
                else
                {
                    $result['complete'][$species_id] = $filename;
                }    
                
            }
                
            else
                $result['missing'][$species_id] = $filename;
            
            
            
            
            
        }
        
        
        
        return $result;
    }
    
    
    
    
    private function getThresholds($species_ids)
    {
        
        
        $result = array();
        
        $errors = array();
        foreach ($species_ids as $species_id) 
        {

            $threshold_result = DatabaseMaxent::GetMaxentThreshold($species_id);
            if ($threshold_result instanceof ErrorMessage)
            {
                $errors[$species_id] = $threshold_result;
                continue;
            }
            
            $result[$species_id] = $threshold_result['threshold'];
            
        }
        
        if (count($errors) > 0 )
            return new ErrorMessage(__METHOD__, __LINE__, $errors);
    
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
    private function CalculateSpeciesRichness($species_files,$output_filename, $null_value = null)
    {

         // $species_files  species id to filename 
        
        $species_ids = array_keys($species_files);
        
        
        $thresholds = $this->getThresholds($species_ids);

        
        // assume all files are standard and all align
        // make want to build check for this soon
        
        // try to find the null data from the first file
        if (is_null($null_value))
            $null_value = spatial_util::asciigrid_nodata_value(util::first_element($species_files));

        
        $first_species_id = util::first_key($species_files); // use this a controller for th other files

        
        
        $handles = array();

        $lineCounts = file::lineCounts($species_files,true);  // we can use this check if any file is not right
        $lineCountFirst = util::first_element($lineCounts) ;

        
        
        
        // open all files
        foreach ($species_files as  $species_id => $filename)  
        {
            if (!file_exists($filename))  return new Exception("Could not open for ".__METHOD__." {$filename}");
            $handles[$species_id] = fopen($filename, "rb");
        }
            
        
        
        // ASCII files have 6 rowsa of "metadata" skip those
        for ($index = 0; $index < 6; $index++) {
            foreach ($handles as  $species_id => $handle)  
                $line = fgets($handle); // read lines from all files 
        }
        
        
        
        $result = array();
        // we can only read one line at a time from each file 
        // as we don't have enough memory to to read all files in memory at one time
        for ($lineNum = 6; $lineNum < $lineCountFirst ; $lineNum++) {
        
            $result_row = array();
            
            $cells = array();
            foreach ($handles as  $species_id => $handle) 
                $cells[$species_id] = explode(" ",fgets($handle));  // load a line from each file - and convert to cells

            
            // process a line for each file 
            // species ID =>  array() or values fro that file 
            
            // process across  line
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
        $file_result =   implode("\n",file::Head(util::first_element($species_files), 6))."\n"   
                        .implode("\n",$result).
                        "\n";
        
        file_put_contents($output_filename, $file_result);

        if (!file_exists($output_filename)) 
            return new Exception("Failed to Write file for Median {$output_filename}");

            
        $outputFileLineCount  = file::lineCount($output_filename);

        // check to see if output file line count = $lineCountFirst
        if ($lineCountFirst != $outputFileLineCount)
            return new Exception("Failed to create Median number of input and output lines don't match  $lineCountFirst != $outputFileLineCount ");
        
        
        return $output_filename;
        
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
    

    public function ClazzList() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }
    

    public function FamilyList() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }

    
    public function GenusList() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }
    
    
    public function SpeciesList() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }
    
    public function SpeciesCounts() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }

    public function SpeciesIgnored() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }
    
    public function SpeciesMissing() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }

    public function CombinationsMissing() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }

    
    
    public function SpeciesIgnoredCount() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }
    
    public function SpeciesMissingCount() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }

    public function SpeciesTotalCount() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }
    

    public function SpeciesFilenamesToSum() {
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
    
    
}


?>
