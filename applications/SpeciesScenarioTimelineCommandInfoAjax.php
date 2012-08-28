<?php
session_start();
include_once dirname(__FILE__).'/includes.php';

$result = array();

$cmdaction = array_util::Value($_POST, "cmdaction", null);
if (is_null($cmdaction))
{
    $result['error'] = "cmdaction passed as NULL";
    echo json_encode($result);
    return;  
}


FinderFactory::Find($cmdaction);


$commandID = array_util::Value($_POST, "cmdID",     null);
$ui_element_id = array_util::Value($_POST, "ui_element_id", null);

$result['msg'] = print_r($_POST,true);
    
$result['commandID'] = $commandID;
$result['ui_element_id'] = $ui_element_id;



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