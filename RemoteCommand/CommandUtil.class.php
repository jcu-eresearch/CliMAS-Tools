<?php
class CommandUtil
{

    /**
     * Convert file back into a Command Object
     * - use this to find the appropriate class to handle it.
     *
     * @param string $filename Local file name on this system
     */
    public static function GetCommandFromFile($filename,$delete = true)
    {

        if (!file_exists($filename)) return null; // todo Log this

        if (filesize($filename) == 0) return null;;

        $file = file_get_contents($filename);
        if ($file == "") return null;


        $object = unserialize($file);

        if ($delete) file::Delete($filename);

        // todo:: check that $object is  actually instanceof iCommand;

        $object instanceof iCommand;

        return $object;
    }

    /**
     * Read command back from File
     * - 
     *
     * @param string $lookupID
     * @param bool $delete
     * @param bool $mustBeLatest - FALSE= if we have a ".previous" then read that one  TRUE =  must be absolute latest version of command (may cause conflicts) -
     * @return null|\iCommand
     */
    public static function GetCommandFromID($lookupID,$delete = true,$mustBeLatest = false)
    {
        
        // check to see if we have a previous versio  - only reason for this will be when 
        // something else is updatiung the command
        // means we get the last status written (not the current)
        if (!$mustBeLatest)
        {
            // check for previous
            $prev_command_filename = self::CommandFilenamePrevious($lookupID);
            if (file_exists($prev_command_filename))
            {
                $object = unserialize(file_get_contents($prev_command_filename));
                $object instanceof iCommand;
                return $object;  // We have a previous command so lets use it instead - this will save on race condictions
            }

        }

        
        // We don't have a previous command or they have chosen to ignore it.

        if (!file_exists(self::CommandFilename($lookupID))) return null; // todo Log this

        try {
            $file = file_get_contents(self::CommandFilename($lookupID));
        } catch (Exception $e) {
            sleep(1);
            $file = file_get_contents(self::CommandFilename($lookupID));  // hopefully after 1 second it will exists again
        }

        $object = unserialize($file);

        if ($delete) file::Delete(self::CommandFilename($lookupID));

        // todo:: check that $object is  actually instanceof iCommand;

        $object instanceof iCommand;

        return $object;
    }




    public static function PutCommandToFile(iCommand $command)
    {

        // save current condition to a previous - if someone checks on status while
        // we are riting this one then they get previous version as per above
        // copy current to previous

        $fn = self::CommandFilename($command->ID());
        
        // make previous version if we can 
        if (file_exists($fn))
            file::copy($fn, self::CommandFilenamePrevious($command->ID()), true);
        else
        

        if (file_exists($fn)) file::Delete($fn);

        $command->LastUpdated(datetimeutil::now());
        $ser = serialize($command);
        file_put_contents($fn,$ser);

        // finished writing to command so we can remove previous
        file::Delete(self::CommandFilenamePrevious($command->ID()));

        return $fn;
    }


    public static function CommandFilename($commandID)
    {
        $fn = CommandConfiguration::CommandQueueFolder().
              CommandConfiguration::osPathDelimiter().
              $commandID.CommandConfiguration::CommandExtension();

        return $fn;
    }

    public static function CommandFilenamePrevious($commandID)
    {
        $fn = CommandConfiguration::CommandQueueFolder().
              CommandConfiguration::osPathDelimiter().
              $commandID.
              CommandConfiguration::CommandExtension().
              ".previous";

        return $fn;
    }

}

?>
