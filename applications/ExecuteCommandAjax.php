<?php
session_start();
include_once dirname(__FILE__).'/includes.php';

$result = array();

foreach ($_POST as $key => $value) 
    Session::add($key, $value);


$action = array_util::Value($_POST, "cmdaction", null);

$cmd = FinderFactory::Action($action);  

if ($cmd instanceof Exception)
{
    $cmd instanceof Exception;
    $result['msg'] = "ERROR:: Action is did not initalised {$action} {$cmd->getMessage()}";
    echo json_encode($result);
    return;  
}
    
$initResult = $cmd->initialise($_POST);

if ($initResult instanceof Exception)
{
    $result['msg'] = "ERROR:: Action is did not initalised {$action}";
    echo json_encode($result);
    return;  
}
    
echo json_encode($cmd->Execute());
return;   // will stop here and return if we have all results requested.

?>