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
        
        if (is_null($result))
        {
            DBO::LogError(__METHOD__."(".__LINE__.")","Failed to Species List via sql [$sql]\n");
            return null;
        }
        
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
        
        if (is_null($first))
        {
            DBO::LogError(__METHOD__."(".__LINE__.")","Failed to SpeciesQuickInformation [$sql]\n");
            return null;
        }

        
        return array_util::Value($first, 'common_name')." (".array_util::Value($first, 'scientific_name').")";
    }
    
    
    public static function SpeciesInfoByID($species_id) 
    {
        
        $sql = "select scientific_name,common_name from species where id = $species_id";
        
        $result = DBO::QueryFirst($sql,'id');
        
        if (is_null($result))
        {
            DBO::LogError(__METHOD__."(".__LINE__.")","Failed to Species Info  species_id = [{$species_id}] [$sql]\n");
            return null;
        }
        
        
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
        
        $filetypeAnd = (is_null($filetype)) ? "" : "and mc.filetype   = ".util::dbq($filetype);
        $descAnd     = (is_null($desc))     ? "" : "and f.description = ".util::dbq($desc);
        
        
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
                  and m.dataname = ".util::dbq($model)."
                  and s.dataname = ".util::dbq($scenario)."
                  and t.dataname = ".util::dbq($time,true)." {$filetypeAnd} {$descAnd}
                  limit 1
                ;";
        
        
        $result = DBO::QueryFirstValue($sql,'file_id');
        
        return $result;
        
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
        
        $filetypeAnd = (is_null($filetype)) ? "" : "and mc.filetype   = ".util::dbq($filetype);
        
        
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
        
        $speciesOccuranceResult = DBO::Query($q);
        
        if (is_null($speciesOccuranceResult)) 
        {
            DBO::LogError(__METHOD__."(".__LINE__.")","Failed to get Species Occurence data from data base using \n q = $q \n speciesID = $speciesID  \n");
            return null;
        }
        
        
        return $speciesOccuranceResult;
    }
    
    


    
    public function SpeciesOccuranceToFile($speciesID, $occurFilename) 
    {
        
        $result = self::SpeciesOccurance($speciesID);
        
        if (is_null($result)) 
        {
            DBO::LogError(__METHOD__."(".__LINE__.")","Failed to get Species Occurence Data speciesID = $speciesID  \n");
            return null;
        }
        
        
        // this will create occurance file where the "name" of the species will be the Species ID from database
        // this seems to have to match the Lambdas filename
        
        $file = '"SPPCODE","LATDEC","LONGDEC"'."\n";  // headers specific to Maxent JAR
        foreach ($result as $row) 
            $file .= $speciesID.",".$row['latitude'].",".$row['longitude']."\n";
        
        file_put_contents($occurFilename, $file);

        
        if (!file_exists($occurFilename)) 
        {
            DBO::LogError(__METHOD__."(".__LINE__.")","Failed to wite Species Occurence Data File speciesID = $speciesID  occurFilename = $occurFilename \n");
            return null;
        }
        
        
        // check the validity of the file - a "good" number of lines
        $lineCount = file::lineCount($occurFilename);
        
        echo "occurFilename = $occurFilename  lineCount = $lineCount\n";

        
        if ($lineCount < self::$OCCURANCE_MINIMUM_LINES) 
        {
            file::Delete($occurFilename);
            DBO::LogError(__METHOD__."(".__LINE__.")","Failed to get Occurence $occurFilename it was too small  speciesID = $speciesID  \n");
            return null;
        }
        
        
        
        
        unset($result);
        unset($file);
        
        return true;
        
    }
    
    
    
    
    
}


?>
