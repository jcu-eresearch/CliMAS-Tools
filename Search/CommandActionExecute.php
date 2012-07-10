<?php
set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__));

include_once dirname(__FILE__).'/includes.php';

$commandActionID = array_util::Value($argv, 1);   // read argv and get ID of command that will be run here.
if (is_null($commandActionID))
{
    // log this as failed to execute command action
    //echo "ERROR:: Was not a CommandAction commandActionID is NULL\n";
    return;
}

$cmd = pgdb::CommandActionRead($commandActionID);
if ( !($cmd instanceof CommandAction))
{
    // it was a command but it was not a command action
    //echo "ERROR:: Was not a COmmandAction\n";
    return;
}

if (is_null($cmd))
{
    // it was a command but it was not a command action
    //echo "ERROR:: cmd is NULL ???";
    return;
}

//*************************************************************************
//* here is where we actually execute the action
//*************************************************************************

$cmd->Execute();


?>
