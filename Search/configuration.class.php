<?php
/*
 * System configuration and default values
 */
class configuration {

    private static $PATH_ROOT = "/www/eresearch/TDH-Tools";


    public static $TRUE  = "TRUE";
    public static $FALSE = "FALSE";
    
    public static function ApplicationName() {return "SRD::";}
    
    public static $SEARCH_PHP = "/www/eresearch/TDH-Tools/Search/Search.php";

    public static function UtilityClasses() {return "/www/eresearch/TDH-Tools/Utilities/includes.php";}
    
    public static function SourceDataFolder() {return "/www/eresearch/source";}
    
    public static function osPathDelimiter()  { return "/"; }
    public static function osExtensionDelimiter() { return "."; }


 
    
}

?>
