<?php
class CommandUtil
{

    /**
     * Based on the destination of the command we choose how to send it there
     *
     * @param Command $cmd
     */
    public function Transfer(Command $cmd)
    {


    }

    /**
     * When called takes Command and updates "Table ? // file on disk " - this will depend on Destination
     * - As "Local Destinations"  - may be some whay of checking on status - file size / db table ...
     *
     * @param Command $cmd
     */
    public function StatusUpdate(Command $cmd)
    {


    }


    public function StatusSend(Command $cmd)
    {


    }

    public function StatusGet(Command $cmd)
    {


    }

    /**
     * Convert file back into a Command Object
     * - use this to find the appropriate class to handle it.
     *
     * @param string $filename Local file name on this system
     */
    public static function GetCommandFromFile($filename,$delete = true)
    {
        if (!file_exists($filename)) return null; // todo Log this

        $file = file_get_contents($filename);
        $object = unserialize($file);

        if ($delete) file::Delete($filename);

        $object instanceof Command;

        return $object;
    }

    public static function GetCommandFromID($lookupID,$delete = true)
    {
        $filename = CommandConfiguration::CommandQueueFolder().CommandConfiguration::osPathDelimiter().$lookupID.CommandConfiguration::CommandExtension();

        if (!file_exists($filename)) return null; // todo Log this
        $file = file_get_contents($filename);
        $object = unserialize($file);

        if ($delete) file::Delete($filename);

        return $object;
    }



    public static function PutCommandToFile(Command $command)
    {
        $fn = CommandConfiguration::CommandQueueFolder().CommandConfiguration::osPathDelimiter().$command->ID().CommandConfiguration::CommandExtension();
        if (file_exists($fn)) file::Delete($fn);
        
        $ser = serialize($command);
        file_put_contents($fn,$ser);
        return $fn;
    }




}

?>
