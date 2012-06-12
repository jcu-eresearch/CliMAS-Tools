<?php
/**
 * System configuration and default values
 *
 * TODO:: Needs to com from Config file that is specific to HOstname
 *
 */

class configuration {

    public static $LOCATION_PREFIX_WEBSERVER = "/www/eresearch/TDH-Tools/";
    public static $LOCATION_PREFIX_HPC       = "/home/jc166922/TDH-Tools/";

    private static function where()
    {
        $hostname = trim(exec("hostname --fqdn"));
        if (stripos( $hostname, "afakes-eresearch") !== FALSE) return self::$LOCATION_PREFIX_WEBSERVER;
        if (stripos( $hostname, "default.domain") !== FALSE) return self::$LOCATION_PREFIX_HPC;
        return null;
    }

    /**
     * Path to Downloads folder accessable from the web
     * @return string|null Filepath
     */
    public static function WebDownloadFolder()
    {
        $hostname = trim(exec("hostname --fqdn"));
        if (stripos( $hostname, "afakes-eresearch") !== FALSE) return "/outputs/";
        if (stripos( $hostname, "default.domain")   !== FALSE) return "/outputs/";
        return null;
    }

    /**
     * Filesystem buddy to WebDownloadFolder
     * @return string|null  Filepath
     */
    public static function FilesDownloadFolder()
    {
        $hostname = trim(exec("hostname --fqdn"));
        if (stripos( $hostname, "afakes-eresearch") !== FALSE) return "/data/dmf/TDH-Tools/outputs/";
        if (stripos( $hostname, "default.domain")   !== FALSE) return "/home/jc166922/TDH-Tools/outputs/";
        return null;
    }

    public static function ApplicationName() { return "TDH-TOOLS"; }

    public static function osPathDelimiter()      { return "/"; }
    public static function osExtensionDelimiter() { return ".";}

    public static function UtilityClasses() { return self::where()."Utilities/includes.php";}
    public static function CommandClasses() { return self::where()."RemoteCommand/Command.includes.php";}
    public static function CommandClassesFolder() { return self::where()."RemoteCommand".self::osPathDelimiter() ;}

    public static function CommandQueueFolder() { return self::where()."queue".self::osPathDelimiter();}
    public static function CommandQueueLog()    { return self::where()."queue".self::osExtensionDelimiter()."log";}
    public static function CommandExtension()   { return self::osExtensionDelimiter()."command";}

    public static function SourceDataFolder() {return self::where()."source".self::osPathDelimiter();}

                                                        ///www/eresearch/TDH-Tools/source/context/

    public static function ContextSpatialLayersFolder() {return self::SourceDataFolder()."context".self::osPathDelimiter();}



    public static function DefaultMapableActionClassname() { return "ContextLayerAustralianStates"; }

    public static function MapableBackgroundLayers() { return "ContextLayerMapableBackgroundLayers"; }


    // web paath to ICONS
    public static function IconSource() { return "/eresearch/TDH-Tools/Resources/icons/"; }

    public static function Descriptions_ClimateModels()     {return self::SourceDataFolder()."descriptions/gcm.csv";}
    public static function Descriptions_EmissionScenarios() {return self::SourceDataFolder()."descriptions/scenario.csv";}
    public static function Descriptions_Years()             {return self::SourceDataFolder()."descriptions/year.txt";}

}

?>


