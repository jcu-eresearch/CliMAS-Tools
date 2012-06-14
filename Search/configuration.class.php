<?php
/**
 * System configuration and default values
 *
 * TODO:: Needs to com from Config file that is specific to HOstname
 *
 */
class configuration {

    //spatialecology.jcu.edu.au
    // /var/www/html/bioclimdata


    public static function ApplicationName() { return "TDH-TOOLS"; }


    private static function where()
    {
        $hostname = trim(exec("hostname --fqdn"));
        if (stripos( $hostname, "afakes-eresearch") !== FALSE) return "/www/eresearch/TDH-Tools/";
        if (stripos( $hostname, "default.domain") !== FALSE) return "/home/ctbccr/spatialecology_interchange/app/";
        if (stripos( $hostname, "spatialecology.jcu.edu.au") !== FALSE) return "/var/www/html/bioclimdata/";

        return null;
    }


    public static function UtilityClasses() {
        return self::where() . "Utilities/includes.php";
    }

    public static function CommandClasses() {
        return self::where() . "RemoteCommand/Command.includes.php";
    }

    public static function CommandClassesFolder() {
        return self::where() . "RemoteCommand" . self::osPathDelimiter();
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

        // web accessabkle version for below
        if (stripos( $hostname, "spatialecology.jcu.edu.au") !== FALSE) return "/download/";

        return null;
    }

    /**
     * Filesystem  buddy to WebDownloadFolder
     * @return string|null  Filepath
     */
    public static function FilesDownloadFolder()
    {
        $hostname = trim(exec("hostname --fqdn"));
        if (stripos( $hostname, "afakes-eresearch") !== FALSE) return "/data/dmf/TDH-Tools/outputs/";
        if (stripos( $hostname, "default.domain")   !== FALSE) return "/home/ctbccr/spatialecology_interchange/outputs/";
        
        // make this availabe to the web server and
        if (stripos( $hostname, "spatialecology.jcu.edu.au") !== FALSE) return "/home/ctbccr/bioclimdata/data/interchange/output/";

        return null;
    }


    public static function ResourcesFolder()
    {

        $hostname = trim(exec("hostname --fqdn"));
        if (stripos( $hostname, "afakes-eresearch") !== FALSE) return "/www/eresearch/TDH-Tools/Resources/";
        if (stripos( $hostname, "default.domain") !== FALSE) return "/home/jc166922/TDH-Tools/Resources/";
        if (stripos( $hostname, "spatialecology.jcu.edu.au") !== FALSE) return "/var/www/html/bioclimdata/Resources/";

        return null;

    }

    public static function Descriptions_ClimateModels() {
        return self::ResourcesFolder() . "descriptions/gcm.csv";
    }

    public static function Descriptions_EmissionScenarios() {
        return self::ResourcesFolder() . "descriptions/scenario.csv";
    }

    public static function Descriptions_Years() {
        return self::ResourcesFolder() . "descriptions/year.txt";
    }

    // web paath to ICONS
    public static function IconSource() {

        $hostname = trim(exec("hostname --fqdn"));
        if (stripos( $hostname, "afakes-eresearch") !== FALSE) return "/eresearch/TDH-Tools/Resources/icons/";
        if (stripos( $hostname, "spatialecology.jcu.edu.au") !== FALSE) return "/bioclimdata/Resources/icons/";

        return "";

    }


    public static function SourceDataFolder() {

        $hostname = trim(exec("hostname --fqdn"));
        if (stripos( $hostname, "afakes-eresearch") !== FALSE) return "/www/eresearch/TDH-Tools/source/";
        if (stripos( $hostname, "default.domain") !== FALSE) return "/home/jc166922/TDH-Tools/source/";
        if (stripos( $hostname, "spatialecology.jcu.edu.au") !== FALSE) return "/var/www/html/bioclimdata/Resources/source/";

        return null;
    }

    public static function ContextSpatialLayersFolder()
    {
        return self::SourceDataFolder() . "context" . self::osPathDelimiter();
    }

    public static function DefaultMapableActionClassname() { return "ContextLayerAustralianStates"; }
    public static function MapableBackgroundLayers() { return "ContextLayerMapableBackgroundLayers"; }

    public static function osPathDelimiter()      { return "/"; }
    public static function osExtensionDelimiter() { return ".";}

}

?>


