<?php
include_once dirname(__FILE__).'/includes.php';
/**
 * Display Command ActionObject 
 * 
 * COmmand Line Argument 1: UNIQUE_ID Command Object ID
 *  
 */

$id = array_util::Value($argv, 1);

$cmd = DatabaseCommands::CommandActionRead($id);
if ($cmd instanceof ErrorMessage)
{
    echo $cmd;
    exit(1);
}

print_r($cmd);

?>
