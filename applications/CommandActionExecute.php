<?php
set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__));

include_once dirname(__FILE__).'/includes.php';

$ActionClassName = array_util::Value($argv, 1);   // read argv and get ID of command that will be run here.
if (is_null($ActionClassName))
{
    $msg = " ActionClassName is NULL";
    echo "ERROR:: {$msg}\n";
    return new ErrorMessage(__FILE__, __LINE__, $msg);    
}

$action = FinderFactory::Find($ActionClassName);
if (is_null($action))
{
    $msg = "ERROR:: Can't find action named '$ActionClassName' ?";
    echo "ERROR:: {$msg}\n";
    return new ErrorMessage(__FILE__, __LINE__, $msg);        
}


//*************************************************************************
//* here is where we actually execute the action
//*************************************************************************
if (!method_exists($action, 'Execute'))
{
    $msg = "ERROR:: Can't find Execute method for '$ActionClassName' ?\n".print_r($action,true);
    echo "ERROR:: {$msg}\n";
    return new ErrorMessage(__FILE__, __LINE__, $msg);  
}

$action->Execute();


?>
