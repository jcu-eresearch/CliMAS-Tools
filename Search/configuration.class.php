<?php
/**
 * System configuration and default values
 *
 * TODO:: Needs to com from Config file that is specific to HOstname
 *
 */
class configuration {

    public static function osPathDelimiter()  { return "/"; }
    public static function osExtensionDelimiter() { return "."; }

    public static function ApplicationName() {return "SRD::";}

    public static function UtilityClasses() {return "/www/eresearch/TDH-Tools/Utilities/includes.php";}
    
    public static function SourceDataFolder() {return "/www/eresearch/source";}

    public static function ContextSpatialLayersFolder() {return "/www/eresearch/source/context";}

    public static function DefaultMapableActionClassname() { return "ContextLayerAustralianRiverBasins"; }

    public static function MapableBackgroundLayers() { return "ContextLayerMapableBackgroundLayers"; }

}

?>
