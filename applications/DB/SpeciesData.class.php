<?php

/**
 *
 * Connect , disconnect and data flow to and from Species database
 *  
 * 
 * 
 */
class SpeciesData extends Object {

    
    public static $OCCURANCE_MINIMUM_LINES = 3;
    
    
    /**
     *
     * For any species make sure we have occurance data
     * 
     * @param type $pattern
     * @return type 
     */
    public static function speciesList($pattern = "%")  //
    {
        $pattern = util::CleanString($pattern);
        $sql = "select species_id,(common_name || ' (' || scientific_name || ')' ) as full_name  from modelled_climates  where (common_name LIKE '%{$pattern}%' or scientific_name LIKE '%{$pattern}%' ) ";
                       
        $result = DBO::Query($sql,'species_id');
        if ($result instanceof ErrorMessage) return ErrorMessage::Stacked(__METHOD__, __LINE__, "", true, $result);
        
        return $result;
    }

    public static function speciesListScientificName($pattern = "%")  //
    {
        $pattern = util::CleanString($pattern);
        $sql = "select id,scientific_name from species where scientific_name LIKE '%{$pattern}%'";

        $result = DBO::Query($sql,'id');
        if ($result instanceof ErrorMessage) return ErrorMessage::Stacked(__METHOD__, __LINE__, "", true, $result);
        
        return $result;
    }
    
    
    /**
     *
     * @param type $speciesID
     * @return null|string  
     */
    public static function SpeciesQuickInformation($speciesID) 
    {
       
        $sql = "select scientific_name,common_name from species where id = {$speciesID} limit 1";
        
        $first = DBO::QueryFirst($sql,'scientific_name');
        if ($first instanceof ErrorMessage) return ErrorMessage::Stacked(__METHOD__, __LINE__, "", true, $first);

        
        return array_util::Value($first, 'common_name')." (".array_util::Value($first, 'scientific_name').")";
    }
    
    
    public static function SpeciesInfoByID($species_id) 
    {
        
        $sql = "select scientific_name,common_name from species where id = $species_id";
        
        $result = DBO::QueryFirst($sql,'id');
        if ($result instanceof ErrorMessage) return ErrorMessage::Stacked(__METHOD__, __LINE__, "", true, $result);        
        
        
        return  $result;
    }
    
    
    /**
     * Get file id from datbaase for this combination
     * 
     * 
     * @param type $species
     * @param type $scenario
     * @param type $model
     * @param type $time
     * @return string|null  file_id for that file  pr Em,pty string says that now file for these parameters
     */
    public static function GetModelledData($speciesID,$scenario, $model, $time,$filetype = null, $desc = null)
    {
        
        $filetypeAnd = (is_null($filetype)) ? "" : "and mc.filetype   = ".util::dbq($filetype,true);
        $descAnd     = (is_null($desc))     ? "" : "and f.description = ".util::dbq($desc,true);
        
        
        $sql = "select mc.id as id
                      ,mc.species_id
                      ,mc.scientific_name
                      ,mc.common_name
                      ,mc.models_id
                      , m.dataname as model_name
                      ,mc.scenarios_id
                      , s.dataname as scenario_name
                      ,mc.times_id
                      , t.dataname as time_name
                      ,mc.filetype
                      , f.description
                      ,mc.file_unique_id as file_id
                from   modelled_climates mc
                      ,models m
                      ,scenarios s
                      ,times t
                      ,files f
                where mc.species_id = {$speciesID}
                  and mc.models_id      = m.id
                  and mc.scenarios_id   = s.id
                  and mc.times_id       = t.id
                  and mc.file_unique_id = f.file_unique_id
                  and m.dataname = ".util::dbq($model,true)."
                  and s.dataname = ".util::dbq($scenario,true)."
                  and t.dataname = ".util::dbq($time,true)." {$filetypeAnd} {$descAnd}
                  limit 1
                ;";
        
        
        $result = DBO::QueryFirstValue($sql,'file_id');
        if ($result instanceof ErrorMessage) return ErrorMessage::Stacked(__METHOD__, __LINE__, "", true, $result);        
        
        return $result;
        
    }

    
    
    
    /**
     *
     * get FileId for all datra files for this species 90
     * 
     * @param type $speciesID
     * @param type $filetype Limit to this filetype only
     * @return type 
     */
    public static function GetAllModelledData($speciesID,$filetype = null,$key = 'combination')
    {
        
        $filetypeAnd = (is_null($filetype)) ? "" : "and mc.filetype   = ".util::dbq($filetype,true);
        
        
        $sql = "select mc.id as id
                      ,mc.species_id
                      ,mc.scientific_name
                      ,mc.common_name
                      ,mc.models_id
                      , m.dataname as model_name
                      ,mc.scenarios_id
                      , s.dataname as scenario_name
                      ,mc.times_id
                      , t.dataname as time_name
                      ,mc.filetype
                      , f.description
                      ,mc.file_unique_id as file_unique_id
                      ,(s.dataname || '_' || m.dataname || '_' || t.dataname) as combination
                      ,(mc.common_name  || ' (' || mc.scientific_name || ')' ) as full_name
                from   modelled_climates mc
                      ,models m
                      ,scenarios s
                      ,times t
                      ,files f
                where mc.species_id = {$speciesID}
                  and mc.models_id      = m.id
                  and mc.scenarios_id   = s.id
                  and mc.times_id       = t.id
                  and mc.file_unique_id = f.file_unique_id
                  {$filetypeAnd}
                  order by 
                      m.dataname
                     ,s.dataname
                     ,t.dataname
                ;";
        
        
        $result = DBO::Query($sql,$key);
        if ($result instanceof ErrorMessage) return ErrorMessage::Stacked(__METHOD__, __LINE__, "", true, $result);
        
        return $result;
        
    }
    
    
    
