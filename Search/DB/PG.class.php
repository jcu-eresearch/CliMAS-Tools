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
        
        if (!util::contains($result[0], "INSERT")) return null; // ttodo return better error value ?? 
        
        $lastId = trim($result[3]);
        
        return $lastId;
        
    }

    private function update($sql) 
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
        $ins = exec($cmd,$result);
        
        if (!util::contains($ins, "UPDATE")) return null;

        $split = explode(" ",$ins);
        $count = trim($split[1]);
        
        return  $count; // update count
        
    }
    
    
    
    public static function WriteCommandAction(CommandAction $cmd) 
    {
        $id = $cmd->ID();
        $data = urlencode(serialize($cmd));
    
        // get count for this ID  , if 0 then iunsert else update
        
        $db = new PG();

        $qid = configuration::CommandQueueID();
        
        
        // check to see if we already have it.
        $count = self::CommandActionCount($id);
        if ($count > 0)
        {
            // update 
            $q = "update ap02_command_action set queueid='{$qid}',data='{$data}',status='{$cmd->Status()}',execution_flag='{$cmd->ExecutionFlag()}' where objectid = '$id';";    
            $updateCount = $db->update($q);    
            if ($updateCount != 1 ) return null;
            
            $q = "select id from ap02_command_action where queueid='{$qid}',objectid = '$id';";    
            $updatedIDResult = $db->query($q);    

            if (is_null($updatedIDResult)) return null;
            
            $result = $updatedIDResult[0]['id'];
            
        }
        else
        {
            // insert
            $q = "INSERT INTO ap02_command_action (queueid,objectid, data,status,execution_flag) VALUES ('{$qid}','{$id}', '{$data}','{$cmd->Status()}','{$cmd->ExecutionFlag()}'); select LASTVAL();"; 
            $result = $db->insert($q); 
        }
        
        
        unset($db);
        
        return $result; // will hold the row id of the object that was just updated.
        
    }
    
    
    public static function ReadCommandAction($commandID) 
    {
        
        $qid = configuration::CommandQueueID();
        
        $q = "select data from ap02_command_action where queueid='{$qid}' and objectid = '{$commandID}';";
        
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
        
        if (count($result) < 3 ) return null;
        
        $serString = urldecode($result[1]);
        
        $object = unserialize($serString);
        $object instanceof CommandAction;
        
        return  $object;
        
    }
    

    public static function CommandActionCount($id = null) 
    {
        
        // we don't have Postgress SQL php addin so  this is the way query works - thru sending to file and readin file.
        
        $qid = configuration::CommandQueueID();
        
        $q = "select count(*) from ap02_command_action";
        
        if (!is_null($id))
            $q = "select count(*) from ap02_command_action where queueid='{$qid}' and objectid = '{$id}'";
        
        $q = "{$q};";
            
        $cmd  = " ";
        $cmd .= "export PGHOST="    .ToolsDataConfiguration::Species_DB_Server()  ." ; ";
        $cmd .= "export PGPORT="    .ToolsDataConfiguration::Species_DB_Port()    ." ; ";
        $cmd .= "export PGDATABASE=".ToolsDataConfiguration::Species_DB_Database()." ; ";
        $cmd .= "export PGUSER="    .ToolsDataConfiguration::Species_DB_Username()." ; ";
        $cmd .= "export PGPASSWORD=".ToolsDataConfiguration::Species_DB_Password()." ; ";
        $cmd .= "psql  --quiet --no-align -c \"$q\"";
        
        
        $result = array();
        exec($cmd,$result);
        
        if (count($result) < 3 ) return null;
        
        $count = trim($result[1]);
        
        return  $count;
        
    }


    /**
     * Pass true into this function to really do it.
     * 
     * @param type $really
     * @return null 
     */
    public static function CommandActionRemoveAll($really = false) 
    {
        
        if (!$really) return;
        
        // we don't have Postgress SQL php addin so  this is the way query works - thru sending to file and readin file.
        
        $qid = configuration::CommandQueueID();
        
        $q = "delete from ap02_command_action where queueid='{$qid}';";
        
        $cmd  = " ";
        $cmd .= "export PGHOST="    .ToolsDataConfiguration::Species_DB_Server()  ." ; ";
        $cmd .= "export PGPORT="    .ToolsDataConfiguration::Species_DB_Port()    ." ; ";
        $cmd .= "export PGDATABASE=".ToolsDataConfiguration::Species_DB_Database()." ; ";
        $cmd .= "export PGUSER="    .ToolsDataConfiguration::Species_DB_Username()." ; ";
        $cmd .= "export PGPASSWORD=".ToolsDataConfiguration::Species_DB_Password()." ; ";
        $cmd .= "psql  --quiet --no-align -c \"$q\"";
        
        
        $result = array();
        $del = exec($cmd,$result);
        
        if (!util::contains($del, "DELETE")) return null;

        $split = explode(" ",$del);
        $count = trim($split[1]);
        
        return  $count;
        
    }
    
    
    public static function CommandActionRemove($commandID) 
    {
        
        // we don't have Postgress SQL php addin so  this is the way query works - thru sending to file and readin file.
        
        $qid = configuration::CommandQueueID();
        
        $q = "delete from ap02_command_action where queueid='{$qid}' and objectid = '{$commandID}';";
        
        $cmd  = " ";
        $cmd .= "export PGHOST="    .ToolsDataConfiguration::Species_DB_Server()  ." ; ";
        $cmd .= "export PGPORT="    .ToolsDataConfiguration::Species_DB_Port()    ." ; ";
        $cmd .= "export PGDATABASE=".ToolsDataConfiguration::Species_DB_Database()." ; ";
        $cmd .= "export PGUSER="    .ToolsDataConfiguration::Species_DB_Username()." ; ";
        $cmd .= "export PGPASSWORD=".ToolsDataConfiguration::Species_DB_Password()." ; ";
        $cmd .= "psql  --quiet --no-align -c \"$q\"";
        
        
        $result = array();
        $del = exec($cmd,$result);
        
        if (!util::contains($del, "DELETE")) return null;

        $split = explode(" ",$del);
        $count = trim($split[1]);
        
        return  $count;
        
    }
    
    
    
    public static function CommandActionListIDs() 
    {
        
        // we don't have Postgress SQL php addin so  this is the way query works - thru sending to file and readin file.
        
        $qid = configuration::CommandQueueID();
        
        $q = "select objectid from ap02_command_action where queueid='{$qid}';";
        
        $cmd  = " ";
        $cmd .= "export PGHOST="    .ToolsDataConfiguration::Species_DB_Server()  ." ; ";
        $cmd .= "export PGPORT="    .ToolsDataConfiguration::Species_DB_Port()    ." ; ";
        $cmd .= "export PGDATABASE=".ToolsDataConfiguration::Species_DB_Database()." ; ";
        $cmd .= "export PGUSER="    .ToolsDataConfiguration::Species_DB_Username()." ; ";
        $cmd .= "export PGPASSWORD=".ToolsDataConfiguration::Species_DB_Password()." ; ";
        $cmd .= "psql  --quiet --no-align -c \"$q\"";
        
        
        $result = array();
        exec($cmd,$result);
        
        if (count($result) < 3 ) return null;
        
        unset($result[util::first_key($result)]);
        unset($result[util::last_key($result)]);
        
        return $result;
        
    }
    

    public static function CommandActionExecutionFlag($id = null) 
    {
        $qid = configuration::CommandQueueID();
        
        if (!is_null($id)) 
            $q = "select objectid,execution_flag from ap02_command_action where queueid='{$qid}' and objectid = '{$id}'"; 
        else
            $q = "select objectid,execution_flag from ap02_command_action where queueid='{$qid}'";    
        
            
            
        $db = new PG();
        $result = $db->query($q);
        unset($db);
        
        $efresult = null;
        
        if (!is_null($id))
        {
            $first_row = util::first_element($result);
            $efresult = $first_row['execution_flag'];
        }
        else
        {
            $efresult = array();
            foreach ($result as $row) 
                $efresult[$row['objectid']] = $row['execution_flag'];
            
        }
        
        unset($result);
        
        return $efresult;
        
    }
    
    
    public static function CommandActionStatus($id = null) 
    {

        $qid = configuration::CommandQueueID();
        
        if (!is_null($id)) 
            $q = "select objectid,status from ap02_command_action where queueid='{$qid}' and objectid = '{$id}'"; 
        else
            $q = "select objectid,status from ap02_command_action where queueid='{$qid}'";    
        
            
        $db = new PG();
        $result = $db->query($q);
        unset($db);
        
        $efresult = null;
        
        if (!is_null($id))
        {
            // one status
            $first_row = util::first_element($result);
            $efresult = $first_row['status'];
            
        }
        else
        {
            // many status
            $efresult = array();
            foreach ($result as $row) 
                $efresult[$row['objectid']] = $row['status'];
            
        }
        
        
        unset($result);
        
        return $efresult;
        
    }
    
    
    
}


?>
