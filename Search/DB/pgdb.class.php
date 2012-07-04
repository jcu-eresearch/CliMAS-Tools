<?php
/**
 *
 * Connect , disconnect and data flow to and from PostGres database
 *  
 * 
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


    
    
    
    public function ImageTableName() {
        if (func_num_args() == 0) return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }
    
    
    
}


?>
