<?php
set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__));

include_once 'includes.php';

$command_id = util::CommandScriptsFoldermandLineOptionValue($argv,'command_id',null);


if (!is_null($command_id))
{
    
    $action = DatabaseCommands::CommandActionRead($command_id);
    
    print_r($action);
    
    
    if (is_null($action) || $action instanceof ErrorMessage)  
    {
        $msg = "ERROR:: Failed to get action from ID {$command_id}".print_r($action,true);
        echo "ERROR:: {$msg}\n";
        exit(1);
    }

    
}
else
{
    $ActionClassName = array_util::Value($argv, 1);   // read argv and get ID of command that will be run here.
    if (is_null($ActionClassName))
    {
        echo "ERROR:: ActionClassName is NULL \n";
        exit(1);
    }

    $action = FinderFactory::Find($ActionClassName);
    if (is_null($action) || $action instanceof ErrorMessage)  
    {
        $msg = "ERROR:: Can't find action named '$ActionClassName' ?".print_r($action,true);
        echo "ERROR:: {$msg}\n";
        exit(1);
    }

    
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

print_r($action);


$action->Execute();


?>
