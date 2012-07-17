<?php
/**
 *
 * Connect , disconnect and data flow to and from PostGres database
 *  
 *  sudo -u postgres psql ap02
 * 
 */
class PGDB extends Object {


    private static $FILE_DB_STORAGE_SIZE = 20000; // number of bytes that each part of a file will be 

    /*
     * @param $via_commandline  True = Force use of Command Line for Database access
     * 
     */
    public function __construct() {
        parent::__construct();
        
        $this->ImageTableName('images');
        $this->FilesTableName('files_data');
        $this->ActionsTableName('command_action');
        
        $this->ModelledDataTableName('modelled_species_data');
        
        $this->QueueID(configuration::CommandQueueID());
        
    }

    public function __destruct() 
    {
        parent::__destruct();
    }

    public function q($str)
    {
        return util::dbq($str);
    }
    
    
    private function ExecuteSQL($sql,$output_filename = null)
    {
        // we don't have Postgress SQL php addin so  this is the way query works - thru sending to file and readin file.
        
        $fn  = file::random_filename();
        
        $cmd  = " ";
        $cmd .= "export PGHOST="    .ToolsDataConfiguration::Species_DB_Server()  ." ; ";
        $cmd .= "export PGPORT="    .ToolsDataConfiguration::Species_DB_Port()    ." ; ";
        $cmd .= "export PGDATABASE=".ToolsDataConfiguration::Species_DB_Database()." ; ";
        $cmd .= "export PGUSER="    .ToolsDataConfiguration::Species_DB_Username()." ; ";
        $cmd .= "export PGPASSWORD=".ToolsDataConfiguration::Species_DB_Password()." ; ";
        $cmd .= "psql  -L {$fn}  --no-align -c \"$sql\" ";

        if (!is_null($output_filename))
            $cmd .= " --output '{$output_filename}'";
        
        exec($cmd);
        
        if (!file_exists($fn)) return null;

        $file = file($fn);
        
        $start_output = "**************************";
        $output = false;
        $result = array();
        foreach ($file as $line) 
        {
            if (trim($line) == $start_output)
            {
                $output = true;
                continue;
            }
                
            if ($output)   // only collect data ofter we have seen the "input /  output flag"
                if (trim($line) != "") $result[] =  $line;  
                
            
        }
        
        return $result;
        
    }
    
    
    public function query($sql,$keyColumn = null) 
    {
        // 
        // we don't have Postgress SQL php addin so  this is the way query works - thru sending to file and readin file.
        //
        
        //echo "\n\nQuery By Command Line \n{$sql}\n";
        
        $resultFilename = file::random_filename();
        
        //echo "resultFilename = $resultFilename\n";
        
        $sql_result = $this->ExecuteSQL($sql,$resultFilename);
        
        if (is_null($sql_result)) return null;
        if (count($sql_result) <= 0) return null;
        
        
        //echo "sql_result = \n";
        //print_r($sql_result);
        
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

        
        file::Delete($resultFilename);
        
        
        return $result;

    }
    
    
    public function insert($sql) 
    {
        
        // we don't have Postgress SQL php addin so  this is the way query works - thru sending to file and readin file.        
        $sql = util::trim_end(trim($sql), ';'). "; select LASTVAL();";
        
        $result = $this->ExecuteSQL($sql);
        
        //echo "insert  result = $result \n";
        //print_r($result);
        
        $last_val_line = array_util::FirstElementsThatContain($result, "lastval");
        
        //echo " last_val_line = $last_val_line \n";
        
        if (is_null($last_val_line)) return null;
        
        $lastId = array_util::Value($result,2);        
        
        //echo "aa lastId = $lastId\n";
        
        if (is_null($lastId)) return null;
        $lastId = trim(array_util::Value($result,2));
        //echo "bb lastId = $lastId\n";
            
        
        if (!is_numeric($lastId))  return null;
        //echo "cc lastId = $lastId\n";
        
        
        
        return trim($lastId);
    }
    
    
    
