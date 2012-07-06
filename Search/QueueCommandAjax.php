<?php
session_start();
include_once dirname(__FILE__).'/includes.php';

$result = array();

foreach ($_POST as $key => $value) 
    Session::add($key, $value);


$action = array_util::Value($_POST, "cmdaction", null);



$cmd = FinderFactory::Action($action);  

if ( !($cmd instanceof CommandAction) )
{
    
    $result['msg'] = "ERROR:: Action is unknown {$action}";
    echo json_encode($result);
    return;
}

$initResult = $cmd->initialise();

if ($initResult instanceof Exception)
{
    $result['msg'] = "ERROR:: Action is did not initalised {$action}";
    echo json_encode($result);
    return;  
}


if ($cmd->ExecutionFlag() == CommandAction::$EXECUTION_FLAG_COMPLETE)
{
    
    $O = OutputFactory::Find($cmd);
    
    $result['head'] = $O->Head();
    $result['title'] = $O->Title();
    $result['content'] = $O->Content();
    
    $result['status'] = $cmd->Status();
    
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
    
    $result['msg'] = "Working here 3";
    echo json_encode($result);
    return;  // queue the command and then return any results re already have and 

    
    $queueID = CommandUtil::Queue($cmd);  
    
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
    
    
    echo json_encode($result);
    return;  // queue the command and then return any results re already have and 
}



$result['msg'] = "somtjing else {$action}";

echo json_encode($result);


?>