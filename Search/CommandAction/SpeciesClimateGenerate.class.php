<?php
/**
 *
 * RUn as HPC server job to pregenerate species climate predictions as a mass batch job
 *  
 */
class SpeciesClimateGenerate {
    
    
    
    private $cmdline = null;
    
    /**
     * Run one or more stages of import 
     * 0 = test
     * 
     * 
     * @param int $stage 
     */
    public static function Execute($stage = "names" ) 
    {
        
        $mcg = new SpeciesClimateGenerate();
        
        $mcg->cmdline = $stage;
        
        if (is_null($stage)) $stage = "names";
        
        if ($stage == "names") 
        {
            
            $mcg->header("Stage Names ");
            
            $method_names = get_class_methods('SpeciesClimateGenerate');

            foreach ($method_names  as $method_name) 
            {
                
                if (util::contains($method_name, "Stage"))
                {
                    echo $mcg->$method_name(true)."\n";
                }
                
            }
            
            return;
        }
        
            
        
        
        if (util::contains($stage, ","))
        {
            foreach (explode(",",$stage)as $stage_num) {
                $method = "Stage{$stage_num}";
                $stage_result = $mcg->$method();
                
                if (!$stage_result) exit(1);

            }
        }
        else
        {
        
            $method = "Stage{$stage}";
            $mcg->$method();
            
        }
        
        
    }


    public function Stage001($name_only = false)
    {

        $name = 'create command action table ';

        if ($name_only) return __METHOD__."::".$name;
        
        $this->header($name);
        
    
        // might not be a good idea to drop table - really need  to copy as we will have update 
        // the models_id in other tables from old to new
        
        
$table_sql = <<<SQL
DROP TABLE IF EXISTS command_action;
CREATE TABLE command_action 
(
    id SERIAL NOT NULL PRIMARY KEY,
    objectID VARCHAR(50) NOT NULL,  -- objectID 
    data text,                      -- php serialised object
    execution_flag varchar(50),     -- execution state
    status varchar(200),            -- current status
    queueid varchar(50),            -- to identify where this job cam from, allows multiple environments to use same queue
    update_datetime TIMESTAMP NULL  -- the last time data was updated
);
GRANT ALL PRIVILEGES ON command_action TO ap02;
GRANT USAGE, SELECT ON SEQUENCE command_action_id_seq TO ap02;
SQL;


        $db = new PGDB();
        
        $table_result = $db->CreateAndGrant($table_sql);
        
        unset($db);
        
        return true;
        
    }


    

    public function Stage01($name_only = false) 
    {
        
        $name = 'Populate Maxent Field Names';

        if ($name_only) return __METHOD__."::".$name;
        
        $this->header($name);
        
        //echo "Count = ".count($names)."\n";
        //echo "Count Unique= ".count(array_unique($names))."\n";

        $db = new PGDB();
        
        $sql = "DROP TABLE IF EXISTS maxent_fields;
                CREATE TABLE maxent_fields 
                (
                    id SERIAL NOT NULL PRIMARY KEY
                    ,name              varchar(256)   -- eg. maxentResults.csv
                    ,update_datetime   timestamp without time zone 
                );
                GRANT ALL PRIVILEGES ON maxent_fields TO ap02;
                GRANT USAGE, SELECT ON SEQUENCE maxent_fields_id_seq TO ap02;
               ";
        
        
        //echo "$sql\n";

        $table_result = $db->CreateAndGrant($sql);

        if (is_null($table_result)) throw new Exception("FAILED to create table maxent_fields - null result from query ");
        
        if (!$db->has_table('maxent_fields')) throw new Exception("FAILED to create table maxent_fields - Can't find table with describe ");
        
        
        $inserted_count = 0;
        $M = matrix::Load('/home/jc166922/test/maxentResults.csv');
        
        $names = matrix::ColumnNames($M);
        
        foreach ($names as $name) 
        {
            $row_sql = "insert into maxent_fields (name) values ('{$name}')";
            
            //echo "$row_sql  ";
            
            $insert_result = $db->insert($row_sql);
            
            if (is_null($insert_result)) throw new Exception("FAILED insert  Maxent field name {$name}");
            
            if (!is_numeric($insert_result))  throw new Exception("FAILED insert Maxent field name {$name}  Insert Result [{$insert_result}] is not a number");
            
            $inserted_count++;
            
            //echo " UR = $update_result\n";
            
        } 
        
        //echo "inserted_count = $inserted_count\n";
        
        if ($inserted_count != count($names))
        {
            throw new Exception("### ERROR:: Failed to insert all Names  inserted_count [{$inserted_count}] != CountNames [".count($names)."] \n");

            return false;
        }
        
        unset($db);
        
        return true;
        
    }
    
    
    
    
    
