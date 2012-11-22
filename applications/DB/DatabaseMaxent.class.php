<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DatabaseMaxent
 *
 * Work with Maxent data to and from database
 *
 *
 */
class DatabaseMaxent extends Object
{

    public static $DisplayThresholdFieldName = "Equate entropy of thresholded and original distributions logistic threshold";

    /**
     *
     * @param type $species_id
     * @return \ErrorMessage
     */
    public static function InsertAllMaxentResults($species_id,$file_pattern = "*")
    {

        if (is_null($species_id))  return new ErrorMessage(__METHOD__,__LINE__,"species_id passed as NULL\n");

        $species_id = trim($species_id);
        if ($species_id == "")  new ErrorMessage(__METHOD__,__LINE__,"species_id passed as EMPTY STRING\n");

        $folder = self::MaxentResultsOutputFolder($species_id);



        if (!is_dir($folder))
            return new ErrorMessage(__METHOD__,__LINE__,"MaxentResultsOutputFolder [$folder] does not exist  species_id = $species_id\n");


        ErrorMessage::Marker("folder = $folder");

        $result = self::InsertMainMaxentResults($species_id);
        if ($result instanceof ErrorMessage) return ErrorMessage::Stacked (__METHOD__,__LINE__,"Failed to Insert Main Maxent Results species_id = $species_id\n", true,$result);



        $result = self::InsertAllMaxentResultsForProjectedClimates($species_id,$file_pattern);
        if ($result instanceof ErrorMessage) return ErrorMessage::Stacked (__METHOD__,__LINE__,"Failed to Insert All Maxent Results For Projected Climates  species_id = $species_id\n", true,$result);

        exit();


        $species_file_ids = self::ModelledSpeciesFiles($species_id);
        if ($result instanceof ErrorMessage) return ErrorMessage::Stacked (__METHOD__,__LINE__,"Failed to get list of FilesID's for species_id = $species_id\n", true,$species_file_ids);


        ErrorMessage::Marker($species_file_ids);

        return $species_file_ids;
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

        $q = "select  m.file_unique_id as file_unique_id
                     ,m.species_id,m.scientific_name
                     ,m.common_name
                     ,m.filetype
                     ,f.description
                from  modelled_species_files m
                     ,files f
               where m.file_unique_id = f.file_unique_id
                 and m.species_id = {$species_id}
              ";


        $result = DBO::Query($q, 'file_unique_id' );
        if ($result instanceof ErrorMessage)
            return ErrorMessage::Stacked (__METHOD__,__LINE__,"Failed to Insert Main Maxent Results species_id = $species_id\n", true, $result);


        return $result;

    }



    /**
     *
     * @param type $species_id
     * @return \ErrorMessage|boolean
     */
    public static function InsertMainMaxentResults($species_id)
    {

        if (is_null($species_id))
            return new ErrorMessage(__METHOD__,__LINE__,"InsertMainMaxentResults species_id is NULL \n");

        $species_id = trim($species_id);
        if ($species_id == "") return new ErrorMessage(__METHOD__,__LINE__,"InsertMainMaxentResults species_id is EMPTY STRING \n");


        ErrorMessage::Marker(" InsertAllMaxentResults for speciesID = $species_id\n");


        $folder = self::MaxentResultsOutputFolder($species_id);
        if (!is_dir($folder)) return new ErrorMessage(__METHOD__,__LINE__,"Species data folder does not exists {[$folder]}\n");


        $fn = array();

        $fn['maxent.log'       ] = $folder.'maxent.log'; // will be checked first - if this does not exists then  - we need to run this first
        $fn['lambdas'          ] = $folder.$species_id.'.lambdas';
        $fn['omission'         ] = $folder.$species_id.'_omission.csv';
        $fn['sampleAverages'   ] = $folder.$species_id.'_sampleAverages.csv';
        $fn['samplePredictions'] = $folder.$species_id.'_samplePredictions.csv';

        foreach ($fn as $filetype => $filename)
        {

            ErrorMessage::Marker(" $filetype => $filename");


            if (!file_exists($filename))
                return new ErrorMessage(__METHOD__,__LINE__,"Maxent Main file does not exist [$filename] species_id = $species_id \n");

            // check to see if we have this already
            $count = DBO::Count('modelled_species_files', "species_id = {$species_id} and filetype = ".util::dbq($filetype,true));
            if ($count instanceof ErrorMessage) return ErrorMessage::Stacked (__METHOD__,__LINE__,"", true, $count);


            ErrorMessage::Marker(" modelled_species_files count [{$count}]\n");

            if ( $count == 0)
            {
                $insert_result = self::InsertSingleMaxentOutput( $species_id,$filename,$filetype,"Maxent output for projected species suitability",null,null,null,true);
                if ($insert_result instanceof ErrorMessage) return ErrorMessage::Stacked (__METHOD__,__LINE__,"", true, $insert_result);
            }

        }


        $result = self::InsertMaxentResultsCSV($species_id);
        if ($result instanceof ErrorMessage) return ErrorMessage::Stacked (__METHOD__,__LINE__,"Failed to Insert Maxent Results CSV \n", true, $result);


        $result = self::InsertMaxentHTMLasZIP($species_id);
        if ($result instanceof ErrorMessage) return ErrorMessage::Stacked (__METHOD__,__LINE__,"Failed to Insert Maxent HTML as ZIP\n", true, $result);


        return true;


    }