    /**
     * 
     * 
     * @param type $speciesID  - Scientific Name here as then across systems and database rebuilds it wont change
     * @return array  [index] => (longitude,latitude)
     */
    public static function SpeciesOccurance($speciesID) 
    {
        $q = "SELECT ST_X(location) AS longitude, ST_Y(location) AS latitude FROM occurrences where species_id = {$speciesID}";
        
        $result = DBO::Query($q);
        if ($result instanceof ErrorMessage) return ErrorMessage::Stacked(__METHOD__, __LINE__, "Failed to get Species Occurence data from data base using \n q = $q \n speciesID = $speciesID  \n", true, $result);        
        
        return $result;
    }
    
    


    
    public function SpeciesOccuranceToFile($speciesID, $occurFilename) 
    {
        
        $result = self::SpeciesOccurance($speciesID);
        if ($result instanceof ErrorMessage) 
            return ErrorMessage::Stacked(__METHOD__, __LINE__, "Failed to get Species Occurence Data speciesID = $speciesID  \n", true, $result);
        
        
        
        // this will create occurance file where the "name" of the species will be the Species ID from database
        // this seems to have to match the Lambdas filename
        
        $file = '"SPPCODE","LATDEC","LONGDEC"'."\n";  // headers specific to Maxent JAR
        foreach ($result as $row) 
            $file .= $speciesID.",".$row['latitude'].",".$row['longitude']."\n";
        
        file_put_contents($occurFilename, $file);

        
        if (!file_exists($occurFilename)) 
            return new ErrorMessage(__METHOD__, __LINE__,"Failed to wite Species Occurence Data File speciesID = $speciesID  occurFilename = $occurFilename \n");
        
        
        
        // check the validity of the file - a "good" number of lines
        $lineCount = file::lineCount($occurFilename);
        

        ErrorMessage::Marker("occurFilename = $occurFilename  lineCount = $lineCount\n");
        
        
        if ($lineCount < self::$OCCURANCE_MINIMUM_LINES) 
            return new ErrorMessage(__METHOD__, __LINE__,"Failed to get Occurence $occurFilename it was too small  speciesID = $speciesID  \n");
        
        
        unset($result);
        unset($file);
        
        return true;
        
    }
    
    public static function Kingdom() 
    {
        $result = matrix::Column(DBO::Unique('species_taxa_tree', 'kingdom'),'kingdom');
        if ($result instanceof ErrorMessage) return ErrorMessage::Stacked(__METHOD__, __LINE__, "", true, $result);
        return $result;
    }

