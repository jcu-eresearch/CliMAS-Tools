<?php 
session_start();
include_once dirname(__FILE__).'/includes.php';


$result = array();

foreach ($_POST as $key => $value) 
    Session::add($key, $value);
    
FinderFactory::Find("SpeciesRichness");  
FinderFactory::Find("SpeciesHotSpots");  


$cmd = FinderFactory::Find("SpeciesHotSpots");  
if ($cmd instanceof Exception)
{
    $cmd instanceof Exception;
    $result['error'] = "ERROR::  SpeciesHotSpots could not be found {$cmd->getMessage()}";
    echo json_encode($result);
    return;  
}



$initResult = $cmd->initialise($_POST);
if (!$initResult )
{
    $result['error'] = "ERROR:: Action is did not initalised {$action}";
    echo json_encode($result);
    return;  
}

$cmd instanceof CommandAction;

if ($cmd->ExecutionFlag() == CommandAction::$EXECUTION_FLAG_COMPLETE)
{
    echo json_encode($cmd->PropertyValues());    
    return;  
}

// we want to now send this job to the HPC to run
$command_id = DatabaseCommands::CommandActionQueue($cmd);
if ($command_id instanceof ErrorMessage)
{
    echo json_encode($command_id);
    return;  
}

$result = configuration::hpc_execute_command_qsub($command_id);

echo json_encode($cmd->PropertyValues());
return;  

?>
