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


    public static function ApplicationName() { return "TDH-TOOLS"; }


    /**
     * Where is the Application folder
     *
     * @return string|null
     */
    private static function where()
    {
        $hostname = trim(exec("hostname --fqdn"));
        if (stripos( $hostname, "afakes-eresearch") !== FALSE) return "/www/eresearch/TDH-Tools/";
        if (stripos( $hostname, "default.domain") !== FALSE) return "/home/jc166922/TDH-Tools/";
        if (stripos( $hostname, "spatialecology.jcu.edu.au") !== FALSE) return "/var/www/html/bioclimdata";

        return null;

    }

    public static function CommandQueueFolder()
    {

        $hostname = trim(exec("hostname --fqdn"));
        if (stripos( $hostname, "afakes-eresearch") !== FALSE) return "/data/dmf/TDH-Tools/queue/";
        if (stripos( $hostname, "default.domain") !== FALSE) return "/home/jc166922/TDH-Tools/queue/";
        if (stripos( $hostname, "spatialecology.jcu.edu.au") !== FALSE) return "/data/dmf/TDH-Tools/queue/";

        return null;

    }

    public static function CommandQueueLog()
    {
        $hostname = trim(exec("hostname --fqdn"));
        if (stripos( $hostname, "afakes-eresearch") !== FALSE) return "/data/dmf/TDH-Tools/queue.log";
        if (stripos( $hostname, "default.domain") !== FALSE) return "/home/jc166922/TDH-Tools/queue.log";
        if (stripos( $hostname, "spatialecology.jcu.edu.au") !== FALSE) return "/data/dmf/TDH-Tools/queue.log";

        return null;

    }


    // where the scripts to be executed by QSUB will be held
    public static function CommandScriptsFolder()
    {
        $hostname = trim(exec("hostname --fqdn"));
        if (stripos( $hostname, "afakes-eresearch") !== FALSE) return "/data/dmf/TDH-Tools/scripts/";
        if (stripos( $hostname, "default.domain") !== FALSE) return "/home/jc166922/TDH-Tools/scripts/";
        if (stripos( $hostname, "spatialecology.jcu.edu.au") !== FALSE) return "/data/dmf/TDH-Tools/scripts/";

        return null;

    }

    // Where the output of actions are to go to be available to webserver
    // this needs to match - configuration::FilesDownloadFolder()
    public static function CommandOutputsFolder()
    {
        $hostname = trim(exec("hostname --fqdn"));
        if (stripos( $hostname, "afakes-eresearch") !== FALSE) return "/data/dmf/TDH-Tools/outputs/";
        if (stripos( $hostname, "default.domain") !== FALSE) return "/home/jc166922/TDH-Tools/outputs/";
        if (stripos( $hostname, "spatialecology.jcu.edu.au") !== FALSE) return "/data/dmf/TDH-Tools/outputs/";

        return null;

    }



    public static function UtilityClasses() {
        return self::where() . "Utilities/includes.php";
    }

    public static function FinderClasses() {
        return self::where() . "Search/Finder/Finder.includes.php";
    }

    public static function CommandClasses() {
        return self::where() . "RemoteCommand/Command.includes.php";
    }

    public static function CommandClassesFolder() {
        return self::where() . "RemoteCommand" . self::osPathDelimiter();
    }


    public static function CommandExtension() {
        
        return self::osExtensionDelimiter() . "command";
    }


    // the name of the php script that will execute commands
    public static function CommandScriptsExecutor() {
        return self::CommandClassesFolder() . "CommandActionExecute.php";
    }

    // Prefix name of scripts with this - so in  a QSTAT they will be obvious
    public static function CommandScriptsPrefix() {
        return self::ApplicationName() . "_";
    }

    // Exetnsion for Command Action Shell Scripts
    public static function CommandScriptsSuffix() {
        return ".sh";
    }

    public static function osPathDelimiter()      { return "/"; }
    public static function osExtensionDelimiter() { return ".";}

    public static function osFileRemove() { return "rm ";}  // OS command use to remove a file


}


?>