    public static function Phylum() 
    {
        $result = matrix::Column(DBO::Unique('species_taxa_tree', 'phylum'),'phylum');
        if ($result instanceof ErrorMessage) return ErrorMessage::Stacked(__METHOD__, __LINE__, "", true, $result);
        return $result;
    }
    
    public static function Clazz() 
    {
        $result = matrix::Column(DBO::Unique('species_taxa_tree', 'clazz'),'clazz');
        if ($result instanceof ErrorMessage) return ErrorMessage::Stacked(__METHOD__, __LINE__, "", true, $result);        
        return $result;
    }
    

    public static function Orderz() 
    {
        $result = matrix::Column(DBO::Unique('species_taxa_tree', 'orderz'),'orderz');
        if ($result instanceof ErrorMessage) return ErrorMessage::Stacked(__METHOD__, __LINE__, "", true, $result);
        return $result;
    }
    
    public static function Family() 
    {
        $result = matrix::Column(DBO::Unique('species_taxa_tree', 'family'),'family');
        if ($result instanceof ErrorMessage) return ErrorMessage::Stacked(__METHOD__, __LINE__, "", true, $result);
        return $result;
    }
    

    public static function Genus() 
    {
        $result = matrix::Column(DBO::Unique('species_taxa_tree', 'genus'),'genus');
        if ($result instanceof ErrorMessage) return ErrorMessage::Stacked(__METHOD__, __LINE__, "", true, $result);
        return $result;
    }

    public static function Species() 
    {
        $result = matrix::Column(DBO::Unique('species_taxa_tree', 'species'),'species');
        if ($result instanceof ErrorMessage) return ErrorMessage::Stacked(__METHOD__, __LINE__, "", true, $result);
        return $result;
    }

    
    public static function SpeciesForKingdom($kingdom) 
    {
        $result  = DBO::Unique('species_taxa_tree', 'species', "kingdom = E'{$kingdom}'");
        if ($result instanceof ErrorMessage) return ErrorMessage::Stacked(__METHOD__, __LINE__, "", true, $result);
        return $result;
        
    }

    public static function SpeciesForClazz($clazz) 
    {
        $result  = DBO::Unique('species_taxa_tree', 'species', "clazz = E'{$clazz}'");
        if ($result instanceof ErrorMessage) return ErrorMessage::Stacked(__METHOD__, __LINE__, "", true, $result);        
        return $result;
    }
    
    public static function SpeciesForFamily($family) 
    {
        $result  = DBO::Unique('species_taxa_tree', 'species', "family = E'{$family}'");
        if ($result instanceof ErrorMessage) return ErrorMessage::Stacked(__METHOD__, __LINE__, "", true, $result);
        return $result;
        
    }
    
    public static function SpeciesForGenus($genus) 
    {
        $result  = DBO::Unique('species_taxa_tree', 'species', "genus = E'{$genus}'");
        if ($result instanceof ErrorMessage) return ErrorMessage::Stacked(__METHOD__, __LINE__, "", true, $result);
        return $result;
        
    }
    
    public static function SpeciesIDsForGenus($genus) 
    {
        $result  = DBO::Unique('species_taxa_tree', 'species_id', "genus = E'{$genus}'");
        if ($result instanceof ErrorMessage) return ErrorMessage::Stacked(__METHOD__, __LINE__, "", true, $result);
        return $result;
        
    }
    
    
    
    public static function GenusForKingdom($kingdom) 
    {
        $result  = DBO::Unique('species_taxa_tree', 'genus', "kingdom = E'{$kingdom}'");
        if ($result instanceof ErrorMessage) return ErrorMessage::Stacked(__METHOD__, __LINE__, "", true, $result);
        return $result;
        
    }

