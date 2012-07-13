<?php
/**
 *
 * RUn as HPC server job to pregenerate species climate predictions as a mass batch job
 *  
 */
class SpeciesClimateGenerate {
    
    
    /**
     * Run one or more stages of import 
     * 0 = test
     * 
     * 
     * @param int $stage 
     */
    public static function Execute($stage = "names" ) 
    {
        if (is_null($stage)) $stage = "names";
        
        if ($stage == "names") 
        {
            
            self::header("Stage Names ");
            
            
            $method_names = get_class_methods('SpeciesClimateGenerate');

            foreach ($method_names  as $method_name) 
            {
                
                if (util::contains($method_name, "Stage"))
                {
                    echo self::$method_name(true)."\n";
                }
                
            }
            
            return;
        }
        
            
        
        
        if (util::contains($stage, ","))
        {
            foreach (explode(",",$stage)as $stage_num) {
                $method = "Stage{$stage_num}";
                $stage_result = self::$method();
                
                if (!$stage_result) exit(1);

            }
        }
        else
        {
        
            $method = "Stage{$stage}";
            self::$method();
            
        }
        
        
    }
    
    

    public static  function Stage01($name_only = false) 
    {
        
        $name = 'Populate Maxent Field Names';

        if ($name_only) return __METHOD__."::".$name;
        
        self::header($name);
        
        $names = matrix::ColumnNames(matrix::Load('/home/jc166922/maxentResults.csv'));
        
        //echo "Count = ".count($names)."\n";
        //echo "Count Unique= ".count(array_unique($names))."\n";
        
        $sql = self::sql_maxent_field_names();
        
        //echo "$sql\n";

        $db = new PGDB();
        $update_result = $db->update($sql);
        //echo "update_result = $update_result\n";

        
        $inserted_count = 0;
        foreach ($names as $name) 
        {
            $row_sql = "insert into maxent_fields (name) values ('{$name}')";
            
            //echo "$row_sql  ";
            
            $update_result = $db->update($row_sql);
            
            $inserted_count += $update_result;
            
            //echo " UR = $update_result\n";
            
        } 
        
        //echo "inserted_count = $inserted_count\n";
        
        if ($inserted_count != count($names))
        {
            echo "### ERROR:: Failed to insert all Names\n";
            return false;
        }
        
        unset($db);
        
        return true;
        
    }
    
    
    private static function sql_maxent_field_names() {
        
$sql = <<<SQL
DROP TABLE IF EXISTS maxent_fields;
CREATE TABLE maxent_fields 
(
    id SERIAL NOT NULL PRIMARY KEY
    ,name              varchar(256)   -- eg. maxentResults.csv
    ,update_datetime   timestamp without time zone 
);
GRANT ALL PRIVILEGES ON maxent_fields TO ap02;
GRANT USAGE, SELECT ON SEQUENCE maxent_fields_id_seq TO ap02;
        
SQL;

        return $sql;

    }

    
    
    
    public static  function Stage02($name_only = false)
    {

        $name = 'Model Descriptions';

        if ($name_only) return __METHOD__."::".$name;
        
        self::header($name);
        
    
        // might not be a good idea to drop table - really need  to copy as we will have update 
        // the models_id in other tables from old to new
        
        
$table_sql = <<<SQL
DROP TABLE IF EXISTS models;
CREATE TABLE models
(
    id SERIAL NOT NULL PRIMARY KEY
    ,dataname          varchar(60)
    ,description       varchar(256)  
    ,moreinfo          varchar(900)
    ,uri               varchar(500) 
    ,metadata_ref      varchar(500) 
    ,update_datetime   timestamp without time zone 
);
GRANT ALL PRIVILEGES ON models TO ap02;
GRANT USAGE, SELECT ON SEQUENCE models_id_seq TO ap02;        
SQL;


        $db = new PGDB();
        
        $table_result = $db->update($table_sql);
        
        //echo "table_result = $table_result\n";
        
        $descs = FinderFactory::Result("ClimateModelAllValues");
        $descs instanceof Descriptions;
        
        $format = "insert into models (dataname,description,moreinfo,uri) values ('{DataName}','{Description}','{MoreInformation}','{URI}');";
         
        $inserted_count = 0;
        foreach ($descs->Descriptions() as $desc) {
            
            $desc instanceof Description;
            $values_sql = $desc->asFormattedString($format);
            
            //echo "\n$values_sql  "; 
            
            $values_result = $db->update($values_sql);
            
            //echo "  UR = {$values_result}"; 
            
            $inserted_count += $values_result;
        
        }
        
        if ($inserted_count != $descs->count())
        {
            echo "\n### ERROR:: Failed to insert Model Description properly\n";
            return false;
        }
        
        unset($db);
        
        return true;
        
    }