    /**
     * this is to store a DB version of the "maxentResults.csv"
     *
     * @param type $speciesID  - Scientific Name here as then across systems and database rebuilds it wont change
     * @return array  [index] => (longitude,latitude)
     */
    public static  function InsertMaxentResultsCSV($species_id)
    {

        $current_result = self::GetMaxentResultsCSV($species_id);

        if (count($current_result) >= 120) return 1; // we already have the InsertMaxentResultsCSV for this species


        $filename = self::MaxentResultsFilename($species_id);
        if (!file_exists($filename))
            return new ErrorMessage(__METHOD__,__LINE__,"Maxent Results File does not exist [$filename] \n");



        if (file::lineCount($filename) < 2)
            return new ErrorMessage(__METHOD__,__LINE__,"Maxent Results File is not Long Enough line count =  ".file::lineCount($filename)." species_id = $species_id\n");



        $m = matrix::Load($filename); // get the maxent results file in


        $maxent_fields = array_flip(matrix::Column(DBO::Query("select id,name from maxent_fields",'id'), 'name'));

        $fr = util::first_element($m);
        if (count($maxent_fields) != count($fr))
            return new ErrorMessage(__METHOD__,__LINE__,"Matrix field count = ".count($maxent_fields)."  count(fr) =  ".count($fr)."\n species_id = $species_id\n");



        // create a big insert of this result
        $subs = array();
        foreach ($fr as $maxent_column_name => $maxent_value)
            $subs[] = "({$species_id},{$maxent_fields[$maxent_column_name]},{$maxent_value})";


        $insert  = "insert into maxent_values (species_id,maxent_fields_id,num) values ".implode(",",$subs);
        $insert_result = DBO::Insert($insert);
        if ($insert_result instanceof ErrorMessage)
            return ErrorMessage::Stacked (__METHOD__,__LINE__,"Insert failed for Maxent Results CSV \n insert =  {$insert}  \nspecies_id = $species_id\n", true, $insert_result);


        return $insert_result;

    }


    private static function MaxentResultsFilename($species_id)
    {
        $result =   configuration::Maxent_Species_Data_folder().
                    $species_id.
                    configuration::osPathDelimiter().
                    configuration::Maxent_Species_Data_Output_Subfolder().
                    configuration::osPathDelimiter().
                    "maxentResults.csv";

        return $result;

    }


    public static  function ListMainMaxentResults($species_id)
    {

        $sql = "select *
                  from modelled_species_files
                 where species_id = {$species_id}
                   and (   filetype = 'maxent.log'
                        or filetype = 'lambdas'
                        or filetype = 'omission'
                        or filetype = 'sampleAverages'
                        or filetype = 'samplePredictions'
                        or filetype = 'ZIPPED_HTML'
                       )
                 ";


        $result = DBO::Query($sql, 'filetype');
        if ($result instanceof ErrorMessage)
            return ErrorMessage::Stacked (__METHOD__,__LINE__," Could not get List Main Maxent Results  species_id = $species_id\n", true, $result);


        return $result;

    }

