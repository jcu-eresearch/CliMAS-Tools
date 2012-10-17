<?php
class database
{

    public $link = null;
    public $result = null;
    public $debug = false;
    public $progress = false;
   
    public $debug_all = false;
    
    public $open_new_connection = false;
    
    private $db     = '';
    private $host   = '';
    private $userID = '';
    private $pwd    = '';
    
    private $insert_block_size = 40;
    

    public function __construct($db = null, $host = null, $userID = null, $pwd = null)
    {

        $this->db     = (!is_null($db))     ? $db     : $this->db;
        $this->host   = (!is_null($host))   ? $host   : $this->host;
        $this->userID = (!is_null($userID)) ? $userID : $this->userID;
        $this->pwd    = (!is_null($pwd))    ? $pwd    : $this->pwd;
        
        //** $this->GrantFullAccess(); //** make we have access to all db's
        
        $this->connect();
        
    }
    
    public function DB()
    {
        return $this->db;
    }
    
    public function __destruct()
    {
        $this->disconnect();
    }

    public function selectTable($db, $tableName, $keyColoumn = "",$where = "",$limit = "")
    {
        if ($where != "") $where = " where $where ";
        if ($limit != "") $limit = " limit {$limit} ";
        
        $q = "select * from `$db`.`$tableName` $where $limit";
        
        return $this->query($q,$keyColoumn);
    }


    public function database_names($like = "%")
    {
        $sql_result = $this->query("SELECT S.`SCHEMA_NAME` as database_name FROM information_schema.SCHEMATA S where SCHEMA_NAME != 'information_schema' and SCHEMA_NAME != 'mysql' and SCHEMA_NAME like '$like';",'database_name');
        
        return array_keys($sql_result);
    }

    
    public function table_names($db, $like = "%")
    {
        $sql = "SELECT TABLE_NAME  as table_name FROM information_schema.TABLES where TABLE_SCHEMA = '$db' and TABLE_NAME like('$like');";
        $sql_result = $this->query($sql,'table_name');
        
        $result = array();
        foreach (array_keys($sql_result) as $value)
            $result[$value] = $value;
        
        return $result;
    }
    
    
    public function connect()
    {
        $this->link = mysql_connect($this->host, $this->userID, $this->pwd,$this->open_new_connection);
        
        if (!$this->link) die('Could not connect: ' . mysql_error());
        
        $db_result = mysql_select_db($this->db);
        if ($db_result == false) die('Could not change to database ' . $this->db."\n");
        
        if ($this->debug_all) echo "Connect to {$this->db}\n";
        
    }

    public function change_db($db)
    {
        return  mysql_select_db($db);
    }

    public function disconnect()
    {
        if ($this->isConnected())
            @mysql_close($this->link);
        
        if ($this->debug_all) echo "Disconnect from {$this->db}\n";
        
        unset($this->link);
        $this->link = null;
    }

    public function isConnected()
    {        
        return (!is_null($this->link));   
    }
    
    
    
    //** the key will be unique and the value will be the last value for that key
    public function KeyedColumn($table,$keyColoumn,$valueColumn,$where,$limit)
    {
        if (is_null($table)       || $table == '') return null;
        if (is_null($keyColoumn)  || $keyColoumn == '') return null;
        if (is_null($valueColumn) || $valueColumn == '') return null;

        if ($where != '') $where = " where $where ";
        if ($limit != '') $limit = " limit $limit ";

        $sql = "select $keyColoumn,$valueColumn from $table $where order by $keyColoumn $limit";
        $sqlResult = $this->query($sql,$keyColoumn);

        $result = array();
        foreach ($sqlResult as $key => $row)  $result[$key] = $row[$valueColumn];

        unset($sqlResult);

        return $result;

    }

    private function clean_query($sql,$above_128 = true) 
    {
        
        //** clean all non typeable chars from query
        for ($index = 0; $index <= 31; $index++) $sql = str_replace(chr($index), ' ', $sql);   
        
        if ($above_128)
            for ($index = 128; $index <= 255; $index++) $sql = str_replace(chr($index), ' ', $sql);   

            
        $sql = trim($sql);
        $sql = util::trim_end($sql, ';');
        
        
        return $sql;
    }
    

    public function query($sql,$keyColoumn = "",$query_names = null)
    {
        
        $this->connect();
        $sql = $this->clean_query($sql);
        
        if (is_string($query_names)) $query_names = explode(",",$query_names);
        
        $result = array();
        $count = 0;
        foreach (explode(';',$sql) as $single_sql)
        {
            $single_sql = trim($single_sql);
            if ($single_sql == "") continue;
            
            $qname = (is_null($query_names)) ? $count  : $query_names[$count];
            if (substr($single_sql,0,2) == "<<") 
            {
                $singe_query_name = util::midStr($single_sql, '<<', '>>');
                $single_sql = str_replace("<<$singe_query_name>>", " ", $single_sql);
                $qname = $singe_query_name;
            }
            
            $result[$qname] = $this->query_single($single_sql,$keyColoumn);
            
            $count++;
        }
        
        $this->disconnect();
        if (count($result) == 1) return $result[0]; //** if there was only one result - i.e. one sql then return it's result - current output
        
        return $result;
        
    }
    