    public static  function Stage03($name_only = false)     
    {
        
        $name = 'Scenario Descriptions';

        if ($name_only) return __METHOD__."::".$name;
        
        self::header($name);
    
        // might not be a good idea to drop table - really need  to copy as we will have update 
        // the models_id in other tables from old to new
        
        
        
$table_sql = <<<SQL
DROP TABLE IF EXISTS scenarios;
CREATE TABLE scenarios
(
    id SERIAL NOT NULL PRIMARY KEY
    ,dataname          varchar(60)
    ,description       varchar(256)  
    ,moreinfo          varchar(900)
    ,uri               varchar(500) 
    ,metadata_ref      varchar(500) 
    ,update_datetime   timestamp without time zone 
);
GRANT ALL PRIVILEGES ON scenarios TO ap02;
GRANT USAGE, SELECT ON SEQUENCE scenarios_id_seq TO ap02;        
SQL;

        $db = new PGDB();
        
        $table_result = $db->update($table_sql);
        
        //echo "table_result = $table_result\n";
        
        $descs = FinderFactory::Result("EmissionScenarioAllValues");
        $descs instanceof Descriptions;
        
        $format = "insert into scenarios (dataname,description,moreinfo,uri) values ('{DataName}','{Description}','{MoreInformation}','{URI}');";
         
        $inserted_count = 0;
        foreach ($descs->Descriptions() as $desc) {
            
            $desc instanceof Description;
            $values_sql = $desc->asFormattedString($format);
            
            //echo "\n$values_sql  "; 
            
            $values_result = $db->update($values_sql);
            
            //echo "  UR = {$values_result}"; 
            
            $inserted_count += $values_result;
        
        }
        
        if ($inserted_count != $descs->count())
        {
            echo "\n### ERROR:: Failed to insert scenarios Description properly\n";
            return FALSE;
        }
        
        unset($db);
        
        return true;
        
    }

    
    
    public static  function Stage04($name_only = false)
    {
        
        $name = 'Times Descriptions';

        if ($name_only) return __METHOD__."::".$name;
        
        self::header($name);
    
        // might not be a good idea to drop table - really need  to copy as we will have update 
        // the models_id in other tables from old to new
        
        
        
$table_sql = <<<SQL
DROP TABLE IF EXISTS times;
CREATE TABLE times
(
    id SERIAL NOT NULL PRIMARY KEY
    ,dataname          varchar(60)
    ,description       varchar(256)  
    ,moreinfo          varchar(900)
    ,uri               varchar(500) 
    ,metadata_ref      varchar(500) 
    ,update_datetime   timestamp without time zone 
);
GRANT ALL PRIVILEGES ON times TO ap02;
GRANT USAGE, SELECT ON SEQUENCE times_id_seq TO ap02;        
SQL;

        $db = new PGDB();
        
        $table_result = $db->update($table_sql);
        
        //echo "table_result = $table_result\n";
        
        $descs = FinderFactory::Result("TimeAllValues");
        $descs instanceof Descriptions;
        
        $format = "insert into times (dataname,description,moreinfo,uri) values ('{DataName}','{Description}','{MoreInformation}','{URI}');";
         
        $inserted_count = 0;
        foreach ($descs->Descriptions() as $desc) {
            
            $desc instanceof Description;
            $values_sql = $desc->asFormattedString($format);
            
            //echo "\n$values_sql  "; 
            
            $values_result = $db->update($values_sql);
            
            //echo "  UR = {$values_result}"; 
            
            $inserted_count += $values_result;
        
        }
        
        if ($inserted_count != $descs->count())
        {
            echo "\n### ERROR:: Failed to insert times Description properly\n";
            return false;
            
        }
        
        unset($db);
        
        return true;
        
    }


    public static  function Stage05($name_only = false)
    {
        
        $name = 'maxent_values';

        if ($name_only) return __METHOD__."::".$name;
        
        self::header($name);
        
$table_sql = <<<SQL
DROP TABLE IF EXISTS maxent_values;
CREATE TABLE maxent_values
(
    id SERIAL NOT NULL PRIMARY KEY
    ,modelled_climates_id  integer       -- many to one with modelled_climates.id
    ,maxent_fields_id      integer       -- many to one with maxent_fields.id
    ,num                   float         -- a numeric value 
    ,update_datetime   timestamp without time zone 
);
GRANT ALL PRIVILEGES ON maxent_values TO ap02;
GRANT USAGE, SELECT ON SEQUENCE maxent_values_id_seq TO ap02;        
SQL;
    

        $db = new PGDB();
        
        $table_result = $db->update($table_sql);
        
        //echo "table_result = $table_result\n";
        
        unset($db);
        
        if ($table_result != 0)
        {
            echo "\n### ERROR:: Failed to add maxent_values properly\n";
            return false;
            
        }
        
        return true;
        
    }
    

