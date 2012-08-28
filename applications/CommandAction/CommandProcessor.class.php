<?php
/**
 * Process Commands in Queue and do soemthig with them
 * Start, Status, Complete
 *
 * launch a qsub job for this action
 * a qsub job will write a script file out to be run
 * and then return back a qsub ID that can be use to track the job
 * though QSTAT - so maybe we can give the QSTAT  id to COmmand and save it
 *
 * then with running we can read the STAT line for that ID and
 *
 * return that into the status update
 * a complete will be if we see qstat qith a C for Cancelling
 *
 * or we don't see that job in qstat any more
 * or COMMAND has been set to Finalised
 * once the qsub job is finished then we finish our job and save the command
 *
 * and what of the output of that Action
 * depeneds on the action to what the output is
 * will most likley be a file list or a serialized Object that
 *
 * will give details of where to what file needs to be downloaded.
 * instatiate Object based on COmmand Action
 *
 * assign command to action
 * execute action  - tyhis action will need to up date command ?
 *
 * or use command to update itself.
 * so if we chnage status or
 *
 */
class CommandProcessor 
{
    public static $QSTAT_RUNNING = "R";
    public static $QSTAT_COMPLETED = "C";
    
    /** 
     *
     * - Read queue folder  configuration::CommandQueueFolder()
     * - look for Commands to process  (Commands are PHPO serialized CommandAction objects)
     * 
     * 
     *  
     */
    public static function ProcessQueue()
    {
        
        FinderFactory::Find("SpeciesHotSpots");
        FinderFactory::Find("SpeciesMaxent");
        FinderFactory::Find("SpeciesRichness");
        
        $processor_running_filename = configuration::ApplicationFolder()."processor_running.flag";
        
        if (file_exists($processor_running_filename))
        {
            echo "Processor already running using filename [{$processor_running_filename}] \n";
            exit(0);            
        }
        
        
        $str  = "\nCommand Processor Started : ". datetimeutil::NowDateTime();
        $str .= "\n";
        $str .= "\ncommad line : ".configuration::ApplicationFolder()."applications/Incoming.php";
        $str .= "\n";
        $str .= "\nDelete this file to stop the TDH Tools command processor\n";
        file_put_contents($processor_running_filename,$str);
        
        
        if (!file_exists($processor_running_filename)) 
        {
            $msg = "Failed to sart command processor for TDH TOOLS Queue:".configuration::CommandQueueID()."\n";
            $E = new ErrorMessage(__METHOD__, __LINE__,$msg );
            echo "ERROR:: $msg.\n";
            exit(1);
        }
        
        
        echo "Reading queue for ".configuration::CommandQueueID()."\n";
        echo "To gracefully stop process delete {$processor_running_filename}\n";
        
        while(file_exists($processor_running_filename))
        {
            
            $commands = DatabaseCommands::CommandActionListIDs();
            if ($commands instanceof ErrorMessage)  
                return ErrorMessage::Stacked (__METHOD__,__LINE__,"", true,$commands); 
            
            
            if (count($commands) > 0)
                foreach ($commands as $commandID) 
                    self::processSingleQueueItem($commandID);
            
            sleep(3);
            
        }
        
        
        echo "Gracefully stopped process for TDH TOOLS Queue:".configuration::CommandQueueID()."\n";
        

    }
    
    
    /**
     * Read Single command file
     *
     * - unserailize and action command based on it's "state" ExecutionFlag (ref: CommandAction statics)
     * 
     * @param string $filepath
     * @return null null
     */
    private function processSingleQueueItem($commandID)
    {

        $command = DatabaseCommands::CommandActionRead($commandID) ;
        
        
        if ($command instanceof ErrorMessage)  return ErrorMessage::Stacked (__METHOD__,__LINE__,"", true,$command); 
        
        if (!($command instanceof CommandAction))
            return new ErrorMessage(__METHOD__,__LINE__,"command read from Queue was Not CommandAction commandID = [{$commandID}]", true); 
            
        
        switch ($command->ExecutionFlag()) {
            case CommandAction::$EXECUTION_FLAG_READY:
                $result = self::Ready(&$command);
                break;

            case CommandAction::$EXECUTION_FLAG_RUNNING:
                $result = self::Running($command);
                break;

            case CommandAction::$EXECUTION_FLAG_QUEUE_DONE:
                $result = self::Finalise($command);   //  the QSUB Queue said it was completd
                break;

            case CommandAction::$EXECUTION_FLAG_FINALISE:
                $result = self::Finalise($command);   //  proces said that we had completed
                break;

            case CommandAction::$EXECUTION_FLAG_COMPLETE:
                $result = self::Completed($command);
                break;

        }

        
        if ($result instanceof ErrorMessage)  
        {
            echo $result;
            exit();
        }
        
        return $result;
        

    }

