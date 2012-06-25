<?php

/**
 *
 * Connect , disconnect and data flow to and from Species database
 *  
 * 
 * 
 */
class SpeciesData extends Object {

    //put your code here

    private $speciesDB = null;

    public function __construct($connect = true) {
        parent::__construct();
        
        if ($connect) $this->connect ();
        
    }

//Host: tdh-tools-2.hpc.jcu.edu.au
//User: ap02
//Password: 
//Database Name: ap02    
    
    
    // SELECT ST_X(location) AS longitude, ST_Y(location) AS latitude FROM occurrences;
    

    public function connect()
    {
        
        $conString = "";
        $conString .= "host="    .ToolsDataConfiguration::Species_DB_Server()." ";
        $conString .= "port="    .ToolsDataConfiguration::Species_DB_Port()." ";        
        $conString .= "dbname="  .ToolsDataConfiguration::Species_DB_Database()." ";
        $conString .= "user="    .ToolsDataConfiguration::Species_DB_Username()." ";
        $conString .= "password=".ToolsDataConfiguration::Species_DB_Password()." ";
        
        $this->speciesDB = pg_connect($conString );

    }

    public function __destruct() {
        
        if (!is_null($this->speciesDB))
        {
            pg_close($this->speciesDB);
            unset($this->speciesDB);
        }

        parent::__destruct();
    }

    
    private function query($sql) 
    {
        
        $result = pg_query($this->speciesDB, $sql);
        if (!$result) {
            // TODO log this 
            return null;
        }
        
        $arr = pg_fetch_all($result);
        
        return $arr;
        
    }

    
    public function speciesList($pattern = "%") 
    {
        $pattern = str_replace("*", "%", $pattern);
        
        if (util::last_char($pattern) != "%") $pattern .= "%";
        
        $q = "select id as species_id, scientific_name, common_name from species where common_name LIKE '{$pattern}'";

        $result = $this->query($q);
        
        return $result;
        
    }
    

}


?>
