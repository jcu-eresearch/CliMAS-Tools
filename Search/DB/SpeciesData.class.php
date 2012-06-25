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
        
//        $conString = "";
//        $conString .= "host="    .ToolsDataConfiguration::Species_DB_Server()." ";
//        $conString .= "port="    .ToolsDataConfiguration::Species_DB_Port()." ";        
//        $conString .= "dbname="  .ToolsDataConfiguration::Species_DB_Database()." ";
//        $conString .= "user="    .ToolsDataConfiguration::Species_DB_Username()." ";
//        $conString .= "password=".ToolsDataConfiguration::Species_DB_Password()." ";
//        
//        $this->speciesDB = pg_connect($conString );

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
        
        // we don't have Postgress SQL php addin so  this is the way query works - thru sending to file and readin file.
        
        $sql = str_replace('"', '\"', $sql);
        
        $resultFilename = file::random_filename();
        
        $cmd  = " ";
        $cmd .= "export PGHOST="    .ToolsDataConfiguration::Species_DB_Server()  ." ; ";
        $cmd .= "export PGPORT="    .ToolsDataConfiguration::Species_DB_Port()    ." ; ";
        $cmd .= "export PGDATABASE=".ToolsDataConfiguration::Species_DB_Database()." ; ";
        $cmd .= "export PGUSER="    .ToolsDataConfiguration::Species_DB_Username()." ; ";
        $cmd .= "export PGPASSWORD=".ToolsDataConfiguration::Species_DB_Password()." ; ";
        $cmd .= 'psql  --quiet --no-align -c "'.$sql.'" --output '.$resultFilename;
        
        exec($cmd);
        
        if (!file_exists($resultFilename)) return null;
        
        $row = 0;
        $result = array();

        $handle = fopen($resultFilename, "r");        
        if ($handle !== FALSE) 
        {
            $headers = fgetcsv($handle, 0, "|");
            
            while (($data = fgetcsv($handle, 0, "|")) !== FALSE) 
            {
            
                if (count($data) == count($headers)) // this should stop any odd lines and the last line (nnnn rows)
                {
                    for ($c=0; $c < count($data); $c++) 
                    {
                        $result[$row][$headers[$c]] = trim($data[$c]);
                    }
                }
                
                $row++;
            }
            fclose($handle);
            
        }

        
        return $result;
        
    }

    
    public function speciesList($pattern = "%") 
    {
        $pattern = str_replace("*", "%", $pattern);
        
        if (util::last_char($pattern) != "%") $pattern .= "%";
        
        $q = "select id as species_id, scientific_name, common_name from species where common_name LIKE '{$pattern}'";

        $result = $this->query($q);
        
        return $result;
        
    }
    

    /**
     * 
     * 
     * @param type $speciesID  - Scientific Name here as then across systems and database rebuilds it wont change
     * @return array  [index] => (longitude,latitude)
     */
    public function speciesOccurance($speciesID) 
    {

        $speciesID = trim(urldecode($speciesID));
        
        // SELECT ST_X(location) AS longitude, ST_Y(location) AS latitude FROM occurrences where species_id = 2966 ;
        
        
        // get id for occurrences table  related to  species.species_id
        
        $speciesDatabaseID = "select id,scientific_name,common_name from species where scientific_name = '{$speciesID}'";
        $speciesDatabaseIDResult = $this->query($speciesDatabaseID);

        
        if (count($speciesDatabaseIDResult) == 0 ) 
        {
            // TODO log - message to user ?? / admnin
            return null;  // could not find 
        }
        
        $firstRow = util::first_element($speciesDatabaseIDResult); // may not be index [0]
        $databaseIDForSpecies = trim($firstRow['id']);
        
        $speciesOccuranceQ = "SELECT ST_X(location) AS longitude, ST_Y(location) AS latitude FROM occurrences where species_id = $databaseIDForSpecies";
        $speciesOccuranceResult = $this->query($speciesOccuranceQ);
        
        
        return $speciesOccuranceResult;
        
    }
    
    
    
    public static function SpeciesOccuranceToFile($speciesID,$filename) 
    {
        
        $SD = new SpeciesData();
        $result = $SD->speciesOccurance($speciesID);
        unset($SD);
        
        $file = '"SPPCODE","LATDEC","LONGDEC"'."\n";  // headers specific to Maxent JAR
        foreach ($result as $index => $row) 
        {
            $line = $speciesID.",".
                    $row['latitude'].",".
                    $row['longitude']."\n";
        
            $file .= $line;
        }
        
        
        file_put_contents($filename, $file);
        
        unset($result);
        unset($file);
        
        return file_exists($filename);
        
        
    }
    

    
    public static function SpeciesQuickInformation($speciesID) 
    {
        
        $speciesID = trim(urldecode($speciesID));
        
        
        // get id for occurrences table  related to  species.species_id
        $speciesDatabaseQ = "select scientific_name,common_name from species where scientific_name = '{$speciesID}' limit 1";

        $SD = new SpeciesData();
        $speciesDatabaseResult = $SD->query($speciesDatabaseQ);
        unset($SD);
        
        if (count($speciesDatabaseResult) == 0 ) 
        {
            // TODO log - message to user ?? / admnin
            return null;  // could not find 
        }
        
        $first = util::first_element($speciesDatabaseResult);
        
        $result = $first['common_name']." (".$first['scientific_name'].")";
        
        return $result;
        
    }
    
    
    
    
}


?>
