<?php
/**
 *
 * Connect , disconnect and data flow to and from PostGres database
 *  
 *  sudo -u postgres psql ap02
 * 
 */
class PGDB extends Object {


    private $pghost     = null;
    private $pgport     = null;
    private $pgdatabase = null;
    private $pguser     = null;
    private $pgpassword = null;
    
    /*
     * @param $via_commandline  True = Force use of Command Line for Database access
     * 
     */
    public function __construct(
                     $pghost     = null
                    ,$pgport     = null
                    ,$pgdatabase = null
                    ,$pguser     = null
                    ,$pgpassword = null
            ) {
        parent::__construct();
        
        $this->QueueID(configuration::CommandQueueID());
        
        $this->pghost     = ToolsDataConfiguration::Species_DB_Server();
        $this->pgport     = ToolsDataConfiguration::Species_DB_Port();
        $this->pgdatabase = ToolsDataConfiguration::Species_DB_Database();
        $this->pguser     = ToolsDataConfiguration::Species_DB_Username();
        $this->pgpassword = ToolsDataConfiguration::Species_DB_Password();
        
        
        if (!is_null($pghost))      $this->pghost     = $pghost;
        if (!is_null($pgport))      $this->pgport     = $pgport;
        if (!is_null($pgdatabase))  $this->pgdatabase = $pgdatabase;
        if (!is_null($pguser))      $this->pguser     = $pguser;
        if (!is_null($pgpassword))  $this->pgpassword = $pgpassword;
        
        
    }

    public function __destruct() 
    {
        parent::__destruct();
    }
    
    public  static function QueueID()
    {
        return configuration::CommandQueueID();
    }
    
