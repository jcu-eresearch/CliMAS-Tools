<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DBO
 *
 * Public static access to Databaase Funtions
 * 
 */
class DBO {
    
    /**
     *
     * Using the $columns_values keys as columns to return and the values as the where clasue
     * - if a column has a vlaue then it's included as part of the where clause 
     * - oif you want the column but don't want to add that colunmn to the where then leave the value of that column null
     * 
     * @param type $table
     * @param type $columns_values
     * @param type $keyColumn
     * @param type $operator
     * @param type $db_logical
     * @return type 
     */
    public static function QueryArray($table,$columns_values,$keyColumn = null,$operator = "=",$db_logical = "and") 
    {
        
        $wheres = array();
        foreach ($columns_values as $key => $value) 
            if (!is_null($value)) $wheres[$key] = $value;
        
        $where = " where ".self::WhereString($wheres, $operator, $db_logical);
        
        $sql = "select ".implode(", ",array_keys($columns_values))." from {$table} {$where} ";
        
        $result = self::Query($sql,$keyColumn);
        if (is_null($result)) 
        {    
            DBO::LogError(__METHOD__."(".__LINE__.")","SQL FAILED: \n sql = {$sql} \n result = ".print_r($result,true)."  \n");
            return null;
        }
        
        return $result;
        
    }

    public static function Query($sql,$keyColumn = null) 
    {
        $db = new PGDB();        
        $result = $db->query($sql, $keyColumn);
        unset($db);
        
        if (is_null($result))
        {
            DBO::LogError(__METHOD__."(".__LINE__.")","Failed to Run SQL \nkeyColumn = [{$keyColumn}]  \nsql =[{$sql}]\n result = ".print_r($result,true)."  \n");
            return null;
        }
        
        
        return $result;
    }
    
    
    
    public static function QueryFirst($sql,$keyColumn = null) 
    {
        
        $result = self::Query($sql, $keyColumn);
        
        if (is_null($result))
        {
            DBO::LogError(__METHOD__."(".__LINE__.")","Failed to Run SQL \nkeyColumn = [{$keyColumn}]  \nsql =[{$sql}]\n  result = ".print_r($result,true)."  \n");
            return null;
        }
            
        if (count($result) == 0 ) return null;
        
        return util::first_element($result);
    }

    public static function QueryFirstValue($sql,$column,$default = null)
    {
        $result = self::query($sql);
        
        if (is_null($result))
        {
            DBO::LogError(__METHOD__."(".__LINE__.")","Failed to Run SQL \ncolumn = [{$column}]  \ndefault = {$default}  \nsql =[{$sql}]\n");
            return null;
        }
        
        $first = util::first_element($result);
        
        return array_util::Value($first, $column, $default);
    }
    

    public static function SetArray($table,$array,$where = null) 
    {

        if (!is_null($where)) $where  = " where {$where} ";
        
        $sql = "update {$table}  set ".util::dbqKeyedArray($array)." {$where}";
        
        $updateCount = self::Update($sql);
        if (is_null($updateCount))
        {
            DBO::LogError(__METHOD__."(".__LINE__.")","Failed to Run SQL \nsql =[{$sql}]\n");
            return null;
        }
        
        return $updateCount;
    }
    
    
    
    public static function InsertArray($table,$array) 
    {

        $keys = array_keys($array);

        $values = array();
        foreach (array_values($array) as $value)  $values[] = util::dbq($value);
        
        $sql = "INSERT INTO {$table} (".  implode(",", $keys) .") values (".  implode(",", $values).")";

        $result = self::Insert($sql);
        if (is_null($result))
        {
            DBO::LogError(__METHOD__."(".__LINE__.")","Failed to Run SQL \nsql =[{$sql}]\n result = ".print_r($result,true)."  \n");
            return null;
        }
        
        unset($values,$keys);
        
        return $result;
    }

    
    public static function Insert($sql) 
    {
        $db = new PGDB();        
        $result = $db->insert($sql);
        
        if (is_null($result))
        {
            DBO::LogError(__METHOD__."(".__LINE__.")","Failed to INSERT SQL \nsql =[{$sql}]\n result = ".print_r($result,true)."  \n");
            return null;
        }
        
        unset($db);
        return $result;
    }
    
    
    public static function Update($sql) 
    {
        $db = new PGDB();        
        $result = $db->update($sql);
        unset($db);
        
        if (is_null($result))
        {
            DBO::LogError(__METHOD__."(".__LINE__.")","Failed to Update  SQL \nsql =[{$sql}]\n result = ".print_r($result,true)."  \n");
            return null;
        }
        
        
        return $result;
        
    }

