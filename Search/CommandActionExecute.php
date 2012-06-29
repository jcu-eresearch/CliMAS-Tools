<?php
include_once 'includes.php';

print_r($conf);


// read argv and get ID of command that will be run here.
$commandActionID = array_util::Value($argv, 1);
if (is_null($commandActionID))
{
    // log this as failed to execute command action
    echo "ERROR:: Was not a CommandAction commandActionID is NULL";
    return;
}

$cmd = CommandUtil::GetCommandFromID($commandActionID,false);
if ( !($cmd instanceof CommandAction))
{
    // it was a command but it was not a command action
    echo "ERROR:: Was not a COmmandAction";
    return;
}

if (is_null($cmd))
{
    // it was a command but it was not a command action
    echo "ERROR:: cmd is NULL ???";
    return;
}

//*************************************************************************
//* here is where we actually execute the action
//*************************************************************************

$cmd->Execute();


?>
