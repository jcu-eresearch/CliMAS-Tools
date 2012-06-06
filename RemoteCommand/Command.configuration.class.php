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
class CommandConfiguration
{

    public static $LOCATION_PREFIX_WEBSERVER = "/www/eresearch/TDH-Tools/";
    public static $LOCATION_PREFIX_HPC       = "/home/jc166922/TDH-Tools/";

    private static function where()
    {
        $hostname = trim(exec("hostname --fqdn"));
        if (stripos( $hostname, "afakes-eresearch") !== FALSE) return self::$LOCATION_PREFIX_WEBSERVER;
        if (stripos( $hostname, "default.domain") !== FALSE) return self::$LOCATION_PREFIX_HPC;
        return null;
    }

    public static function ApplicationName() { return "TDH-TOOLS"; }

    public static function osPathDelimiter()      { return "/"; }
    public static function osExtensionDelimiter() { return ".";}

    public static function UtilityClasses() { return self::where()."Utilities/includes.php";}
    public static function FinderClasses()  { return self::where()."Search/Finder/Finder.includes.php";}
    public static function CommandClasses() { return self::where()."RemoteCommand/Command.includes.php";}
    public static function CommandClassesFolder() { return self::where()."RemoteCommand".self::osPathDelimiter() ;}

    public static function CommandQueueFolder() { return self::where()."queue";}
    public static function CommandQueueLog()    { return self::where()."queue.log";}
    public static function CommandExtension()   { return self::osExtensionDelimiter()."command";}

}


?>
