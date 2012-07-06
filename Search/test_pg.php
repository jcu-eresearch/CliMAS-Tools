<?php
include_once dirname(__FILE__).'/includes.php';
//db_access_images();
//db_access_images()
 //fileStorage();
fileStream();
function fileStream()
{
    
    // to be call ed by Web Browser
    
    // push jpg to database
    // and then display 
    
    $filename = "/home/jc166922/data/projects/brooklyn/images/images/May2010/DSC_5177.JPG";

    $p = new PGDB();
    
    $file_id = $p->InsertFile($filename,'Brooklyn Image');
    
    //$out_filename = $p->ReadFile2Filesystem($file_id,'/home/jc166922/brook.jpg') ;
    
    header('Content-Type: '.$p->ReadFileMimeType($file_id));
    
    $p->ReadFile2Stream($file_id);
    
    $p->RemoveFile($file_id);
    
}

function fileStorage()
{
 
    $filename = "/home/jc166922/Documents/aus_elevation.tif";

    $p = new PGDB();
    
    $file_id = $p->InsertFile($filename,$filename);
    
    echo "Stored File as $file_id\n";
    
    echo "Read back File as $file_id\n";
    
    
    $out_file  = '/home/jc166922/fred.tif' ;
    
    file::Delete($out_file);    
    
    $filename = $p->ReadFile2Filesystem($file_id,$out_file) ;
    
    if (filesize($filename) != filesize($out_file))
    {
        echo "ERROR:: input file and output file sizes do not match\n";
        exit(1);
    }
    
    $mimetype = $p->ReadFileMimeType($file_id);
    
    echo "mimetype = $mimetype\n";
    
    // exec("display {$out_file}&");
    
    echo "Remove File\n";
    
    echo "preremove = ".$p->CountFile($file_id)."\n";
    
    $p->RemoveFile($file_id);
        
    echo "postremove = ".$p->CountFile($file_id)."\n";
    
    
    unset($p);
    
}

function db_access()
{

    echo "Test to see if we can write to postgress database using Native PHP calls.\n";


echo "Read table with \n";
$q = "select id,objectid,execution_flag,status,queueid from ap02_command_action;";
echo "$q\n";

$DB = new PGDB();
$r = $DB->query($q,'id');
print_r($r);


echo "Insert table with \n";
$q = "insert into ap02_command_action (objectid,execution_flag,status,queueid) values ('1234','NOT WORKING','TEST STATUS','ME')";
echo "$q\n";

$i = $DB->insert($q);

echo "After Insert select back  last Inserted id = {$i} \n\n";

$q = "select * from ap02_command_action where id = {$i};";

echo "$q\n";
$r = $DB->query($q,'id');
print_r($r);


$last_insert = $i;


echo "Update table with \n";
$q = "update ap02_command_action set execution_flag = 'NEW FLAG' where id = {$last_insert};";
echo "$q\n";

$rowsUpdated = $DB->update($q);

echo "After update rows updated = $rowsUpdated\n\n";

$q = "select * from ap02_command_action where id = {$last_insert};";

echo "$q\n";
$r = $DB->query($q,'id');
print_r($r);

unset($DB);
    
}

function db_access_images()
{
    
    $DB = new PGDB();
    
    $test_lookup = 'aus_elevation.tif';
    
    echo "Count Image   ... $test_lookup  \n";
    $hasImage = $DB->HasImage($test_lookup);
    
    echo "Result of Count Image  for $test_lookup    === {$hasImage} \n";
    
    
    echo "Insert Image into database\n";
    
    echo "File size inbound = ".filesize('/home/jc166922/Documents/aus_elevation.tif')."\n";
    
    $ii = $DB->InsertImage('/home/jc166922/Documents/aus_elevation.tif',$test_lookup);

    echo "AFter Insert Image into database  insert id = $ii \n";

    
    echo "get Image back to /home/jc166922/fred.tif \n";    
    $ii = $DB->GetImage($test_lookup,'/home/jc166922/fred.tif');

    
    if (file_exists('/home/jc166922/fred.tif'))
    {
        echo "put file  to /home/jc166922/fred.tif \n";
        echo "File size outbound = ".filesize('/home/jc166922/fred.tif')."\n";
        
    }
    else
    {
        echo "failed to file  to /home/jc166922/fred.tif \n";
    }
    
    
    echo "Count Image   ... $test_lookup  \n";
    $hasImage = $DB->HasImage($test_lookup);
    
    echo "Result of Count Image  for $test_lookup    === {$hasImage} \n";
    
    unset($DB);
    

}
?>