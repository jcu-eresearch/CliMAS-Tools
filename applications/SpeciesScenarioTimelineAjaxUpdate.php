<?php
session_start();
include_once dirname(__FILE__).'/includes.php';

$result = array();

foreach ($_POST as $key => $value) 
    Session::add($key, $value);

$action_id = array_util::Value($_POST, "action_id", null);
if (is_null($action_id))
{
    $result['msg'] = "ERROR:: Can't get Command update using NULL ID ";
    echo json_encode($result);
    return;  
}

FinderFactory::Action(array_util::Value($_POST, "cmdaction", null));   // make sure the correcxt class is loaded

$cmd = DatabaseCommands::CommandActionRead($action_id);
if (is_null($cmd))
{
    $result['msg'] = "ERROR:: Can't get Command update using ID {action_id}";
    echo json_encode($result);
    return;  
}

$cmd instanceof CommandAction;

$result['status'] = $cmd->Status();
$result['cmd'] = print_r($cmd);

echo json_encode($result);
return;   // will stop here and return if we have all results requested.


?>
