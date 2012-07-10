<?php
/**
 *
 * Connect , disconnect and data flow to and from PostGres database
 *  
 *  sudo -u postgres psql ap02
 * 
 */
class PGDB extends Object {

    
    private $DB = null;

    /*
     * @param $via_commandline  True = Force use of Command Line for Database access
     * 
     */
    public function __construct($via_commandline = false) {
        parent::__construct();
        
        $this->ViaCommandLine($via_commandline);
        
        $this->connect();
        
    }

    public function q($str)
    {
        return "'".$str."'";
    }
    
    
    public function connect()
    {

        if (!function_exists('pg_connect'))  
                $this->ViaCommandLine(true); 
        
            
        if (!$this->ViaCommandLine())
        {
            $conString = "";
            $conString .= "host="    .ToolsDataConfiguration::Species_DB_Server()." ";
            $conString .= "port="    .ToolsDataConfiguration::Species_DB_Port()." ";        
            $conString .= "dbname="  .ToolsDataConfiguration::Species_DB_Database()." ";
            $conString .= "user="    .ToolsDataConfiguration::Species_DB_Username()." ";
            $conString .= "password=".ToolsDataConfiguration::Species_DB_Password()." ";

            $this->DB = pg_connect($conString);

            if ($this->DB === FALSE) 
            {
                $this->ViaCommandLine(true);
            }
        }

        
        $this->ImageTableName('images');
        $this->FilesTableName('files_data');
        $this->ActionsTableName('ap02_command_action');
        
        $this->ModelledDataTableName('modelled_species_data');
        
        $this->QueueID(configuration::CommandQueueID());
        
        
        
        
        
    }
    
    public function __destruct() {
        
        
        if (!is_null($this->DB))
        {
            if (function_exists('pg_close'))
            {
                pg_close($this->DB);
                unset($this->DB);
            }            
        }

        parent::__destruct();
    }

    
    public function query($sql,$keyColumn = null) 
    {

        if ($this->ViaCommandLine()) return $this->queryByCommandLine($sql,$keyColumn);
        
        
        if (is_null($this->DB)) return null; //TODO: fail nicely needs to handle
        
        $pg_result = pg_exec($this->DB, $sql);
        
        $numrows = pg_numrows($pg_result);
        
        if ($numrows <= 0) return array();  //return empty array
        
        $result = array();
        for($ri = 0; $ri < $numrows; $ri++) 
        {
            if (is_null($keyColumn))
                $result[$ri] = pg_fetch_array($pg_result, $ri,PGSQL_ASSOC);
            else
            {
                $row = pg_fetch_array($pg_result, $ri,PGSQL_ASSOC);
                {
                    if (array_key_exists($keyColumn, $row))
                        $result[$row[$keyColumn]] = $row;
                    else
                        $result[$ri] = $row; // fall back incase column does not exist
                    
                }
                
            }
            
        }
        
        unset($pg_result);
        
        return $result;
        
    }
    
    
    /**
     * Used when pg_connect does not exist - 
     * 
     * @param type $sql
     * @return null 
     */
    private function queryByCommandLine($sql,$keyColumn = null) 
    {
        
        // 
        // we don't have Postgress SQL php addin so  this is the way query works - thru sending to file and readin file.
        //
        $sql = str_replace('"', '\"', $sql);

        //echo "\n\nQuery By Command Line \n{$sql}\n";
        
        
        $resultFilename = file::random_filename();
        
        $cmd  = " ";
        $cmd .= "export PGHOST="    .ToolsDataConfiguration::Species_DB_Server()  ." ; ";
        $cmd .= "export PGPORT="    .ToolsDataConfiguration::Species_DB_Port()    ." ; ";
        $cmd .= "export PGDATABASE=".ToolsDataConfiguration::Species_DB_Database()." ; ";
        $cmd .= "export PGUSER="    .ToolsDataConfiguration::Species_DB_Username()." ; ";
        $cmd .= "export PGPASSWORD=".ToolsDataConfiguration::Species_DB_Password()." ; ";
        $cmd .= 'psql  --quiet --no-align -c "'.$sql.'" --output '.$resultFilename;
        
        //echo "resultFilename = $resultFilename\n";
        
        exec($cmd);
        
        if (!file_exists($resultFilename)) return null;
        
        $row = 0;
        $result = array();

        $keyColumnIndex = -1;
        
        $handle = fopen($resultFilename, "r");        
        if ($handle !== FALSE) 
        {
            $headers = fgetcsv($handle, 0, "|");
            
            if (!is_null($keyColumn))
                $keyColumnIndex = array_util::ArrayKey($headers, $keyColumn);
            
            while (($data = fgetcsv($handle, 0, "|")) !== FALSE) 
            {
            
                if (count($data) == count($headers)) // this should stop any odd lines and the last line (nnnn rows)
                {
                    for ($c=0; $c < count($data); $c++) 
                    {
                        
                        if (is_null($keyColumn))
                        {
                            $result[$row][$headers[$c]] = trim($data[$c]);    
                        }
                        else
                        {
                            $result[trim($data[$keyColumnIndex])][$headers[$c]] = trim($data[$c]); 
                        }
                        
                        
                    }
                }
                
                $row++;
            }
            fclose($handle);
            
        }

        
        return $result;
        
    }
    
    
    
