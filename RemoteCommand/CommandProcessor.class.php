<?php
include_once 'Command.includes.php';

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


    public static function ProcessQueue()
    {



        for ($index = 1; $index <= 20; $index++)
        {
            $files = file::find_files(CommandConfiguration::CommandQueueFolder(),CommandConfiguration::CommandExtension()); // find command files to process
            $files = file::arrayFilterOut($files, "previous"); // if there are previous files here ignore them

            foreach ($files as $filepath)
                self::processSingleQueueItem($filepath);
            
            sleep(3);
        }

    }


    private function processSingleQueueItem($filepath)
    {
        $command = CommandUtil::GetCommandFromFile($filepath,false);
        if (is_null($command))
        {
            // echo datetimeutil::now()."checking $filepath\nCommand was NULL\n\n";
            return; // todo:: Log as exception /??
        }

        // echo "\n".$command->ID()."  == ".$command->ExecutionFlag()." .. ".$command->Status();

        switch ($command->ExecutionFlag()) {
            case Command::$EXECUTION_FLAG_READY:
                self::Ready($command);
                break;

            case Command::$EXECUTION_FLAG_RUNNING:
                self::Running($command);
                break;

            case Command::$EXECUTION_FLAG_TIMEOUT:
                self::Timeout($command);
                break;

            case Command::$EXECUTION_FLAG_QUEUE_DONE:
                self::Finalise($command);   //  the Queue said it was completd
                break;

            case Command::$EXECUTION_FLAG_FINALISE:
                self::Finalise($command);   //  proces said that we had completed
                break;

            case Command::$EXECUTION_FLAG_COMPLETE:
                // Do nothing
                break;

        }

        CommandUtil::PutCommandToFile($command);

    }

    /**
     * Command eneters here should be at READY stage
     *
     *
     * @param iCommand $cmd
     *
     * @return mixed COmmand stage at RUNNING
     *
     */
    private static function Ready(iCommand $cmd)
    {
        $cmd->ExecutionFlag(Command::$EXECUTION_FLAG_RUNNING);
        self::scriptIt($cmd);
    }

    private static function Running(iCommand $cmd)
    {
        // echo "Checking on Action ".$cmd->ActionName()."  ".$cmd->Status()."\n";

        // if you want to do something with a RUNNING JOB
        // DO IT HERE

        // WARNING that the job may also be updating the command file so try not to over write with $cmd

        // here we need to check to see if QSTAT has completed

        // if so then move JOB to FINALISED

        $queueID = $cmd->QueueID();

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
                        $cmd->ExecutionFlag(Command::$EXECUTION_FLAG_COMPLETE);

                        // echo "\nQueue said job is finished= \n".$result."\n\n";
                    }
                    
                }

            }

        }


    }

    private static function Finalise(iCommand $cmd)
    {
        $cmd->ExecutionFlag(Command::$EXECUTION_FLAG_COMPLETE);
    }

    private static function Timeout(iCommand $cmd)
    {

    }

    private static function Log(iCommand $command,$msg)
    {
        $log  = $command->LastUpdated().",";
        $log .= $command->ID().",";
        $log .= $command->ExecutionFlag().",";
        $log .= $command->LocationName().",";
        $log .= $command->Status().",";
        $log .= $command->Result().",";
        $log .= $msg;
        $log .= "\n";

        file_put_contents(CommandConfiguration::CommandQueueLog(),$log , FILE_APPEND);

        echo "$log";

    }


    private static function scriptIt(iCommand $cmd)
    {
        $cmd->QueueID(self::executeScript( self::generateScript($cmd) ) ); // from executeScript

    }


    /**
     * create shell script with  "CommandActionExecute.php  <command id>"
     *
     * @param iCommand $cmd
     */
    private static function generateScript(iCommand $cmd)
    {

        // qsub shell script
        $script  = "";
        $script .= "# QSUB script from ".CommandConfiguration::ApplicationName()."\n";
        $script .= "# Written to execute Command Action with id {$cmd->ID()} \n";
        $script .= "# this file will usually only exist if it's associated command exists (".CommandUtil::CommandFilename($cmd->ID()).")\n";


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

        $script .= "php '".CommandConfiguration::CommandScriptsExecutor()."'  {$cmd->ID()}";
        $script .= "\n";

        $script_filename = CommandConfiguration::CommandScriptsFolder().
                           CommandConfiguration::CommandScriptsPrefix().
                           $cmd->ID().
                           CommandConfiguration::CommandScriptsSuffix();


        $script .= CommandConfiguration::osFileRemove()." {$script_filename}\n"; // script will remove it self when done

        file_put_contents($script_filename, $script);  // write script to script_filename

        return $script_filename;

    }

    private static function executeScript($scriptFilename)
    {
        exec("chmod u+x {$scriptFilename}"); // may not be needed
        $qsub_id = exec("qsub {$scriptFilename}");  // will do QSUB exec and then get the return with the QSUB ID
        return $qsub_id;

    }


}
?>
