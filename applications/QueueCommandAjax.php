<?php
/**
 * Give an action name Find Action, instatciate, pass post variables into object to initialise, execute and return status
 * 
 * Usually called via HTML / PHP page Ajax call
 * 
 */
session_start();
include_once dirname(__FILE__).'/includes.php';

$result = array();


foreach ($_POST as $key => $value) 
    Session::add($key, $value);


$action = array_util::Value($_POST, "cmdaction", null);

$cmd = FinderFactory::Action($action);  

if ($cmd instanceof ErrorMessage )
{
    $result['error'] = print_r($cmd,true);
    echo json_encode($result);
    return;    
}


if ( !($cmd instanceof CommandAction) )
{
    
    $result['msg'] = "ERROR:: Action is unknown {$action}";
    echo json_encode($result);
    return;
}


$initResult = $cmd->initialise($_POST);

if ($cmd instanceof ErrorMessage )
{
    $result['error'] = print_r($cmd,true);
    echo json_encode($result);
    return;    
}


if ($initResult instanceof Exception)
{
    $result['error'] = $initResult->getMessage();
    echo json_encode($result);
    return;  
}



if ($cmd->ExecutionFlag() == CommandAction::$EXECUTION_FLAG_COMPLETE)
{
    
    $O = OutputFactory::Find($cmd);
    
    if ($O instanceof Output)
    {
        $result['content'] = $O->Content();
        $result['status'] = $cmd->Status();    
        
    }
    else
    {
        $result['content'] = $O;
    }
    
    $result['ExecutionFlag'] = $cmd->ExecutionFlag();    
    
    echo json_encode($result);
    return;   // will stop here and return if we have all results requested.
}




if ($cmd->ExecutionFlag() == CommandAction::$EXECUTION_FLAG_READY)
{
    
    $O = OutputFactory::Find($cmd);
    
    
    if ($O instanceof Output)
    {
        $result['content'] = $O->Content();
        $result['status'] = $cmd->Status();        
    }
    else
    {
        $result['content'] = $O;
    }
    
    $queueID = DatabaseCommands::CommandActionQueue($cmd);
    
    if ($queueID instanceof Exception)
    {
        $result['queueID'] =  -1;
        $result['msg'] = $queueID->getMessage();
    }
    else
    {
        $result['queueID'] = $queueID;
        $result['msg'] = "Ready to go";
    }
    
    $result['ExecutionFlag'] = $cmd->ExecutionFlag();    
    
    echo json_encode($result);
    return;  // queue the command and then return any results re already have and 
}

$result['msg'] = "somthing else {$action}";

echo json_encode($result);


?>