    public function insert($sql) 
    {

        return $this->insertByCommandLine($sql);
        
//        $sql = str_replace(";", '', $sql);
//        
//        $sql .= "; select LASTVAL();";
//        
//        $pg_result = pg_exec($this->DB, $sql);
//
//        $insert_result = pg_fetch_array($pg_result, 0,PGSQL_ASSOC);
//        
//        $lastid = array_util::Value($insert_result,'lastval',null);
//        
//        if (is_null($lastid)) return null; // TODO: Better Error ??
//        
//        return $lastid;
        
    }

    
    private function insertByCommandLine($sql) 
    {
        
        // we don't have Postgress SQL php addin so  this is the way query works - thru sending to file and readin file.
        $sql = trim($sql);
        $sql = util::trim_end($sql, ';');
        
        $sql .= "; select LASTVAL();";
        
        $cmd  = " ";
        $cmd .= "export PGHOST="    .ToolsDataConfiguration::Species_DB_Server()  ." ; ";
        $cmd .= "export PGPORT="    .ToolsDataConfiguration::Species_DB_Port()    ." ; ";
        $cmd .= "export PGDATABASE=".ToolsDataConfiguration::Species_DB_Database()." ; ";
        $cmd .= "export PGUSER="    .ToolsDataConfiguration::Species_DB_Username()." ; ";
        $cmd .= "export PGPASSWORD=".ToolsDataConfiguration::Species_DB_Password()." ; ";
        $cmd .= "psql  --quiet --no-align -c \"$sql\" ";
        
        $result = array();
        exec($cmd,$result);
        
        if (!util::contains($result[0], "lastval")) return null; // ttodo return better error value ?? 
        
        $lastId = trim($result[1]);
        
        return $lastId;
        
    }
    
    
    
    
    public function update($sql) 
    {
        
        if ($this->ViaCommandLine()) return $this->updateByCommandLine($sql);
        
        $result = pg_exec($this->DB, $sql);
        return pg_affected_rows($result);
    }

    
    private function updateByCommandLine($sql) 
    {
        
        // we don't have Postgress SQL php addin so  this is the way query works - thru sending to file and readin file.
        
        $cmd  = " ";
        $cmd .= "export PGHOST="    .ToolsDataConfiguration::Species_DB_Server()  ." ; ";
        $cmd .= "export PGPORT="    .ToolsDataConfiguration::Species_DB_Port()    ." ; ";
        $cmd .= "export PGDATABASE=".ToolsDataConfiguration::Species_DB_Database()." ; ";
        $cmd .= "export PGUSER="    .ToolsDataConfiguration::Species_DB_Username()." ; ";
        $cmd .= "export PGPASSWORD=".ToolsDataConfiguration::Species_DB_Password()." ; ";
        $cmd .= "psql --no-align -c \"$sql\" ";
        
        //echo "$cmd\n";
        
        $result = array();
        $ins = exec($cmd,$result);
        
        if (!util::contains($ins, "UPDATE")) return null;

        $split = explode(" ",$ins);
        $count = trim($split[1]);
        
        return  $count; // update count
        
    }
    
    
    