    public static  function CountMainMaxentResults($species_id)
    {

        $result = self::ListMainMaxentResults($species_id);
        if ($result instanceof ErrorMessage)  return ErrorMessage::Stacked (__METHOD__,__LINE__,"", true, $result);

        return count($result);

    }



    /**
     *
     * If we have run processes and we don't have the results from the file system in databaase - so import them
     *
     */
    private static  function InsertAllMaxentResultsForProjectedClimates($species_id,$file_pattern)
    {

        $species_folder = self::MaxentResultsOutputFolder($species_id);

        // get ascii grids
        // $files = file::folder_with_extension($species_folder, 'asc' ,configuration::osPathDelimiter());

        $files = file::LS($species_folder.$file_pattern, null, true);
        $files = file::arrayFilterOut($files, "aux");
        $files = file::arrayFilterOut($files, "xml");
        $files = file::arrayFilter($files,'asc' );


        ErrorMessage::Marker("ProjectedClimates for {$species_id}");

        $result = array();

        // for the "completeness of a model run we are only looking at the ASC grids" - all othert files are auxillary
        foreach ($files as $basename => $filename)
        {

            ErrorMessage::Marker("ASCII Grid {$species_id} file .. [{$filename}]");


            if (file_exists($filename))
            {
                $file_id = self::InsertSingleMaxentProjectedFile($species_id,$filename,'ASCII_GRID', 'Spatial data of projected species suitability:'.basename($filename), true);
                if ($file_id instanceof ErrorMessage)
                    return ErrorMessage::Stacked (__METHOD__,__LINE__,"Failed to Insert Single Maxent Projected ASCII Grid File {$filename}  \nspecies_id = $species_id\n", true, $file_id);

                ErrorMessage::Marker("Create quick look {$species_id} file .. [{$filename}]");
                $qlfn = SpeciesMaxentQuickLook::CreateImage($species_id, $filename); // build quick look from asc
                if ($qlfn instanceof ErrorMessage)
                    return ErrorMessage::Stacked (__METHOD__,__LINE__,"Failed to Create Quick Look from ASCII Grid File {$filename}  \nspecies_id = $species_id\n", true, $qlfn);


                ErrorMessage::Marker("Load quick look {$species_id} file .. [{$filename}]");
                $quick_look_file_id = self::InsertSingleMaxentProjectedFile($species_id,$qlfn,'QUICK_LOOK', 'Quick look image of projected species suitability:'.basename($filename),true);
                if ($quick_look_file_id instanceof ErrorMessage)
                    return ErrorMessage::Stacked (__METHOD__,__LINE__,"Failed to Insert Single Maxent Projected Quick Look File {$qlfn}  \nspecies_id = $species_id\n", true, $quick_look_file_id);

            }



        }


        return true;

    }


    /**
     * Used to store the Maxent results html and plots that come from Maxent
     *
     * @param type $species_id
     */
    private static  function InsertMaxentHTMLasZIP($species_id)
    {

        $filetype = "ZIPPED_HTML";

        $html_count = DBO::Count('modelled_species_files', "species_id = {$species_id} and filetype = ".util::dbq($filetype,true));

        if ($html_count  >= 1) return 1;  // we already have it


        $htmlfilename = configuration::Maxent_Species_Data_folder().
                        $species_id.
                        configuration::osPathDelimiter().
                        configuration::Maxent_Species_Data_Output_Subfolder().
                        configuration::osPathDelimiter().
                        $species_id.".html";


        if (!file_exists($htmlfilename))
            return new ErrorMessage(__METHOD__,__LINE__,"Failed to Insert Single Maxent Projected HTML File  can't find {$htmlfilename}  \nspecies_id = $species_id\n");


        $plots_folder = self::MaxentResultsOutputFolder($species_id)."plots";
        if (!is_dir($plots_folder))
            return new ErrorMessage(__METHOD__,__LINE__,"Failed to Insert Single Maxent Projected HTML File plots folder doesn ot exist  {$plots_folder}  \nspecies_id = $species_id\n");


        $zipfilename = file::random_filename().".zip";  // store these into a single zip and then add zip to database.

        $cmd = "cd ".self::MaxentResultsOutputFolder($species_id)."; ".
               "zip '{$zipfilename}' '{$htmlfilename}'; ".
               "zip '{$zipfilename}' '{$plots_folder}/*'";

        exec($cmd);

        if (!file_exists($zipfilename))
            return new ErrorMessage(__METHOD__,__LINE__,"Failed to Insert Single Maxent Projected HTML zipfile  does not exist  {$zipfilename}  \nspecies_id = $species_id\n");


        $file_unique_id = self::InsertSingleMaxentOutput ($species_id,$zipfilename,$filetype,"HTML results zipped");
        if ($file_unique_id instanceof ErrorMessage)
            return ErrorMessage::Stacked (__METHOD__,__LINE__,"Failed to Insert Single Maxent Projected HTML zipfile  \nspecies_id = $species_id\n zipfilename = $zipfilename \n filetype = $filetype", true, $file_unique_id);


        file::Delete($zipfilename);

        return $file_unique_id;


    }


