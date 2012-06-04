<?php
/**
 * System configuration and default values
 *
 * TODO:: Needs to com from Config file that is specific to HOstname
 *
 *  Have to create Hostname based Config lookup - so we can get the correct folder details based on what server the process is running from
 *
 *
 */
class CommandConfiguration {

    public static function osPathDelimiter()  { return "/"; }
    public static function osExtensionDelimiter() { return "."; }
    public static function ApplicationName() {return "TDH-TOOLS";}
    public static function UtilityClasses() {return "/home/jc166922/TDH-Tools/Utilities";}
    public static function FinderClasses() {return "/home/jc166922/TDH-Tools/Search/Finder/Finder.includes.php";}



    /**
     * Where we will look for commands to be run
     * @return string
     */
    public static function QueueFolder() {return "/data/dmf/TDH-Tools/queue";}

    public static function CommandExtension() {return self::osExtensionDelimiter()."command";}

    public static function CommandQueueFolder() {return "/data/dmf/TDH-Tools/queue";}

    public static function CommandQueueLog() {return "/data/dmf/TDH-Tools/queue.log";}

    

    /*
     *  Location where jobs that require high numbers of paralelling processing
     *  @var string Location Name
     */
    public static $LOCATION_HPC = "HIGH-PROCESSOR";


    /**
     * Process running "locally" on web server
     * @var string Location Name
     */
    public static $LOCATION_WEBSERVER = "WEBSERVER";

    /**
     * Processing running on Database
     * @var string Location Name
     */
    public static $LOCATION_DATABASE = "DATABASE";

}

?>