    public   function Stage02($name_only = false)
    {

        $name = 'Model Descriptions';

        if ($name_only) return __METHOD__."::".$name;
        
        $this->header($name);
        
    
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
        
        $table_result = $db->CreateAndGrant($table_sql);
        
        if (is_null($table_result)) throw new Exception("FAILED to create table models - null result from query ");
        if (!$db->has_table('models')) throw new Exception("FAILED to create table models - Can't find table with describe ");
        
        
        
        $descs = FinderFactory::Result("ClimateModelAllValues");
        $descs instanceof Descriptions;
        
        $format = "insert into models (dataname,description,moreinfo,uri) values ({DataName},{Description},{MoreInformation},{URI});";
         
        $inserted_count = 0;
        foreach ($descs->Descriptions() as $desc) {
            
            $desc instanceof Description;
            $values_sql = $desc->asFormattedString($format,true);
            
            //echo "\n values_sql = $values_sql  "; 
            
            $values_result = $db->insert($values_sql);
            
            if (is_null($values_result)) throw new Exception("\nFAILED insert Model Descriptions using sql = {$values_sql}\nresult = $values_result\n\n");
            
            if (!is_numeric($values_result))  throw new Exception("\nFAILED insert Model Descriptions using sql = {$values_sql} [{$values_result}] is not a number\n");
            
            
            //echo "  UR = {$values_result}"; 
            
            $inserted_count++;
        
        }
        
        if ($inserted_count != $descs->count())
        {
            throw new Exception("\n### ERROR:: Failed to insert Model Description properly {$inserted_count} != {$descs->count()}\n");
            return false;
        }
        
        unset($db);
        
        return true;
        
    }


    public   function Stage03($name_only = false)     
    {
        
        $name = 'Scenario Descriptions';

        if ($name_only) return __METHOD__."::".$name;
        
        $this->header($name);
    
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
        
        $table_result = $db->CreateAndGrant($table_sql);
        
        if (is_null($table_result)) throw new Exception("FAILED to create table scenarios - null result from query ");
        if (!$db->has_table('scenarios')) throw new Exception("FAILED to create table scenarios - Can't find table with describe ");
        
        
        //echo "table_result = $table_result\n";
        
        $descs = FinderFactory::Result("EmissionScenarioAllValues");
        $descs instanceof Descriptions;
        
        $format = "insert into scenarios (dataname,description,moreinfo,uri) values ({DataName},{Description},{MoreInformation},{URI});";
         
        $inserted_count = 0;
        foreach ($descs->Descriptions() as $desc) {
            
            $desc instanceof Description;
            $values_sql = $desc->asFormattedString($format,true);
            
            //echo "\n\n$values_sql\n\n"; 
            
            $values_result = $db->insert($values_sql);
            
            if (is_null($values_result)) throw new Exception("\nFAILED insert Scenario Descriptions using sql = {$values_sql}\n result = $values_result\n\n");
            
            if (!is_numeric($values_result))  throw new Exception("\nFAILED insert Scenario Descriptions using sql = {$values_sql} [{$values_result}] is not a number\n");
            
            //echo "  UR = {$values_result}"; 
            
            $inserted_count++;
        
        }
        
        if ($inserted_count != $descs->count())
        {
            throw new Exception("\n### ERROR:: Failed to insert scenarios Description properly {$inserted_count} != {$descs->count()}\n");
            return FALSE;
        }
        
        unset($db);
        
        return true;
        
    }

    
    