    private function query_single($sql,$keyColoumn = "")
    {
        
        $sql = $this->clean_query($sql);

        $sql_result = mysql_query($sql, $this->link);

        if ($sql_result == FALSE) return FALSE;

        try
        {
            $row = @mysql_fetch_assoc($sql_result);
        }
        catch (Exception $exc) {
            return array();
        }
        
        $result = array();
        while ($row)
        {
            if ($keyColoumn == "" )
                $result[] = $row;
            else
                $result[$row[$keyColoumn]] = $row;
            
            
            $row = @mysql_fetch_assoc($sql_result);
        }

        
        $this->result = $result;
        
        return $result;

    }

    public function jagged_query($sql,$key_column)
    {
        $this->connect();

        $sql = $this->clean_query($sql);

        $sql_result = mysql_query($sql, $this->link);

        if ($sql_result == FALSE) return FALSE;

        try
        {
            $row = @mysql_fetch_assoc($sql_result);
        }
        catch (Exception $exc) {
            return array();
        }
        
        $result = array();
        while ($row)
        {
            $result[$row[$key_column]][] = $row;            
            $row = @mysql_fetch_assoc($sql_result);
        }
        
        $this->result = $result;

        mysql_free_result($sql_result);
        
        $this->disconnect();
        
        return $result;

    }
    
    //** $array = key field name  value = column type
    public function change_types_of_columns($db,$table, $array)
    {    
        $changes = array();
        foreach ($array as $field => $datatype)
            $changes[] = "CHANGE  `$field`  `$field` $datatype NULL";
        
        $sql = "ALTER TABLE  `{$db}`.`{$table}` ". join(',',$changes).";";
        
        return $this->update($sql);
    }

    
    public function change_column_type($db,$table, $field,$datatype)
    {    
        $sql = "ALTER TABLE  `{$db}`.`{$table}` CHANGE  `$field`  `$field` $datatype NULL";
        return $this->update($sql);
    }
    
    public function change_column_type_to_double($db,$table, $field)
    {       
        return $this->change_column_type($db, $table, $field, 'double');
    }
    
    public function change_column_type_to_varchar($db,$table, $field,$size = 100)
    {            
        return $this->change_column_type($db, $table, $field, "varchar($size)");
    }
    
    
    public function count($table, $field = NULL,$where = NULL)
    {
        $this->connect();
        $result = -1;

        if (!is_null($field)) $groupby = " group by $field ";
        if (!is_null($where)) $where = " where $where ";

        $result = array();
        if (is_null($field))
            $sql = "select count(*) as count from $table $where";
        else
            $sql = "select $field, count(*) as count from $table $where $groupby";

        $sql = $this->clean_query($sql);
        
        $sql_result = mysql_query($sql, $this->link);

        if ($sql_result == FALSE)
            $result = -1;
        else
        {
            try
            {
                $row = mysql_fetch_assoc($sql_result);
                $result = trim($row['count']);
            }
            catch (Exception $exc) {
                $result = -1;
            }
        }
        
        $this->disconnect();
        
        return $result;

    }

    public function single_value_query($sql)
    {
        $sql_result = $this->query($sql);
        $first_row = util::first_element($sql_result);        
        return util::first_element($first_row);        
    }
    
    
    public function max($table,$id_field,$value_field,$where = NULL)
    {
        $result = null;

        if (!is_null($where) && $where != '') $where = " where $where ";

        $sql = "select $id_field,max($value_field) as 'max' from $table $where group by $id_field order by $id_field";

        $sql_result = $this->query($sql, $id_field);

        $result = array();
        foreach ($sql_result as $id => $row)
            $result[$id] = $row['max'];

        unset($sql_result);

        return $result;

    }

    public function min($table,$id_field,$value_field,$where = NULL)
    {
        $result = null;

        if (!is_null($where)) $where = " where $where ";

        $sql = "select $id_field,min($value_field) as 'min' from $table $where group by $id_field order by $id_field";


        $sql_result = $this->query($sql, $id_field);

        $result = array();
        foreach ($sql_result as $id => $row)
            $result[$id] = $row['min'];

        unset($sql_result);

        return $result;

    }


    public function insert($sql)
    {
        $this->connect();
        $sql = $this->clean_query($sql);
        $sql_result = mysql_query($sql,$this->link);
        $affected = mysql_affected_rows();
        $this->disconnect();
        return $affected;
    }

    public function delete($sql)
    {
        $this->connect();
        $sql = $this->clean_query($sql);
        $sql_result = mysql_query($sql,$this->link);
        $affected = mysql_affected_rows();
        $this->disconnect();
        return $affected;
    }