    private static function MaxentResultsOutputFolder($species_id)
    {

        $result =   configuration::Maxent_Species_Data_folder().
                    $species_id.
                    configuration::osPathDelimiter().
                    configuration::Maxent_Species_Data_Output_Subfolder().
                    configuration::osPathDelimiter();

        return $result;

    }




    /**
     *
     *  - Take one of the projected outputs from maxent and look at the filename and find scenario model time
     *  - import this into the database and return the fuile id
     *  - update Results
     *
     * @param type $speciesID
     * @param type $filename   Is a filename with a format of  scenario_model_time.ext
     * @param type $filetype
     * @param type $desc
     * @return string|\ErrorMessage  unique file id of this file in DB
     */
    public static  function InsertSingleMaxentProjectedFile($speciesID,$filename,$filetype,$desc,$compressed = true)
    {

        $basename = util::toLastChar(basename($filename),".");

        list($scenario, $model, $time) = explode("_",$basename);

        $file_results = SpeciesData::GetModelledData($speciesID,$scenario, $model, $time,$filetype, $desc);
        if ($file_results instanceof ErrorMessage)  return ErrorMessage::Stacked (__METHOD__,__LINE__,"", true, $file_results);

        if (!is_null($file_results))  return $file_results;  // we already have this in DB


        $file_id = self::InsertSingleMaxentOutput(   $speciesID
                                                    ,$filename
                                                    ,$filetype
                                                    ,$desc
                                                    ,$scenario
                                                    ,$model
                                                    ,$time
                                                    ,$compressed
                                                    );

        if ($file_id instanceof ErrorMessage)
            return ErrorMessage::Stacked (__METHOD__,__LINE__,"Failed to Insert Single Maxent Projected HTML zipfile  \nspecies_id = $speciesID \n filename =  $filename \n filetype = $filetype \n desc = $desc \n scenario  = $scenario \n model = $model \n time = $time\n", true, $file_id);


        return $file_id;

    }


    public static  function InsertSingleMaxentModelledOutput($speciesID,$filetype,$desc,$scenario, $model, $time)
    {

        $result =
            InsertSingleMaxentOutput($speciesID
                                    ,self::species_output_projection_filename($speciesID, $scenario, $model, $time)
                                    ,$desc
                                    ,$filetype
                                    ,$scenario
                                    ,$model
                                    ,$time
                                    ) ;

        if ($result instanceof ErrorMessage)  return ErrorMessage::Stacked (__METHOD__,__LINE__,"", true, $result);

        return $result;

    }

