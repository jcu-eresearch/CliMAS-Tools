<?php
session_start();
include_once dirname(__FILE__).'/includes.php';

$result = array();

FinderFactory::Find("SpeciesHotSpots");

$action_id = array_util::Value($_POST, "action_id",     null);

$cmd = DatabaseCommands::CommandActionRead($action_id);
if ($cmd instanceof Exception)
{
    $cmd instanceof Exception;
    $result['error'] = "ERROR:: Could find / read  with ID {$commandID} {$cmd->getMessage()}";
    echo json_encode($result);
    return;  
}

$cmd instanceof CommandAction;


echo json_encode($cmd->PropertyValues());
return;   // will stop here and return if we have all results requested.

?>