    public function update($sql)
    {
        $this->connect();
        
        $sql = util::trim_end(trim($sql), ";");

        $sql = $this->clean_query($sql);
        
        if (!util::contains($sql, ';'))
        {
            $sql_result = mysql_query($sql, $this->link);
            $affected = mysql_affected_rows();
            $this->disconnect();
            
            return $affected;
        }

        //** multiple quries
        $result_affected_rows = array();
        foreach (explode(';',$sql) as $single_sql)
        {
            $sql_result = mysql_query($single_sql.";", $this->link);
            $result_affected_rows[$sql_result] = mysql_affected_rows();
        }
            
        
        $this->disconnect();
        
        return $result_affected_rows;
    }


    public function Index($db,$table,$column)
    {
        if (is_null($db) || $db == "") return null;
        if (is_null($table) || $table == "") return null;
        if (is_null($column) || $column == "") return null;

        $sql = "ALTER TABLE `$db`.`$table` ADD INDEX (  `$column` );";

        $update_rows = $this->update($sql);

        return $update_rows;
    }

    //** $column_array = column names to index
    public function IndexColumns($db,$table,$column_array)
    {
        if (is_null($db) || $db == "") return null;
        if (is_null($table) || $table == "") return null;
        
        $adds = array();
        foreach ($column_array as $column)
            $adds[] = "ADD INDEX (`$column` )";
        
        $sql = "ALTER TABLE `$db`.`$table` ".join(',',$adds).";";
        
        $update_rows = $this->update($sql);

        return $update_rows;
    }
    
    
    
    public function AddTextColumn($db,$table,$column,$size = 100)
    {
        if (is_null($db) || $db == "") return null;
        if (is_null($table) || $table == "") return null;
        if (is_null($column) || $column == "") return null;
        
        if ($this->hasColumn($db,$table,$column))
        {
            return 1;            
        }
        

        $sql  = "ALTER TABLE  `$db`.`$table` ADD `$column` VARCHAR( $size ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL;\n";
        $add_column_ok = $this->update($sql);

        if(is_null($add_column_ok))
        {
            return null;
        }

        return $this->Index($db, $table, $column);
    }

    public function AddDateColumn($db,$table,$column)
    {
        if (is_null($db) || $db == "") return null;
        if (is_null($table) || $table == "") return null;
        if (is_null($column) || $column == "") return null;

        if ($this->hasColumn($db,$table,$column))
        {
            return 1;            
        }
        

        $sql  = "ALTER TABLE  `$db`.`$table` ADD `$column` DATETIME NULL;\n";
        $add_column_ok = $this->update($sql);

        
        if(is_null($add_column_ok))
        {
            return null;
        }

        return $this->Index($db, $table, $column);
    }


    public function hasColumn($db,$table,$column)
    {
        $short_table = $this->query("select * from `$db`.`$table` limit 1");
        $first_row = util::first_element($short_table);

        return (array_key_exists($column, $first_row));

    }

    public function ColumnNames($db,$table)
    {   
        $short_table = $this->query("select COLUMN_NAME as columns FROM `information_schema`.`COLUMNS` where TABLE_SCHEMA = '{$db}' and table_name = '{$table}';",'columns');
        return array_keys($short_table);
    }


    public function AddNumericColumn($db,$table,$column)
    {
        

        if (is_null($db) || $db == "") return null;
        if (is_null($table) || $table == "") return null;
        if (is_null($column) || $column == "") return null;

        if ($this->hasColumn($db,$table,$column))
        {
            return 1;            
        }
        

        $sql  = "ALTER TABLE  `$db`.`$table` ADD `$column` DOUBLE NULL;\n";
        $add_column_ok = $this->update($sql);

        if(is_null($add_column_ok))
        {
            return null;
        }
        

        return $this->Index($db, $table, $column);
    }

    public function Set($db,$table,$column,$value)
    {
        

        if (is_null($db) || $db == "") return null;
        if (is_null($table) || $table == "") return null;
        if (is_null($column) || $column == "") return null;
        if (is_null($value) || $value == "") return null;

        if (!$this->hasColumn($db,$table,$column))
        {
            return null;
        }


        $sql  = "update `$db`.`$table` set `$column` = $value;";


        $update_rows = $this->update($sql);


        return $update_rows;
    }


    public function DropTable($db,$table)
    {

       if (is_null($db) || $db == "") return null;
        if (is_null($table) || $table == "") return null;

        $update_rows = $this->update("drop table if exists `$db`.`$table`;");

        return $update_rows;
    }

    public function CreateTable($db,$table,$sql,$drop_table_first = false, $full_index = false)
    {

       if (is_null($db) || $db == "") return null;
        if (is_null($table) || $table == "") return null;
        if (is_null($sql) || $sql == "") return null;

        if ($drop_table_first) $this->DropTable($db, $table);
        
        $create_sql = "create table `$db`.`$table` \n".str_replace(';', '', $sql).";";
        
        $row_count = $this->update($create_sql);

        
        if ($full_index)
            $this->IndexColumns($db,$table,$this->ColumnNames($db,$table));

        return $row_count;
    }

    
    
