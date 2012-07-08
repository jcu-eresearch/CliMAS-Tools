<?php
class CommandUtil
{
    
    /**
     * Called from Webserver to Queue a command
     *
     * @param Command $command
     */
    public static function Queue(CommandAction $command)
    {
        return PGDB::CommandActionQueue($command);
    }


    public static function CommandFromQueue($id)
    {
        return PGDB::CommandActionRead($id);
    }
    
    
    public static function QueueUpdateExecutionFlag(CommandAction $command, $flag)
    {
        $command->ExecutionFlag($flag);
        return PGDB::CommandActionQueue($command);
    }

    
    public static function QueueUpdateExecutionFinalised(CommandAction $command)
    {
        return self::QueueUpdateExecutionFlag($command, CommandAction::$EXECUTION_FLAG_FINALISE);        
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
        return PGDB::CommandActionStatus($command);
    }
    
    
    
    public static function QueueUpdateStatus(CommandAction $command, $status = null)
    {
        $command->Status($status);
        return PGDB::QueueCommandAction($command);        
    }


    
    
}
?>