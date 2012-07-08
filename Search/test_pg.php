<?php
include_once dirname(__FILE__).'/includes.php';

$ok = true;

if (!$ok)   
{
    echo "FAILED:: To start test\n";
    exit(1);
}

echo "START ALL TESTS\n";
echo "==========================================================\n";
echo "\n";
echo "\n";



//echo "START TEST:  db_access with PG_ functions\n";
//echo "==========================================================\n";
//$ok = db_access(false);
//if (!$ok)   
//{
//    echo "FAILED::  \n";
//    echo "FAILED:: db_access with PG_ functions \n";
//    echo "FAILED::  \n";
//    exit(1);
//}
//
//
//
//echo "START TEST:  db_access with Command Line calls\n";
//echo "==========================================================\n";
//$ok = db_access(true);
//if (!$ok)   
//{
//    echo "FAILED::  \n";
//    echo "FAILED:: db_access With Command Line calls\n";
//    echo "FAILED::  \n";
//    exit(1);
//}
//
//
//
//echo "START TEST:  fileStorage with PG_ functions \n";
//echo "==========================================================\n";
//$ok = fileStorage(false);
//if (!$ok)
//{
//    echo "FAILED:: fileStorage with PG_ functions \n";
//    exit(1);
//}
//

echo "START TEST:  fileStorage with Command Line \n";
echo "==========================================================\n";
$ok = fileStorage(true);
if (!$ok)
{
    echo "FAILED:: fileStorage with Command Line \n";
    exit(1);
}
 


//fileStream();



function db_access($viaCommandLine)
{
    
    if (!$viaCommandLine)
        echo "TESTING:: db_access with native 'pg_' functions\n";
    else
        echo "TESTING:: db_access with exec calls to command lines\n";
    
    
    $q = "select column_name,data_type from INFORMATION_SCHEMA.COLUMNS where table_name = 'ap02_command_action';";
    echo "READ TABLE:: \n";
    echo "---------------------------------------------------------------------------------------------\n";
    echo "$q\n";
    echo "---------------------------------------------------------------------------------------------\n";
    echo "EXPECTED:: Row Count >= [7]\n";

    $DB = new PGDB($viaCommandLine);
    $r = $DB->query($q);
    
    echo "ACTUAL::   Row Count [".count($r)."] \n";
    
    matrix::display($r, $delim = " ",null,20);
    
    if (count($r) < 7) 
    {
        echo "FAILED::   Row Count [".count($r)."] >= 7 \n";
        return false;
    }

    $q = "insert into ap02_command_action (objectid,execution_flag,status,queueid) values ('1234','NOT WORKING','TEST STATUS','ME')";
    echo "INSERT INTO TABLE:: \n";
    echo "---------------------------------------------------------------------------------------------\n";
    echo "$q\n";
    echo "---------------------------------------------------------------------------------------------\n";
    echo "EXPECTED:: insert Count = [1] and Last Inserted row ID > -1\n";
    
    $i = $DB->insert($q);

    if (is_null($i)) 
    {
        echo "FAILED:: Insert id is null \n";
        return false;
    }
    
    echo "ACTUAL:: Row ID = [{$i}]\n";

    if ($i <= -1) 
    {
        echo "FAILED:: Insert id = {$i} \n";
        return false;
    }
    
    
    
    $q = "select * from ap02_command_action where id = {$i};";
    echo "SELECT DATA:: \n";
    echo "---------------------------------------------------------------------------------------------\n";
    echo "$q\n";
    echo "---------------------------------------------------------------------------------------------\n";
    echo "EXPECTED:: 1 row of data\n";
    echo "EXPECTED:: execution_flag == 'NOT WORKING'\n";
    
    $r = $DB->query($q,'id');
    matrix::display($r, $delim = " ",null,20);

    $first = util::first_element($r);

    echo "ACTUAL:: execution_flag == 'NOT WORKING'  ?= {$first['execution_flag']}  \n";
    
    if ($first['execution_flag'] != 'NOT WORKING') 
    {
        echo "FAILED:: reading data back from table = {$i} \n";
        return false;
    }
    
    
    $q = "update ap02_command_action set execution_flag = 'NEW FLAG' where id = {$i};";
    echo "UPDATE TABLE:: \n";
    echo "---------------------------------------------------------------------------------------------\n";
    echo "$q\n";
    echo "---------------------------------------------------------------------------------------------\n";
    echo "EXPECTED:: Update Count = [1]\n";
    $u = $DB->update($q);
    
    if (is_null($u)) 
    {
        echo "FAILED:: update returned null \n";
        return false;
    }

    
    
    $q = "select * from ap02_command_action where id = {$i};";
    echo "SELECT DATA:: \n";
    echo "---------------------------------------------------------------------------------------------\n";
    echo "$q\n";
    echo "---------------------------------------------------------------------------------------------\n";
    echo "EXPECTED:: 1 row of data\n";
    echo "EXPECTED:: execution_flag == 'NEW FLAG'\n";
    
    $r = $DB->query($q,'id');
    matrix::display($r, $delim = " ",null,20);

    $first = util::first_element($r);

    echo "ACTUAL:: execution_flag == 'NEW FLAG'  ?= {$first['execution_flag']}  \n";
    
    if ($first['execution_flag'] != 'NEW FLAG') 
    {
        echo "FAILED:: reading data back from table after update \n";
        return false;
    }

    
    echo "Closing Database:: \n";
    echo "---------------------------------------------------------------------------------------------\n";
    unset($DB);
    
    return true;
    
}


