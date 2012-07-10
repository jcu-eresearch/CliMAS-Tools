<?php

/**
 *
 * Connect , disconnect and data flow to and from Species database
 *  
 * 
 * 
 */
class SpeciesData extends Object {

    
    /**
     *
     * For any species make sure we have occurance data
     * 
     * @param type $pattern
     * @return type 
     */
    public static function speciesList($pattern = "%",$min_count = 100) 
    {
        
        $db = new PGDB();
        
        $pattern = str_replace("*", "%", $pattern);
        
        if (util::last_char($pattern) != "%") $pattern .= "%";
   
        $q = "select s.id as species_id ,s.scientific_name,s.common_name,sp.count as occurance_count, (s.common_name || ' (' || s.scientific_name || ')' ) as full_name   from species s, species_occurence sp  where s.id=sp.species_id and sp.count >= {$min_count}  and (s.common_name LIKE '{$pattern}' or  s.scientific_name LIKE '{$pattern}' ) ";
        
        $result = $db->query($q);
        
        unset($db);
        
        return $result;
        
    }
    

    
    
    
    

    
    public static function SpeciesQuickInformation($speciesID) 
    {
        
        $db = new PGDB();
        
        $q = "select scientific_name,common_name from species where scientific_name = '{$speciesID}' limit 1";

        $db_result = $db->query($q, 'scientific_name');
        
        $first = util::first_element($db_result);
        
        $result = $first['common_name']." (".$first['scientific_name'].")";
        
        unset($db);        
        
        return $result;
        
    }
    
    
    
    
}


?>