    public static function GenusForFamily($family) 
    {
        $result  = DBO::Unique('species_taxa_tree', 'genus', "family = E'{$family}'");
        if ($result instanceof ErrorMessage) return ErrorMessage::Stacked(__METHOD__, __LINE__, "", true, $result);
        return $result;
        
    }
    
    
    public static function FamilyForKingdom($kingdom) 
    {
        $result  = DBO::Unique('species_taxa_tree', 'family', "kingdom = E'{$kingdom}'");
        if ($result instanceof ErrorMessage) return ErrorMessage::Stacked(__METHOD__, __LINE__, "", true, $result);
        return $result;
        
    }
    
    
    public static function SpeciesWithOccuranceData($count = 0) 
    {
        $sql = "select o.species_id,s.scientific_name,s.common_name,o.count   from species_occur o, species s where o.species_id = s.id and o.count >= {$count}";
        $result  = DBO::Query($sql,'species_id');
        
        if ($result instanceof ErrorMessage) return ErrorMessage::Stacked(__METHOD__, __LINE__, "", true, $result);
        
        return $result;
        
    }
    

    public static function id4ScientificName($scientificName) 
    {
        $sql = "select id,scientific_name,common_name from species where scientific_name = E'{$scientificName}';";
        
        $result = DBO::Query($sql,'id');
        if ($result instanceof ErrorMessage) return ErrorMessage::Stacked(__METHOD__, __LINE__, "", true, $result);
        
        return $result;
    }
    
    public static function TaxaForClazz($clazz) 
    {
        $result =  DBO::Query("select * from species_taxa_tree where clazz = E'{$clazz}'", 'species_id');
        if ($result instanceof ErrorMessage) return ErrorMessage::Stacked(__METHOD__, __LINE__, "", true, $result);
        return $result;
    }
    
    public static function TaxaForClazzWithOccurances($clazz,$count =0) 
    {
        
        $sql = "select 
                     o.species_id
                    ,o.count as species_count
                    ,st.id as species_taxa_id
                    ,st.kingdom
                    ,st.phylum
                    ,st.clazz
                    ,st.orderz
                    ,st.family
                    ,st.genus
                    ,st.species
                    ,sp.common_name
                from 
                     species_occur o
                    ,species_taxa_tree st 
                    ,species sp
                where o.species_id = st.species_id
                  and o.species_id = sp.id
                  and o.count >= {$count}
                  and st.clazz = E'{$clazz}' 
                ";
        
        $result = DBO::Query($sql, 'species_id');
                  
        if ($result instanceof ErrorMessage) return ErrorMessage::Stacked(__METHOD__, __LINE__, "", true, $result);                  
        
        return $result;
    }
    
    
    
    public static function GenerateMedianFor($species_id, $user_scenario = "%",$user_model = "%",$user_time = "%",$overwrite_output = false,$output_filename = null)  
    {
        

        $folder = self::species_data_folder($species_id);        
        
        
        if (is_null($output_filename)) 
        {
            $scenario_bit = $user_scenario;
            $model_bit    = $user_model;
            $time_bit     = $user_time;
            
            if (util::contains($scenario_bit, "%")) $scenario_bit = ($scenario_bit == "%") ? "ALL" : "MULTIPLE";

            if (util::contains($model_bit, "%")) $model_bit = ($model_bit == "%") ? "ALL" : "MULTIPLE";
            
            if (util::contains($time_bit, "%")) $time_bit = ($time_bit == "%") ? "ALL" : "MULTIPLE";
            
            $output_filename =  "{$folder}{$scenario_bit}_{$model_bit}_{$time_bit}_median.asc";
            
        }
        
        if ($overwrite_output) file::Delete($output_filename);

        
        if (file_exists($output_filename)) return $output_filename;
        
        
        $scenarios = DatabaseClimate::GetScenariosNamed($user_scenario);
        $models    = DatabaseClimate::GetModelsNamed($user_model);
        $times     = DatabaseClimate::GetTimesNamed($user_time);
        

        // make this simpe combnation
        
        $files = array();
        foreach ($scenarios as $scenario) 
            foreach ($models as $model) 
                foreach ($times as $time) 
                    $files["{$scenario}_{$model}_{$time}"] = "{$folder}{$scenario}_{$model}_{$time}.asc";

                    
        // check files exist
        foreach ($files as $combo => $filename) 
            if (!file_exists($filename))  
                return new ErrorMessage(__METHOD__, __LINE__, "Required File [{$filename}] does not exist");
            
        
        $median_result = spatial_util::median($files,$output_filename);
        if ($median_result instanceof ErrorMessage) return ErrorMessage::Stacked(__METHOD__, __LINE__, "", true, $median_result);
        
        
        if (!file_exists($output_filename))
            return new ErrorMessage(__METHOD__, __LINE__, "After Median Output file does not exist [{$output_filename}]");

            
        return $output_filename;
        
        
    }
    
    
    
