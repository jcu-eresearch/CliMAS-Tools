<?php
include_once dirname(__FILE__).'/includes.php';

echo "Store Compressed File\n";

$prog = array_util::Value( $argv, 0);
$filename = array_util::Value($argv, 1);
$description = array_util::Value($argv, 2);
$filetype = array_util::Value($argv, 3);

if (is_null($filename)) usage($prog);

function usage($prog)
{
   echo "usage: {$prog} filename \n" ;
   exit(1);
}

if (is_null($description)) $description = basename($filename);
if (is_null($filetype)) $filetype = 'ZIP';

$result = DatabaseFile::InsertCompressedFile($filename,$description,$filetype);
if ($result instanceof ErrorMessage) 
{
    echo $result."\n";
    exit(1);
}

echo "file_unique_id: ". $result."\n";

?>
