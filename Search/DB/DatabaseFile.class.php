<?php

/**
 * Description of DatabaseFile
 *
 * Mathods to store and get files fro a database
 * 
 */
class DatabaseFile extends Object
{
    
    private static $FILE_DB_STORAGE_SIZE = 20000; // number of bytes that each part of a file will be 
    
    
    public static function  FilesTable()      {return "files";}
    public static function  FilesDataTable()  {return "files_data";}

    /**
     * 
     * Read File info for a File ID 
     * 
     * Array Keys
     * 
     * file_unique_id    
        mimetype         
        file_description 
        category         
        totalparts       
        total_filesize   
     * 
     */
    public static  function FileInfo($file_unique_id) 
    {
        
        $q = "select * from ".self::FilesTable()." where file_unique_id = ".util::dbq($file_unique_id)." limit 1";
        $result = DBO::Query($q);
        
        if (is_null($result)) 
        {    
            DBO::LogError(__METHOD__."(".__LINE__.")","Failed to File Info for file_unique_id =  $file_unique_id \n sql = {$q} \n");
            return null;
        }
        
        return util::first_element($result);
        
    }
    
    
    /**
     *
     * Check that all parts for a file exist
     * 
     * @param type $file_unique_id 
     */
    public static   function CheckFile($file_unique_id) 
    {
        
        
        
        
    }
    
    
    
    /**
     *
     * @param type $srcFilename Path to file to insert ito DB
     * @param type $description Some string that can be used to lookup the file later
     * @param type $category    Category of the file e.g. Image, html ...
     * @return string unique_file_id - use this id to get file backl from database;
     * @throws Exception 
     */
    public static  function InsertFile($srcFilename,$description,$filetype = null) 
    {
        
        if (!file_exists($srcFilename)) 
        {    
            DBO::LogError(__METHOD__."(".__LINE__.")","Failed to Add file to database file does not exist Filename = [{$srcFilename}] description = $description,filetype =  $filetype  \n");
            return null;
        }
        
        
        $chunck_size = self::$FILE_DB_STORAGE_SIZE;

        $total_filesize = filesize($srcFilename);
        $totalparts = ceil($total_filesize / $chunck_size);
        
        $mimetype = mime_content_type($srcFilename);
        
        $file_unique_id =  uniqid();
        
        $partnum = 0;
        $handle = fopen($srcFilename, "rb");
        
        $total_read = 0;
        while (!feof($handle)) {

            $contents = fread($handle, $chunck_size);
            
            $total_read += strlen($contents);
            
            $data = base64_encode($contents);

            $sql  = "insert into ".self::FilesDataTable()."  (file_unique_id,partnum,totalparts,data) values (".util::dbq($file_unique_id).",{$partnum},{$totalparts},'{$data}')";
            
            $insertResult = DBO::Insert($sql);
            if (is_null($insertResult)) 
            {    
                DBO::LogError(__METHOD__."(".__LINE__.")","FAILED:: to insert ".self::FilesDataTable()." in to DB {$srcFilename} with description {$description} at part {$partnum} using a comand line call\n sql = {$sql}");
                
                // remove bits of file that where inserted 
                $delete_result = DBO::Delete(self::FilesDataTable(), "file_unique_id = ".util::dbq($file_unique_id));
                if (is_null($delete_result)) 
                {
                    DBO::LogError(__METHOD__."(".__LINE__.")","FAILED:: Tried to remove bits from failed file insert and failed as well to insert file in to DB {$srcFilename} with description {$description} at part {$partnum} using a comand line call");                    
                }        
                
                return null;
            }
            
            $partnum++;
        }        


        
        // write the description of the file 
        $sql  = "insert into ".self::FilesTable()." (file_unique_id,mimetype,totalparts,total_filesize,description,filetype)  values ".
                "(".util::dbq($file_unique_id).",".util::dbq($mimetype).",{$totalparts},$total_filesize,".util::dbq($description).",".util::dbq($filetype).")";

                
        $insertResult = DBO::Insert($sql);
        if (is_null($insertResult)) 
        {
            DBO::LogError(__METHOD__."(".__LINE__.")","FAILED:: to insert file (fileinfo) in to DB {$srcFilename} with description {$description} at part {$partnum} using a comand line call"); 
            return null;
        }        
        
        return $file_unique_id;

    }
    
    
    public static   function HasFile($file_unique_id) 
    {
        
        $sql = "select * from ".self::FilesTable()." where file_unique_id = ".util::dbq($file_unique_id)." limit 1";
        
        $query_result = DBO::Query($sql);
        
        if (is_null($query_result)) 
        {
            DBO::LogError(__METHOD__."(".__LINE__.")","FAILED:: to check if a file id exists {$file_unique_id} using sql = {$sql}"); 
            return null;
        }        

        if (count($query_result) > 0 ) return true;
        
        return true;

    }
    
    
    
