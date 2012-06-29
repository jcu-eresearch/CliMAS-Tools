<?php
class CommandUtil
{


    /**
     * Read command back from File
     * - 
     *
     * @param string $lookupID
     * @param bool $delete
     * @param bool $mustBeLatest - FALSE= if we have a ".previous" then read that one  TRUE =  must be absolute latest version of command (may cause conflicts) -
     * @return null|\iCommand
     */
    public static function GetCommandFromID($lookupID)
    {
        
        $ca = PG::ReadCommandAction($lookupID);
        
        if (is_null($ca)) return "Failed to Read Command back from Server during GetCommandFromID";  // todo: log
        $ca instanceof CommandAction;
        
        if (!method_exists($ca, "ExecutionFlag"))
        {
            return null;
        }
        
        return $ca;
        
    }


    public static function PutCommand(CommandAction $command)
    {
        return self::Queue($command);
    }

    
    /**
     * Called from Webserver to Queue a command
     *
     * @param Command $command
     */
    public static function Queue(CommandAction $command)
    {
        
        $writeID = PG::WriteCommandAction($command);
        if (is_null($writeID)) return null;
        
        return $command->ID();
    }


    /**
     * Used by Webserver  to get Current values of Object while it's out of Webserver control
     * - other system will serialize staes and certain times and reseralize the object
     * 
     * @param Command/String $command
     *
     * @return Command Updated with Status
     *
     */
    public static function QueueStatus($command)
    {
        return PG::CommandActionStatus($command->ID());;
    }


    public static function QueueUpdateExecutionFlag(CommandAction $command, $flag)
    {
        $command->ExecutionFlag($flag);

        $write = self::PutCommand($command);
        if (is_null($write)) return "Failed to write Command back to Server, trying to update execution flag";  // todo: log

        return $ca->ExecutionFlag();

    }

    public static function QueueUpdateExecutionFinalised(CommandAction $command)
    {
        return self::QueueUpdateExecutionFlag($command, CommandAction::$EXECUTION_FLAG_FINALISE);        
    }
    
    
    public static function QueueUpdateStatus(CommandAction $command, $status = null)
    {

        $command->Status($status);

        $write = self::PutCommand($command);
        if (is_null($write)) return "Failed to Rwrite Command back to Server, trying to QueueUpdateStatus";  // todo: log

        return $command->Status();

    }



    public static function CommandFromQueue($command)
    {
        
        $ca = PG::ReadCommandAction($command->ID());
        if (is_null($ca)) return "Failed to Read Command back from Server";  // todo: log
        $ca instanceof CommandAction;
        
        if (!method_exists($ca, "ExecutionFlag"))
        {
            return null;
        }
        
        return $ca;

    }

    
    
}

?>
