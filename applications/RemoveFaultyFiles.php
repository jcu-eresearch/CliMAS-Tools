<?php
/**
 * Run from commandline to remove files that have less parts than their total
 *  
 */
include_once 'includes.php';
echo "Remove Faulty Files\n";
$result = DatabaseFile::RemoveFaultyFiles();

if ($result instanceof ErrorMessage)
{
    print_r($result);    
    exit(1);
}


matrix::display($result);
?>
