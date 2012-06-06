<?php
class CommandFactory {

    /**
     * Called from Webserver to Queue a command
     *
     * @param Command $command
     */
    public static function Queue(Command $command)
    {   
        $filename = CommandUtil::PutCommandToFile($command);

        $command->Tags("fn",$filename);

        $command->Result("Queued to file ".$filename);
    }


    /**
     * Used by Webserver to get Current values of Object while it's out of Webserver control
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

        $updatedCommand->Result("<br>Status from File ???????");

        $updatedCommand instanceof Command;
        return $updatedCommand; // 
    }




    public static function Find($commandName)
    {


        
    }



    public static function Execute(Command $command)
    {
        
        // look up and retive list of commands to be executed
        
        // unserialize objects (a Command)
        
        // find Action for this command
        
        // instantite action Object
        
        // set source of action to the command
        
        // execute Action
        
        
        // take Result from Action back to commnand
        
        // serialize command
        
        // send command back to caller  (Web server) - 
        
        
        $command->Result("Executed");
        
    }




    private static function GetAction(Command $cmd)
    {
        $action = FinderFactory::Find($cmd->ActionName());
        $action instanceof Action;
        return $action;
    }






}

?>
