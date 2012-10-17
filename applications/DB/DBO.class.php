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
        
        if ($result instanceof ErrorMessage)  
            return ErrorMessage::Stacked (__METHOD__,__LINE__,"SQL FAILED: \n sql = {$sql}", true,$result);

        
        return $result;
        
    }

    public static function Query($sql,$keyColumn = null) 
    {
        $db = new PGDB();        
        $result = $db->query($sql, $keyColumn);
        unset($db);
        
        if ($result instanceof ErrorMessage)  
            return ErrorMessage::Stacked (__METHOD__,__LINE__,"Failed to Run SQL \nkeyColumn = [{$keyColumn}]  \nsql =[{$sql}]", true,$result);
        
        
        return $result;
    }
    
    
    
    public static function QueryFirst($sql,$keyColumn = null) 
    {
        
        $result = self::Query($sql, $keyColumn);
        
        if ($result instanceof ErrorMessage)  
            return ErrorMessage::Stacked (__METHOD__,__LINE__,"Failed to Run SQL \nkeyColumn = [{$keyColumn}]  \nsql =[{$sql}]", true,$result);
        
            
        if (count($result) == 0 ) return null;
        
        return util::first_element($result);
    }

    public static function QueryFirstValue($sql,$column,$default = null)
    {
        $result = self::query($sql);
        
        if ($result instanceof ErrorMessage)  
            return ErrorMessage::Stacked (__METHOD__,__LINE__,"Failed to Run SQL \ncolumn = [{$column}]  \ndefault = {$default}  \nsql =[{$sql}]", true,$result);
        
        return array_util::Value(util::first_element($result), $column, $default);
    }
    

    public static function SetArray($table,$array,$where = null) 
    {

        if (!is_null($where)) $where  = " where {$where} ";
        
        $sql = "update {$table}  set ".util::dbqKeyedArray($array)." {$where}";
        
        $result = self::Update($sql);
        
        if ($result instanceof ErrorMessage)  
            return ErrorMessage::Stacked (__METHOD__,__LINE__,"Failed to Run SQL \nsql =[{$sql}]\n", true,$result);
        
        
        return $result;
    }
    
    
    
    public static function InsertArray($table,$array,$forceCharacter = false) 
    {

        $keys = array_keys($array);

        $values = array();
        foreach (array_values($array) as $value)  
            $values[] = util::dbq($value,$forceCharacter);
        
        $sql = "INSERT INTO {$table} (".  implode(",", $keys) .") values (".  implode(",", $values).")";

        $result = self::Insert($sql);
        if ($result instanceof ErrorMessage)  
            return ErrorMessage::Stacked (__METHOD__,__LINE__,"Failed to INSERT ARRAY via SQL \nsql =[{$sql}]\n", true,$result);
        
        
        unset($values,$keys);
        
        return $result;
    }

    
    public static function Insert($sql) 
    {
        $db = new PGDB();        
        $result = $db->insert($sql);
        
        if ($result instanceof ErrorMessage)  
            return ErrorMessage::Stacked (__METHOD__,__LINE__,"Failed to INSERT SQL \nsql =[{$sql}] \n", true,$result);
        
        unset($db);
        return $result;
    }
    
    
    public static function Update($sql) 
    {
        $db = new PGDB();        
        $result = $db->update($sql);
        unset($db);
        
        if ($result instanceof ErrorMessage)  
            return ErrorMessage::Stacked (__METHOD__,__LINE__,"Failed to Update  SQL \nsql =[{$sql}]", true,$result);
        
        return $result;
        
    }

    public static function CreateAndGrant($sql) 
    {
        $db = new PGDB();        
        $result = $db->CreateAndGrant($sql);
        unset($db);
        
        if ($result instanceof ErrorMessage)  
            return ErrorMessage::Stacked (__METHOD__,__LINE__,"Failed to Create or Grant Access to table   SQL \nsql =[{$sql}]", true,$result);
        
        
        return $result;
        
    }
    
    public static function Delete($table,$where) 
    {
        $db = new PGDB();        
        $result = $db->delete($table,$where);
        unset($db);

        if ($result instanceof ErrorMessage)  
            return ErrorMessage::Stacked (__METHOD__,__LINE__,"Failed to Delete from table = $table  where = [{$where}]", true,$result);
        
        
        return $result;
    }
    
    public static function DeleteAll($table) 
    {
        $db = new PGDB();        
        $result = $db->delete_all($table);
        unset($db);

        if ($result instanceof ErrorMessage)  
            return ErrorMessage::Stacked (__METHOD__,__LINE__,"Failed to Delete ALL from table = $table", true,$result);
        
        return $result;
    }
    
    public static function Count($table,$where = null) 
    {        
        $db = new PGDB();
        $result = $db->count($table,$where);
        unset($db);
        
        if ($result instanceof ErrorMessage)  
            return ErrorMessage::Stacked (__METHOD__,__LINE__,"Failed to Count from table = $table  where = [{$where}] ", true,$result);
        
        
        return $result;
    }

    
    public function GetSingleRowValue($sql,$column)
    {

        $db = new PGDB();
        $result = $db->getSingleRowValue($sql,$column);
        unset($db);
        
        if ($result instanceof ErrorMessage)  
            return ErrorMessage::Stacked (__METHOD__,__LINE__,"Failed to get data column = $column \n sql = $sql", true,$result);
        
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

        if ($result instanceof ErrorMessage)  
            return ErrorMessage::Stacked (__METHOD__,__LINE__,"Failed to get data for Has_table \n", true,$result);
        
        return $result;
    }
    
    
    
    public static function LogError($from,$str) 
    {
        $db = new PGDB();     
        
        $dt = util::dbq(datetimeutil::NowDateTime().true);
        $from = util::dbq($from,true);
            
        $str =  util::dbq(substr(print_r($str,true),0,3000));  
        
        $sql = "insert into error_log (error_date_time,source_code_from,error_message) values ({$dt},{$from},{$str});";
        
        $result = $db->insert($sql,false,false);
        if ($result instanceof ErrorMessage ) throw new Exception($result);
        if ($result instanceof Exception ) throw new Exception($result->getMessage());
        
        
        unset($db);
        return $result;
    }
    
    
    public static function Unique($table,$field,$where = null,$as_array = false) 
    {
        $db = new PGDB();
        $result = $db->Unique($table,$field,$where,$as_array);        
        unset($db);
        
        if ($result instanceof ErrorMessage)  
            return ErrorMessage::Stacked (__METHOD__,__LINE__,"Failed to get data for  Unique  table =  $table,$field = field, where =".print_r($where,true), true,$result);
        
        return $result;   
    }
    
    
}

?>