    public function CreateTableFromArray($db,$table_name,$column_name_array,$drop_table_first = false, $full_index = false)
    {
          

      if (is_null($db))
        {
            return null;
        }

        if (is_null($table_name))
        {
            return null;
        }

        if (is_null($column_name_array))
        {
            return null;
        }

        if ($db == "")
        {
            return null;
        }

        if ($table_name == "")
        {
            return null;
        }

        if (count($column_name_array) == 0)
        {
            return null;
        }

        $tableName = $this->cleanColumnName($table_name);


        if ($drop_table_first) $this->DropTable($db, $table_name);

        
        $result  = "\nCREATE TABLE  `$db`.`$tableName`  (";
        $result .= "\n `ID` int(11) NOT NULL auto_increment,";

        $colNames = array();
        foreach ($column_name_array as $column_name => $column_type)  //** expect $column_name_array to be   ['column_name'] = db_type
        {
            $cleanName = $this->cleanColumnName($column_name,"C");
            if ($cleanName == "") continue;
            $colNames[$column_name] = $cleanName;

            $result .= "\n `$cleanName` $column_type default NULL";

            if (util::contains(strtoupper($column_type), 'VARCHAR')) $result .= " COLLATE utf8_unicode_ci";

            $result .= ",";

        }

        $result .= "\n `updated` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,";
        
        $result .= "\n PRIMARY KEY  (`ID`),";

        $index = "";

        if ($full_index)
        {
            foreach ($colNames as $column_name)
                $index .= "\n KEY `$column_name` (`$column_name`),";

            $result .= substr($index,0,strlen($index) - 1); //** drop last comma
        }

        $result .= "\n ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci;";

        $this->update($result);

        return $tableName;
    }



    public function matrixToTable($matrix,$rowID_name, $to_db,$tableName,$numeric_prefix = 'F',$indexes = NULL,$drop_table = false)
    {
         
        
        $sql_create_db = "create database if not exists $to_db;";
        $this->update($sql_create_db);
        
        if ($drop_table) $this->DropTable($to_db, $tableName);

       
        $column_name_lookup = array(); //** store keyed array of old name to new name
       
        $create_table_sql = $this->matrixToTable_CreateTable( $matrix,$rowID_name, $to_db,$tableName,$column_name_lookup,$numeric_prefix, $indexes);
        
        
        $this->update($create_table_sql); //** create table
        
        if ($this->progress) 
            $P = new progress(1, count($matrix), 1, 10);
        
        //** loop thru matrix row by row and buoild an SQL for that row.
        $count = 1;
        $block_count = 1;
        $block_insert = array();
        foreach ($matrix as $row_id => $row)
        {            
            
            if ($this->progress) 
            {
                $P->step_to($count);
                $P->display_percent();
            }
            
            $insert = "";
            $insert_column_names = array();
            $insert_column_values = array();

            $row_insert =  array();
            
            //** get data from matrix under old name and write new name in to "$insert_column_values"
            foreach ($column_name_lookup as $old_name => $name_info)
            {
                $insert_column_names[] = $name_info['new'];  //** get column name 'new cleaned name'

                //** if current column is the row id field then get data from row'sID'
                $cell_value = ($name_info['isRowID'] ) ? $row_id : $row[$name_info['old']];
                
                $cell_value = (is_array($cell_value)) ?  print_r($cell_value,true) : trim($cell_value);
                
                $db_cell_value = 'NULL'; //** default value to send to DB is NULL
                if (trim($cell_value) != "" || is_null($cell_value))
                    $db_cell_value = ($name_info['type'] == "DOUBLE") ?  $cell_value : "'".$cell_value."'"; //** data type is varchar so wrap in quotes

                $insert_column_values[] = $db_cell_value;

            }

            for ($index = 0; $index < count($insert_column_values); $index++) 
                $row_insert[$insert_column_names[$index]] = $insert_column_values[$index];

            $block_insert[] = $row_insert;
            
            if ($block_count >= $this->insert_block_size)
            {                
                
                $inserted_count = $this->InsertArrayBulk($to_db, $tableName, $block_insert);                
                unset($block_insert);
                $block_insert = array();
                $block_count = 1;
            }
            
            $count++;
            $block_count++;
            
        }

        if (count($block_insert) != 0)
        {
            $inserted_count = $this->InsertArrayBulk($to_db, $tableName, $block_insert); //** write anything left in block
        }
        
        unset($block_insert);
        
        return $this->count("`$to_db`.`$tableName`");
    }


    
    
    
    public function matrixToTable_CreateTable( $matrix,$rowID_name, $to_db,$tableName,&$column_name_lookup,$numeric_prefix = 'F',$indexes = NULL)
    {
        //** SQL to create Table
        

        $result = "";
        
        $result .= "\nCREATE TABLE  `{$to_db}`.`$tableName`  (";
        $result .= "\n `ID` int(11) NOT NULL auto_increment,";

        $matrix_column_names = matrix::ColumnNames($matrix);

        
        $rowID_newname = $this->cleanColumnName($rowID_name,$numeric_prefix); //** add the "row_ID column"
        $rowID_Type = matrix::ColumnTypeForDB($matrix);
        
        
        $result .= "\n `$rowID_newname` $rowID_Type default NULL";        
        if ($rowID_Type != "DOUBLE") $result .= " COLLATE utf8_unicode_ci";
        $result .= ",";

        $column_name_lookup[$rowID_name]['old']  = $rowID_name;
        $column_name_lookup[$rowID_name]['new']  = $rowID_newname;
        $column_name_lookup[$rowID_name]['type'] = $rowID_Type;
        $column_name_lookup[$rowID_name]['isRowID'] = true;


        foreach ($matrix_column_names  as $rawColumnName)
        {
            $cleanName = $this->cleanColumnName($rawColumnName,$numeric_prefix);
            if ($cleanName == "") continue;

            $columnType = matrix::ColumnTypeForDB($matrix,$rawColumnName);
            $result .= "\n `$cleanName` $columnType default NULL";
            
            if ($columnType != "DOUBLE") $result .= " COLLATE utf8_unicode_ci";
            
            $result .= ",";

            $column_name_lookup[$rawColumnName]['old']  = $rawColumnName;
            $column_name_lookup[$rawColumnName]['new']  = $cleanName;
            $column_name_lookup[$rawColumnName]['type'] = $columnType;
            $column_name_lookup[$rawColumnName]['isRowID'] = false;

        }

        $result .= "\n `updated` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,";
        
        $result .= "\n PRIMARY KEY  (`ID`),";
        $result .= "\n KEY `Index_$rowID_name` (`$rowID_name`)";

        if (!is_null($indexes))
        {
            //** create index for columns

            $index = "";
            foreach ($matrix_column_names  as $rawColumnName)
            {
                $cleanName = $this->cleanColumnName($rawColumnName,$numeric_prefix);
                if ($cleanName == "") continue;
                $index .= "\n KEY `Index_$cleanName` (`$cleanName`),";
            }

            if ($index != "")
                $result .= ",".substr($index,0,strlen($index) - 1); //** add index code and drop last comma

        }


        $result .= "\n ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci;";

        return $result;

    }


