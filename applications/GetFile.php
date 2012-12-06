<?php
/**
 * Command line tool for read a file stored in the database back to a filesystem file.
 *  
 */

if (php_sapi_name() != "cli") return;

include_once 'includes.php';
$id = array_util::Value($argv, 1);
$fn = array_util::Value($argv, 2);

if (is_null($fn) || is_null($id))
{
    echo "usage {$argv[0]} file_unique_id filename\n";
    exit(1);
}

$result = DatabaseFile::ReadFile2Filesystem($id, $fn, true, false);
if ($result instanceof ErrorMessage)  
{
    ErrorMessage::Stacked (__METHOD__,__LINE__,"FAILED:: To read a file back from DB {$id} on way to writing to filesystem  dest_filename =  [$fn] \n", true,$result);
    exit(1);
}

if (!file_exists($fn))
{
    $E = new ErrorMessage(__METHOD__,__LINE__,"FAILED:: File Does Not exist ... reading file back from DB {$id} on way to writing to filesystem  dest_filename =  [$fn] \n");
    exit(1);    
}

echo "{$argv[0]}:: file created from ({$id}) as [{$fn}]\n";

?>