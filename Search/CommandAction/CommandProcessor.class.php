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
        
        echo "Reading queue for ".configuration::CommandQueueID()."\n";
        
        for ($index = 1; $index <= 20; $index++)
        {
            foreach (PG::CommandActionListIDs() as $commandID) 
                self::processSingleQueueItem($commandID);
            
            sleep(3);
        }

    }

    
    
    /**
     *
     * Read Single command file
     * - unserailize and action command based on it's "state" ExecutionFlag (ref: CommandAction statics)
     * 
     * @param string $filepath
     * @return null null
     */
    private function processSingleQueueItem($commandID)
    {

        $command = CommandUtil::GetCommandFromID($commandID);

        if (is_null($command))
        {
            // echo datetimeutil::now()."checking $filepath\nCommand was NULL\n\n";
            return; // todo:: Log as exception /??
        }

         echo "\n".$command->ID()."  == ".$command->ExecutionFlag()." .. ".$command->Status();

        if (!($command instanceof CommandAction))
        {
            return null; // todo:: Log as exception /??
        }

        switch ($command->ExecutionFlag()) {
            case CommandAction::$EXECUTION_FLAG_READY:
                self::Ready($command);
                break;

            case CommandAction::$EXECUTION_FLAG_RUNNING:
                self::Running($command);
                break;

            case CommandAction::$EXECUTION_FLAG_QUEUE_DONE:
                self::Finalise($command);   //  the QSUB Queue said it was completd
                break;

            case CommandAction::$EXECUTION_FLAG_FINALISE:
                self::Finalise($command);   //  proces said that we had completed
                break;

            case CommandAction::$EXECUTION_FLAG_COMPLETE:
                // Do nothingfs
                break;

        }

        

    }

    /**
     * Command entes here at READY stage
     *
     * @param CommandAction $cmd
     * 
     * Changes ExecutionFlag  to  CommandAction::$EXECUTION_FLAG_RUNNING
     * 
     */
    private static function Ready(CommandAction $cmd)
    {
        $cmd->ExecutionFlag(CommandAction::$EXECUTION_FLAG_RUNNING);
        self::scriptIt($cmd);
        CommandUtil::PutCommand($cmd);
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

            echo "Check ing $queueID \n";
        
        $result = "Unknown";
        if (!is_null($queueID))
        {
            
            $firstBit = util::leftStr($queueID, ".");
            $result = exec("qstat -f $firstBit | grep -e job_state");

            if (util::contains($result, "job_state"))
            {
                // appropriate update from qstat    job_state = R
                
                $split = explode("=",$result);
                if (count($split) == 2)
                {
                    if (trim($split[1]) == self::$QSTAT_COMPLETED)
                    {
                        $cmd->ExecutionFlag(CommandAction::$EXECUTION_FLAG_COMPLETE);
                        CommandUtil::PutCommand($cmd);
                        
                        // echo "\nQueue said job is finished= \n".$result."\n\n";
                    }
                    
                }

            }

        }


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
        CommandUtil::PutCommand($cmd);
    }


    /**
     *
     * For debugging to see progess and state of command as it flows thru
     * 
     * @param CommandAction $command
     * @param type $msg 
     */
    private static function Log(CommandAction $command,$msg)
    {
        $log  = $command->LastUpdated().",";
        $log .= $command->ID().",";
        $log .= $command->ExecutionFlag().",";
        $log .= $command->LocationName().",";
        $log .= $command->Status().",";
        $log .= $command->Result().",";
        $log .= $msg;
        $log .= "\n";

        file_put_contents(configuration::CommandQueueLog(),$log , FILE_APPEND);

        echo "$log";

    }


    /**
     * Generate script to be QSUB'ed 
     * QSUB can handle simple command line arguments, but too complex and it overruns the line length
     * 
     * and send generated script to QSUB
     * 
     * @param CommandAction $cmd 
     */
    private static function scriptIt(CommandAction $cmd)
    {
        $cmd->QueueID(self::executeScript( self::generateScript($cmd) ) ); // from executeScript
        
    }


    /**
     * Create TCSH script that will be written to the filesystem to be sent to the GRID
     *
     * The actaul command be run is "CommandActionExecute.php  <command id>"
     * 
     * 
     * @param iCommand $cmd
     */
    private static function generateScript(CommandAction $cmd)
    {

        // qsub shell script
        $script  = "";
        $script .= "# QSUB script from ".configuration::ApplicationName()."\n";
        $script .= "# Written to execute Command Action with id {$cmd->ID()} \n";


        // echo "cmd is class ".get_class($cmd)."\n";

        if ($cmd instanceof CommandAction)
        {
            // echo "cmd can be  class CommandAction\n";

            $obj = $cmd;
            $obj instanceof CommandAction;
            $script .= "#\n";
            $script .= "# Command Object Values\n";
            $strs = explode("\n",$obj->__toString());

            foreach ($strs as $str)
                $script .= "# ".trim($str)."\n";

        }


        $script .= "# \n";
        $script .= "# datetime script written:".datetimeutil::NowDateTime()."\n";
        $script .= "\n";

        $script .= "php '".configuration::CommandScriptsExecutor()."'  {$cmd->ID()}";
        $script .= "\n";

        $script_filename = configuration::CommandScriptsFolder().
                           configuration::CommandScriptsPrefix().
                           $cmd->ID().
                           configuration::CommandScriptsSuffix();

        echo "script_filename = $script_filename\n";
        
        $script .= "rm {$script_filename}\n"; // script will remove it self when done

        file_put_contents($script_filename, $script);  // write script to script_filename
        
        
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
        
        // exec("chmod u+x '{$scriptFilename}'"); // may not be needed
        
        $cmd = "cd ".configuration::CommandScriptsFolder()." ;  qsub {$scriptFilename}";
        
        echo "cmd {$cmd}\n";
        
        $qsub_id = exec($cmd);  // will do QSUB exec and then get the return with the QSUB ID
        return $qsub_id;

    }


}
?>