    public static function hasCompleteDataset($species_id,$combinations = null)
    {
        
        // get list of Models, Scenarios , Times 
        
        // check that all ASC files exist for this species.

        if (is_null($combinations))
            $combinations  = DatabaseClimate::CombinationsSingleLevel();        
        
        $folder = self::species_data_folder($species_id);
        
        foreach ($combinations as $combination => $value) 
        {
            $filename = "{$folder}{$combination}.asc";
            if (!file_exists($filename)) return false;
        }
        
        return true;
        
    }

    private static function species_data_folder($species_id)
    {
        $folder = configuration::Maxent_Species_Data_folder()
                    .$species_id
                    .configuration::osPathDelimiter()
                    .configuration::Maxent_Species_Data_Output_Subfolder()
                    .configuration::osPathDelimiter()
                    ;
        
        return $folder;
        
    }
    
    
    public static function ScenarioTimeMedian($species_id,$scenario,$time,$overwrite_output = false,$output_filename = null)
    {
        
        $result = self::GenerateMedianFor(
                     $species_id
                    ,$scenario
                    ,"%"
                    ,$time
                    ,$overwrite_output
                    ,$output_filename
                    ) ;
                
        if ($result instanceof ErrorMessage) return ErrorMessage::Stacked(__METHOD__, __LINE__, "", true, $result);                          
        
        return $result;
        
        
    }
    

    
    public static function ScenarioTimeMediansForSpecies($species_id)
    {
        
        $scenarios = DatabaseClimate::GetScenarios();        
        if($scenarios instanceof ErrorMessage) return ErrorMessage::Stacked (__METHOD__, __LINE__, "", true, $scenarios);
        
        $times     = DatabaseClimate::GetTimes();
        if($times instanceof ErrorMessage) return ErrorMessage::Stacked (__METHOD__, __LINE__, "", true, $times);

        
        
        // loop thru Scenario and Time
        foreach ($scenarios as $scenario) 
            foreach ($times as $time) 
            {
                ErrorMessage::Marker("Create Median for $scenario / $time  \n");
                
                $result = self::ScenarioTimeMedian($species_id,$scenario,$time,false);
                if ($result instanceof ErrorMessage) 
                    return ErrorMessage::Stacked(__METHOD__, __LINE__, "", true, $result);                          

            }
                
            
    }    
    
    
    public static function LoadData($species_id,$echo = false)    
    {
        
        $result =  DatabaseMaxent::InsertAllMaxentResults($species_id,$echo);
        if ($result instanceof ErrorMessage) return ErrorMessage::Stacked(__METHOD__, __LINE__, "", true, $result);                                  
        
        return $result;
        
    }
    

    /**
     * Find ACSII grids for this species and create quicklooks
     * 
     * @param type $species_id
     * @param type $pattern     - Limit the ASCII GRID files to this pattern
     */
    public static function CreateQuickLook($species_id,$pattern,$echo = false)
    {
        if (is_null($pattern)) $pattern = '*';
        
        $folder = self::species_data_folder($species_id);
        
        $files = file::LS($folder."*{$pattern}*.asc", null , true);
        

        $count = 0;
        foreach ($files as $filename => $pathname) 
        {
            
            if ($echo) echo "Create Quick Look for [$species_id] .. [$filename] \n";
            $qlfn = SpeciesMaxentQuickLook::CreateImage($species_id, $pathname); // build quick look from asc 
            
            
            if ($echo) echo "Quicklook created for [$species_id] .. [".basename($qlfn)."]\n";
            
            $count++;
            
        }
        
    }
    
    
    public static function RemoveDataforSpecies($species_id,$echo = false)    
    {
        
        
        //$result  = DBO::Delete('maxent_values', "species_id = {$species_id}");        
        
        
    }
    
    
}


?>