    /**
     *
     * i
     * 
     * @param type $table
     * @param type $where   ~~~~~ then this allows deletetion of whoile table of data (very scary)
     * @return null 
     */
    public function delete($table,$where) 
    {
        if ($where == "~~~~~") 
        {
            if ($this->ViaCommandLine()) return $this->updateByCommandLine($sql);    
            $result =  pg_exec($this->DB, "delete from {$table};");
            return pg_affected_rows($result);               
        }
        else
        {
            if ($this->ViaCommandLine()) return $this->updateByCommandLine($sql);    
            $result =  pg_exec($this->DB, "delete from {$table} where {$where};");
            return pg_affected_rows($result);   
        }
        
        return null;
    }
    

    public function last_insert()
    {
        $sql = " select LASTVAL();";
        $lastval_result = $this->query($sql);        
        $first = util::first_element($lastval_result);
        $lastid = array_util::Value($first,'lastval',null);
        return $lastid;   
    }


    public function HasImage($file_unique_id) 
    {
        $count =  count($this->CountFile($file_unique_id));
        return $count > 0;
    }
    
    
    public function InsertImage($srcFilename,$description) 
    {
        return $this->InsertFile($srcFilename, $description,'image');
    }
    
    public function GetImage($file_unique_id,$dest_filename) 
    {
        $imageResult = $this->ReadFile2Filesystem($file_unique_id, $dest_filename);
        return  $imageResult;
    }

    
    /**
     * 
     * Read File info for a File ID 
     * 
     * Array Keys
     * 
     * file_unique_id    
        mimetype         
        file_description 
        category         
        totalparts       
        total_filesize   
     * 
     */
    public function FileInfo($file_unique_id) 
    {
        
        $q = "select * from {$this->FilesTableName()} where file_unique_id = '{$file_unique_id}' limit 1";
        $result = $this->query($q);
        
        $first = util::first_element($result);
        
        unset($first['data']);    // this is incomplete
        unset($first['id']);      // is the database id of the data row and is not useful
        unset($first['partnum']); // partnum not needed
        
        
        return $first;
        
    }
    
    
    /**
     *
     * @param type $srcFilename Path to file to insert ito DB
     * @param type $description Some string that can be used to lookup the file later
     * @param type $category    Category of the file e.g. Image, html ...
     * @return string unique_file_id - use this id to get file backl from database;
     * @throws Exception 
     */
    public function InsertFile($srcFilename,$description,$category = 'file') 
    {
        return $this->InsertFileByCommandLine($srcFilename, $description, $category);
    }
    

    /**
     *
     * @param type $srcFilename
     * @param type $description
     * @param type $category
     * @return type
     * @throws Exception 
     */
    private function InsertFileByCommandLine($srcFilename,$description,$category = 'file') 
    {
        
        $chunck_size = 20000;

        $total_filesize = filesize($srcFilename);
        $totalparts = ceil($total_filesize / $chunck_size);
        
        $mimetype = mime_content_type($srcFilename);
        
        $file_unique_id =  uniqid();
        
        $partnum = 0;
        $handle = fopen($srcFilename, "rb");
        
        $total_read = 0;
        while (!feof($handle)) {

            $contents = fread($handle, $chunck_size);
            
            $total_read += strlen($contents);
            
            $data = base64_encode($contents);

            $sql  = "insert into {$this->FilesTableName()} ";
            $sql .=  "(file_unique_id,mimetype,partnum,file_description,totalparts,total_filesize,data,category)";
            $sql .=  " values ";
            $sql .=  "('{$file_unique_id}','{$mimetype}',{$partnum},'{$description}',{$totalparts},$total_filesize,'{$data}','{$category}')";
            $insertResult = $this->insert($sql);    
            
            //echo "sql = $sql\n";
            
             // echo "insertResult = $insertResult   total_read = $total_read of {$total_filesize}\n";
            
            if (!is_numeric($insertResult) || $insertResult == -1) 
            {
                throw new Exception("FAILED:: to insert file in to DB {$srcFilename} with description {$description} at part {$partnum} using a comand line call");
            }
            
            $partnum++;
        }        

        //$this->update("update {$this->FilesTableName()} set total_filesize = {$total_read} where file_unique_id = '{$file_unique_id}'");
        
        
        return $file_unique_id;
        
    }
    
    
    public function HasFile($file_unique_id) 
    {
        $count =  count($this->CountFile($file_unique_id));
        return $count > 0;
    }
    
    
    
