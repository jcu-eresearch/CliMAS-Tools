<?php
session_start();
include_once 'includes.php';

$queueID = array_util::Value($_POST, "queueID", null);

$result = array();


if (is_null($queueID))
{
    $result['msg'] = "The job with {$queueID} is not available";
    echo json_encode($result);
    return;   // will stop here and return if we have all results requested.
}


$result['queueID'] = $queueID;


$db = new PGDB();
$cmd = $db->CommandActionRead($queueID);
unset($db);


if (is_null($cmd))
{
    $result['msg'] = "The job with {$queueID} is not available 2";
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