    public function fromDelimited($filename,$db,$tableName = null, $delim = ",")
    {        
        return fromCSV($filename,$db,$tableName, $delim);
    }

    public function fromCSV($filename,$db,$tableName = null, $delim = ",",$add_date_columns = false)
    {

       if (!file_exists($filename))
        {
            return NULL;
        }

        if (is_null($tableName))
            $tableName = $this->cleanColumnName(str_replace(".".file::getFileExtension($filename),"",util::fromLastSlash($filename)));


        $sql = $this->getCreateTableText($db,$filename,$tableName,$delim,'F',$add_date_columns);
        
        $sql_result = $this->update($sql);
        
        $row_count = -1;
        if ( $this->debug )
            $row_count = $this->count($db,$tableName);

        return $row_count;

    }



    private function getCreateTableText($db,$filename,$tableName = NULL,$delim = ",",$numeric_prefix = 'F',$add_date_columns = false)
    {
          

      if (!file_exists($filename))
        {
            return NULL;
        }

        if (file::lineCount($filename) <= 1)
        {
            return NULL;
        }


        $file = file($filename);

        if (is_null($tableName))
            $tableName = $this->cleanColumnName(util::toLastChar(util::fromLastSlash($filename), '.'));

        
        $split = str_getcsv($file[0], $delim, '"');


        $result  = "";
        $result .= "\nDROP TABLE IF EXISTS `$db`.`$tableName`;";
        $result .= "\nCREATE TABLE  `$db`.`$tableName`  (";
        $result .= "\n `ID` int(11) NOT NULL auto_increment,";

        $colCount = 0;

        $colNames = array();
        foreach ($split as $rawColumnName)
        {
            $columnType = $this->getColumnType($file, $colCount,$delim);
            
            
            $cleanName = $this->cleanColumnName($rawColumnName,$numeric_prefix);
            
            //echo "$cleanName\n";
            
            if ($cleanName == "") continue;

            // check to see if the name of the column has "date" somewhere
            // if so then we will make this a datetime column
            if ($add_date_columns && util::contains(strtolower($cleanName), "date"))  $columnType = "DATE";
            
            
            $colNames[] = $cleanName;

            if ($columnType == "DOUBLE")
                $result .= "\n `$cleanName` $columnType default NULL,";
            else
                $result .= "\n `$cleanName` $columnType default NULL COLLATE utf8_unicode_ci ,";


            $colCount++;
        }

        $result .= "\n `updated` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,";
        
        $result .= "\n PRIMARY KEY  (`ID`),";

        $index = "";
        $index_count = 0;
        foreach ($split as $rawColumnName)
        {
            if ($index_count > 30) continue; //** if we have more than 30 columns only index the first 30

            $cleanName = $this->cleanColumnName($rawColumnName,$numeric_prefix);
            if ($cleanName == "") continue;

            $index .= "\n KEY `Index_$cleanName` (`$cleanName`),";

            $index_count++;

        }

        $result .= substr($index,0,strlen($index) - 1); //** drop last comma

        $result .= "\n ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci;";

        $fullPath = realpath($filename);

        $result .= "\n\nload data local infile '$fullPath' into table `$db`.`$tableName`";
        $result .= "\nfields terminated by '$delim'";
        $result .= "\noptionally enclosed by '\"' ";
        $result .= "\nlines terminated by '\\n'";
        $result .= "\nIGNORE 1 LINES";
        $result .= "\n(`".join("`,`",$colNames)."`);";

        $lastCol = util::last_element($colNames);

        $trim_sql = "\n\nupdate `$db`.`$tableName` set `$lastCol` = trim(char(13) from `$lastCol`);";

        $result .= $trim_sql;

        
        

        return $result;

    }

