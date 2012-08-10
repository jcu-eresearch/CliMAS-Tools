<?php
include_once dirname(__FILE__).'/includes.php';

echo "Remove Compressed File\n";

$prog = array_util::Value( $argv, 0);
$id = array_util::Value($argv, 1);

if (is_null($id)) usage($prog);

function usage($prog)
{
   echo "usage: {$prog} file_unique_id \n" ;
   exit(1);
}

$result = DatabaseFile::RemoveFileAll($id);
if ($result instanceof ErrorMessage)
{
    print_r($result);    
    exit(1);
}


print_r($result);


?>
