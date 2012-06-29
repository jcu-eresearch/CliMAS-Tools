<?php

/**
 *
 * Connect , disconnect and data flow to and from Species database
 *  
 * 
 * 
 */
class PG extends Object {


    private $DB = null;

    public function __construct($connect = true) {
        parent::__construct();
        
        if ($connect) $this->connect ();
        
    }

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
        
        if (!is_null($this->DB))
        {
            pg_close($this->DB);
            unset($this->DB);
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
    
    private function insert($sql) 
    {
        
        
        // we don't have Postgress SQL php addin so  this is the way query works - thru sending to file and readin file.
        
        $cmd  = " ";
        $cmd .= "export PGHOST="    .ToolsDataConfiguration::Species_DB_Server()  ." ; ";
        $cmd .= "export PGPORT="    .ToolsDataConfiguration::Species_DB_Port()    ." ; ";
        $cmd .= "export PGDATABASE=".ToolsDataConfiguration::Species_DB_Database()." ; ";
        $cmd .= "export PGUSER="    .ToolsDataConfiguration::Species_DB_Username()." ; ";
        $cmd .= "export PGPASSWORD=".ToolsDataConfiguration::Species_DB_Password()." ; ";
        $cmd .= "psql  --quiet --no-align -c \"$sql\" ";
        
        $result = array();
        exec($cmd,$result);

        print_r($result);
        
        if (!util::contains($result[0], "INSERT")) return null; // ttodo return better error value ?? 
        
        $lastId = trim($result[3]);
        
        
        echo "lastId = $lastId\n";
        
        return $lastId;
        
    }
    
    
    public static function WriteCommandAction(CommandAction $cmd) 
    {
        $id = $cmd->ID();
        $data = serialize($cmd);
    
        $q = "INSERT INTO ap02_command_action (objectid, data) VALUES ('{$id}', '{$data}'); select LASTVAL();";
        
        echo "WriteCommandAction \n{$q}\n";
        
        
        $db = new PG();
        $insert_result = $db->insert($q);
        unset($db);

        echo "insert_result {$insert_result}\n";
        
        return $insert_result;
        
    }
    
    
    public static function ReadCommandAction($commandID) 
    {
        
        $q = "select data from ap02_command_action where objectid = '{$commandID}';";

        
        echo "ReadCommandAction \n{$q}\n";
        
        // we don't have Postgress SQL php addin so  this is the way query works - thru sending to file and readin file.
        
        $cmd  = " ";
        $cmd .= "export PGHOST="    .ToolsDataConfiguration::Species_DB_Server()  ." ; ";
        $cmd .= "export PGPORT="    .ToolsDataConfiguration::Species_DB_Port()    ." ; ";
        $cmd .= "export PGDATABASE=".ToolsDataConfiguration::Species_DB_Database()." ; ";
        $cmd .= "export PGUSER="    .ToolsDataConfiguration::Species_DB_Username()." ; ";
        $cmd .= "export PGPASSWORD=".ToolsDataConfiguration::Species_DB_Password()." ; ";
        $cmd .= "psql  --quiet --no-align -c \"$q\"";
        
        $result = array();
        exec($cmd,$result);
        
        if (count($result[2]) < 3 ) return null;
        
        $serString = trim($result[2]);
        
        //$serString = str_replace("\'","'", $serString);
        
        echo "serString \n{$serString}\n";
        
        $object = unserialize($serString);
        $object instanceof CommandAction;
        
        print_r($object);
        
        return  $object;
        
    }
    

    
    
    
}


?>
