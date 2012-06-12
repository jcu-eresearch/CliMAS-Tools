<?php
class CommandFactory {

    /**
     * Called from Webserver to Queue a command
     *
     * @param Command $command
     */
    public static function Queue(iCommand $command)
    {
        CommandUtil::PutCommandToFile($command);
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
        $lookupID = ($command instanceof Command) ? $command->ID() : $command;

        $updatedCommand = CommandUtil::GetCommandFromID($lookupID,false); // Updated Command - something else will write and u[dated version of this object
        if (is_null($updatedCommand)) return "Failed to Read Command back from Server";  // todo: log
        
        $updatedCommand instanceof Command;
        return $updatedCommand->Status(); //

    }


    public static function QueueUpdateExecutionFlag(iCommand $command, $flag)
    {

        $lookupID = ($command instanceof iCommand) ? $command->ID() : $command;

        echo "Find command   lookupID = {$lookupID}\n";

        $updatedCommand = CommandUtil::GetCommandFromID($lookupID,false); // Updated Command - something else will write and u[dated version of this object

        if (is_null($updatedCommand)) return "Failed to Read Command back from Server";  // todo: log

        $updatedCommand instanceof iCommand;

        $updatedCommand->ExecutionFlag($flag);

        CommandUtil::PutCommandToFile($updatedCommand);

        return $updatedCommand->Status();

    }




    public static function CommandFromQueue($command)
    {
        $lookupID = ($command instanceof Command) ? $command->ID() : $command;

        $updatedCommand = CommandUtil::GetCommandFromID($lookupID,false); // Updated Command - something else will write and u[dated version of this object
        if (is_null($updatedCommand)) return null;  // todo: log

        if (!method_exists($updatedCommand, "ExecutionFlag"))
        {
            return null;
        }

        $updatedCommand instanceof Command;
        return $updatedCommand; //

    }

}

?>