    private static  function species_output_projection_filename($speciesID, $scenario, $model, $time)
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
    public static function InsertSingleMaxentOutput($species_id,$filename,$filetype = null,$desc = null,$scenario = null, $model = null, $time = null,$compressed = false)
    {

        if (!file_exists($filename))
            return new ErrorMessage(__METHOD__,__LINE__,"Failed to Insert Single Maxent  \n species_id = $species_id \n filename =  $filename \n filetype = $filetype \n desc = $desc \n scenario  = $scenario \n model = $model \n time = $time\n");


        $file_unique_id = DatabaseFile::InsertFile($filename, $desc,$filetype,$compressed);
        if ($file_unique_id instanceof ErrorMessage)  return ErrorMessage::Stacked (__METHOD__,__LINE__,"", true, $file_unique_id);


        $commonName = SpeciesData::SpeciesCommonNameSimple($species_id);
        if ($commonName instanceof ErrorMessage)  return ErrorMessage::Stacked (__METHOD__,__LINE__,"", true, $commonName);

        $speciesName = SpeciesData::SpeciesName($species_id);
        if ($speciesName instanceof ErrorMessage)  return ErrorMessage::Stacked (__METHOD__,__LINE__,"", true, $speciesName);


        $q  = "insert into modelled_species_files
                ( species_id
                 ,scientific_name
                 ,common_name
                 ,filetype
                 ,file_unique_id
                ) values (
                  {$species_id}
                 ,".util::dbq($speciesName,true)."
                 ,".util::dbq($commonName,true)."
                 ,".util::dbq($filetype,true)."
                 ,".util::dbq($file_unique_id,true)."
                )";


        $result = DBO::Insert($q);
        if ($result instanceof ErrorMessage)  return ErrorMessage::Stacked (__METHOD__,__LINE__,"", true, $result);

        if (!is_numeric($result))  return new ErrorMessage(__METHOD__,__LINE__,"Failed to Insertinto modelled_species_files \n q= {$q} \nspecies_id = $species_id \n filename =  $filename \n filetype = $filetype \n desc = $desc \n");


        // give model output reference data $scenario , $model , $time
        if (!is_null($scenario) && !is_null($model) && !is_null($time) )
        {

            $scenario_id = DatabaseClimate::getScenarioID($scenario);
            if ($scenario_id instanceof ErrorMessage)  return new ErrorMessage(__METHOD__,__LINE__,"Failed to Id's for scenario = [{$scenario_id}]");

            $model_id  = DatabaseClimate::getModelID($model);
            if ($model_id instanceof ErrorMessage)  return new ErrorMessage(__METHOD__,__LINE__,"Failed to Id's for model = [{$model}]");

            $time_id = DatabaseClimate::getTimeID($time);
            if ($model_id instanceof ErrorMessage)  return new ErrorMessage(__METHOD__,__LINE__,"Failed to Id's for time = [{$time}]");


            $q = "insert into modelled_climates
                  (  species_id
                    ,scientific_name
                    ,common_name
                    ,models_id
                    ,scenarios_id
                    ,times_id
                    ,filetype
                    ,file_unique_id
                  ) values (
                    {$species_id}
                   ,".util::dbq($speciesName,true)."
                   ,".util::dbq($commonName,true)."
                   ,{$model_id}
                   ,{$scenario_id}
                   ,{$time_id}
                   ,".util::dbq($filetype,true)."
                   ,".util::dbq($file_unique_id,true)."
                   ) ;
                 ";


           $modelled_climates_result = DBO::Insert($q);

            if (is_null($modelled_climates_result))
                return new ErrorMessage(__METHOD__,__LINE__,"Failed to insert into modelled_climates \n $ = $q \n result = $modelled_climates_result \n species_id = $species_id \n");


        }


        return $file_unique_id;

    }