    public static  function Stage06($name_only = false)
    {
        
        $name = 'modelled_climates';

        if ($name_only) return __METHOD__."::".$name;
        
        self::header($name);
    
        
$table_sql = <<<SQL
DROP TABLE IF EXISTS modelled_climates;
CREATE TABLE modelled_climates
(
    id SERIAL NOT NULL PRIMARY KEY
    ,species_id        integer        -- may become out of sync with species (use scientific_name to resync)
    ,scientific_name   varchar(256)   -- Will be Unique
    ,common_name       varchar(256)
    ,models_id         integer
    ,scenarios_id      integer
    ,times_id          integer
    ,update_datetime   timestamp without time zone 
);
GRANT ALL PRIVILEGES ON modelled_climates TO ap02;
GRANT USAGE, SELECT ON SEQUENCE modelled_climates_id_seq TO ap02;        
SQL;

        $db = new PGDB();
        
        $table_result = $db->update($table_sql);
        
        //echo "table_result = $table_result\n";
        
        unset($db);

        if ($table_result != 0)
        {
            echo "\n### ERROR:: Failed to add modelled_climates properly\n";
            return false;
            
        }
        
        
        return true;
        
    }
    
    
    public static  function Stage07($name_only = false)
    {
        
        $name = 'files_data';

        if ($name_only) return __METHOD__."::".$name;
        
        self::header($name);

        
$table_sql = <<<SQL
DROP TABLE IF EXISTS files_data;
CREATE TABLE files_data 
(
    id SERIAL NOT NULL PRIMARY KEY
    ,file_unique_id   varchar(60)
    ,mimetype         varchar(100)
    ,file_description varchar(500)
    ,category         varchar(100)
    ,partnum          float
    ,totalparts       float
    ,total_filesize   float
    ,data             text
    ,update_datetime  timestamp without time zone 
);

GRANT ALL PRIVILEGES ON files_data TO ap02;
GRANT USAGE, SELECT ON SEQUENCE files_data_id_seq TO ap02;
SQL;
    
        //echo "Create Table for files_data \n";

        $db = new PGDB();
        
        $table_result = $db->update($table_sql);
        
        //echo "table_result = $table_result\n";
        
        unset($db);
        
        if ($table_result != 0)
        {
            echo "\n### ERROR:: Failed to add files_data properly\n";
            return false;
            
        }
        
        
        return true;
        
        
        
    }
    
    
    public static  function Stage08($name_only = false)
    {
        
        $name = 'modelled_climate_files';

        if ($name_only) return __METHOD__."::".$name;
        
        self::header($name);

        
$table_sql = <<<SQL
DROP TABLE IF EXISTS modelled_climate_files;
CREATE TABLE modelled_climate_files
(
    id SERIAL NOT NULL PRIMARY KEY
    ,file_unique_id        varchar(60)
    ,modelled_climates_id  integer       -- Many to 1 with  modelled_climates.id
    ,update_datetime  timestamp without time zone 
);

GRANT ALL PRIVILEGES ON modelled_climate_files TO ap02;
GRANT USAGE, SELECT ON SEQUENCE modelled_climate_files_id_seq TO ap02;
SQL;
    
        //echo "Create Table for modelled_climate_files \n";

        $db = new PGDB();
        
        $table_result = $db->update($table_sql);
        
        //echo "table_result = $table_result\n";
        
        unset($db);

        
        if ($table_result != 0)
        {
            echo "\n### ERROR:: Failed to add modelled_climate_files properly\n";
            return false;
            
        }
        
        return true;
        
    }
    

    
    

    private static function header($str = "")
    {
        //echo "\n".str_repeat("=", 70);
        echo "\n=== {$str}\n";
        //echo "\n".str_repeat("=", 70)."\n";
        
    }
    
    
    public static  function Stage1($name_only = false) 
    {
        
        $name = 'Bulid tables for climate model data stages(01,02,03,04,05,06,07,08) ';

        if ($name_only) return __METHOD__."::".$name;
        
        self::header($name);
        
        self::Execute('01,02,03,04,05,06,07,08');
        
        
    }
    

    public static  function Stage2($name_only = false) 
    {
        
        $name = 'Test insert of Maxent Data';
        if ($name_only) return __METHOD__."::".$name;

        $species_id = 2;
        
        $maxentResults = configuration::Maxent_Species_Data_folder().$species_id.configuration::osPathDelimiter().configuration::Maxent_Species_Data_Output_Subfolder().configuration::osPathDelimiter()."maxentResults.csv";

        echo "maxentResults = $maxentResults\n";
        
        $db = new PGDB();
        
        $insert_result = $db->InsertMaxentResults($species_id,$maxentResults);
        
        unset($db);
        
        
        echo "insert_result = $insert_result\n";
        
        
    }    
    


    public static  function Stage99($name_only = false) 
    {
        
        $name = 'Pregenerate all species / models / scenarios / times using - Maxent';
        if ($name_only) return __METHOD__."::".$name;
        
        self::header($name);

        $db = new PGDB();
        
        $scenarios = implode(" ",matrix::Column($db->Unique('scenarios', 'dataname'),'dataname'));
        $models    = implode(" ",matrix::Column($db->Unique('models',    'dataname'),'dataname'));
        $times     = implode(" ",matrix::Column($db->Unique('times',     'dataname'),'dataname'));

        $species_id = 2;
        
        
        
    }
    
    private static function preGenerateSpecies($species_id,$scenarios,$models,$times) 
    {
        
        
        // check to see that this combination exists ?
        
        
        // look at file system to see if file exists. 
        // if we have the files then 
        
        
        
        // if file exsist then just update database if required 
        
        
        // import into database ?
        // maxent data
        

        
        
    }
    
    
    
    
    
}

?>