    public function update($sql) 
    {
        //echo "update sql = $sql\n";
        
        $result = $this->ExecuteSQL($sql);

        print_r($result);
        
        $update_line = array_util::FirstElementsThatContain($result, "UPDATE");
        
        if (is_null($update_line)) return null;
        if (count($update_line) <= 0 ) return null;
        
        $ins = array_util::FirstElementsThatContain($result,"UPDATE" );

        //echo "ins = $ins\n";
        
        $split = explode(" ",$ins);
        $count = trim($split[1]);
        
        return  $count; // update count
    }
    
    
    public function CreateAndGrant($sql) 
    {
        $result = $this->ExecuteSQL($sql);
        
        $grant = array_util::FirstElementsThatContain($result,"GRANT" );
        if (is_null($grant)) return null;
        if (count($grant)  <=0 ) return null;
        
        return  $result; // 

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
        
        $where = trim($where);
        
        if ($where == "~~~~~")
            $q = "delete from $table";
        else
            $q = "delete from $table where $where";
        
        //echo "delete q = $q\n";
        
        $result = $this->ExecuteSQL($q);
        
        //echo "delete result\n";
        
        //print_r($result);

        $delete_line = array_util::FirstElementsThatContain($result, "DELETE");
        
        if (is_null($delete_line)) return null;
        if (count($delete_line) <= 0 ) return null;
        
        $delete_count = trim(str_replace("DELETE", "", $delete_line)); 
        
        //echo "delete delete_count = {$delete_count}\n";
        
        return $delete_count;
    }
    

    public function last_insert()
    {
        $sql = " select LASTVAL();";
        $lastval_result = $this->query($sql);        
        $first = util::first_element($lastval_result);
        $lastid = array_util::Value($first,'lastval',null);
        return $lastid;   
    }


    public function describe_table($table_name)
    {
        
        $q = "select column_name,data_type from INFORMATION_SCHEMA.COLUMNS where table_name = {$this->q($table_name)};";
        
        // echo "q = $q\n";
        $result = $this->query($q,"column_name");
        
        if(is_null($result)) return null;
        if(count($result) == 0) return null;
        
        return $result;
        
    }
    
    public function has_table($table_name)
    {
        return (count($this->describe_table($table_name)) > 0);
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
        
        $q = "select * from files where file_unique_id = {$this->q($file_unique_id)} limit 1";
        $result = $this->query($q);
        
        if(is_null($result)) return false;
        if(count($result) <= 0) return false;
        
        return util::first_element($result);
        
    }
    
    
    /**
     *
     * @param type $srcFilename Path to file to insert ito DB
     * @param type $description Some string that can be used to lookup the file later
     * @param type $category    Category of the file e.g. Image, html ...
     * @return string unique_file_id - use this id to get file backl from database;
     * @throws Exception 
     */
    public function InsertFile($srcFilename,$description) 
    {
        return $this->InsertFileByCommandLine($srcFilename, $description);
    }
    

    /**
     *
     * @param type $srcFilename
     * @param type $description
     * @param type $category
     * @return type
     * @throws Exception 
     */
    private function InsertFileByCommandLine($srcFilename,$description) 
    {
        
        $chunck_size = self::$FILE_DB_STORAGE_SIZE;

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

            $sql  = "insert into files_data  (file_unique_id,partnum,totalparts,data) values ".
                    "({$this->q($file_unique_id)},{$partnum},{$totalparts},'{$data}')";
            
            $insertResult = $this->insert($sql);    
            
            //echo "sql = $sql\n";
            
             // echo "insertResult = $insertResult   total_read = $total_read of {$total_filesize}\n";
            
            if (!is_numeric($insertResult) || $insertResult == -1) 
            {
                throw new Exception("FAILED:: to insert file in to DB {$srcFilename} with description {$description} at part {$partnum} using a comand line call");
            }
            
            $partnum++;
        }        

        
        $description = str_replace("'", '', $description);
        
