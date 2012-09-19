<?php
/**
 * Used via Web page to retrive status of Command  via an AJAX call
 * 
 *  
 */
session_start();
include_once 'includes.php';

$result = array();

$queueID = array_util::Value($_POST, "queueID", null);
if (is_null($queueID))
{
    $result['msg'] = "queueID passed as NULL";
    echo json_encode($result);
    return;   // will stop here and return if we have all results requested.
}


$result['queueID'] = $queueID;

$cmd = DatabaseCommands::CommandActionRead($queueID);


if ($cmd instanceof ErrorMessage)
{
    $result['error'] = $cmd;
    echo json_encode($result);
    return;   // will stop here and return if we have all results requested.
}


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


?>