function fileStorage($viaCommandLine)
{
 
    echo "STORE FILES / IMAGES in Database {$viaCommandLine}\n";
    echo "---------------------------------------------------------------------------------------------\n";
    
    $filename = configuration::SourceDataFolder()."aus_elevation.tif";    

    echo "Source Data Folder      - ".configuration::SourceDataFolder()."\n";
    echo "Source Data Folder File - {$filename}\n";
    echo "---------------------------------------------------------------------------------------------\n";
    
    if (!file_exists($filename)) 
    {
        echo "FAILED:: file not found {$filename}  \n";
        return false;
    }
    
    
    $p = new PGDB($viaCommandLine);
    
    echo "\n via Command Line {$p->ViaCommandLine()}\n" ;
    
    $filesize = filesize($filename);
    
    echo "\nFILE SIZE:: {$filesize}\n" ;
    
    
    echo "INSERT FILE:: \n";
    echo "---------------------------------------------------------------------------------------------\n";
    echo "EXPECTED:: File ID - Unique value   e.g. 4ff926ac5a8169.41673632\n";
    
    
    $file_id = $p->InsertFile($filename,$filename);

    if (is_null($file_id))
    {
        echo "FAILED:: file_id return as NULL  \n";
        return false;
    }
    
    echo "ACTUAL:: File ID {$file_id}\n";
    
    
    echo "READ FILE and write to filesystem:: \n";
    echo "---------------------------------------------------------------------------------------------\n";
    echo "EXPECTED:: get file from databse with id {$file_id}\n";
    echo "EXPECTED:: and result size =  {$filesize}\n";
    
    $save_to_filename = configuration::SourceDataFolder()."aus_elevation_new.tif";    
    echo "EXPECTED:: new file to exist with same size {$save_to_filename}\n";
    
    file::Delete($save_to_filename);
    
    
    $filename_back = $p->ReadFile2Filesystem($file_id,$save_to_filename) ;    

    if (is_null($filename_back))
    {
        echo "FAILED:: filename_back  is NULL \n";
        return false;        
    }

    
    if (!file_exists($filename_back))
    {
        echo "FAILED:: to file from {$file_id} to $filename_back  does not exist\n";
        return false;        
    }
    
    echo "ACTUAL:: Filesize of {$filename_back} = ".  filesize($filename_back)." ?= {$filesize}\n";

    if (filesize($filename_back) != $filesize)
    {
        echo "FAILED:: writing proper sized file to  $filename_back \n";
        return false;        
    }

    
    echo "Visual Comparision:: \n";
    echo "---------------------------------------------------------------------------------------------\n";
    echo "\n";
    echo "original display {$filename}& \n";
    echo "copy     display {$filename_back}& \n";
    
    unset($p);
    
    return true;
    
}







function fileStream($viaCommandLine)
{
    
    // to be call ed by Web Browser
    
    // push jpg to database
    // and then display 
    
    $filename = "/home/jc166922/data/projects/brooklyn/images/images/May2010/DSC_5177.JPG";

    $p = new PGDB($viaCommandLine);
    
    $file_id = $p->InsertFile($filename,'Brooklyn Image');
    
    //$out_filename = $p->ReadFile2Filesystem($file_id,'/home/jc166922/brook.jpg') ;
    
    header('Content-Type: '.$p->ReadFileMimeType($file_id));
    
    $p->ReadFile2Stream($file_id);
    
    $p->RemoveFile($file_id);
    
    return true;
    
}



function store_command_action($viaCommandLine)
{
    
    
}



?>
