<?php

/**
 * Description of MapServer
 *
 * @author Adam Fakes (James Cook University)
 */
class MapServerConfiguration {


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


    //** This folder must be configured to be accessible by the Apache
    public static function pathToMapfiles()   { return self::where()."tmp/MapserverMapfiles"; }
    public static function pathToImages()     { return self::where()."tmp/MapserverImages"; }
    public static function pathToImagesWeb()  { return    "/eresearch/TDH-Tools/tmp/MapserverImages"; } // webserver4 accessible version of path for images

    public static function pathToMapSymbols()  { return self::where()."Resources/symbols/symbols35.sym";}
    public static function pathToMapFonts()    { return self::where()."Resources/fonts/fonts.list";}
    


    public static function mapfileExtension()  { return "map"; }

    public static function imageHeight() { return 500;}
    public static function imageWidth()  { return 800;}
    public static function imageType()   { return "PNG24";}

    public static function imageMaxHeight() { return 100000;}
    public static function imageMaxWidth()  { return 100000;}

    public static function imageMinHeight() { return 100;}
    public static function imageMinWidth()  { return 100;}


    public static function imageTypePNG() { return "PNG24";}
    public static function imageTypeJPG() { return "JPG";}
    public static function imageTypePDF() { return "PDF";}

    public static function imageTypes()
    {
        $result = array();
        $result[self::imageTypePNG()] = self::imageTypePNG();
        $result[self::imageTypeJPG()] = self::imageTypeJPG();
        $result[self::imageTypePDF()] = self::imageTypePDF();

        return $result;
    }

    public static function NORTH() { return "north";}
    public static function SOUTH() { return "south";}
    public static function EAST()  { return "east";}
    public static function WEST()  { return "west";}

    public static function CoordinatesFormat() { return "{West} {South} {East} {North}";}

    public static function ColourFormat() { return "{Red} {Green} {Blue}";}

    public static $SPATIAL_TYPE_POLYGON  = "POLYGON";
    public static $SPATIAL_TYPE_LINE     = "LINE";
    public static $SPATIAL_TYPE_POINT    = "POINT";

    public static $SPATIAL_TYPE_RASTER   = "RASTER";


}

?>
