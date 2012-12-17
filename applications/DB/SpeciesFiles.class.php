<?php

/**
 *
 * Access Species Files via filesystem 
 * basically a DBO for file access - so pathing information does have to known across the whoile app.
 *  
 * If a method has thesame nameas one one in Species Data then this is the same output but from filesystem
 * 
 */
class SpeciesFiles {
    
    
    /**
     * Interaction between file system and Database - use file system - folder to find species - id's
     * and then use database to return Name 
     * 
     * - Only return species information for Folders that exist
     * 
     * @param type $pattern
     * @return \ErrorMessage 
     */
    public static function speciesList($pattern = "")  //
    {
        
        $pattern = util::CleanString($pattern);
        $pattern = "%{$pattern}%";
        
        $data = SpeciesData::ComputedNameList($pattern, false);
        if ($data instanceof ErrorMessage) 
            return ErrorMessage::Stacked(__METHOD__, __LINE__, "", true, $data);
        
        
        $speciesNames = SpeciesData::speciesList($pattern);
        if ($speciesNames instanceof ErrorMessage) 
            return ErrorMessage::Stacked(__METHOD__, __LINE__, "", true, $speciesNames);

        
        // keys to species id - value = pathname to data
        $folders = file::LSfolders(configuration::Maxent_Species_Data_folder()."*");
        
        $result = array();
        foreach ($data as $name => $species_id ) 
        {
            
            if (array_key_exists($species_id,$folders ))
            {
                
                $row = array();
                $row['species_id']      = $species_id ;
                $row['scientific_name'] = $speciesNames[$species_id]['scientific_name'];
                $row['common_name']     = $name;
                $row['full_name']       = ($name == $row['scientific_name']) 
                                                ? $row['scientific_name'] 
                                                : "{$name} ({$row['scientific_name']})";
                
                $result[] = $row;
                
            }
            
        }

        
        
        return $result;
    }
    
    
    
    
    /**
     *
     * Instead of FILE id will return the species path name 
     * 
     * @param type $speciesID
     * @param type $filetype Limit to this filetype only
     * @param string $extraWhere (usually in for format   column op value)
     * @return type 
     */
    public static function GetAllModelledData($species_id)
    {
        
        if (is_null($species_id)) return null;
        
        $species_id = trim($species_id);
        if ($species_id == "") return null;
        
        $folder = self::species_data_folder($species_id);
        
            
        $name = SpeciesData::SpeciesName($species_id);
        $common_name  =  SpeciesData::SpeciesCommonNameSimple($species_id);
        
        $result = array();
        
        $files = file::LS("{$folder}*.asc", null, true); // all ascii grids from the data folder
        
        ksort($files);

        
        foreach ($files as $basename => $pathname) 
        {
            
            $combination = str_replace(".asc", "", $basename);
            
            if (!util::contains($combination, "_")) continue;
            
            try {
               
                
                list($scenario,$model,$time) = explode("_",$combination);
                
                $combination_pathname =  $species_id
                                         .configuration::osPathDelimiter()
                                         .configuration::Maxent_Species_Data_Output_Subfolder()
                                         .configuration::osPathDelimiter()
                                         .$combination;

                
                $row = array();

                $row['species_id']      = $species_id;
                $row['scientific_name'] = $name;
                $row['common_name']     = $common_name;
                $row['scenario']        = $scenario;
                $row['model']           = $model;
                $row['time']            = $time;
                $row['ASCII_GRID']      = $combination_pathname.".asc"; 
                $row['QUICK_LOOK']      = $combination_pathname.".png"; 

                $result[$combination]  = $row;
                
            } catch (Exception $exc) {
                
                ErrorMessage::Marker("combination = $combination  ...  pathname = $pathname " .$exc->getMessage());
                
            }

            
        }
        
        return $result;
        
    }
    
    
    public static function species_data_folder($species_id)
    {
        $folder = configuration::Maxent_Species_Data_folder()
                    .$species_id
                    .configuration::osPathDelimiter()
                    .configuration::Maxent_Species_Data_Output_Subfolder()
                    .configuration::osPathDelimiter()
                    ;

        echo("<pre>species_data_folder is " .$folder . "</pre>");
        
        return $folder;
    }
    
    public static function species_data_folder_web($species_id)
    {
        $folder = configuration::Maxent_Species_Data_folder_web()
                    .$species_id
                    .configuration::osPathDelimiter()
                    .configuration::Maxent_Species_Data_Output_Subfolder()
                    .configuration::osPathDelimiter()
                    ;
        
        return $folder;
        
    }
    
    
    
    
}

?>
