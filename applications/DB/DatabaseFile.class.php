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
        
        $q = "select * from ".self::FilesTable()." where file_unique_id = ".util::dbq($file_unique_id,true)." limit 1";
        $result = DBO::QueryFirst($q);
        
        if ($result instanceof ErrorMessage)  
            return ErrorMessage::Stacked (__METHOD__,__LINE__,"Failed to File Info for file_unique_id =  $file_unique_id \n sql = {$q} ", true,$result);
        
        return $result;
        
    }
    
    
    /**
     *
     * @param type $theFilename Path to file to insert ito DB
     * @param type $description Some string that can be used to lookup the file later
     * @param type $category    Category of the file e.g. Image, html ...
     * @return string unique_file_id - use this id to get file backl from database;
     * @throws Exception 
     */
    public static function InsertFile($srcFilename,$description,$filetype = null,$compressed = false) 
    {
        
        if (!file_exists($srcFilename)) 
            return new ErrorMessage(__METHOD__,__LINE__,"Failed to Add file to database file does not exist Filename = [{$srcFilename}] description = $description,filetype =  $filetype  \n");
        
            
        $theFilename = $srcFilename;
            
        if ($compressed)
        {
            $tmp = file::random_filename().".zip";
            $cmd = "zip -J {$tmp} {$theFilename}";  // -J removes paths so oly the filename is stored - this is only valid if you want to store one file

            exec($cmd);
            if (!file_exists($tmp))  
                return new ErrorMessage(__METHOD__,__LINE__,"Compression Requested Failed to create zip file for {$theFilename}");
            
            $theFilename = $tmp;
            
        }
        
        
        $chunck_size = self::$FILE_DB_STORAGE_SIZE;

        $total_filesize = filesize($theFilename);
        $totalparts = ceil($total_filesize / $chunck_size);

        // if the storage is compressed then we want to keep the file type of the original file
        // so when we pass it back it will have the proper mime type
        $mimetype = ($compressed) ? mime_content_type($srcFilename) : mime_content_type($theFilename);
        
        $file_unique_id =  uniqid();
        
        $partnum = 0;
        $handle = fopen($theFilename, "rb");
        
        $total_read = 0;
        while (!feof($handle)) {

            $contents = fread($handle, $chunck_size);
            
            $total_read += strlen($contents);
            
            $data = util::dbq(base64_encode($contents),true);

            $sql  = "insert into ".self::FilesDataTable()."  (file_unique_id,partnum,totalparts,data) values (".util::dbq($file_unique_id,true).",{$partnum},{$totalparts},{$data})";
            
            $insertResult = DBO::Insert($sql);
            if ($insertResult instanceof ErrorMessage) 
            {    
                self::RemoveFile($file_unique_id); // remove bits of file that where inserted                 
                $qe = "insert into ".self::FilesDataTable()."  (file_unique_id,partnum,totalparts,data) values (".util::dbq($file_unique_id,true).",{$partnum},{$totalparts},'Encoded Data Removed')";
                return ErrorMessage::Stacked (__METHOD__,__LINE__,$qe, true,$insertResult);
            }
            
            $partnum++;
        }        

        if ($compressed) file::Delete($tmp); // remove compressed file
        

        if ($compressed)
        {
            // write the description of the file - compressed file
            $sql  = "insert into ".self::FilesTable()." (file_unique_id,mimetype,totalparts,total_filesize,description,filetype,compressed)  values ".
                    "(".util::dbq($file_unique_id,true).",".util::dbq($mimetype,true).",{$totalparts},".filesize($srcFilename).",".util::dbq($description,true).",".util::dbq($filetype,true).",".util::dbq("zip").")";
            
        }
        else
        {
            // write the description of the file 
            $sql  = "insert into ".self::FilesTable()." (file_unique_id,mimetype,totalparts,total_filesize,description,filetype)  values ".
                    "(".util::dbq($file_unique_id,true).",".util::dbq($mimetype,true).",{$totalparts},".filesize($srcFilename).",".util::dbq($description,true).",".util::dbq($filetype,true).")";
        }
                
        $insertResult = DBO::Insert($sql);
        if ($insertResult instanceof ErrorMessage)  
            return ErrorMessage::Stacked (__METHOD__,__LINE__,"FAILED:: to insert file (fileinfo) in to DB {$theFilename} with description {$description} at part {$partnum} using a comand line call", true,$insertResult);
                
        
        return $file_unique_id;

    }
    
    
    public static   function HasFile($file_unique_id) 
    {
        
        $sql = "select * from ".self::FilesTable()." where file_unique_id = ".util::dbq($file_unique_id,true)." limit 1";
        
        $result = DBO::Query($sql);
        
        if ($result instanceof ErrorMessage)  
            return ErrorMessage::Stacked (__METHOD__,__LINE__,"FAILED:: to check if a file id exists {$file_unique_id} using sql = {$sql}", true,$result);


        if (count($result) <= 0 ) return false;
        
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
        // need to know if this is compressed ?
        
        
        $sql = "select data from ".self::FilesDataTable()." where file_unique_id = ".util::dbq($file_unique_id,true)." order by partnum;";
        
        $result = DBO::Query($sql);
        
        if ($result instanceof ErrorMessage)  
            return ErrorMessage::Stacked (__METHOD__,__LINE__,"FAILED:: To read a file back from DB {$file_unique_id} with parameters  echo_data = [$echo_data], collect_data = [$collect_data] ", true,$result);

        
        if (self::isCompressed($file_unique_id) )
        {
            // compressed file - have to read out to files system and then hand back

            $result_file = '';
            foreach ($result as $row) 
            {    
                $datapart =  base64_decode($row['data']);
                $result_file .= $datapart;
            }
            
            // write $result_file  to file system and uncompress
            
            $tmp_filename_zip = file::random_filename().".zip";
            
            $fw =   file_put_contents($tmp_filename_zip, $result_file);
            if ($fw === false) 
                return new ErrorMessage (__METHOD__,__LINE__,"FAILED:: To read a file back compressed file from DB, could not write tmp file [{$tmp_filename_zip}]   {$file_unique_id} with parameters  echo_data = [$echo_data], collect_data = [$collect_data]");

            if (!file_exists($tmp_filename_zip)) 
                return new ErrorMessage (__METHOD__,__LINE__,"FAILED:: To read a file back compressed file from DB tmp file does not exist {$tmp_filename_zip} ...   {$file_unique_id} with parameters  echo_data = [$echo_data], collect_data = [$collect_data]");
            
                
            $tmp_unzipped_filename = file::random_filename();
            
            // unzip $tmp_filename
            exec("unzip -p '{$tmp_filename_zip}' > $tmp_unzipped_filename");
            
            if (!file_exists($tmp_unzipped_filename)) 
            {
                file::Delete($tmp_unzipped_filename);
                return new ErrorMessage (__METHOD__,__LINE__,"FAILED:: To read a file back compressed file from DB could not unzip  {$tmp_filename_zip} ...   {$file_unique_id} with parameters  echo_data = [$echo_data], collect_data = [$collect_data]");
            }
            
            // nice to check size as well ?
            
            $real_file_contents = null;
            if ($echo_data || $collect_data)
                $real_file_contents = file_get_contents($tmp_unzipped_filename); 

            if (is_null($real_file_contents)) return null;
            
            file::Delete($tmp_unzipped_filename);
            
            if ($echo_data) echo $real_file_contents;

            unset($row);
            unset($result);        
            unset($result_file);
            
            if ($collect_data) return $real_file_contents;            
            
            
        }
        else
        {
            // can stream file out or collect for later
            if ($collect_data) $result_file = '';
            foreach ($result as $row) 
            {    
                $datapart =  base64_decode($row['data']);
                if ($collect_data) $result_file .= $datapart;
                if ($echo_data) echo $datapart;
            }

            unset($row);
            unset($result);        

            if ($collect_data) return $result_file;
        }
            
        
        
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
    public static function ReadFile2Filesystem($file_unique_id,$dest_filename = null,$overwrite = true,$reuse = false) 
    {
        
        if (is_null($dest_filename)) $dest_filename = file::random_filename(); // 

        if (!$reuse ) file::Delete($dest_filename);

        
        if ($overwrite)  file::Delete ($dest_filename);

        if ($reuse && file_exists($dest_filename)) return $dest_filename;

        
        $result_file =  self::ReadFile($file_unique_id);
        if ($result_file instanceof ErrorMessage)  
            return ErrorMessage::Stacked (__METHOD__,__LINE__,"FAILED:: To read a file back from DB {$file_unique_id} on way to writing to filesystem  dest_filename =  [$dest_filename] \n", true,$result_file);
        
            
        $info = self::FileInfo($file_unique_id);
        if ($info instanceof ErrorMessage)  
            return ErrorMessage::Stacked (__METHOD__,__LINE__,"Can't get fileinfo for {$file_unique_id}", true,$info);
        
        
        try {
            $fw = fopen($dest_filename,'wb');    
        } catch (Exception $exc) {
            return new ErrorMessage(__METHOD__,__LINE__,"Tried to open file for writing {$dest_filename} \n".$exc->getTraceAsString() );
        }
        
        fwrite($fw,$result_file,$info['total_filesize']);
        fclose($fw);

        if (!file_exists($dest_filename))  
            return new ErrorMessage(__METHOD__,__LINE__,"FAILED:: read a file back from DB {$file_unique_id} could not write to {$dest_filename} \n");
        
        return $dest_filename;
        
    }
    
    /**
     * Stream file from database and just eacho data to STDOUT
     * 
     * @param type $file_unique_id 
     */
    public static  function ReadFile2Stream($file_unique_id) 
    {        
        self::ReadFile($file_unique_id,true,false);  // read file stream and dont collect data - ignore return varaibales
    }
    
    public static   function ReadFileMimeType($file_unique_id) 
    {        
        $sql = "select mimetype from ".self::FilesTable()." where file_unique_id = ".util::dbq($file_unique_id,true)." limit 1";
        
        $result = DBO::QueryFirstValue($sql, 'mimetype');
        
        if ($result instanceof ErrorMessage)  
            return ErrorMessage::Stacked (__METHOD__,__LINE__,"Can't read mimetype for {$file_unique_id} using sql [{$sql}]", true,$result);
        
        return $result;
    }

    public static   function isCompressed($file_unique_id) 
    {        
        $sql = "select compressed from ".self::FilesTable()." where file_unique_id = ".util::dbq($file_unique_id,true)." limit 1";
        
        $result = DBO::QueryFirstValue($sql, 'compressed');
        
        if ($result instanceof ErrorMessage)  
            return ErrorMessage::Stacked (__METHOD__,__LINE__,"Can't read mimetype for {$file_unique_id} using sql [{$sql}]", true,$result);
        
        $result = trim($result);
        if ($result == "") return false;
            
        return true;
    }
    
    
    
    public static  function ReadFileDescription($file_unique_id) 
    {        
        $sql = "select description from ".self::FilesTable()." where file_unique_id = ".util::dbq($file_unique_id,true)." limit 1";
        
        $result = DBO::QueryFirstValue($sql, 'description');
        
        if ($result instanceof ErrorMessage)  
            return ErrorMessage::Stacked (__METHOD__,__LINE__,"Can't read ReadFileDescription for {$file_unique_id} using sql [{$sql}]", true,$result);
        
        return $result;
    }
    
    public static  function RemoveFile($file_unique_id) 
    {        
        
        $result = DBO::Delete(self::FilesTable(),    "file_unique_id = ".util::dbq($file_unique_id,true));
        if ($result instanceof ErrorMessage)  
            return ErrorMessage::Stacked (__METHOD__,__LINE__,"Can't remove file with id  {$file_unique_id} ", true,$result);

        
        $result = DBO::Delete(self::FilesDataTable(),"file_unique_id = ".util::dbq($file_unique_id,true));        
        if ($result instanceof ErrorMessage)  
            return ErrorMessage::Stacked (__METHOD__,__LINE__,"Can't remove file with id  {$file_unique_id} ", true,$result);
        
        
        if (self::HasFile($file_unique_id)) return false; // if file still exists in DB then delet failed
        return true;
    }

    
    public static function RemoveFileAll($id)
    {
        $results = array();
        $results['files_data']             = DBO::Delete('files_data',             'file_unique_id = '.util::dbq($id,true));
        $results['files']                  = DBO::Delete('files',                  'file_unique_id = '.util::dbq($id,true));
        $results['modelled_climates']      = DBO::Delete('modelled_climates',      'file_unique_id = '.util::dbq($id,true));
        $results['modelled_species_files'] = DBO::Delete('modelled_species_files', 'file_unique_id = '.util::dbq($id,true));        
        
        foreach ($results as $key => $value) 
        {
            if ($value instanceof ErrorMessage)
                return new ErrorMessage(__METHOD__,__LINE__,"FAILED:: RemoveFileAll for i {$file_unique_id} \n ".print_r($results,true));
                
        }
        
        
        return $results;
    }
           
    public static function RemoveFaultyFiles()
    {
        $q = 'select f.file_unique_id,f.filetype,f.totalparts,count(*) as parts_count from files f, files_data fd  where f.file_unique_id = fd.file_unique_id group by f.file_unique_id,f.filetype,f.totalparts having count(*) != f.totalparts;';

        $result = DBO::Query($q);
        if ($result instanceof ErrorMessage)
                return new ErrorMessage(__METHOD__,__LINE__,"FAILED:: Remove to get list of Fault Files \n sql = [{$q}] ",true);
        
        
        foreach ($result  as $key => $row) 
        {
            $subResult = self::RemoveFileAll($row['file_unique_id']);
            
            if ($subResult instanceof ErrorMessage)
                return ErrorMessage::Stacked (__METHOD__,__LINE__,"Can't remove files with id {$row['file_unique_id']}", true,$subResult);
            
        }
        
        return $result;
        
    }
    
    
    
    public static function InsertCompressedFile($srcFilename,$description = null,$filetype = null) 
    {
        $file_unique_id = self::InsertFile($srcFilename, $description, $filetype,true);
        
        if ($file_unique_id instanceof ErrorMessage)
            return ErrorMessage::Stacked (__METHOD__,__LINE__,"Failed to insert Compressed File srcFilename = [$srcFilename], description =[$description],filetype = [$filetype]", true,$file_unique_id);
        
        return $file_unique_id;
        
    }
    
    
    /**
     * List of Running jobs - - species ID's
     * 
     * @return type 
     */
    public static function RunningJobs($jobPrefix)  
    {
        
        $jobs = array();
        
        exec("qstat | grep '{$jobPrefix}'| grep 'R normal' ",$jobs);
        
        
        $result = array();
        foreach ($jobs as $key => $value) 
        {
            $job_id = trim(substr($value,27,13));
            $result[$job_id] = $value;
        }    

        return $result;
        
        
    }
    
    
    
    
}

?>
