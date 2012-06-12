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
        if (stripos( $hostname, "afakes-eresearch") !== FALSE) 
        {
            return self::$LOCATION_PREFIX_WEBSERVER;
        }

        if (stripos( $hostname, "default.domain") !== FALSE) 
        {
            return self::$LOCATION_PREFIX_HPC;
        }

        return null;
    }

    private static function shareWhere()
    {

        switch (self::where())
        {
            case self::$LOCATION_PREFIX_HPC:       return "/home/jc166922/TDH-Tools/"; break;
            case self::$LOCATION_PREFIX_WEBSERVER: return "/data/dmf/TDH-Tools/"; break;
        }

        return null;
    }


    public static function ApplicationName() { return "TDH-TOOLS"; }

    public static function osPathDelimiter()      { return "/"; }
    public static function osExtensionDelimiter() { return ".";}

    public static function osFileRemove() { return "rm ";}  // OS command use to remove a file


    public static function UtilityClasses() { return self::where()."Utilities/includes.php";}
    public static function FinderClasses()  { return self::where()."Search/Finder/Finder.includes.php";}
    public static function CommandClasses() { return self::where()."RemoteCommand/Command.includes.php";}
    public static function CommandClassesFolder() { return self::where()."RemoteCommand".self::osPathDelimiter() ;}


    // This is where the web server can place a file and it will end up on the HPC
    // at the moment we have a mounted NFS drive
    
    public static function CommandQueueFolder() { return self::shareWhere()."queue";}
    public static function CommandQueueLog()    { return self::shareWhere()."queue.log";}
    public static function CommandExtension()   { return self::osExtensionDelimiter()."command";}

    // where the scripts to be executed by QSUB will be held
    public static function CommandScriptsFolder() { return self::shareWhere()."scripts/";}


    // Where the output of actions are to go to be available to webserver
    // this needs to match - configuration::FilesDownloadFolder()
    public static function CommandOutputsFolder() { return self::shareWhere()."outputs/";}


    // the name of the php script that will execute commands
    public static function CommandScriptsExecutor() { return self::CommandClassesFolder()."CommandActionExecute.php";}

    // Prefix name of scripts with this - so in  a QSTAT they will be obvious
    public static function CommandScriptsPrefix() { return self::ApplicationName()."_";}

    // Exetnsion for Command Action Shell Scripts
    public static function CommandScriptsSuffix() { return ".sh";}




}


?>