    /**
     * Command entes here at READY stage
     *
     * @param CommandAction $cmd
     * 
     * Changes ExecutionFlag  to  CommandAction::$EXECUTION_FLAG_RUNNING
     * 
     */
    private static function Ready(CommandAction &$cmd)
    {
        $cmd->ExecutionFlag(CommandAction::$EXECUTION_FLAG_RUNNING);
        
        
        $script_result = self::scriptIt(&$cmd);
        if ($script_result instanceof ErrorMessage)  
            return ErrorMessage::Stacked (__METHOD__,__LINE__,"Failed to get script to run ", true,$script_result); 
        
        $result = DatabaseCommands::CommandActionQueue($cmd);
        
        $d = DatabaseCommands::CommandActionRead($cmd->ID());

        
        if ($result instanceof ErrorMessage)  
            return ErrorMessage::Stacked (__METHOD__,__LINE__,"Can't update command action into Queue ".print_r($cmd,true), true,$result); 

        
        return $result;
        
    }

    
    /**
     * Check on Running job  $cmd->ExecutionFlag() ==  CommandAction::$EXECUTION_FLAG_RUNNING
     * 
     * Using ID check QSTAT to check in the job is still actually running 
     * 
     * Changes ExecutionFlag  to  CommandAction::$EXECUTION_FLAG_COMPLETE if Job is not in QSTAT 
     * 
     * @param CommandAction $cmd 
     */
    private static function Running(CommandAction $cmd)
    {

        // if you want to do something with a RUNNING JOB
        // DO IT HERE

        // WARNING that the job may also be updating the command file so try not to over write with $cmd

        // here we need to check to see if QSTAT has completed

        // if so then move JOB to FINALISED

        $queueID = $cmd->QueueID();
        
        
        $result = "Unknown";
        if (!is_null($queueID))
        {
            
            $result = exec("qstat -f {$cmd->QueueID()} | grep -e job_state");

            
            if (util::contains($result, "job_state"))
            {
                // appropriate update from qstat    job_state = R
                
                $split = explode("=",$result);
                if (count($split) == 2)
                {
                    if (trim($split[1]) == self::$QSTAT_COMPLETED)
                    {
                        $cmd->ExecutionFlag(CommandAction::$EXECUTION_FLAG_COMPLETE);
                        $result = DatabaseCommands::CommandActionQueue($cmd);
                        
                        if ($result instanceof ErrorMessage)  
                            return ErrorMessage::Stacked (__METHOD__,__LINE__,"Could not update Command to Queue", true,$result); 
                        
                    }
                    
                }

            }

        }

        
        $d = DatabaseCommands::CommandActionRead($cmd->ID());
        
        
        
        return $result;

    }

    /**
     * Command will be in FINALISED state if the the process being set it.
     * 
     * Changes ExecutionFlag  to  CommandAction::$EXECUTION_FLAG_COMPLETE 
     * 
     * @param CommandAction $cmd 
     */
    private static function Finalise(CommandAction $cmd)
    {
        $cmd->ExecutionFlag(CommandAction::$EXECUTION_FLAG_COMPLETE);
        DatabaseCommands::CommandActionQueue($cmd);
    }

    
    private static function Completed(CommandAction $cmd)
    {
        // delete if oder than 2 days 
        
        
    }
    

    /**
     * Generate script to be QSUB'ed 
     * QSUB can handle simple command line arguments, but too complex and it overruns the line length
     * 
     * and send generated script to QSUB
     * 
     * @param CommandAction $cmd 
     */
    private static function scriptIt(CommandAction &$cmd)
    {
        
        $script = self::generateScript($cmd);
        if ($script instanceof ErrorMessage) 
            return ErrorMessage::Stacked (__METHOD__,__LINE__,"Failed to create script for command ".print_r($cmd,true), true,$script); 
        
        
        $exe = self::executeScript( $script );
        if ($exe instanceof ErrorMessage) 
            return ErrorMessage::Stacked (__METHOD__,__LINE__,"Failed to execute script for command \nscript = [$script]", true,$script); 
        
        $cmd->QueueID($exe); // from executeScript

        
        
        return $exe;
        
    }


    /**
     * Create TCSH script that will be written to the filesystem to be sent to the GRID
     *
     * The actual command be run is "CommandActionExecute.php  <command id>"
     * 
     * 
     * @param iCommand $cmd
     */
    private static function generateScript(CommandAction $cmd)
    {
        
        // qsub shell script
        $script  = "#!/bin/bash\n";
        $script .= "# QSUB script from ".configuration::ApplicationName()."\n";
        $script .= "# Written to execute Command Action with id {$cmd->ID()} \n";
        $script .= "# datetime script written:".datetimeutil::NowDateTime()."\n";
        $script .= "php '".configuration::CommandScriptsExecutor()."' --command_id={$cmd->ID()}\n";
        $script .= "\n";

        $script_filename = configuration::CommandScriptsFolder().
                           configuration::CommandScriptsPrefix().
                           $cmd->ID().
                           configuration::CommandScriptsSuffix();
        
        
        $fpc = file_put_contents($script_filename, $script);  // write script to script_filename
        
        if ($fpc === FALSE) 
            return ErrorMessage::Stacked (__METHOD__,__LINE__,"Failed to write script for command script_filename = [{$script_filename}] \nscript = [$script]", true,$script); 
        
        
        return $script_filename;

    }

    /**
     * Execute script 
     * i.e. push script into QSUB queue and return qsub ID
     * 
     * @param string $scriptFilename
     * @return string Queue ID from QSTAT
     */
    private static function executeScript($scriptFilename)
    {
        $cmd = "cd ".configuration::CommandScriptsFolder()." ;  qsub '{$scriptFilename}'";
        $qsub_id = exec($cmd);  // will do QSUB exec and then get the return with the QSUB ID
        $firstBit = util::leftStr($qsub_id, ".");
        
        return $firstBit; // return job id - the rest is domain name
    }


}
?>