    public static function CreateAndGrant($sql) 
    {
        $db = new PGDB();        
        $result = $db->CreateAndGrant($sql);
        unset($db);
        if (is_null($result))
        {
            DBO::LogError(__METHOD__."(".__LINE__.")","Failed to Create or Grant Access to table   SQL \nsql =[{$sql}]\n result = ".print_r($result,true)."  \n");
            return null;
        }
        
        return $result;
        
    }
    
    public static function Delete($table,$where) 
    {
        $db = new PGDB();        
        $result = $db->delete($table,$where);
        unset($db);

        if (is_null($result))
        {
            DBO::LogError(__METHOD__."(".__LINE__.")","Failed to Delete from table = $table  where = [{$where}]  result = ".print_r($result,true)."  \n");
            return null;
        }
        
        
        return $result;
    }
    
    public static function DeleteAll($table) 
    {
        $db = new PGDB();        
        $result = $db->delete_all($table);
        unset($db);
        
        if (is_null($result))
        {
            DBO::LogError(__METHOD__."(".__LINE__.")","Failed to Delete ALL from table = $table result = ".print_r($result,true)."  \n");
            return null;
        }
        
        return $result;
    }
    
    public static function Count($table,$where) 
    {        
        $db = new PGDB();
        $count = $db->count($table,$where);
        unset($db);
        
        if (is_null($count))
        {
            DBO::LogError(__METHOD__."(".__LINE__.")","Failed to Count from table = $table  where = [{$where}]  result = ".print_r($count,true)."  \n");
            return null;
        }
        
        
        return $count;
    }

    
    public function GetSingleRowValue($sql,$column)
    {

        $db = new PGDB();
        $result = $db->getSingleRowValue($sql,$column);
        unset($db);
        
        if (is_null($result))
        {
            DBO::LogError(__METHOD__."(".__LINE__.")","Failed to get data column = $column \n sql = $sql  \n result = ".print_r($result,true)."  \n");
            return null;
        }
        
        
        return $result;

    }

    
    /**
     * Build the "where" parts of a query for single operator
     * 
     * e.g.   $operator = "=", $db_logical = "and"
     * fieldname1  =>   fielvalue1 
     * fieldname2  =>   fielvalue2
     * fieldname3  =>   fielvalue3
     * 
     * return    fieldname1 = fielvalue1 and fieldname2 = fielvalue2 and fieldname3 = fielvalue3
     * 
     * 
     * @param type $array
     * @param type $operator 
     * 
     * @return string   
     */
    public static function WhereString($array,$operator = "=",$db_logical = "and") 
    {
    
        $results = array();
        foreach ($array as $key => $value) 
        {
            if (is_numeric($value))
                $results[] = $key." ".$operator." ".$value;    
            else
                $results[] = $key." ".$operator." ".util::dbq($value);
        }
        

        $result = implode(" ".$db_logical." ", $results);
        
        return $result;
        
    }
    
    
    public function HasTable($table_name)
    {
        $db = new PGDB();
        $result = $db->has_table($table_name);
        unset($db);
        
        return $result;
    }
    
    
    
    public static function LogError($from,$str) 
    {
        $db = new PGDB();     
        
        $dt = util::dbq(datetimeutil::NowDateTime());
        $from = util::dbq($from);
        
        if (is_array($str) || is_object($str))
            $str =  substr( util::dbq(str_replace("'", "'", print_r($str,true))),0,3000);
        else
            $str =  substr( util::dbq(str_replace("'", "'", $str)),0,3000);        
        
        $sql = "insert into error_log (error_date_time,source_code_from,error_message) values ({$dt},{$from},{$str})";
        
        $result = $db->insert($sql,false);
        
        
        echo "$from ... $str\n";
        
        unset($db);
        return $result;
    }
    
    
    public static function Unique($table,$field,$where = null,$as_array = false) 
    {
        $db = new PGDB();
        $result = $db->Unique($table,$field,$where = null,$as_array = false);
        unset($db);
        return $result;   
    }
    
    
}

?>