    /**
     * Get files that have been stored in database via  InsertFile
     * 
     * @param string $unique_id
     * @param bool $echo_data - if you want to display the data as it comes out of the database - ie.e. stream it out to some where
     * @param bool $collect_data - if you want to collect data to a file to be used later - probably want to set tis to FALSE for streaming
     * @return string|null  -  string - Binary string of file<br> or Null on error
     *  
     */
    public static   function ReadFile($file_unique_id,$echo_data = false,$collect_data = true) 
    {
        
        $sql = "select data from ".self::FilesDataTable()." where file_unique_id = ".util::dbq($file_unique_id)." order by partnum;";
        
        $query_result = DBO::Query($sql);
        if (is_null($query_result)) 
        {
            DBO::LogError(__METHOD__."(".__LINE__.")","FAILED:: Toread a file back from DB {$file_unique_id} with parameters  echo_data = [$echo_data], collect_data = [$collect_data] \n"); 
            return null;
        }        

        
        if ($collect_data) $result_file = '';
        foreach ($query_result as $row) 
        {    
            $datapart =  base64_decode($row['data']);
            if ($collect_data) $result_file .= $datapart;
            if ($echo_data) echo $datapart;
        }
        
        unset($row);
        unset($query_result);        
        
        if ($collect_data) return $result_file;
        
        return true;
    }

    
    /**
     *
     * Write file from database to filesystem 
     * 
     * @param type $file_unique_id
     * @param type $dest_filename  - leave off / null to be random filename will be the return value
     * @return null|string  - Destination filename
     */
    public static   function ReadFile2Filesystem($file_unique_id,$dest_filename = null) 
    {
        if (is_null($dest_filename)) $dest_filename = file::random_filename(); // 
        
        $result_file =  self::ReadFile($file_unique_id);
        
        if (is_null($result_file)) 
        {
            DBO::LogError(__METHOD__."(".__LINE__.")","FAILED:: To read a file back from DB {$file_unique_id} on way to writing to filesystem  dest_filename =  [$dest_filename] \n"); 
            return null;
        }        
        
        $info = self::FileInfo($file_unique_id);
        
        $fw = fopen($dest_filename,'wb');
        fwrite($fw,$result_file,$info['total_filesize']);
        fclose($fw);

        if (!file_exists($dest_filename))  
        if (is_null($result_file)) 
        {
            DBO::LogError(__METHOD__."(".__LINE__.")","FAILED:: read a file back from DB {$file_unique_id} OK  failed to write to filesystem  dest_filename =  [$dest_filename] \n"); 
            return null;
        }        
        
        return $dest_filename;
        
    }
    
    /**
     * Stream file from database and just eacho data to STDOUT
     * 
     * @param type $file_unique_id 
     */
    public static   function ReadFile2Stream($file_unique_id) 
    {        
        self::ReadFile($file_unique_id,true,false);  // read file stream and dont collect data - ignore return varaibales
    }
    
    public static   function ReadFileMimeType($file_unique_id) 
    {        
        $sql = "select mimetype from ".self::FilesTable()." where file_unique_id = ".util::dbq($file_unique_id)." limit 1";
        
        return DBO::QueryFirstValue($sql, 'mimetype');
    }

    
    public static  function ReadFileDescription($file_unique_id) 
    {        
        $sql = "select description from ".self::FilesTable()." where file_unique_id = ".util::dbq($file_unique_id)." limit 1";
        return DBO::QueryFirstValue($sql, 'description');
    }
    
    public static  function RemoveFile($file_unique_id) 
    {        
        DBO::Delete(self::FilesTable(),    "file_unique_id = ".util::dbq($file_unique_id));
        DBO::Delete(self::FilesDataTable(),"file_unique_id = ".util::dbq($file_unique_id));        
        if (self::HasFile($file_unique_id)) return false; // if file still exists in DB then delet failed
        return true;
    }

    
    
}

?>
