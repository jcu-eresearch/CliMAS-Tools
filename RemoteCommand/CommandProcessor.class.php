<?php
include_once 'Command.includes.php';

/**
 * Process Commands in Queue and do soemthig with them
 * Start, Status, Complete
 *
 */
class CommandProcessor 
{

    public static function ProcessQueue()
    {
        for ($index = 1; $index <= 20; $index++)
        {
            $files = file::find_files(CommandConfiguration::CommandQueueFolder(),CommandConfiguration::CommandExtension()); // find command files to process

            foreach ($files as $filepath) self::processSingleQueueItem($filepath);
            
            sleep(3);
        }

    }


    private function processSingleQueueItem($filepath)
    {

        $command = CommandUtil::GetCommandFromFile($filepath,false);

        if (is_null($command))
        {
            echo datetimeutil::now()."checking $filepath\nCommand was NULL\n\n\n";
            return; // todo:: Log as exception /??
        }

        // for an Individual Command

        // update the Command with date and time
        $command->LastUpdated(datetimeutil::now());


        switch ($command->ExecutionFlag()) {
            case Command::$EXECUTION_FLAG_READY:
                self::Ready($command);
                CommandUtil::PutCommandToFile($command);
                break;

            case Command::$EXECUTION_FLAG_RUNNING:
                self::Running($command);
                CommandUtil::PutCommandToFile($command);
                break;

            case Command::$EXECUTION_FLAG_TIMEOUT:
                self::Timeout($command);
                CommandUtil::PutCommandToFile($command);
                break;

            case Command::$EXECUTION_FLAG_FINALISE:
                self::Finalise($command); // if we read in the file and it was already complete do nothing - this is jst waiting for the other server to read it
                CommandUtil::PutCommandToFile($command);
                break;

            case Command::$EXECUTION_FLAG_COMPLETE:
                // Do nothing
                break;

        }


    }



    private static function Ready(Command $cmd)
    {
        $cmd->Status("Execute Action".$cmd->ActionName());
        $cmd->ExecutionFlag(Command::$EXECUTION_FLAG_RUNNING);
        $cmd->Result(0);

        self::Log($cmd,"start here");

        // launch a qsub job for this action
        // a qsub job will write a script file out to be run 
        // and then return back a qsub ID that can be use to track the job
        // though QSTAT - so maybe we can give the QSTAT  id to COmmand and save it
        
        // then with running we can read the STAT line for that ID and 
        // return that into the status update

        // a complete will be if we see qstat qith a C for Cancelling
        // or we don't see that job in qstat any more

        // once the qsub job is finished then we finish our job and svane the command
        // and what of thew output of that Action
        // depeneds on the action to what the output is

        // will most likley be a file list or a serialized Object that
        // will give details of where to what file needs to be downloaded.



    }

    private static function Running(Command $cmd)
    {
        $cmd->Status("Running some action ".$cmd->ActionName());



        
        // for testing - this should loop 20 or so times and then set to complete
        $res = $cmd->Result() + 1;
        $cmd->Result($res);

        $cmd->Status("Running Current res = {$res}"." ".$cmd->ExecutionFlag());

        if ($cmd->Result() >= 20) $cmd->ExecutionFlag(Command::$EXECUTION_FLAG_FINALISE);

    }

    private static function Finalise(Command $cmd)
    {
        $cmd->Status("Have finished this action  ".$cmd->ActionName());

        $cmd->Result("Result now == ".$cmd->Result()." ".Command::$EXECUTION_FLAG_COMPLETE);

        $cmd->ExecutionFlag(Command::$EXECUTION_FLAG_COMPLETE);

    }

    private static function Timeout(Command $cmd)
    {

    }

    private static function Log(Command $command,$msg)
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


}
?>