    /**
     * What projected file related to species do you want to remove
     *
     * @param type $species_id
     * @param type $scenario
     * @param type $model
     * @param type $time
     * @param type $filetype  - leave null to remove all of this grouping
     * @return boolean
     * @throws Exception
     */
    public static function RemoveSingleMaxentOutput($species_id,$scenario, $model, $time, $filetype = null)
    {

        $filetype_and = (is_null($filetype)) ? "" : "and mc.filetype= ".util::dbq($filetype,true);

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
                      ,mc.filetype
                      ,mc.file_unique_id as file_id
                from   modelled_climates mc
                      ,models m
                      ,scenarios s
                      ,times t
                where mc.species_id = {$species_id}
                  and mc.models_id     = m.id
                  and mc.scenarios_id  = s.id
                  and mc.times_id      = t.id
                  and m.dataname = ".util::dbq($model,true)."
                  and s.dataname = ".util::dbq($scenario,true)."
                  and t.dataname = ".util::dbq($time,true)."
                  {$filetype_and}
                 limit 1
                ;";


        $row = util::first_element(DBO::Query($sql));
        if (is_null($row))  return new ErrorMessage(__METHOD__,__LINE__,"No Such files can be found with \n sql = $sql\n");


        // use these id's to remove reference row and file.
        $id = $row['id'];
        $file_id = $row['file_id'];

        $del_modelled_climates = DBO::Delete("modelled_climates", "id = {$id}");
        if (is_null($del_modelled_climates ))  return new ErrorMessage(__METHOD__,__LINE__,"failed to delete from modelled_climates id = {$id}\n");


        $count_modelled_climates = DBO::Count("modelled_climates", "id = {$id}");

        if (is_null($count_modelled_climates) || $count_modelled_climates <= 0)
            return new ErrorMessage(__METHOD__,__LINE__,"Can't RemoveSingleMaxentOutput id = {$id} \n Result =  $del_modelled_climates\n");


        $del_modelled_climates_file = DBO::RemoveFile($file_id);
        if (is_null($del_modelled_climates_file))
            return new ErrorMessage(__METHOD__,__LINE__,"Can't RemoveSingleMaxentOutput del_modelled_climates_file   file_id = {$file_id}\n");


        return true;

    }

    public static  function GetMaxentResultsCSV($species_id)
    {

        $sql = "select v.species_id,v.maxent_fields_id ,f.name as maxent_name,v.num  from maxent_values v, maxent_fields f  where v.maxent_fields_id = f.id and species_id = {$species_id}";
        $result = DBO::Query( $sql,  'maxent_name' );

        if (is_null($result)) return new ErrorMessage(__METHOD__,__LINE__,"Failed to get Get Maxent Results CSV from using SQL = {$sql} \n");

        return $result;

    }

    /**
     * Single Maxent Result CSV Value
     *
     * @param type $species_id
     * @param type $MaxentFieldName
     * @return null
     */
    public static  function GetMaxentResult($species_id,$MaxentFieldName)
    {

        if (is_null($species_id)) return new ErrorMessage(__METHOD__,__LINE__,"species_id passed as NULL");

        if (is_null($MaxentFieldName)) return new ErrorMessage(__METHOD__,__LINE__,"MaxentFieldName passed as NULL");


        $q = "select v.species_id,v.maxent_fields_id ,f.name as maxent_name,v.num  from maxent_values v, maxent_fields f  where v.maxent_fields_id = f.id and species_id = {$species_id} and f.name = ".util::dbq($MaxentFieldName,true);
        $result = DBO::Query($q, 'maxent_name' );


        if ($result instanceof ErrorMessage)
            return ErrorMessage::Stacked(__METHOD__,__LINE__,"Failed to get Get Maxent Results CSV Valiue  from using SQL = {$q} \n",true,$result);

        if (count($result) == 0)
            return new ErrorMessage(__METHOD__,__LINE__,"Failed to get Get Maxent Results CSV Valiue  from using Row Count = 0  SQL = {$q} \n");


        $first = util::first_element($result);
        $field_value = array_util::Value($first, 'num');

        return $field_value;

    }



    public static function GetMaxentThresholdForSpeciesFromFile($species_id)
    {

        $maxentResultsFilename = SpeciesData::species_data_folder($species_id)."../maxentResults.csv";
        if (!file_exists($maxentResultsFilename))
            return new ErrorMessage(__METHOD__,__LINE__,"Tried to Maxent Threshold from file but copuld not find it {$maxentResultsFilename}",true);



        $maxentResult = matrix::Load($maxentResultsFilename);


        $first = util::first_element($maxentResult);

        $name = "Equate entropy of thresholded and original distributions logistic threshold";

        $threshold_from_file = array_util::Value($first, $name);

        return $threshold_from_file;

    }