    /**
     *
     * @param type $sql
     * @param type $output_filename
     * @param type $log_error
     * @return array\ErrorMessage  
     */
    public function ExecuteSQL(
                     $sql
                    ,$output_filename = null
                    ,$log_error = true
            )
    {
        // we don't have Postgress SQL php addin so  this is the way query works - thru sending to file and readin file.
        
        $fn  = file::random_filename();
        
        $export = "export ";
        
        $cmd  = "#!/bin/bash\n";
        $cmd .= "{$export} PGHOST="    .$this->pghost."\n";
        $cmd .= "{$export} PGPORT="    .$this->pgport."\n";
        $cmd .= "{$export} PGDATABASE=".$this->pgdatabase."\n";
        $cmd .= "{$export} PGUSER="    .$this->pguser."\n";
        $cmd .= "{$export} PGPASSWORD=".$this->pgpassword."\n";
        $cmd .= "psql -F'|' -L {$fn}  --no-align -c \"{$sql}\" ";
        
        if (!is_null($output_filename))
            $cmd .= " --output '{$output_filename}'";
        
        $sql_name = file::random_filename();    
        file_put_contents($sql_name,$cmd);
        exec("chmod u+x {$sql_name}");
        exec($sql_name);
        file::Delete($sql_name);
        
        if (!file_exists($fn))
            return new ErrorMessage(__METHOD__,__LINE__,"Failed to run SQL CMD  OutputFile Does Not exist   = [$cmd]  \n",$log_error);
            

        $file = file($fn);
           
        if (file::lineCount($fn) <= 1) return new ErrorMessage(__METHOD__,__LINE__,"not enouigh lines in SQL output Log file CMD = [$cmd] \n",$log_error);
        
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
    
    
    public function  query($sql,$keyColumn = null,$log_error = true) 
    {
        
        $sql = str_replace("\t", " ", $sql);
        
        $resultFilename = file::random_filename();
        
        $sql_result = $this->ExecuteSQL($sql,$resultFilename);
        
        if ($sql_result instanceof ErrorMessage) return $sql_result;

        if (count($sql_result) <= 0 ) return new ErrorMessage(__METHOD__,__LINE__,"Failed to Run SQL result count is ZERO \nkeyColumn = [{$keyColumn}]   \nsql =[{$sql}]\n",$log_error);
        
        if (!file_exists($resultFilename))  return new ErrorMessage(__METHOD__,__LINE__,"Failed to Run SQL with output file - outputfile does not exists trying to access [{$resultFilename}]  \nkeyColumn = [{$keyColumn}]   \nsql =[{$sql}]\n",$log_error);
        
        $result = array();

        $keyColumnIndex = -1;
        
        $handle = fopen($resultFilename, "r");    
        
        if ($handle === FALSE )  return new ErrorMessage(__METHOD__,__LINE__,"Failed to Run SQL with output file - could not get read access [{$resultFilename}]  \nkeyColumn = [{$keyColumn}]   \nsql =[{$sql}]\n",$log_error);
        
        
        if ($handle !== FALSE) 
        {

            $line = stream_get_line($handle, 10000, "\n");  //fgets($handle);
            $headers = str_getcsv($line,"|");
            
            if (!is_null($keyColumn)) $keyColumnIndex = array_util::ArrayKey($headers, $keyColumn);
            
            $row = 0;
            while (!feof($handle))
            {                
                $line = stream_get_line($handle, 10000, "\n"); // fgets($handle);
                
                if (util::contains($line, "rows)") || util::contains($line, "row)") ) continue;
                
                try {
                    $data = str_getcsv($line,"|");
                } catch (Exception $exc) {
                    
                        $E = new ErrorMessage(__METHOD__,__LINE__,"Failed to readv output file - $row = [{$row}]  outputfile [{$resultFilename}]  \nkeyColumn = [{$keyColumn}]   \nsql =[{$sql}]\n",$log_error);
                        exit();                        
                    
                }
                
                if (count($data) == count($headers)) // this should stop any odd lines and the last line (nnnn rows)
                {
                    for ($c=0; $c < count($data); $c++) 
                    {
                        if (is_null($keyColumn))
                            $result[$row][$headers[$c]] = trim($data[$c]);    
                        else
                            $result[trim($data[$keyColumnIndex])][$headers[$c]] = trim($data[$c]);                         
                    }
                    
                }
                
                $row++;
            }
            
            fclose($handle);
            
        }


        
        file::Delete($resultFilename);    
        
        
        return $result;

    }
    
            
    /**
     *
     * @param string $sql
     * @param type $log_error
     * @param type $readLastVal
     * @return $string\ErrorMessage   Last id inserted into Table
     */
    public function insert($sql,$log_error = true,$readLastVal = true) 
    {
        
        if ($readLastVal)
            $sql = util::trim_end(trim($sql), ';'). "; select LASTVAL();";
        
        $result = $this->ExecuteSQL($sql);
        
        if ($result instanceof ErrorMessage)  return new ErrorMessage(__METHOD__,__LINE__,"Failed to Insert SQL  \nsql =[{$sql}]\n",$log_error);
        
        $last_val_line = array_util::FirstElementsThatContain($result, "lastval");        
        if (is_null($last_val_line)) 
            return new ErrorMessage(__METHOD__,__LINE__,"Failed to Insert SQL last_val_line is NULL \nsql =[{$sql}]\n",$log_error);

            
        
        $lastId = array_util::Value($result,2);  // get thrid line of result
        if (is_null($lastId))  
                return new ErrorMessage(__METHOD__,__LINE__,"Failed to Insert SQL lastId is NULL \nsql =[{$sql}]\n",$log_error);            

        
        $lastId = trim($lastId);
        if (!is_numeric($lastId) )   
            return new ErrorMessage(__METHOD__,__LINE__,"Failed to Insert SQL lastId is NOT Numeric\n  lastId = $lastId \nsql =[{$sql}]\n",$log_error);
        
        
        return trim($lastId);
    }
    
    
    
    public function update($sql,$log_error = true) 
    {
        $result = $this->ExecuteSQL($sql);
        $update_line = array_util::FirstElementsThatContain($result, "UPDATE");
        
        
        if ( (is_null($update_line) || count($update_line) <= 0)  )
            return new ErrorMessage(__METHOD__,__LINE__,"Failed to UPDATE Could not find a line in output with 'UPDATE'  \n ExecuteSQL Result = [".print_r($result,true)."] \n sql =[{$sql}]\n",$log_error);
        
        $ins = array_util::FirstElementsThatContain($result,"UPDATE" );
        
        $split = explode(" ",$ins);
        $count = trim($split[1]);
        
        return  $count; // update count
    }
    
    
    public function CreateAndGrant($sql,$log_error = true) 
    {
        $result = $this->ExecuteSQL($sql);
        
        $grant = array_util::FirstElementsThatContain($result,"GRANT" );
        
        if ((is_null($grant) || count($grant) <= 0)  )
            return new ErrorMessage(__METHOD__,__LINE__,"Failed to GRANT Could not find a line in output with 'GRANT'  \n ExecuteSQL Result = [".print_r($result,true)."] \n sql =[{$sql}]\n",$log_error);
        
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
    public function delete($table,$where,$log_error = true) 
    {
        if (is_null($table)) return null;
        if (is_null($where)) return null;
        
        $table = trim($table);
        $where = trim($where);

        if ($table == "") return null;
        if ($where == "") return null;
        
        $q = "delete from $table where $where";
        
        $result = $this->ExecuteSQL($q);
        if ( $result instanceof ErrorMessage) return $result;

        
        $delete_line = array_util::FirstElementsThatContain($result, "DELETE");
        
        if ( (is_null($delete_line) || count($delete_line) <= 0))
            return new ErrorMessage(__METHOD__,__LINE__,"Failed to DELETE Could not find a line in output with 'DELETE'  \n ExecuteSQL Result = [".print_r($result,true)."] \ntable = {$table} \n where = {$where} \n",$log_error);
        
            
        $delete_count = trim(str_replace("DELETE", "", $delete_line)); 
        
        return $delete_count;
    }
    
    
    public function delete_all($table,$log_error = true) 
    {
        if (is_null($table)) return null;
        $table = trim($table);

        if ($table == "") return null;

        $result = $this->ExecuteSQL("delete from $table");
        if ($result instanceof ErrorMessage) return $result;
        
        
        $delete_line = array_util::FirstElementsThatContain($result, "DELETE");
        
        if ((is_null($delete_line) || count($delete_line) <= 0)  )
            return new ErrorMessage(__METHOD__,__LINE__,"Failed to DELETE Could not find a line in output with 'DELETE'  \n ExecuteSQL Result = [".print_r($result,true)."] \ntable = {$table} \n",$log_error);
        
        $delete_count = trim(str_replace("DELETE", "", $delete_line)); 
        
        return $delete_count;
        
    }
    

    public function describe_table($table_name,$log_error = true)
    {
        $result = $this->query("select column_name,data_type from INFORMATION_SCHEMA.COLUMNS where table_name = ".util::dbq($table_name,true).";","column_name");
        if ($result instanceof ErrorMessage) return $result;        
        
        
        return $result;
        
    }
    
    public function has_table($table_name,$log_error = true)
    {
        
        $result = $this->describe_table($table_name,$log_error);
        if ($result instanceof ErrorMessage) return $result;
        
        return (count($result) > 0);
    }
    
    
    public function count($table,$where_src = null,$log_error = true) 
    {   
        
        $where = "";
        if (!is_null($where_src))
        {
            if (is_array($where_src))
                $where = " where " . DBO::WhereString($where_src);
            else
                $where = " where ".$where_src;
            
        }
        
        
        $sql = "select count(*) as row_count from $table {$where}";
        $count_result = $this->query($sql,null,$log_error);
        
        if ($count_result instanceof ErrorMessage) return $count_result;
        
        
        $count = array_util::Value(util::first_element($count_result), 'row_count', -1);
        
        return $count;

    }

    
    public function Unique($table,$field,$where = null,$as_array = false) 
    {        
        if (!is_null($where)) $where = " where {$where} " ;
        
        $sql = "select $field from $table $where group by $field order by $field";
        
        $result = $this->query($sql,$field);
        if ($result instanceof ErrorMessage) return $result;
        
        if (!$as_array) return $result;
        
        
        return matrix::Column($result, $field);
    }

    public function CountUnique($table,$field,$where = null,$log_error = true) 
    {        
        return count($this->Unique($table, $field,$where,$log_error));
    }
    
    
    public function getSingleRowValue($sql,$column,$log_error = true)
    {
        
        $result = $this->query($sql,$column);
        if ($result instanceof ErrorMessage) return $result;

        return array_util::Value(util::first_element($result), $column);
    }
    
    
    
}


?>