    public function cleanColumnName($rawColumnName,$numeric_prefix = 'F')
    {
          

          $rawColumnName = trim($rawColumnName);

            $rawColumnName = str_replace('"','',$rawColumnName);
            $rawColumnName = str_replace("'",'',$rawColumnName);
            $rawColumnName = str_replace(";",'',$rawColumnName);
            $rawColumnName = str_replace("(",'',$rawColumnName);
            $rawColumnName = str_replace(")",'',$rawColumnName);
            $rawColumnName = str_replace("[",'',$rawColumnName);
            $rawColumnName = str_replace("]",'',$rawColumnName);
            $rawColumnName = str_replace("{",'',$rawColumnName);
            $rawColumnName = str_replace("}",'',$rawColumnName);
            $rawColumnName = str_replace("%",'',$rawColumnName);
            $rawColumnName = str_replace("$",'',$rawColumnName);
            $rawColumnName = str_replace("*",'',$rawColumnName);
            $rawColumnName = str_replace(':','',$rawColumnName);
            $rawColumnName = str_replace('&','',$rawColumnName);
            $rawColumnName = str_replace('?','',$rawColumnName);
            $rawColumnName = str_replace('<','',$rawColumnName);
            $rawColumnName = str_replace('>','',$rawColumnName);
            $rawColumnName = str_replace('^','',$rawColumnName);
            $rawColumnName = str_replace('#','',$rawColumnName);

            $rawColumnName = str_replace("-",'_',$rawColumnName);
            $rawColumnName = str_replace(' ','_',$rawColumnName);
            $rawColumnName = str_replace("/**",'_',$rawColumnName);
            $rawColumnName = str_replace('\\','_',$rawColumnName);
            $rawColumnName = str_replace('.','_',$rawColumnName);

            $rawColumnName = trim($rawColumnName);

            $rawColumnName = strtolower($rawColumnName);

            $first_char = util::first_char($rawColumnName);

            switch ($first_char) {
                case '0':
                case '1':
                case '2':
                case '3':
                case '4':
                case '5':
                case '6':
                case '7':
                case '8':
                case '9':
                    $rawColumnName = $numeric_prefix.$rawColumnName; //** if the name starts with a number  then we need to mak it start with a letter
                    break;

            }

            $rawColumnName = trim(util::trim_end($rawColumnName, "_"));
            
            return $rawColumnName;
    }


    private function getColumnType($fileArray, $colCount,$delim)
    {
        


        $rowCount = 0;
        $numberCount = 0;

        $maxStringLength = 0;

        foreach ($fileArray as $fileLine)
        {
            
            if ($rowCount > 0)
            {
                $split = str_getcsv($fileLine, $delim);
                $cell = trim($split[$colCount]);

                if ($cell == "") continue;

                $maxStringLength = max(strlen($cell),$maxStringLength);

                if (is_numeric($cell)) $numberCount++;    
                
            }
            
            $rowCount++;

        }

        if ( $numberCount >  ( ($rowCount -1 ) * 0.8) ) return "DOUBLE"; //** if 80% of values are numbers its a number column

        if ($maxStringLength == 0) $maxStringLength = 1;
        
        $maxStringLength = round($maxStringLength * 3,0); //**make it 3 times the max length

        $result_type = "varchar($maxStringLength)";
        
        return $result_type;

    }

    public function toCSV($sql,$outputFilename)
    {
        matrix::Save(self::query($sql), $outputFilename);        
        return file_exists($outputFilename);
    }

