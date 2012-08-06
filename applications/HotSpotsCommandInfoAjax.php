<?php
session_start();
include_once dirname(__FILE__).'/includes.php';

$result = array();

$commandID = array_util::Value($_POST, "cmdID",     null);
$elementID = array_util::Value($_POST, "elementID", null);

$result['msg'] = print_r($_POST,true);
    
$result['commandID'] = $commandID;
$result['elementID'] = $elementID;

$cmd = DatabaseCommands::CommandActionRead($commandID);
if ($cmd instanceof Exception)
{
    $cmd instanceof Exception;
    $result['error'] = "ERROR:: Could find / read  with ID {$commandID} {$cmd->getMessage()}";
    echo json_encode($result);
    return;  
}

$cmd instanceof CommandAction;


$result['msg'] = print_r($cmd,true);


echo json_encode($result);
return;   // will stop here and return if we have all results requested.

?>