    public   function Stage04($name_only = false)
    {
        
        $name = 'Times Descriptions';

        if ($name_only) return __METHOD__."::".$name;
        
        $this->header($name);
    
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
        
        $table_result = $db->CreateAndGrant($table_sql);
        
        if (is_null($table_result)) throw new Exception("FAILED to create table times - null result from query ");
        if (!$db->has_table('times')) throw new Exception("FAILED to create table times - Can't find table with describe ");
        
        
        //echo "table_result = $table_result\n";
        
        $descs = FinderFactory::Result("TimeAllValues");
        $descs instanceof Descriptions;
        
        $format = "insert into times (dataname,description,moreinfo,uri) values ({DataName},{Description},{MoreInformation},{URI});";
         
        $inserted_count = 0;
        foreach ($descs->Descriptions() as $desc) {
            
            $desc instanceof Description;
            $values_sql = $desc->asFormattedString($format,true);
            
            //echo "\n$values_sql  "; 
            
            $values_result = $db->insert($values_sql);
            
            if (is_null($values_result)) throw new Exception("\nFAILED insert Times Descriptions using sql = {$values_sql}\nresult = $values_result\n\n");
            
            if (!is_numeric($values_result))  throw new Exception("\nFAILED insert Times Descriptions using sql = {$values_sql} [{$values_result}] is not a number\n");
            
            //echo "  UR = {$values_result}"; 
            
            $inserted_count++;
        
        }
        
        if ($inserted_count != $descs->count())
        {
            throw new Exception("\n### ERROR:: Failed to insert Times Description properly {$inserted_count} != {$descs->count()}\n");            
            return false;
        }
        
        unset($db);
        
        return true;
        
    }


    public   function Stage05($name_only = false)
    {
        
        $name = 'maxent_values';

        if ($name_only) return __METHOD__."::".$name;
        
        $this->header($name);
        
$table_sql = <<<SQL
DROP TABLE IF EXISTS maxent_values;
CREATE TABLE maxent_values
(
    id SERIAL NOT NULL PRIMARY KEY
    ,species_id  integer   
    ,maxent_fields_id      integer       -- many to one with maxent_fields.id
    ,num                   float         -- a numeric value 
    ,update_datetime   timestamp without time zone 
);
GRANT ALL PRIVILEGES ON maxent_values TO ap02;
GRANT USAGE, SELECT ON SEQUENCE maxent_values_id_seq TO ap02;        
SQL;

        $db = new PGDB();
        
        $table_result = $db->CreateAndGrant($table_sql);

        if (is_null($table_result)) throw new Exception("FAILED to create table maxent_values - null result from query ");
        if (!$db->has_table('maxent_values')) throw new Exception("FAILED to create table maxent_values - Can't find table with describe ");
        
        
        unset($db);
        
        
        return true;
        
    }
    

    public   function Stage06($name_only = false)
    {
        
        $name = 'modelled_climates';

        if ($name_only) return __METHOD__."::".$name;
        
        $this->header($name);
    
        
$table_sql = <<<SQL
   
DROP TABLE IF EXISTS modelled_species_files;
CREATE TABLE modelled_species_files
(
    id SERIAL NOT NULL PRIMARY KEY
    ,species_id        integer        
    ,scientific_name   varchar(256)   -- Will be Unique
    ,common_name       varchar(256)
    ,file_unique_id    varchar(60)
    ,update_datetime   timestamp without time zone 
);
GRANT ALL PRIVILEGES ON modelled_species_files TO ap02;
GRANT USAGE, SELECT ON SEQUENCE modelled_species_files_id_seq TO ap02;        


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
    ,file_unique_id   varchar(60)
    ,update_datetime   timestamp without time zone 
);
GRANT ALL PRIVILEGES ON modelled_climates TO ap02;
GRANT USAGE, SELECT ON SEQUENCE modelled_climates_id_seq TO ap02;        

SQL;

        $db = new PGDB();
        
        $table_result = $db->CreateAndGrant($table_sql);
        
        if (is_null($table_result)) throw new Exception("FAILED to create table modelled_species_files & modelled_climates - null result from query ");

        if (!$db->has_table('modelled_species_files')) throw new Exception("FAILED to create table modelled_species_files - Can't find table with describe ");
        if (!$db->has_table('modelled_climates')) throw new Exception("FAILED to create table modelled_climates - Can't find table with describe ");

