<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of MapServer
 *
 * @author jc166922
 */
class MapServerConfiguration {

    // This folder must be configured to be accessible by the Apache
    public static function pathToMapfiles()   { return "/www/eresearch/MapserverMapfiles"; }
    public static function pathToImages()     { return "/www/eresearch/MapserverImages"; }
    public static function pathToImagesWeb()  { return     "/eresearch/MapserverImages"; } // HTML img src version

    public static function pathToMapSymbols()  { return "/www/eresearch/TDH-Tools/Resources/symbols/symbols35.sym";}
    public static function pathToMapFonts()    { return "/www/eresearch/TDH-Tools/Resources/fonts/fonts.list";}

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
    public static $SPATIAL_TYPE_LINE = "LINE";
    public static $SPATIAL_TYPE_POINT    = "POINT";

    public static $SPATIAL_TYPE_RASTER   = "RASTER";


}

?>