    public function pivotToCSV($db, $sql,$outputFilename,$pivotFieldsStr)
    {
        
        $pivotFields = explode(',',$pivotFieldsStr);

        if (file_exists($sql)) $sql = file_get_contents($sql); //** if they passed a filename get the sql from there

        $sqlResult = $this->quickQuery($db,$sql);
                           //**sqlPivot($array,    $columnID,      $rowID,         $cellID,         $nullValue)
        $pivotResult = util::sqlPivot($sqlResult,$pivotFields[0],$pivotFields[1],$pivotFields[2], "");

        util::saveMatrix($pivotResult,$outputFilename);
    }


    public function uniqueValues($db, $table, $field, $where = '',$limit = '')
    {
          

      if ($where != '') $where = " where $where ";
        if ($limit != '') $limit = " limit $limit ";

        $sql = "SELECT `$field`  FROM `$db`.`$table` $where group by `$field` order by `$field` $limit;";


        return matrix::Column($this->query($sql), $field);
    }


    //** $db              : name of database to connect to
    //** $table_name      : table name to create
    //** $field_prefix    : if column name is numeric then prefix it with this.
    //** $sql             : the SQL to select data
    //** $pivot_column    : What column of SQL result will we look at for Unique values to create
    //**                    columns of pivot table.    MUST EXIST IN SQL result
    //** $pivot_row       : What column of SQL result will we look at for Unique values to create
    //**                    rows id's of pivot table   MUST EXIST IN SQL result
    //** $pivot_value     : What column of SQL result will we look at for Unique values to retive
    //**                    value. ie the cell values  MUST EXIST IN SQL result
    //** $pivot_operation : how do we summarise the values +,-,*,/**, avg may be more see   $this->sqlPivot
    //**
    //** $null_value      : default null value for table

    public function PivotQuery($to_db,$table_name,$field_prefix,$sql, $pivot_column, $pivot_row, $pivot_value,$pivot_operation,$null_value = null, $get_stats = false,$min_row_count = null)
    {
        
        
        
        $add_db = "create database if not exists {$to_db};";
        $add_db_result = $this->query($add_db);
        
        $add_db_result = print_r($add_db_result,true);
        
        

        $sql_result = $this->query($sql);


        
        if (count($sql_result) <= 0) return;

        //** check to see if Pivot columns exists in resulkt set
        $first_row = util::first_element($sql_result);
        if (!array_key_exists($pivot_column, $first_row) ||
            !array_key_exists($pivot_row,    $first_row) ||
            !array_key_exists($pivot_value,  $first_row)
            )
        {
            return null;
        }

        
        if (!is_null($min_row_count))
        {
            //** need to count the number of rows assign to each  $pivot_row
            //** $pivot_row is the name of the column that holds the unique values to assign to the row header

            //** unique value counts for $pivot_row
            $histogram = matrix::ColumnHistogram($sql_result, $pivot_row);  //** count per unique value
            

            $sql_row_ids_to_remove = array();
            foreach ($histogram as $histogram_row_id => $count)
            {
                if ($count >= $min_row_count) continue;


                //** here we need to get a list of keys from $sql_result where $row_id  the value in  result[$pivot_row] =  $histogram_row_id
                foreach ($sql_result as $sql_row_id => $sql_row)
                {
                    //** if the column from the sql result we have chosen for the pivrot row id
                    //** has the same value as the $histogram_row_id we are going to mark it for removal
                    if ($sql_row[$pivot_row] == $histogram_row_id)
                        $sql_row_ids_to_remove[] = $sql_row_id;

                }

            }

            foreach ($sql_row_ids_to_remove as $id) unset($sql_result[$id]);

            unset($histogram);
            unset($sql_row_ids_to_remove);
        }


        
        $pivot = util::sqlPivot($sql_result,$pivot_column,$pivot_row,$pivot_value, $null_value, $pivot_operation,false);
        
        
        unset($sql_result);

        if ($get_stats)
        {
            $stats = matrix::RowStatistics($pivot,$null_value);
            
            //** Add Stats to Table
            foreach ($pivot as $row_id => $row)
                foreach ($stats[$row_id] as $stats_column => $stats_value)
                    $pivot[$row_id][$stats_column] = $stats_value;

        }

        
        $row_count = $this->matrixToTable($pivot,$pivot_row,$to_db, $table_name,$field_prefix ,NULL);


        unset($pivot);

        return $row_count;
        
    }


    public function add_column_varchar($db,$table,$column_name,$size = 100)
    {
        
        return $this->add_column($db,$table,$column_name,"VARCHAR($size)");
    }

    public function add_column_decimal($db,$table,$column_name,$size = 15,$decimal_places = 5)
    {
          
      return $this->add_column($db,$table,$column_name,"double");
    }

    public function add_column_double($db,$table,$column_name)
    {
         
       return $this->add_column($db,$table,$column_name,"double");
    }

