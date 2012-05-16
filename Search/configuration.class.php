<?php
/*
 * System configuration and default values
 */
class configuration {
    
    public static function ApplicationName() {return "SRD::";}
    
    // public static $SEARCH_PHP = "/www/eresearch/TDH-Tools/Search/Search.php";


    public static function UtilityClasses() {return "/www/eresearch/TDH-Tools/Utilities/includes.php";}
    
    public static function SourceDataFolder() {return "/www/eresearch/source";}
    
    public static function osPathDelimiter()  { return "/"; }
    public static function osExtensionDelimiter() { return "."; }

    public static function ContextSpatialLayersFolder() {return "/www/eresearch/source/context";}


    public static function DefaultLayerFinderName() {return "ContextLayer";}

    public static function DefaultLayerFinderActionName()
    {
        return FinderFactory::Find(self::DefaultLayerFinderName())->DefaultAction();

    }



}

?>
