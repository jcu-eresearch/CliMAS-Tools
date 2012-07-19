<?php
/**
 *
 * Connect , disconnect and data flow to and from PostGres database
 *  
 *  sudo -u postgres psql ap02
 * 
 */
class PGDB extends Object {


    /*
     * @param $via_commandline  True = Force use of Command Line for Database access
     * 
     */
    public function __construct() {
        parent::__construct();
        
        $this->QueueID(configuration::CommandQueueID());
        
    }

    public function __destruct() 
    {
        parent::__destruct();
    }
    
    public  static function QueueID()
    {
        return configuration::CommandQueueID();
    }
    
    public function ExecuteSQL($sql,$output_filename = null)
    {
        // we don't have Postgress SQL php addin so  this is the way query works - thru sending to file and readin file.
        
        $fn  = file::random_filename();
        
        $cmd  = " ";
        $cmd .= "export PGHOST="    .ToolsDataConfiguration::Species_DB_Server()  ." ; ";
        $cmd .= "export PGPORT="    .ToolsDataConfiguration::Species_DB_Port()    ." ; ";
        $cmd .= "export PGDATABASE=".ToolsDataConfiguration::Species_DB_Database()." ; ";
        $cmd .= "export PGUSER="    .ToolsDataConfiguration::Species_DB_Username()." ; ";
        $cmd .= "export PGPASSWORD=".ToolsDataConfiguration::Species_DB_Password()." ; ";
        $cmd .= "psql -L {$fn}  --no-align -c \"$sql\" ";

        if (!is_null($output_filename))
            $cmd .= " --output '{$output_filename}'";
        
        exec($cmd);
        
        if (!file_exists($fn))
        {
            DBO::LogError(__METHOD__."(".__LINE__.")","Failed to run SQL CMD = [$cmd] \n");
            return null;
        }

        $file = file($fn);
           
        if (file::lineCount($fn) <= 1)
        {
            DBO::LogError(__METHOD__."(".__LINE__.")","not enouigh lines in SQL output Log file CMD = [$cmd] \n");
            return null;
        }
        
        
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
    
    
    public function  query($sql,$keyColumn = null) 
    {
        $resultFilename = file::random_filename();
        
        $sql_result = $this->ExecuteSQL($sql,$resultFilename);
        
        if (is_null($sql_result))
        {
            DBO::LogError(__METHOD__."(".__LINE__.")","Failed to Run SQL \nkeyColumn = [{$keyColumn}]   \nsql =[{$sql}]\n");
            return null;
        }

        if (count($sql_result) <= 0)
        {
            DBO::LogError(__METHOD__."(".__LINE__.")","Failed to Run SQL result count is ZERO \nkeyColumn = [{$keyColumn}]   \nsql =[{$sql}]\n");
            return null;
        }
        
        if (!file_exists($resultFilename)) 
        {
            DBO::LogError(__METHOD__."(".__LINE__.")","Failed to Run SQL with output file - outputfile does not exists trying to access [{$resultFilename}]  \nkeyColumn = [{$keyColumn}]   \nsql =[{$sql}]\n");
            return null;
        }
        
        
        
        $result = array();

        $keyColumnIndex = -1;
        
//        if ($keyColumn == "zzzzz")
//        {
//            echo "\nLine count = ".file::lineCount($resultFilename);
//            
//            echo "\nfile size  = ".  filesize($resultFilename);
//            
//            echo "\n";
//         
//            $keyColumn = null;
//            
//        }
//        
        
        
        $handle = fopen($resultFilename, "r");    
        
        if ($handle === FALSE) 
        {
            DBO::LogError(__METHOD__."(".__LINE__.")","Failed to Run SQL with output file - could not get read access [{$resultFilename}]  \nkeyColumn = [{$keyColumn}]   \nsql =[{$sql}]\n");
            return null;
        }
        
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
                    
                    DBO::LogError(__METHOD__."(".__LINE__.")","Failed to readv output file - $row = [{$row}]  outputfile [{$resultFilename}]  \nkeyColumn = [{$keyColumn}]   \nsql =[{$sql}]\n");
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

        
//        if ($keyColumn == "zzzzz")
//        {
//            echo "\nAfter";
//            
//            echo "\nLine count = ".file::lineCount($resultFilename);
//            
//            echo "\nfile size  = ".  filesize($resultFilename);
//            
//            echo "\n";
//         
//            $keyColumn = null;
//            
//        }

        
        file::Delete($resultFilename);    
        
        
        return $result;

    }
    
            
    
    public function insert($sql) 
    {
        
        $sql = util::trim_end(trim($sql), ';'). "; select LASTVAL();";
        
        $result = $this->ExecuteSQL($sql);
        
        if (is_null($result)) 
        {
            DBO::LogError(__METHOD__."(".__LINE__.")","Failed to Insert SQL  \nsql =[{$sql}]\n");
            return null;
        }
        
        $last_val_line = array_util::FirstElementsThatContain($result, "lastval");        
        if (is_null($last_val_line)) 
        {
            DBO::LogError(__METHOD__."(".__LINE__.")","Failed to Insert SQL  cant get lastval \nsql =[{$sql}]\n");
            return null;
        }
        
        
        $lastId = array_util::Value($result,2);  // get thrid line of result
        if (is_null($lastId)) 
        {
            DBO::LogError(__METHOD__."(".__LINE__.")","Failed to Insert SQL lastId is NULL \nsql =[{$sql}]\n");
            return null;
        }
            
        
        
        $lastId = trim($lastId);
        if (!is_numeric($lastId))  
        {
            DBO::LogError(__METHOD__."(".__LINE__.")","Failed to Insert SQL lastId is NOT Numeric\n  lastId = $lastId \nsql =[{$sql}]\n");
            return null;
        }
        
        
        return trim($lastId);
    }
    
    
    
    public function update($sql) 
    {
        $result = $this->ExecuteSQL($sql);
        $update_line = array_util::FirstElementsThatContain($result, "UPDATE");
        
        if (is_null($update_line) || count($update_line) <= 0)
        {
            DBO::LogError(__METHOD__."(".__LINE__.")","Failed to UPDATE Could not find a line in output with 'UPDATE'  \n ExecuteSQL Result = [".print_r($result,true)."] \n sql =[{$sql}]\n");
            return null;
        }
        
        $ins = array_util::FirstElementsThatContain($result,"UPDATE" );
        
        $split = explode(" ",$ins);
        $count = trim($split[1]);
        
        return  $count; // update count
    }
    
    
    public function CreateAndGrant($sql) 
    {
        $result = $this->ExecuteSQL($sql);
        
        $grant = array_util::FirstElementsThatContain($result,"GRANT" );
        
        if (is_null($grant) || count($grant) <= 0)
        {
            DBO::LogError(__METHOD__."(".__LINE__.")","Failed to GRANT Could not find a line in output with 'GRANT'  \n ExecuteSQL Result = [".print_r($result,true)."] \n sql =[{$sql}]\n");
            return null;
        }
        
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
        if (is_null($table)) return null;
        if (is_null($where)) return null;
        
        $table = trim($table);
        $where = trim($where);

        if ($table == "") return null;
        if ($where == "") return null;
        
        $q = "delete from $table where $where";
        
        $result = $this->ExecuteSQL($q);

        $delete_line = array_util::FirstElementsThatContain($result, "DELETE");
        
        if (is_null($delete_line) || count($delete_line) <= 0 )
        {
            DBO::LogError(__METHOD__."(".__LINE__.")","Failed to DELETE Could not find a line in output with 'DELETE'  \n ExecuteSQL Result = [".print_r($result,true)."] \ntable = {$table} \n where = {$where} \n");
            return null;
        }
        
        $delete_count = trim(str_replace("DELETE", "", $delete_line)); 
        
        return $delete_count;
    }
    
    
    public function delete_all($table) 
    {
        if (is_null($table)) return null;
        $table = trim($table);

        if ($table == "") return null;

        $result = $this->ExecuteSQL("delete from $table");

        $delete_line = array_util::FirstElementsThatContain($result, "DELETE");
        
        if (is_null($delete_line) || count($delete_line) <= 0 )
        {
            DBO::LogError(__METHOD__."(".__LINE__.")","Failed to DELETE Could not find a line in output with 'DELETE'  \n ExecuteSQL Result = [".print_r($result,true)."] \ntable = {$table} \n");
            return null;
        }
        
        $delete_count = trim(str_replace("DELETE", "", $delete_line)); 
        
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
        $result = $this->query("select column_name,data_type from INFORMATION_SCHEMA.COLUMNS where table_name = ".util::dbq($table_name).";","column_name");
        
        if (is_null($result) || count($result) <= 0 )
        {
            DBO::LogError(__METHOD__."(".__LINE__.")","Failed to DESCRIBE Could not find a line in output with 'DELETE'  \n ExecuteSQL Result = [".print_r($result,true)."] \ntable = {$table_name} \n");
            return null;
        }
        
        return $result;
        
    }
    
    public function has_table($table_name)
    {
        return (count($this->describe_table($table_name)) > 0);
    }
    
    
    public function count($table,$where_src) 
    {        
        if (is_array($where_src))
            $where = DBO::WhereString($where_src);
        else
            $where = $where_src;
        
        $sql = "select count(*) as row_count from $table where {$where}";
        $count_result = $this->query($sql);
        
        if (is_null($count_result))
        {
            DBO::LogError(__METHOD__."(".__LINE__.")","Failed to COUNT  \n ExecuteSQL Result = [".print_r($count_result,true)."] \ntable = {$table} \n where_src = {$where_src}  \n ");
            return null;
        }
        
        $count = array_util::Value(util::first_element($count_result), 'row_count', -1);
        
        return $count;

    }

    
    public function Unique($table,$field,$where = null,$as_array = false) 
    {        
        if (!is_null($where)) $where = " where {$where} " ;
        
        $result = $this->query("select $field from $table $where group by $field order by $field",$field);
        
        if (is_null($result))
        {
            DBO::LogError(__METHOD__."(".__LINE__.")","Failed to Unique  \n ExecuteSQL Result = [".print_r($result,true)."] \n table = [$table] \nfield  = [$field] \n where = [$where] \n as_array = [$as_array] \n");
            return null;
        }
        
        if (!$as_array)return $result;
        
        
        //unset( $result[ count($result) - 1]);
        
        return matrix::Column($result, $field);
    }

    public function CountUnique($table,$field,$where = null) 
    {        
        return count($this->Unique($table, $field,$where));
    }
    
    
    public function getSingleRowValue($sql,$column)
    {
        
        $result = $this->query($sql,$column);
        
        if (is_null($result))
        {
            DBO::LogError(__METHOD__."(".__LINE__.")","Failed to Single Row Value  \n ExecuteSQL Result = [".print_r($result,true)."] \n sql = [$sql] \ncolumn  = [$column] \n");
            return null;
        }

        return array_util::Value(util::first_element($result), $column);
    }
    
    
    
}


?>
