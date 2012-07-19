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
    
    
    public static function InsertAllMaxentResults($species_id) 
    {
        
        if (is_null($species_id)) 
        {
            DBO::LogError(__METHOD__."(".__LINE__.")","species_id passed as NULL\n");
            return null;
        }
        
        $species_id = trim($species_id);        
        if ($species_id == "") 
        {
            DBO::LogError(__METHOD__."(".__LINE__.")","species_id passed as EMPTY STRING\n");
            return null;
        }
        
        $folder = self::MaxentResultsOutputFolder($species_id);        
        if (!is_dir($folder)) 
        {
            DBO::LogError(__METHOD__."(".__LINE__.")","MaxentResultsOutputFolder [$folder] does not exist  species_id = $species_id\n");
            return null;
        }
        
        $result = self::InsertMainMaxentResults($species_id);
        if (is_null($result))
        {
            DBO::LogError(__METHOD__."(".__LINE__.")","Failed to Insert Main Maxent Results species_id = $species_id\n");
            return null;
        }

        
        $result = self::InsertAllMaxentResultsForProjectedClimates($species_id);
        if (is_null($result))
        {
            DBO::LogError(__METHOD__."(".__LINE__.")","Failed to Insert All Maxent Results For Projected Climates  species_id = $species_id\n");
            return null;
        }
        
        $species_file_ids = self::ModelledSpeciesFiles($species_id);
        if (is_null($species_file_ids))
        {
            DBO::LogError(__METHOD__."(".__LINE__.")","Failed to get list of FilesID's for species_id = $species_id\n");
            return null;
        }
                
        
        
        
        
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
        
        return DBO::Query($q, 'file_unique_id' );
        
    }
    
    
    
    
    public static function InsertMainMaxentResults($species_id)
    {
     
        $folder = self::MaxentResultsOutputFolder($species_id);
        
        $fn = array();
        
        $fn['lambdas'          ] = $folder.$species_id.'.lambdas';
        $fn['omission'         ] = $folder.$species_id.'_omission.csv';
        $fn['sampleAverages'   ] = $folder.$species_id.'_sampleAverages.csv';
        $fn['samplePredictions'] = $folder.$species_id.'_samplePredictions.csv';
        $fn['maxent.log'       ] = $folder.'maxent.log';
        
        foreach ($fn as $filetype => $filename)  
        {
            
            if (!file_exists($filename))
            {
                DBO::LogError(__METHOD__."(".__LINE__.")","Maxent Main file does not exist [$filename] species_id = $species_id \n");
                return null;
            }
            
            
            // check to see if we have this already
            $count = DBO::Count('modelled_species_files', "species_id = {$species_id} and filetype = ".util::dbq($filetype));
            
            
            if ( $count == 0)
                self::InsertSingleMaxentOutput( $species_id,$filename,$filetype,"Maxent output for projected species suitability");
                
        }
        
        $result = self::InsertMaxentResultsCSV($species_id);
        if (is_null($result))
        {
            DBO::LogError(__METHOD__."(".__LINE__.")","Failed to Insert Maxent Results CSV \n");
            return null;
        }
        
        
        $result = self::InsertMaxentHTMLasZIP($species_id);
        if (is_null($result))
        {
            DBO::LogError(__METHOD__."(".__LINE__.")","Failed to Insert Maxent HTML as ZIP\n");
            return null;
        }
        
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
        {
            DBO::LogError(__METHOD__."(".__LINE__.")","Maxent Results File does not exist [$filename] \n");
            return null;
        }
        
        if (file::lineCount($filename) < 2)
        {
            DBO::LogError(__METHOD__."(".__LINE__.")","Maxent Results File is not Long Enough line count =  ".file::lineCount($filename)." species_id = $species_id\n");
            return null;
        }
        
        $m = matrix::Load($filename); // get the maxent results file in 

        
        $maxent_fields = array_flip(matrix::Column(DBO::Query("select id,name from maxent_fields",'id'), 'name'));
        
        $fr = util::first_element($m);  
        if (count($maxent_fields) != count($fr))
        {    
            DBO::LogError(__METHOD__."(".__LINE__.")","Matrix field count = ".count($maxent_fields)."  count(fr) =  ".count($fr)."\n species_id = $species_id\n");
            return null;
        }
        
        // create a big insert of this result
        $subs = array();
        foreach ($fr as $maxent_column_name => $maxent_value) 
            $subs[] = "({$species_id},{$maxent_fields[$maxent_column_name]},{$maxent_value})";
            
            
        $insert  = "insert into maxent_values (species_id,maxent_fields_id,num) values ".implode(",",$subs);
        $insert_result = DBO::Insert($insert);
        
        if (is_null($insert_result))
        {    
            DBO::LogError(__METHOD__."(".__LINE__.")","Insert failed for Maxent Results CSV \n insert =  {$insert}  \nspecies_id = $species_id\n");
            return null;
        }
        
        
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
    
    
    
    /**
     *
     * If we have run processes and we don't have the results from the file system in databaase - so import them 
     *  
     */
    private static  function InsertAllMaxentResultsForProjectedClimates($species_id)
    {
        
        $species_folder = self::MaxentResultsOutputFolder($species_id);

        // get ascii grids  
        $files = file::folder_with_extension($species_folder, 'asc' ,configuration::osPathDelimiter());
        $files = file::arrayFilterOut($files, "aux");
        $files = file::arrayFilterOut($files, "xml");
        
        
        // for the "completeness of a model run we are only looking at the ASC grids" - all othert files are auxillary 
        foreach ($files as $filename) 
        {
            $file_id = self::InsertSingleMaxentProjectedFile($species_id,$filename,'ASCII_GRID', 'Spatial data of projected species suitability:'.basename($filename));
            if (is_null($file_id))
            {    
                DBO::LogError(__METHOD__."(".__LINE__.")","Failed to Insert Single Maxent Projected ASCII Grid File {$filename}  \nspecies_id = $species_id\n");
                return null;
            }
            
                
            $qlfn = SpeciesMaxentQuickLook::CreateImage($species_id, $filename); // build quick look from asc 
            if (is_null($qlfn))
            {    
                DBO::LogError(__METHOD__."(".__LINE__.")","Failed to Create Quick Look from ASCII Grid File {$filename}  \nspecies_id = $species_id\n");
                return null;
            }

            $quick_look_file_id = self::InsertSingleMaxentProjectedFile($species_id,$qlfn,'QUICK_LOOK', 'Quick look image of projected species suitability:'.basename($filename));            
            if (is_null($quick_look_file_id))
            {    
                DBO::LogError(__METHOD__."(".__LINE__.")","Failed to Insert Single Maxent Projected Quick Look File {$qlfn}  \nspecies_id = $species_id\n");
                return null;
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
        
        $html_count = DBO::Count('modelled_species_files', "species_id = {$species_id} and filetype = ".util::dbq($filetype));
        
        if ($html_count  >= 1) return 1;  // we already have it 
        
        
        $htmlfilename = configuration::Maxent_Species_Data_folder().
                        $species_id.
                        configuration::osPathDelimiter().
                        configuration::Maxent_Species_Data_Output_Subfolder().
                        configuration::osPathDelimiter().
                        $species_id.".html";
        
        
        //echo "htmlfilename = $htmlfilename\n";
        
        if (!file_exists($htmlfilename)) 
        {    
            DBO::LogError(__METHOD__."(".__LINE__.")","Failed to Insert Single Maxent Projected HTML File  can't find {$htmlfilename}  \nspecies_id = $species_id\n");
            return null;
        }

        
        $plots_folder = self::MaxentResultsOutputFolder($species_id)."plots";
        if (!is_dir($plots_folder))
        {    
            DBO::LogError(__METHOD__."(".__LINE__.")","Failed to Insert Single Maxent Projected HTML File plots folder doesn ot exist  {$plots_folder}  \nspecies_id = $species_id\n");
            return null;
        }
        
        
        
        $zipfilename = file::random_filename().".zip";  // store these into a single zip and then add zip to database.
        
        $cmd = "cd ".self::MaxentResultsOutputFolder($species_id)."; ". 
               "zip '{$zipfilename}' '{$htmlfilename}'; ".
               "zip '{$zipfilename}' '{$plots_folder}/*'";
        
        exec($cmd);
        
        if (!file_exists($zipfilename)) 
        {    
            DBO::LogError(__METHOD__."(".__LINE__.")","Failed to Insert Single Maxent Projected HTML zipfile  does not exist  {$zipfilename}  \nspecies_id = $species_id\n");
            return null;
        }

        $file_unique_id = self::InsertSingleMaxentOutput ($species_id,$zipfilename,$filetype,"HTML results zipped");
        
        if (is_null($file_unique_id)) 
        {    
            DBO::LogError(__METHOD__."(".__LINE__.")","Failed to Insert Single Maxent Projected HTML zipfile  \nspecies_id = $species_id\n zipfilename = $zipfilename \n filetype = $filetype");
            return null;
        }
        
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
     * @param type $species_id
     * @param type $desc
     * @param type $filename  Is a filename with a format of  scenario_model_time.ext
     * @return type 
     */
    public static  function InsertSingleMaxentProjectedFile($speciesID,$filename,$filetype,$desc) 
    {
        
        $basename = util::toLastChar(basename($filename),".");
        
        list($scenario, $model, $time) = explode("_",$basename);
        
        $file_results = SpeciesData::GetModelledData($speciesID,$scenario, $model, $time,$filetype, $desc);
        
        if (!is_null($file_results))  return $file_results;  // we already have this in DB
        
        
        $file_id = self::InsertSingleMaxentOutput(   $speciesID
                                                    ,$filename
                                                    ,$filetype
                                                    ,$desc
                                                    ,$scenario 
                                                    ,$model
                                                    ,$time
                                                    );
        
        if (is_null($file_id)) 
        {    
            DBO::LogError(__METHOD__."(".__LINE__.")","Failed to Insert Single Maxent Projected HTML zipfile  \nspecies_id = $speciesID \n filename =  $filename \n filetype = $filetype \n desc = $desc \n scenario  = $scenario \n model = $model \n time = $time\n");
            return null;
        }
        
        
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
    public static function InsertSingleMaxentOutput($species_id,$filename,$filetype = null,$desc = null,$scenario = null, $model = null, $time = null) 
    {
     
        if (!file_exists($filename))
        {    
            DBO::LogError(__METHOD__."(".__LINE__.")","Failed to Insert Single Maxent  \n species_id = $species_id \n filename =  $filename \n filetype = $filetype \n desc = $desc \n scenario  = $scenario \n model = $model \n time = $time\n");
            return null;
        }
            
        
        $file_unique_id = DatabaseFile::InsertFile($filename, $desc,$filetype);
        if (is_null($file_unique_id)) 
        {    
            DBO::LogError(__METHOD__."(".__LINE__.")","Failed to Insert Single Maxent  \n species_id = $species_id \n filename =  $filename \n filetype = $filetype \n desc = $desc \n");
            return null;
        }
                
        
        $info = SpeciesData::SpeciesInfoByID($species_id);
        
        $q  = "insert into modelled_species_files 
                ( species_id
                 ,scientific_name
                 ,common_name
                 ,filetype
                 ,file_unique_id
                ) values (
                  {$species_id}
                 ,".util::dbq($info['scientific_name'])."
                 ,".util::dbq($info['common_name'])."
                 ,".util::dbq($filetype)."
                 ,".util::dbq($file_unique_id)."
                )";
        
        //echo "Insert into Modelled Species Files \n$q\n";

        $result = DBO::Insert($q);
        if (is_null($result) || !is_numeric($result)) 
        {    
            DBO::LogError(__METHOD__."(".__LINE__.")","Failed to Insertinto modelled_species_files \n q= {$q} \nspecies_id = $species_id \n filename =  $filename \n filetype = $filetype \n desc = $desc \n");
            return null;
        }
        
        
        // give model output reference data $scenario , $model , $time 
        if (!is_null($scenario) && !is_null($model) && !is_null($time) )
        {

            $scenario_id = DatabaseClimate::getScenarioID($scenario);
            $model_id    = DatabaseClimate::getModelID($model);
            $time_id     = DatabaseClimate::getTimeID($time);

            if (is_null($scenario_id) || is_null($model_id) || is_null($time_id)) 
            {    
                DBO::LogError(__METHOD__."(".__LINE__.")","Failed to Id's for scenario_id, model_id, time_id \nscenario_id = [{$scenario_id}]\n model_id  = [{$model_id}] \n time_id  = [{$time_id}] \n species_id = $species_id \n");
                return null;
            }
            
            
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
                   ,".util::dbq($info['scientific_name'])."
                   ,".util::dbq($info['common_name'])."
                   ,{$model_id}
                   ,{$scenario_id}
                   ,{$time_id}
                   ,".util::dbq($filetype)."
                   ,".util::dbq($file_unique_id)."
                   ) ;
                 ";
            
                   
           $modelled_climates_result = DBO::Insert($q);

            if (is_null($modelled_climates_result)) 
            {    
                DBO::LogError(__METHOD__."(".__LINE__.")","Failed to insert into modelled_climates \n $ = $q \n result = $modelled_climates_result \n species_id = $species_id \n");
                return null;
            }
           
           
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
        
        $filetype_and = (is_null($filetype)) ? "" : "and mc.filetype= ".util::dbq($filetype);
        
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
                  and m.dataname = ".util::dbq($model)."
                  and s.dataname = ".util::dbq($scenario)."
                  and t.dataname = ".util::dbq($time)."
                  {$filetype_and}
                 limit 1
                ;";
        
        
        $row = util::first_element(DBO::Query($sql));
        if (is_null($row)) 
        {    
            DBO::LogError(__METHOD__."(".__LINE__.")","No Such files can be found with \n sql = $sql\n");
            return null;
        }
        
        
        // use these id's to remove reference row and file.
        $id = $row['id'];
        $file_id = $row['file_id'];
        
        $del_modelled_climates = DBO::Delete("modelled_climates", "id = {$id}"); 
        if (is_null($del_modelled_climates )) 
        {    
            DBO::LogError(__METHOD__."(".__LINE__.")"," failed to delete from modelled_climates id = {$id}\n");
            return null;
        }

        
        $count_modelled_climates = DBO::Count("modelled_climates", "id = {$id}");
        if (is_null($count_modelled_climates) || $count_modelled_climates <= 0)  
        {    
            DBO::LogError(__METHOD__."(".__LINE__.")","Can't RemoveSingleMaxentOutput id = {$id} \n Result =  $del_modelled_climates\n");
            return null;
        }
        
        
        $del_modelled_climates_file = DBO::RemoveFile($file_id);        
        if (is_null($del_modelled_climates_file))  
        {    
            DBO::LogError(__METHOD__."(".__LINE__.")","Can't RemoveSingleMaxentOutput del_modelled_climates_file   file_id = {$file_id}\n");
            return null;
        }
        
        
        return true;
        
    }
    
    public static  function GetMaxentResultsCSV($species_id)
    {

        $sql = "select v.species_id,v.maxent_fields_id ,f.name as maxent_name,v.num  from maxent_values v, maxent_fields f  where v.maxent_fields_id = f.id and species_id = {$species_id}";
        $result = DBO::Query( $sql,  'maxent_name' );
        
        if (is_null($result))  
        {    
            DBO::LogError(__METHOD__."(".__LINE__.")","Failed to get Get Maxent Results CSV from using SQL = {$sql} \n");
            return null;
        }
        
        
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
        
        $q = "select v.species_id,v.maxent_fields_id ,f.name as maxent_name,v.num  from maxent_values v, maxent_fields f  where v.maxent_fields_id = f.id and species_id = {$species_id} and f.name = ".util::dbq($MaxentFieldName);
        $result = DBO::Query($q, 'maxent_name' );
        
        if (is_null($result) || count($result) == 0)  
        {    
            DBO::LogError(__METHOD__."(".__LINE__.")","Failed to get Get Maxent Results CSV Valiue  from using SQL = {$q} \n");
            return null;
        }
        
        $first = util::first_element($result);
        $field_value = array_util::Value($first, 'num');
        
        return $field_value;
        
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
        
        if (is_null($species_id) ||  $species_id == "" ) 
        {    
            DBO::LogError(__METHOD__."(".__LINE__.")","species_id passed as NULL\n");
            return null;
        }

        
        $remove_result = self::RemoveMaxentValues($species_id);
        if (is_null($remove_result)) 
        {    
            DBO::LogError(__METHOD__."(".__LINE__.")","Failed to remove result  species_id = {$species_id}\n");
            return null;
        }

        
        $remove_result = self::RemoveModelledSpeciesFiles($species_id);
        if (is_null($remove_result)) 
        {    
            DBO::LogError(__METHOD__."(".__LINE__.")","Failed to remove Remove Modelled Species Files  species_id = {$species_id}\n");
            return null;
        }
        
        return true;
        
        
    }

    
    public static  function RemoveMaxentValues($species_id) 
    {
        $result  = DBO::Delete('maxent_values', "species_id = {$species_id}");
        
        if (is_null($result)) 
        {    
            DBO::LogError(__METHOD__."(".__LINE__.")","Failed to maxent_values for species_id = {$species_id}\n");
            return null;
        }

        return $result;   
    }
    

    public static  function RemoveModelledSpeciesFiles($species_id)
    {
        
        
        $file_ids = self::ModelledSpeciesFiles($species_id);
        if (is_null($file_ids)) 
        {    
            DBO::LogError(__METHOD__."(".__LINE__.")","Failed to Get list of file id's for  species_id = {$species_id}\n");
            return null;
        }
        
        
        $remove_results = array();
        foreach (array_keys($file_ids) as $file_id) 
        {
            
            $remove_result =  self::RemoveSingleModelledSpeciesFile($species_id,$file_id);
            if (is_null($remove_result)) 
            {    
                DBO::LogError(__METHOD__."(".__LINE__.")","Failed to Remove file for file_id = $file_id   species_id = {$species_id}\n");
                return null;
            }
            
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
        DatabaseFile::RemoveFile($file_id);  // removes file entries for database NOT  from the filesystem
        DBO::Delete('modelled_species_files', "species_id = '{$species_id}'  and file_unique_id = ".util::dbq($file_id));
        
        return true;
        
    }
    
    
    
    
    
    
}

?>
