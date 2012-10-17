<?php
set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__));

include_once dirname(__FILE__).'/includes.php';

$commandActionID = array_util::Value($argv, 1);   // read argv and get ID of command that will be run here.
if (is_null($commandActionID)) return new ErrorMessage(__FILE__, __LINE__, "CommandActionID passed on Command Line was NULL");


$cmd = DatabaseCommands::CommandActionRead($commandActionID);
if ($cmd instanceof ErrorMessage)  return  ErrorMessage::Stacked(__FILE__, __LINE__, "",true,$cmd);
    

//*************************************************************************
//* here is where we actually execute the action
//*************************************************************************


$cmd->Execute();


?>
