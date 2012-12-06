<?php
set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__));
if (php_sapi_name() != "cli") return;

include_once 'includes.php';

$cmd = null;

$command_id = util::CommandScriptsFoldermandLineOptionValue($argv,'command_id',null);
$qsub   = util::CommandScriptsFoldermandLineOptionValue($argv,'qsub',null);

if (!is_null($command_id))
{
    
    $cmd = DatabaseCommands::CommandActionRead($command_id);
    
    if (is_null($cmd) || $cmd instanceof ErrorMessage)  
    {
        $msg = "ERROR:: Failed to get action from ID {$command_id}".print_r($cmd,true);
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

    $cmd = FinderFactory::Find($ActionClassName);
    if (is_null($cmd) || $cmd instanceof ErrorMessage)  
    {
        $msg = "ERROR:: Can't find action named '$ActionClassName' ?".print_r($cmd,true);
        echo "ERROR:: {$msg}\n";
        exit(1);
    }

    
}

//*************************************************************************
//* here is where we actually execute the action
//*************************************************************************
if (!method_exists($cmd, 'Execute'))
{
    $msg = "ERROR:: Can't find Execute method for '$ActionClassName' ?\n".print_r($cmd,true);
    echo "ERROR:: {$msg}\n";
    return new ErrorMessage(__FILE__, __LINE__, $msg);  
}

if (is_null($qsub))
{
    $cmd->Execute();
    exit(0);
}

// RUN VIA QSUB - which means at this point we create a script that will call CommandActionExecute - with out qsubbut with the same CommandID


        // qsub shell script
        $script  = "#!/bin/bash\n";
        $script .= "# QSUB script from ".configuration::ApplicationName()."\n";
        $script .= "# Written to execute Command Action with id {$command_id} \n";
        $script .= "# datetime script written:".datetimeutil::NowDateTime()."\n";
        $script .= "php '".configuration::CommandScriptsExecutor()."' --command_id={$command_id}\n";
        $script .= "\n";

        $scripts_folder = configuration::CommandScriptsFolder();

        $script_filename = configuration::CommandScriptsFolder().
                           configuration::CommandScriptsPrefix().
                           $command_id.
                           configuration::CommandScriptsSuffix();
        
        
        $fpc = file_put_contents($script_filename, $script);  // write script to script_filename
        
        if ($fpc === FALSE) 
            return ErrorMessage::Stacked (__METHOD__,__LINE__,"Failed to write script for command script_filename = [{$script_filename}] \nscript = [$script]", true,$script); 
            
        $qsub_command = "cd {$scripts_folder}; qsub $script_filename;";
        
        echo "Executing as QSUB job {$script_filename}\n";
        
        $qsub_id = exec($qsub_command);
        
        echo "qsub_id = $qsub_id\n";
        

?>