        // write the description of the file 
        $sql  = "insert into files (file_unique_id,mimetype,totalparts,total_filesize,description)  values ".
                "({$this->q($file_unique_id)},{$this->q($mimetype)},{$totalparts},$total_filesize,{$this->q($description)})";

                
        $insertResult = $this->insert($sql);    
        
        if (!is_numeric($insertResult) || $insertResult == -1) 
        {
            throw new Exception("FAILED:: to insert file in to DB {$srcFilename} with description {$description} at part {$partnum} using a comand line call");
        }
        
        
        return $file_unique_id;
        
    }
    
    
    public function HasFile($file_unique_id) 
    {
        
        $q = "select * from files where file_unique_id = {$this->q($file_unique_id)} limit 1";
        
        $query_result = $this->query($q);
        
        if(is_null($query_result)) return false;
        
        if (count($query_result) <= 0) return false;

        return true;

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
        
        $sql = "select data from {$this->FilesTableName()} where file_unique_id = {$this->q($file_unique_id)} order by partnum;";
        
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

    
    /**
     *
     * Write file from database to filesystem 
     * 
     * @param type $file_unique_id
     * @param type $dest_filename  - leave off / null to be random filename will be the return value
     * @return null|string  - Destination filename
     */
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
    
    /**
     * Stream file from database and just eacho data to STDOUT
     * 
     * @param type $file_unique_id 
     */
    public function ReadFile2Stream($file_unique_id) 
    {        
        $this->ReadFile($file_unique_id,true,false);  // read file stream and dont collect data - ignore return varaibales
    }
    
    public function ReadFileMimeType($file_unique_id) 
    {        
        $sql = "select mimetype from files where file_unique_id = {$this->q($file_unique_id)} limit 1";
        
        $mimetype_result = $this->query($sql);
        $first = util::first_element($mimetype_result);
        
        return trim($first['mimetype']);
        
    }

    
    public function ReadFileDescription($file_unique_id,$replace_space = " ") 
    {        
        $sql = "select description from files where file_unique_id = {$this->q($file_unique_id)} limit 1";
        
        $mimetype_result = $this->query($sql);
        $first = util::first_element($mimetype_result);
        
        return str_replace(" ",$replace_space,trim($first['description']));
        
    }

    
    public function RemoveFile($file_unique_id) 
    {        
        
        $this->delete('files',                 "file_unique_id = {$this->q($file_unique_id)}");
        $this->delete($this->FilesTableName(), "file_unique_id = {$this->q($file_unique_id)}");
        
        if ($this->HasFile($file_unique_id)) return false; // if file still exists in DB then delet failed
        
        return true;
        
    }

    
    
    public function Count($table,$where) 
    {        
        $sql = "select count(*) as row_count from $table where {$where}";
        
        $count_result = $this->query($sql);
        
        if (is_null($count_result)) return null;
        if (count($count_result) < 0) return null;
        
        $first = util::first_element($count_result);
        
        $count = array_util::Value($first, 'row_count', -1);
        
        return $count;
        
    }

    public function Unique($table,$field,$where = null,$as_array = false) 
    {        
        if (!is_null($where)) $where = " where {$where} " ;
        
        $sql = "select $field from $table $where group by $field order by $field";
        
        $result = $this->query($sql);
        if (is_null($result)) return null;
        if (!$as_array)return $result;
        
        return matrix::Column($result, $field);
        
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
        $count = $db->Count($db->ActionsTableName(), "queueid = {$db->q($db->QueueID())} and objectid = {$db->q($command_id)}");
        if ($count > 0)
        {
            // update 
            $q = "update {$db->ActionsTableName()} set data = '{$data}',status={$db->q($cmd->Status())},execution_flag={$db->q($cmd->ExecutionFlag())} where objectid = {$db->q($command_id)};"; 
            
            $updateCount = $db->update($q);    
            if ($updateCount != 1 ) return null;
            
        }
        else
        {
            // insert
            $q = "INSERT INTO {$db->ActionsTableName()} 
                    (queueid,
                     objectid, 
                     data,
                     status,
                     execution_flag) VALUES ( 
                    {$db->q($db->QueueID())},
                    {$db->q($command_id)}, 
                    '{$data}',
                    {$db->q($cmd->Status())},
                    {$db->q($cmd->ExecutionFlag())}
                    );"; 
            
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

        $q = "select objectid,status from {$db->ActionsTableName()} where queueid={$db->q($db->QueueID())} and objectid = {$db->q($id)}"; 
        
        $result = $db->query($q,'objectid');
        if (count($result) <= 0 ) return null;
        
        $first_row = util::first_element($result);
        
        $status = array_util::Value($first_row, 'status', null);
        
        return $status;
        
    }
    
    public static function CommandActionRead($commandID) 
    {

        $db = new PGDB();
        
        $q = "select data from {$db->ActionsTableName()} where queueid={$db->q($db->QueueID())} and objectid = {$db->q($commandID)};";
        
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
        $num_removed = $db->update("delete from {$db->ActionsTableName()} where queueid= {$db->q($db->QueueID())};");
        unset($db);
        
        return  $num_removed;
        
    }
    
    
    public static function CommandActionRemove($commandID) 
    {

        $db = new PGDB();
        $num_removed = $db->update("delete from {$db->ActionsTableName()} where queueid={$db->q($db->QueueID())}  and objectid = {$db->q($commandID)};");
        unset($db);
        
        return  $num_removed;
        
    }
    
    
    public static function CommandActionListIDs() 
    {
        $db = new PGDB();
        $result = $db->query("select objectid from {$db->ActionsTableName()} where queueid={$db->q($db->QueueID())};",'objectid');
        if (count($result) <= 0 ) return null;
        
        unset($db);
        
        return array_keys($result);
        
    }
    
    
    public static function CommandActionExecutionFlag($commandID) 
    {
        
        $db = new PGDB();
        $q = "select objectid,execution_flag from {$db->ActionsTableName()} where queueid={$db->q($db->QueueID())} and objectid = {$db->q($commandID)}"; 
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
    
    
    
    /**
     * 
     * 
     * @param type $speciesID  - Scientific Name here as then across systems and database rebuilds it wont change
     * @return array  [index] => (longitude,latitude)
     */
    public static function SpeciesOccurance($speciesID) 
    {

        $db = new PGDB();
        $q = "SELECT ST_X(location) AS longitude, ST_Y(location) AS latitude FROM occurrences where species_id = {$speciesID}";
        $speciesOccuranceResult = $db->query($q);
        unset($db);
        return $speciesOccuranceResult;
        
    }
    
    
    public function SpeciesInfoByID($species_id) 
    {
        
        
        $q = "select scientific_name,common_name from species where id = $species_id";
        $results = $this->query($q,'id');
        
        if (is_null($results)) return null;
        if (count($results) == 0) return null;
        
        $first = util::first_element($results);
        
        return  $first;
        
    }
    
    
    
    /**
     * All files stored for this species - get file list back from DB of all files store for a species
     * 
     * Key = File id    Value = Row 
     * 
     * @param type $species_id
     * @return null 
     */
    public function ModelledSpeciesFiles($species_id)
    {
    
        $q = "select 
                m.file_unique_id as file_unique_id ,
                m.species_id,m.scientific_name,
                m.common_name,
                f.description 
              from 
                modelled_species_files m, 
                files f  
              where  m.file_unique_id = f.file_unique_id 
                and  m.species_id = {$species_id}";

                
        echo __METHOD__.": q = $q \n";
                
                
        $result = $this->query($q, 'file_unique_id' );

        if (is_null($result)) return null;
        
        return $result;
        
    }
    
    
    
    public function InsertAllMaxentResults($species_id) 
    {
        
        $folder = pgdb::MaxentResultsOutputFolder($species_id);
        
        if (!is_dir($folder)) 
        {
            throw new Exception("InsertAllMaxentResults $folder does not exist\n");
        }
        
        
        $fn = array();
        
        $fn['lambdas'              ] = $folder.$species_id.'.lambdas';
        $fn['omission.csv'         ] = $folder.$species_id.'_omission.csv';
        $fn['sampleAverages.csv'   ] = $folder.$species_id.'_sampleAverages.csv';
        $fn['samplePredictions.csv'] = $folder.$species_id.'_samplePredictions.csv';
        $fn['maxent.log'           ] = $folder.'maxent.log';

        foreach ($fn as $desc => $filename) 
        {
            
            $this->InsertSingleMaxentOutput($species_id,$filename,$desc);
            
        }
        
        echo "InsertAllMaxentResults folder = $folder \n";
        $this->InsertMaxentResultsCSV($species_id);
        
        $this->InsertMaxentHTMLasZIP($species_id);
        
        //print_r($this->GetMaxentResultsCSV($species_id));
        
        echo "RESULTS of insert all Maxent data files\n";
        echo "============================================================================\n";
        
        //
        $file_ids = $this->ModelledSpeciesFiles($species_id);
        
        matrix::display($file_ids, " ", null, 15);
        
        
        // plots and html need to be one unit
        // $fn['html'                 ] = $folder.$species_id.'.html';
        

    }
    
    
    /**
     * Used to store the Maxent results html and plots that come from Maxent
     * 
     * @param type $species_id 
     */
    
    private function InsertMaxentHTMLasZIP($species_id) 
    {
             $html_filename = $this->MaxentResultsOutputFolder($species_id).$species_id.".html";
        $plots_file_pattern = $this->MaxentResultsOutputFolder($species_id)."plots/*";
     
        echo "html_filename = $html_filename\n";
        echo "plots_file_pattern = $plots_file_pattern\n";
        
        // store these into a single zip and then add zip to database.

        $zipfilename = file::random_filename().".zip";
        
        echo "zipfilename = $zipfilename\n";
        
        $cmd = "cd ".$this->MaxentResultsOutputFolder($species_id)."; ". 
               "zip '{$zipfilename}' '{$html_filename}'; ".
               "zip '{$zipfilename}' '{$plots_file_pattern}'";
               
        echo "cmd = $cmd\n";
        
        exec($cmd);
        
        if (!file_exists($zipfilename)) return null;
        
        $file_unique_id = $this->InsertSingleMaxentOutput ($species_id,  $zipfilename,"HTML results zipped");
        
        file::Delete($zipfilename);
        
        return $file_unique_id;
        
        
    }
    
    public function InsertSingleMaxentModelledOutput($species_id,$desc,$scenario, $model, $time) 
    {
        $fn = $this->species_output_projection_filename($speciesID, $scenario, $model, $time);
        return $this->InsertSingleMaxentOutput($species_id,$fn,$desc,$scenario, $model, $time);
    }
    
    private function species_output_projection_filename($speciesID, $scenario, $model, $time)
    {
        $output_file    =   configuration::Maxent_Species_Data_folder().
                            $speciesID.
                            configuration::osPathDelimiter().
                            configuration::Maxent_Species_Data_Output_Subfolder().
                            configuration::osPathDelimiter().
                            "{$scenario}_{$model}_{$time}".'.asc';

        return $output_file;
        
    }

    
    
    
    /**
     * Store a file to be associated with a species
     * 
     * 
     * @param type $species_id
     * @param type $desc
     * @param type $filename
     * @return null
     * @throws Exception 
     */
    public function InsertSingleMaxentOutput($species_id,$filename,$desc = null,$scenario = null, $model = null, $time = null) 
    {
     
        if (!file_exists($filename))
        {
            throw new Exception("InsertModelledSpeciesFile file does not exist {$filename}");
        }
        
        echo "Insert for {$species_id} {$filename}  desc = {$desc}\n ";
        
        
        $file_unique_id = $this->InsertFile($filename, $desc);
        
        if (is_null($file_unique_id)) 
        {
            throw new Exception("Failed to insert file {$filename}");
        }
        
        
        $info = $this->SpeciesInfoByID($species_id);
        
        $q  = "insert into modelled_species_files 
                (species_id,
                 scientific_name,
                 common_name,
                 file_unique_id
                ) values (
                 {$species_id},
                 {$this->q($info['scientific_name'])},
                 {$this->q($info['common_name'])},
                 {$this->q($file_unique_id)}
                )";
        
        echo "Insert into Modelled Species Files \n$q\n";

        $result = $this->insert($q);
        
        echo "Insert into Modelled Species Files result = $result \n";
        
        if ($result <= 0) return null;
        
        // give model output reference data $scenario , $model , $time 
        if (!is_null($scenario) && !is_null($model) && !is_null($time) )
        {

            
            $scenario_id = $this->getScenarioID($scenario);
            $model_id = $this->getModelID($model);
            $time_id = $this->getTimeID($time);
            

            $q = "insert into modelled_climates
                  (  species_id
                    ,scientific_name
                    ,common_name
                    ,models_id
                    ,scenarios_id
                    ,times_id
                    ,file_unique_id
                  ) values ( 
                    {$species_id}
                   ,{$this->q($info['scientific_name'])}
                   ,{$this->q($info['common_name'])}
                   ,{$model_id}
                   ,{$scenario_id}
                   ,{$time_id}
                   ,{$this->q($file_unique_id)}
                   ) ;
                 ";
            
                   
           $modelled_climates_result = $this->insert($q);
                   
           echo "modelled_climates_result  = $modelled_climates_result";
           
        }
        
        
        echo "InsertModelledSpeciesFile result = $result";
        
        return $file_unique_id;
        
    }
    
    public function RemoveSingleMaxentOutput($species_id,$scenario, $model, $time ) 
    {
        
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
                      ,mc.file_unique_id as file_id
                from   modelled_climates mc
                      ,models m
                      ,scenarios s
                      ,times t
                where mc.species_id = {$species_id}
                  and mc.models_id     = m.id
                  and mc.scenarios_id  = s.id
                  and mc.times_id      = t.id
                  and m.dataname = {$this->q($model)}
                  and s.dataname = {$this->q($scenario)}
                  and t.dataname = {$this->q($time)}
                 limit 1
                ;";
        
        
        $row = util::first_element($this->query($sql));
        
        // use these id's to remove reference row and file.
        $id = $row['id'];
        $file_id = $row['file_id'];
        
        $del_modelled_climates = $this->delete("modelled_climates", "id = {$id}");        
        
        $count_modelled_climates = $this->Count("modelled_climates", "id = {$id}");
        if (is_null($count_modelled_climates) || $count_modelled_climates <= 0) 
        {
            throw new Exception("Can't RemoveSingleMaxentOutput id = {$id} \n Result =  $del_modelled_climates");
        }
        
        $del_modelled_climates_file = $this->RemoveFile($file_id);        
        if (!$del_modelled_climates_file) 
        {
            throw new Exception("Can't RemoveSingleMaxentOutput del_modelled_climates_file   file_id = {$file_id}");
        }
        
        
        return true;
        
    }
    
    
    
    
    
    // $scenario = null, $model = null, $time = null    
    public function getScenarioID($scenario) 
    {
        $q = "select id from scenarios where dataname = {$this->q($scenario)};";
        
        $result = $this->query($q,'id');
        
        if (is_null($result)) return null;
        if (count($result) == 0) return null;

        $first_row = util::first_element($result);
        
        $id = array_util::Value($first_row, 'id');

        return $id;
        
    }

    public function getModelID($model) 
    {
        $q = "select id from models where dataname = {$this->q($model)};";
        
        $result = $this->query($q,'id');
        
        if (is_null($result)) return null;
        if (count($result) == 0) return null;

        $first_row = util::first_element($result);
        
        $id = array_util::Value($first_row, 'id');

        return $id;
        
    }
    

    public function getTimeID($time) 
    {
        $q = "select id from times where dataname = {$this->q($time)}";
        
        $result = $this->query($q,'id');
        
        if (is_null($result)) return null;
        if (count($result) == 0) return null;

        $first_row = util::first_element($result);
        
        $id = array_util::Value($first_row, 'id');

        return $id;
        
    }
    
    
    
    
    
    /**
     * this is a DB version of the "maxentResults.csv"
     * 
     * @param type $speciesID  - Scientific Name here as then across systems and database rebuilds it wont change
     * @return array  [index] => (longitude,latitude)
     */
    public function InsertMaxentResultsCSV($species_id) 
    {
        
        $filename = self::MaxentResultsFilename($species_id);
        
        echo __METHOD__." species_id = $species_id\n";
        echo __METHOD__." filename = $filename\n";
        
        if (!file_exists($filename)) return null;
        
        // field name to db_table id
        $maxent_fields = array_flip(matrix::Column($this->query("select id,name from maxent_fields",'id'), 'name'));
        
        if (file::lineCount($filename) < 2) return null;
        
        
        // get the maxent results file in 
        $m = matrix::Load($filename);
        
        // create a big insert of this result
        $fr = util::first_element($m);
        
        if (count($maxent_fields) != count($fr))
        {
            echo "Matrix field count = ".count($maxent_fields)."  count(fr) =  ".count($fr)."\n";
            return null;
        }
        
        
        $subs = array();
        foreach ($fr as $maxent_column_name => $maxent_value) 
            $subs[] = "({$species_id},{$maxent_fields[$maxent_column_name]},{$maxent_value})";
            
            
        $insert  = "insert into maxent_values (species_id,maxent_fields_id,num) values ".implode(",",$subs);
        $insert_result = $this->update($insert);
        
        echo __METHOD__." Maxent Insert = \n{$insert }\n";
        
        echo __METHOD__." Maxent Insert result = {$insert_result}\n";
        
        
        return $insert_result;
        
    }
    
    
    public function GetMaxentResultsCSV($species_id)
    {
        
        $q = "select v.species_id,v.maxent_fields_id ,f.name as maxent_name,v.num  from maxent_values v, maxent_fields f  where v.maxent_fields_id = f.id and species_id = {$species_id}";
        
        $result = $this->query($q, 'maxent_name' );

        if (is_null($result)) return null;
        if (count($result) == 0) return null;
        
        return $result;
        
        
    }
    
    public function GetMaxentResult($species_id,$MaxentFieldName)
    {
        
        $q = "select v.species_id,v.maxent_fields_id ,f.name as maxent_name,v.num  from maxent_values v, maxent_fields f  where v.maxent_fields_id = f.id and species_id = {$species_id} and f.name = {$this->q($MaxentFieldName)} ";
        
        echo __METHOD__." .. q = $q\n";
        
        $result = $this->query($q, 'maxent_name' );

        
        print_r($result);
        
        
        if (is_null($result)) return null;
        if (count($result) == 0) return null;

        $first = util::first_element($result);
        
        $field_value = $first[$MaxentFieldName];
        
        
        return $field_value;
        
    }

    
    
    
    
    /**
     * Remove Maxent specxies database from database
     * 
     * @param type $species_id
     * @throws Exception 
     */
    public function RemoveAllMaxentResults($species_id,$really_remove = false) 
    {
        
        if ($really_remove === false) return;
        
        if (is_null($species_id) ||  $species_id == "" ) 
        {
            echo "species_id passed as NULL\n";
            return null;
        }
            
        
        echo "Remove Maxent Values {$species_id}\n";
        $remove_result = $this->RemoveMaxentValues($species_id);
        
        if (is_null($remove_result)) 
        {
            throw new Exception("Remove Maxent Values failed");
        }
        

        echo "remove_modelled_species_files{$species_id}\n";
        $remove_result = $this->RemoveModelledSpeciesFiles($species_id);
        if (is_null($remove_result)) 
        {
            throw new Exception("remove_modelled_species_files failed");
        }
        
        
    }

    
    public function RemoveMaxentValues($species_id) 
    {
        $result  = $this->delete('maxent_values', "species_id = {$species_id}");
        if (is_null($result)) return null;
        return $result;   
    }
    
    
    

    public function RemoveModelledSpeciesFiles($species_id)
    {
        
        echo "Remove Files for {$species_id} \n";
        
        $file_ids = $this->ModelledSpeciesFiles($species_id);
        
        $remove_results = array();
        foreach ($file_ids as $file_id => $row) 
        {
            echo "Remove File for {$species_id} {$file_id} ". implode(",",$row)."\n";
            
            $remove_result =  $this->RemoveSingleModelledSpeciesFile($species_id,$file_id);
            
            if (is_null($remove_result))       
            {
                throw new Exception("Failed to remove file for  $species_id, $file_id ");
            }
            
            $remove_results[] = $remove_result;
            
            
        }
        
        return $remove_results;
        
    }

    /**
     * Remove MOdelled species file and it's reference row from modelled_species_files
     * 
     * @param type $species_id
     * @param type $file_id 
     */
    public function RemoveSingleModelledSpeciesFile($species_id,$file_id)
    {

        $info = $this->FileInfo($file_id);
        
        echo "Remove file for \n";
        print_r($info);
        echo "\n";
        
        // remove file
        $file_remove_result = $this->RemoveFile($file_id);
        echo "Remove File result {$file_remove_result}\n";
        
        if ($this->HasFile($file_id)) return null;
        
        
        // remove referenced to modelled species
        $remove_ref_result  = $this->delete('modelled_species_files', "species_id = '{$species_id}'  and file_unique_id =  {$this->q($file_id)} ");
        echo "Remove $remove_ref_result  for {$species_id} .. {$file_id} \n";
        
        
        return true;
        
    }
    
    
    
    
    
    public static  function MaxentResultsFilename($species_id) 
    {
        
        $result = self::MaxentResultsOutputFolder($species_id)."maxentResults.csv";
        
        return $result;
    }
    
    public static  function MaxentResultsOutputFolder($species_id) 
    {
        
        $result =   configuration::Maxent_Species_Data_folder().
                    $species_id.
                    configuration::osPathDelimiter().
                    configuration::Maxent_Species_Data_Output_Subfolder().
                    configuration::osPathDelimiter();
        
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
     * @return string|null  file_+id for that file
     */
    public function GetModelledData($species,$scenario, $model, $time)
    {
        
        $species = str_replace("_", " ", $species);
        
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
                      ,mc.file_unique_id as file_id
                from   modelled_climates mc
                      ,models m
                      ,scenarios s
                      ,times t
                where mc.species_id = {$species}
                  and mc.models_id     = m.id
                  and mc.scenarios_id  = s.id
                  and mc.times_id      = t.id
                  and m.dataname = {$this->q($model)}
                  and s.dataname = {$this->q($scenario)}
                  and t.dataname = {$this->q($time)}
                ;";
        
        
        $result = $this->query($sql);
        
        echo "GetModelledData sql = $sql\n";
        
        if (is_null($result)) return new Exception(__METHOD__." result is NULL for query {$sql}");
        
        if (count($result) == 0) return null;
        
        $first = util::first_element($result);
        
        $file_id = array_util::Value($first, 'file_id');
        
        if (count($result) == 0) return new Exception(__METHOD__." result no column called file_id "); ;
        
        return $file_id;
        
    }
    
    
    
//    public static function InsertModelledData($species,$scenario, $model, $time,$file_id)
//    {
//        
//        
//        $sql  = "insert 
//                 into {$this->ModelledDataTableName()} 
//                (scientific_name,
//                 model_name, 
//                 scenario_name, 
//                 time_name, 
//                 data_category, 
//                 maxent_threshold, 
//                 file_id
//                 ) values (
//                '{$species}',
//                '{$model}',
//                '{$scenario}',
//                '{$time}',
//                'QuickLook',
//                '',
//                '{$file_id}'
//                )";
//        
//        //echo "insert sql = $sql";
//        
//        $insert_id = $this->insert($sql);
//        
//        //echo "insert_id = $insert_id";
//        
//        return $insert_id;
//        
//    }
    
    
    
    
    
}


?>