    /**
     * Get files that have been stored in database via  InsertFile
     * 
     * @param string $unique_id
     * @param bool $echo_data - if you want to display the data as it comes out of the database - ie.e. stream it out to some where
     * @param bool $collect_data - if you want to collect data to a file to be used later - probably want to set tis to FALSE for streaming
     * @return string|null  -  string - Binary string of file<br> or Null on error
     *  
     */
    public function ReadFile($file_unique_id,$echo_data = false,$collect_data = true) 
    {
        
        $sql = "select data from {$this->FilesTableName()} where file_unique_id = '{$file_unique_id}' order by partnum;";
        
        //echo "Read File here  {$sql} \n";
        
        $query_result = $this->query($sql);
        
        if(is_null($query_result)) return null;
        
        if (count($query_result) <= 0) return null;
        
        if ($collect_data) $result_file = '';
        foreach ($query_result as $row) 
        {    
            $datapart =  base64_decode($row['data']);
            
            if ($collect_data) $result_file .= $datapart;
            
            if ($echo_data) echo $datapart;

        }
        
        unset($row);
        unset($query_result);        
        
        if ($collect_data) return $result_file;
        
        return true;
        
    }

    
    public function ReadFile2Filesystem($file_unique_id,$dest_filename = null) 
    {
        
        if (is_null($dest_filename)) $dest_filename = file::random_filename(); // 
        
        $result_file =  $this->ReadFile($file_unique_id);
        
        if (is_null($result_file)) return null;
        
        $info = $this->FileInfo($file_unique_id);
        
        $fw = fopen($dest_filename,'wb');
        fwrite($fw,$result_file,$info['total_filesize']);
        fclose($fw);
        

        if (!file_exists($dest_filename)) 
        {
            return null;
        }
        
        return $dest_filename;
        
    }
    
    public function ReadFile2Stream($file_unique_id) 
    {        
        $this->ReadFile($file_unique_id,true,false);  // read file stream and dont collect data - ignore return varaibales
    }
    
    public function ReadFileMimeType($file_unique_id) 
    {        
        $sql = "select mimetype from {$this->FilesTableName()} where file_unique_id = '{$file_unique_id}' limit 1";
        
        $mimetype_result = $this->query($sql);
        $first = util::first_element($mimetype_result);
        
        return trim($first['mimetype']);
        
    }
    

    
    public function RemoveFile($file_unique_id) 
    {        
        
        $sql = "delete from {$this->FilesTableName()} where file_unique_id = '{$file_unique_id}'";
        $this->update($sql);        

        if ($this->CountFile($file_unique_id) > 0)   // check to see if there are any rows left with this id
            return false; 
        
        return true;
        
    }


    public function CountFile($file_unique_id) 
    {        
        $count =  $this->CountUnique($this->FilesTableName(), 'file_unique_id', "file_unique_id = '{$file_unique_id}'");
        return $count;
    }
    
    
    public function Count($table,$where) 
    {        
        $sql = "select count(*) as row_count from $table where {$where}";
        
        $count_result = $this->query($sql);
        
        if (is_null($count_result)) return -1;
        if (count($count_result) <= 0) return -1;
        
        $first = util::first_element($count_result);
        
        $count = array_util::Value($first, 'row_count', -1);
        
        return $count;
        
    }

