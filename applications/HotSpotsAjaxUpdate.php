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

$ui_element_id = array_util::Value($_POST, "ui_element_id", null);


FinderFactory::Action("SpeciesHotSpots");   // make sure the  class is loaded


$cmd = DatabaseCommands::CommandActionRead($action_id);
if ($cmd instanceof ErrorMessage)
{
    $result['msg'] = "ERROR:: Can't get Command update using ID {action_id}";
    echo json_encode($result);
    return;  
}

$cmd instanceof CommandAction;
$cmd->ui_element_id($ui_element_id);

echo json_encode($cmd->PropertyValues());
return;   // will stop here and return if we have all results requested.


?>
