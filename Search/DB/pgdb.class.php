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

    public function __construct($connect = true) {
        parent::__construct();
        
        if ($connect) $this->connect ();
        
    }

    public function connect()
    {
        
        $conString = "";
        $conString .= "host="    .ToolsDataConfiguration::Species_DB_Server()." ";
        $conString .= "port="    .ToolsDataConfiguration::Species_DB_Port()." ";        
        $conString .= "dbname="  .ToolsDataConfiguration::Species_DB_Database()." ";
        $conString .= "user="    .ToolsDataConfiguration::Species_DB_Username()." ";
        $conString .= "password=".ToolsDataConfiguration::Species_DB_Password()." ";
        
        $this->DB = pg_connect($conString );
        
        if ($this->DB === FALSE) $this->DB = null;

        $this->ImageTableName('images');
        $this->FilesTableName('files_data');

    }

    
    
    public function __destruct() {
        
        if (!is_null($this->DB))
        {
            pg_close($this->DB);
            unset($this->DB);
        }

        parent::__destruct();
    }

    
    public function query($sql,$keyColumn = null) 
    {
        
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
    
    public function insert($sql) 
    {

        $sql = str_replace(";", '', $sql);
        
        $sql .= "; select LASTVAL();";
        
        $pg_result = pg_exec($this->DB, $sql);

        $insert_result = pg_fetch_array($pg_result, 0,PGSQL_ASSOC);
        
        $lastid = array_util::Value($insert_result,'lastval',null);
        
        if (is_null($lastid)) return null; // TODO: Better Error ??
        
        return $lastid;
        
    }

    public function update($sql) 
    {
        $result = pg_exec($this->DB, $sql);
        return pg_affected_rows($result);
    }

    
    public function delete($table,$where) 
    {
        $result =  pg_exec($this->DB, "delete from {$table} where {$where};");
        return pg_affected_rows($result);   
    }
    
    public function InsertImage($srcFilename,$lookup,$encoder = "base64_encode",$decoder = "base64_decode") 
    {
        
        $assoc_array = array();
        
        $assoc_array['lookup'] = $lookup;
        $assoc_array['encoder'] = $encoder;
        $assoc_array['decoder'] = $decoder;        
        $assoc_array['filesize'] = filesize($srcFilename);
        $assoc_array['mimetype'] = mime_content_type($srcFilename);
        
        $assoc_array['data'] =  $encoder(file_get_contents($srcFilename)) ;

        $insertResult = pg_insert($this->DB, $this->ImageTableName(),$assoc_array );
        
        if ($insertResult === FALSE) return null;
        
        return $this->last_insert();
        
    }


    
    public function last_insert()
    {
        
        $lastval_result = pg_exec($this->DB, "select LASTVAL();");

        $insert_result = pg_fetch_array($lastval_result, 0,PGSQL_ASSOC);
        
        $lastid = array_util::Value($insert_result,'lastval',null);
        
        return $lastid;
        
    }
    
    
    
    public function HasImage($lookup) 
    {
        
        $q = "select count(*) image_count from {$this->ImageTableName()} where lookup = '{$lookup}'";
        
        $result = $this->query($q);
        
        if (is_null($result)) return false;
        if (count($result) <= 0 ) return false;
        
        $first = util::first_element($result);
        
        
        $count = array_util::Value($first,'image_count' , false);
        
        return $count;
        
    }
    
    public function GetImage($lookup,$filename) 
    {

        $assoc_array = array();
        $assoc_array['lookup'] = $lookup;
        
        $pg_result = pg_select($this->DB, $this->ImageTableName(), $assoc_array);
        
        if (count($pg_result) <= 0) 
        {
            unset($pg_result);
            return null;
        }
        
        $row = util::first_element($pg_result);
        
        $decoder = $row['decoder'];
        
        file_put_contents($filename, $decoder($row['data']) );
        
        if (!file_exists($filename)) 
        {
            unset($pg_result);
            return null;
        }
        
        return $row['id'];
        
    }


    public function InsertFile($srcFilename,$lookup) 
    {
        
        /**
         *Array of part id's fir this file - will be in order of adding to database. 
         * 
         */
        $parts = array();
        
        $chunck_size = 40000;

        $total_filesize = filesize($srcFilename);
        $totalparts = ceil($total_filesize / $chunck_size);

        //echo "Expect to be {$totalparts} rows in Table\n";
        
        $mimetype = mime_content_type($srcFilename);
        
        $file_unique_id =  uniqid();
        
        $partnum = 0;
        $handle = fopen($srcFilename, "rb");
        
        while (!feof($handle)) {

            $contents = fread($handle, $chunck_size);
            
            $assoc_array = array();
            $assoc_array['file_unique_id'] = $file_unique_id;
            $assoc_array['mimetype'] = $mimetype;
            $assoc_array['partnum'] = $partnum;
            $assoc_array['file_description'] = $lookup;
            $assoc_array['totalparts'] = $totalparts;
            $assoc_array['total_filesize'] = $total_filesize;
            $assoc_array['data'] = base64_encode($contents);
            
            $insertResult = pg_insert($this->DB, $this->FilesTableName(),$assoc_array );
            
            if ($insertResult === FALSE) 
            {
                throw new Exception("FAILED:: to insert file in to DB {$srcFilename} with description {$lookup} at part {$partnum}");
            }

            //echo "Just written part {$partnum} / {$totalparts} rows in Table\n";
            
            $parts[] = $this->last_insert();
            
            $partnum++;
            
        }        
        
        
        
        return $file_unique_id;
        
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
    public function ReadFile($unique_id,$echo_data = false,$collect_data = true) 
    {
        
        if (is_null($this->DB)) return null; //TODO: fail nicely needs to handle
        
        $sql = "select * from {$this->FilesTableName()} where file_unique_id = '{$unique_id}' order by partnum;";
        
        $pg_result = pg_exec($this->DB, $sql);
        
        $numrows = pg_numrows($pg_result);
        
        if ($numrows <= 0) return null;
        
        $result_file = '';
        for($ri = 0; $ri < $numrows; $ri++) 
        {
            $row = pg_fetch_array($pg_result, $ri,PGSQL_ASSOC);
            $datapart =  base64_decode($row['data']);
            
            if ($collect_data) $result_file .= $datapart;
            
            if ($echo_data) echo $datapart;
            
        }
        unset($row);
        unset($pg_result);        
        
        if ($collect_data)  return $result_file;
        
        return true;
        
    }

    
    public function ReadFile2Filesystem($unique_id,$dest_filename) 
    {
        
        $result_file =  $this->ReadFile($unique_id);
        
        file_put_contents($dest_filename, $result_file);

        if (!file_exists($dest_filename)) 
        {
            echo "Error rewriting file back from $unique_id  to $dest_filename";
            return null;
        }
        
        return $dest_filename;
        
    }
    
    public function ReadFile2Stream($unique_id) 
    {        
        $this->ReadFile($unique_id,true,false);  // read file stream and dont collect data - ignore return varaibales
    }
    
    public function ReadFileMimeType($unique_id) 
    {        
        $sql = "select mimetype from {$this->FilesTableName()} where file_unique_id = '{$unique_id}' limit 1";
        
        $mimetype_result = $this->query($sql);
        $first = util::first_element($mimetype_result);
        
        return trim($first['mimetype']);
        
    }
    

    
    public function RemoveFile($unique_id) 
    {        
        
        $sql = "delete from {$this->FilesTableName()} where file_unique_id = '{$unique_id}'";
        $this->update($sql);        
        
        $postremove = $this->Count($this->FilesTableName(), "file_unique_id = '{$unique_id}'");

        if ($postremove > 0) return false;
        
        return true;
        
    }


    public function CountFile($unique_id) 
    {        
        $filecount = self::Count($this->FilesTableName(),"file_unique_id = '{$unique_id}'");
        return $filecount;
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
    
    
    
    
    public function ImageTableName() {
        if (func_num_args() == 0) return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }
    
    public function FilesTableName() {
        if (func_num_args() == 0) return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }
    
    
}


?>