    public function Unique($table,$field,$where = null) 
    {        
        if (!is_null($where)) $where = " where {$where} " ;
        
        $sql = "select $field from $table $where group by $field order by $field";
        
        $result = $this->query($sql);
        if (is_null($result)) return null;
        return $result;
    }

    public function CountUnique($table,$field,$where = null) 
    {        
        return count($this->Unique($table, $field,$where));
    }
    
    /**
     * Add new Action to queue or update the current action
     * 
     * @param CommandAction $cmd
     * @return null 
     */
    public static function CommandActionQueue(CommandAction $cmd) 
    {
        
        $cmd instanceof CommandAction;

        $db = new PGDB();
        
        $data = base64_encode(serialize($cmd));
        
        $command_id = $cmd->ID();
        
        // check to see if we already have it.
        $count = $db->Count($db->ActionsTableName(), "queueid = '{$db->QueueID()}' and objectid = '{$command_id}'");
        if ($count > 0)
        {
            // update 
            $q = "update {$db->ActionsTableName()} set data = '{$data}',status='{$cmd->Status()}',execution_flag='{$cmd->ExecutionFlag()}' where objectid = '{$command_id }';"; 
            
            $updateCount = $db->update($q);    
            if ($updateCount != 1 ) return null;
            
        }
        else
        {
            // insert
            $q = "INSERT INTO {$db->ActionsTableName()} (queueid,objectid, data,status,execution_flag) VALUES ('{$db->QueueID()}','{$command_id}', '{$data}','{$cmd->Status()}','{$cmd->ExecutionFlag()}');"; 
            if (is_null($db->insert($q))) return null; 
            
        }
        
        unset($db);
        
        return  $command_id; // will hold the row id of the object that was just updated.
        
    }
    
    
    public static function CommandActionStatus($src) 
    {
        
        if (is_null($src))  return null;
        
        $id = ($src instanceof CommandAction) ? $id->ID() : $src;
        
        $db = new PGDB();

        $q = "select objectid,status from {$db->ActionsTableName()} where queueid='{$db->QueueID()}' and objectid = '{$id}'"; 
        
        $result = $db->query($q,'objectid');
        if (count($result) <= 0 ) return null;
        
        $first_row = util::first_element($result);
        
        $status = array_util::Value($first_row, 'status', null);
        
        return $status;
        
    }
    
    public static function CommandActionRead($commandID) 
    {

        $db = new PGDB();
        
        $q = "select data from {$db->ActionsTableName()} where queueid='{$db->QueueID()}' and objectid = '{$commandID}';";
        
        $result = $db->query($q,'objectid');
        
        
        if (count($result) <= 0 ) 
        {
            return null;
        }
        
        $first_row = util::first_element($result);
        
        $data = array_util::Value($first_row, 'data', null);
        if (is_null($data)) return null;
        
        $serString = base64_decode($data);
        
        $object = unserialize($serString);
        $object instanceof CommandAction;
        
        return  $object;
        
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
        $qid = configuration::CommandQueueID();
        
        $db = new PGDB();
        $num_removed = $db->update("delete from {$db->ActionsTableName()} where queueid='{$db->QueueID()}';");
        unset($db);
        
        return  $num_removed;
        
    }
    
    
    public static function CommandActionRemove($commandID) 
    {

        $db = new PGDB();
        $num_removed = $db->update("delete from {$db->ActionsTableName()} where queueid='{$db->QueueID()}' and objectid = '{$commandID}' ;");
        unset($db);
        
        return  $num_removed;
        
    }
    
    
    public static function CommandActionListIDs() 
    {
        $db = new PGDB();
        $result = $db->query("select objectid from {$db->ActionsTableName()} where queueid='{$db->QueueID()}';",'objectid');
        if (count($result) <= 0 ) return null;
        
        unset($db);
        
        return array_keys($result);
        
    }
    
    
    public static function CommandActionExecutionFlag($commandID) 
    {
        
        $db = new PGDB();
        $q = "select objectid,execution_flag from {$db->ActionsTableName()} where queueid='{$db->QueueID()}' and objectid = '{$commandID}'"; 
        $result = $db->query($q,'objectid');
        if (count($result) <= 0 ) return null;
        
        $first_row = util::first_element($result);
        
        $value = array_util::Value($first_row, 'execution_flag', null);
        
        return $value;
        
    }
    
    
    