        unset($db);

        
        return true;
        
    }
    
    
    public   function Stage07($name_only = false)
    {
        
        $name = 'files_data';

        if ($name_only) return __METHOD__."::".$name;
        
        $this->header($name);

        
$table_sql = <<<SQL
DROP TABLE IF EXISTS files;
CREATE TABLE files 
(
    id SERIAL NOT NULL PRIMARY KEY
    ,file_unique_id   varchar(60)
    ,mimetype         varchar(50)
    ,description      varchar(500)
    ,totalparts       float
    ,total_filesize   float
    ,update_datetime  timestamp without time zone 
);

GRANT ALL PRIVILEGES ON files TO ap02;
GRANT USAGE, SELECT ON SEQUENCE files_id_seq TO ap02;


DROP TABLE IF EXISTS files_data;
CREATE TABLE files_data
(
    id SERIAL NOT NULL PRIMARY KEY
    ,file_unique_id   varchar(60)
    ,partnum          float
    ,totalparts       float
    ,data             text
    ,update_datetime  timestamp without time zone 
);

GRANT ALL PRIVILEGES ON files_data TO ap02;
GRANT USAGE, SELECT ON SEQUENCE files_data_id_seq TO ap02;
SQL;
    
        //echo "Create Table for files_data \n";

        $db = new PGDB();
        
        $table_result = $db->CreateAndGrant($table_sql);
        
        if (is_null($table_result)) throw new Exception("FAILED to create table files &  files_data - null result from query ");

        if (!$db->has_table('files')) throw new Exception("FAILED to create table files - Can't find table with describe ");
        if (!$db->has_table('files_data')) throw new Exception("FAILED to create table files_data - Can't find table with describe ");
        
        unset($db);
        
        return true;
        
    }
    

    private  function header($str = "")
    {
        //echo "\n".str_repeat("=", 70);
        echo "\n=== {$str}\n";
        //echo "\n".str_repeat("=", 70)."\n";
        
    }
    
    
    public   function Stage1($name_only = false) 
    {
        
        $name = 'Bulid tables for climate model data stages(01,02,03,04,05,06,07) ';

        if ($name_only) return __METHOD__."::".$name;
        
        $this->header($name);
        
        $this->Execute('01,02,03,04,05,06,07');
        
        
    }
    

    public   function Stage2($name_only = false) 
    {
        
        $name = 'Test insert of Maxent Data';
        if ($name_only) return __METHOD__."::".$name;

        $species_id = 2;
        
        $db = new PGDB();
        
        $insert_result = $db->InsertMaxentResultsCSV($species_id);
        
        print_r($db->GetMaxentResultsCSV($species_id));
        
        unset($db);
        
        
        echo "insert_result = $insert_result\n";
        
        
    }    
    
    
    public function Stage3($name_only = false) 
    {
        
        $name = 'Test insert of Data from Filesystem to database';
        if ($name_only) return __METHOD__."::".$name;

        $species_id = 3081;
        
        $db = new PGDB();
        
        $db->RemoveAllMaxentResults($species_id,true);        
        $db->InsertAllMaxentResults($species_id);
        
        unset($db);
    }    

    public function Stage4($name_only = false) 
    {

        $db = new PGDB();
        
        $name = 'Test insert of Data - running models first';
        if ($name_only) return __METHOD__."::".$name;

        $species_id = 3081;
        
        $scenarios = matrix::Column($db->Unique('scenarios', 'dataname'),'dataname');
        $models    = matrix::Column($db->Unique('models',    'dataname'),'dataname');
        $times     = matrix::Column($db->Unique('times',     'dataname'),'dataname');
//        
//        echo "scenarios = ".implode(", ", $scenarios)."\n";
//        echo "models    = ".implode(", ", $models)."\n";
//        echo "times     = ".implode(", ", $times)."\n";

        
        $scenario = $scenarios[1];
        $model    = $models[0];
        $time     = $times[0];

        echo "test scenarios = ".$scenario."\n";
        echo "test models    = ".$model."\n";
        echo "test times     = ".$time."\n";
        
        
        $M = new SpeciesMaxent();
        
        $src = array();
        $src['species']  = $species_id;
        $src['scenario'] = $scenario;
        $src['model']    = $model;
        $src['time']     = $time;
        
        $M->initialise($src);

        
        if (!$M->ExecutionFlag() == CommandAction::$EXECUTION_FLAG_COMPLETE)
        {
            echo "====================================\n";
            echo "RUNNING MOdel On GRID\n";
            echo "====================================\n";
            
            $M->Execute();
            
        }
            echo "====================================\n";
            echo "Writing data to DB\n";
            echo "====================================\n";
        
        
        
        unset($db);
    }    
    
    
    
    public function Stage99($name_only = false) 
    {
        
        $name = 'Pregenerate all species / models / scenarios / times using - Maxent';
        if ($name_only) return __METHOD__."::".$name;
        
        $this->header($name);

        $db = new PGDB();
        
        $scenarios = implode(" ",matrix::Column($db->Unique('scenarios', 'dataname'),'dataname'));
        $models    = implode(" ",matrix::Column($db->Unique('models',    'dataname'),'dataname'));
        $times     = implode(" ",matrix::Column($db->Unique('times',     'dataname'),'dataname'));

        $species_id = 2;

        
        
        
        
    }
    
    private  function preGenerateSpecies($species_id,$scenarios,$models,$times) 
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
