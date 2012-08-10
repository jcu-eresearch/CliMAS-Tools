<?php
session_start();
include_once dirname(__FILE__).'/includes.php';

$result = array();

foreach ($_POST as $key => $value) 
    Session::add($key, $value);


$action = array_util::Value($_POST, "cmdaction", null);

$cmd = FinderFactory::Action($action);  

if ($cmd instanceof ErrorMessage)
{
    $result['msg'] = $cmd;
    echo json_encode($result);
    return;  
}
    
$initResult = $cmd->initialise($_POST);

if ($initResult instanceof Exception)
{
    $result['msg'] = $initResult->getMessage();
    echo json_encode($result);
    return;  
}

if ($initResult instanceof ErrorMessage)
{
    $result['msg'] = $initResult;
    echo json_encode($result);
    return;  
}
    
echo json_encode($cmd->Execute());
return;   // will stop here and return if we have all results requested.

?>