    public function ImageTableName() {
        if (func_num_args() == 0) return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }
    
    public function FilesTableName() {
        if (func_num_args() == 0) return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }
    
    public function ActionsTableName() {
        if (func_num_args() == 0) return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }
    
    public function ModelledDataTableName() {
        if (func_num_args() == 0) return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }
        
    
    
    
    public function ViaCommandLine() {
        if (func_num_args() == 0) return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }
    
    public function QueueID() {
        if (func_num_args() == 0) return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }
    
    
    public function InsertModelledData($species,$scenario, $model, $time,$file_id)
    {
        
        $sql  = "insert into {$this->ModelledDataTableName()} (scientific_name,  model_name, scenario_name, time_name, data_category, maxent_threshold, file_id) values ";
        $sql .= "(";
        $sql .= "'{$species}',";
        $sql .= "'{$model}',";
        $sql .= "'{$scenario}',";
        $sql .= "'{$time}',";
        $sql .= "'QuickLook',";
        $sql .= "'',";
        $sql .= "'{$file_id}'";
        $sql .= ")";
        
        //echo "insert sql = $sql";
        
        $insert_id = $this->insert($sql);
        
        //echo "insert_id = $insert_id";
        
        return $insert_id;
        
    }
    
    
    
    public function GetModelledData($species,$scenario, $model, $time)
    {
        
        $sql  = "select file_id,data_category, maxent_threshold from {$this->ModelledDataTableName()}   ";
        $sql .= "where species  = '{$species}'";
        $sql .= "  and scenario = '{$scenario}'";
        $sql .= "  and model    = '{$model}'";
        $sql .= "  and time     = '{$time}'";
        $sql .= "order by species,scenario,model,time";
        
        $result = $this->query($sql,'file_id');
        
        if (is_null($result)) return null;
        
        $file_ids = array_keys($result);
        
        if (count($file_ids) == 0) return null;
        
        if (count($file_ids) == 1) return $file_ids[0];
        
        return $file_ids;
        
    }
    
    
    /**
     * 
     * 
     * @param type $speciesID  - Scientific Name here as then across systems and database rebuilds it wont change
     * @return array  [index] => (longitude,latitude)
     */
    public static  function SpeciesOccurance($species_scientific_name) 
    {
        
        // SELECT ST_X(location) AS longitude, ST_Y(location) AS latitude FROM occurrences where species_id = 2966 ;
        // get id for occurrences table  related to  species.species_id

        $db = new PGDB();
        
        $q = "select id,scientific_name,common_name from species where scientific_name = '{$species_scientific_name}'";

        echo "SpeciesOccurance ID q = $q\n";
        
        $db_result = $db->query($q, 'scientific_name');
        
        if (is_null($db_result)) return null;
        if (count($db_result) == 0 )  return null;  // could not find 
        
        $firstRow = util::first_element($db_result); // may not be index [0]
        
        $databaseIDForSpecies = array_util::Value($firstRow,'id');
        if (is_null($databaseIDForSpecies)) return null;

        $q = "SELECT ST_X(location) AS longitude, ST_Y(location) AS latitude FROM occurrences where species_id = $databaseIDForSpecies";
        
        echo "SpeciesOccurance data q = $q\n";
        
        $speciesOccuranceResult = $db->query($q);
        
        unset($db);
        
        return $speciesOccuranceResult;
        
    }
    
    
    
    
    
}


?>