    public function add_column($db,$table,$column_name,$db_column_type = "VARCHAR(100)")
    {
        
        $collate = "";
        if (util::contains(strtoupper($db_column_type), 'VARCHAR'))
            $collate = "CHARACTER SET utf8 COLLATE utf8_unicode_ci";

        $sql  = "ALTER TABLE  `$db`.`$table` ADD  `$column_name` $db_column_type $collate NULL;";
        $sql .= "ALTER TABLE  `$db`.`$table` ADD INDEX (  `$column_name` ) ;";

        return $this->update($sql);

    }


    public function hasTable($db,$table)
    {


      $sql = "SELECT count(*) as count FROM information_schema.`TABLES` where TABLE_SCHEMA = '$db'   and TABLE_NAME   = '$table';";
        $sql_result = $this->query($sql);

        $first_row = util::first_element($sql_result);

        if ($first_row['count'] > 0) return true;

        return false;

    }

    public function InsertArray($db,$table,$array)
    {
            

    $useable = array();
        foreach ($array as $key => $value)
            $useable[$key] = (is_null($value) || trim($value) == "") ? 'NULL' : $value;

        $sql   = "insert into `$db`.`$table` (".join(',',array_keys($useable)).") values (".join(',',array_values($useable)).");";


        return $this->insert($sql);

    }

    //** create one (or more) large insert statements
    //** $array = two levels 
    //** [row_index] =  row (column_name => [cell],column_name => [cell],column_name => [cell],column_name => [cell])
    public function InsertArrayBulk($db,$table,$array)
    {
          
      
        if (count($array) == 0) return 0;
        
        $useable = array();

        $column_names = array_keys(util::first_element($array));
        
        $sql   = "insert into `$db`.`$table` (`".join('`,`',$column_names)."`) values ";

        foreach ($array as $row_index => $row) 
        {
            foreach ($row as $column_name => $value)
                $useable[$column_name] = (is_null($value) || trim($value) == "") ? 'NULL' : $value;
            
            $sql  .= "(".join(',',array_values($useable))."),";
        }

        $sql = util::trim_end(trim($sql), ",").";";
        

        return $this->insert($sql);

    }
    
    
    public function GroupBy($db,$table,$key_column,$value_column,$where = "")
    {
        

        $where = ($where == "") ?  "" : " where $where ";
        
        $sql = "SELECT {$key_column},{$value_column} FROM `$db`.$table $where group by {$key_column},{$value_column} order by {$key_column},{$value_column};";

        $sql_result = $this->query($sql, $key_column);

        $result = array();
        foreach ($sql_result as $row_id => $row)
            $result[$row[$key_column]] = $row[$value_column];

        unset($sql_result);

        return $result;

    }

    public function Grant($user = null,$db = null,$table = "*",$privilege = "ALL")
    {
        

        if (is_null($db)) $db = $this->db;
        if (is_null($user)) $user = $this->userID;
        
        $sql = "GRANT $privilege PRIVILEGES ON {$db}.{$table} TO '{$user}'@'%' WITH GRANT OPTION;";
        return $this->update($sql);
    }
    

    private function GrantFullAccess()
    {      
        $sql = "GRANT ALL PRIVILEGES ON *.* TO '{$this->user}'@'%' WITH GRANT OPTION;";
        return $this->update($sql);
    }
    

    public function AndClauseFromKeyedArray($src)
    {      
       
        $result_array = array();
        foreach ($src as $key => $value)
            $result_array[] =  (is_numeric($value)) ?  "`$key` = $value" :  "`$key` = '$value'";
        
        return join(' and ',$result_array);
    }
    
    public function CopyTable($fromDB,$fromTable,$toDB,$toTable,$drop_table_first = false, $full_index = false)
    {      
        $row_count = $this->CreateTable($toDB, $toTable, "select * from `$fromDB`.`$fromTable` ",$drop_table_first, $full_index);
        return $row_count;
    }
    
    public function Table2File($db, $tableName,$filename,$delim = ",",$replace_delim = null,$write_row_limit = 5000)
    {      
        $row_count = $this->count("`{$db}`.`{$tableName}`");

        $matrix = $this->selectTable($db, $tableName, "", "", "0,1");
        if (!is_null($replace_delim))
            $matrix = matrix::ReplaceStringValue($matrix, $delim, $replace_delim);
        
        matrix::Save($matrix, $filename,$delim, null, null, true, false); //** write the headers and the first row
         
        
        for ($row_number = 1; $row_number < $row_count; $row_number += $write_row_limit) {
            $matrix = $this->selectTable($db, $tableName, "", "", "{$row_number},{$write_row_limit}");

            if (!is_null($replace_delim))
                $matrix = matrix::ReplaceStringValue($matrix, $delim, $replace_delim);
            
            matrix::Save($matrix, $filename,$delim, null, null, false, true);
            unset($matrix);                
        }
        
        if (!file_exists($filename)) return null; //** if file does not exists then it's wrong -- failed
        
        $file_row_count = file::lineCount($filename); //** check the number of rows in the file
        
        return  (($file_row_count - 1) == $row_count); //** return true if the number of rows in the file macthes the number of rows from the file.
    }
    
    
}
?>