    public static  function GetMaxentThresholdForSpecies($species_id)
    {

        $result = self::GetMaxentThreshold($species_id);
        if ($result instanceof ErrorMessage)
            return ErrorMessage::Stacked(__METHOD__,__LINE__,'',true,$result);


        if (!is_null($result))
            return $result;

        $result = self::GetMaxentThresholdForSpeciesFromFile($species_id);
        if (!is_null($result)) return $result;


        return new ErrorMessage(__METHOD__,__LINE__,"Failed to get Maxent Threshold ",true);
    }


    public static function GetMaxentThreshold($species_id = null)
    {
        if (is_null($species_id)) return null;

        $MaxentFieldName = util::dbq(self::$DisplayThresholdFieldName,true);

        $where = "";
        if (!is_null($species_id))
            $where = "and species_id = {$species_id}";



        // threshold for species
        $q = "select
                v.species_id
                ,v.maxent_fields_id
                ,f.name
                ,v.num as threshold
                from maxent_values v
                    , maxent_fields f
                where v.maxent_fields_id = f.id
                and f.name = {$MaxentFieldName}
                {$where}
                ";

        $result = DBO::Query($q, 'species_id' );


        if ($result instanceof ErrorMessage)
            return ErrorMessage::Stacked(__METHOD__,__LINE__,"Failed to get Get Maxent Results CSV Valiue  from using SQL = {$q} \n",true,$result);

        if (count($result) == 0)
            return null;


        if (!is_null($species_id))
        {

            $first = util::first_element($result);

            return array_util::Value($first, 'threshold');
        }


        return $result;

    }







    /**
     * Remove Maxent specxies database from database
     *
     * @param type $species_id
     * @throws Exception
     */
    public static  function RemoveAllMaxentResults($species_id,$really_remove = false)
    {

        if ($really_remove === false) return;

        if (is_null($species_id) ||  $species_id == "" )  return new ErrorMessage(__METHOD__,__LINE__,"species_id passed as NULL\n");


        $remove_result = self::RemoveMaxentValues($species_id);
        if (is_null($remove_result))  return new ErrorMessage(__METHOD__,__LINE__,"Failed to remove result  species_id = {$species_id}\n");


        $remove_result = self::RemoveModelledSpeciesFiles($species_id);
        if (is_null($remove_result))  return new ErrorMessage(__METHOD__,__LINE__,"Failed to remove Remove Modelled Species Files  species_id = {$species_id}\n");

        return true;


    }


    public static  function RemoveMaxentValues($species_id)
    {
        $result  = DBO::Delete('maxent_values', "species_id = {$species_id}");

        if (is_null($result))  return new ErrorMessage(__METHOD__,__LINE__,"Failed to maxent_values for species_id = {$species_id}\n");

        return $result;
    }


    public static  function RemoveModelledSpeciesFiles($species_id)
    {


        $file_ids = self::ModelledSpeciesFiles($species_id);
        if (is_null($file_ids))  return new ErrorMessage(__METHOD__,__LINE__,"Failed to Get list of file id's for  species_id = {$species_id}\n");


        $remove_results = array();
        foreach (array_keys($file_ids) as $file_id)
        {

            $remove_result =  self::RemoveSingleModelledSpeciesFile($species_id,$file_id);
            if (is_null($remove_result))   return new ErrorMessage(__METHOD__,__LINE__,"Failed to Remove file for file_id = $file_id   species_id = {$species_id}\n");

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
    public static  function RemoveSingleModelledSpeciesFile($species_id,$file_id)
    {

        $result = DatabaseFile::RemoveFile($file_id);  // removes file entries for database NOT  from the filesystem
        if ($result instanceof ErrorMessage)  return $result;


        $result = DBO::Delete('modelled_species_files', "species_id = '{$species_id}'  and file_unique_id = ".util::dbq($file_id,true));
        if ($result instanceof ErrorMessage)  return $result;


        return true;

    }



    public static function RemoveAllResultsforSpecies($species_id)
    {

        if (is_null($species_id))  return new Exception("RemoveAllResultsforSpecies species_id is NULL \n");

        $species_id = trim($species_id);
        if ($species_id == "") return new Exception("RemoveAllResultsforSpecies species_id is EMPTY STRING \n");

        $result = self::RemoveAllMaxentResults($species_id,true);


        return $result;


    }




}

?>
