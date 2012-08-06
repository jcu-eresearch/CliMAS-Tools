<?php
session_start();
include_once dirname(__FILE__).'/includes.php';

$result = array();

foreach ($_POST as $key => $value) 
    Session::add($key, $value);


$id = array_util::Value($_POST, "id", null);
if (is_null($id))
{
    $result['msg'] = "ERROR:: Can't get Command update using NULL ID ";
    echo json_encode($result);
    return;  
}

$cmd = DatabaseCommands::CommandActionRead($id);
if (is_null($cmd))
{
    $result['msg'] = "ERROR:: Can't get Command update using ID {$id}";
    echo json_encode($result);
    return;  
}

$cmd instanceof CommandAction;

$result['status'] = $cmd->Status();


echo json_encode($result);
return;   // will stop here and return if we have all results requested.


?>
