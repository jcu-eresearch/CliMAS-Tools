<?php
/**
 * Description of MapServer
 *
 * @author Adam Fakes (James Cook University)
 */
class MapServerConfiguration {

    //** This folder must be configured to be accessible by the Apache
    public static function pathToMapfiles()   
    { 
        $folder = configuration::TempFolder()."MapserverMapfiles";
        file::mkdir_safe($folder);
        return $folder; 
    }
    
    public static function pathToImages()     
    { 
        $folder = configuration::FilesDownloadFolder()."MapserverImages";
        file::mkdir_safe($folder);
        return $folder;         
    }
    
    public static function pathToImagesWeb()  { return configuration::WebDownloadFolder()."MapserverImages"; } // webserver4 accessible version of path for images

    public static function pathToMapSymbols()  { return configuration::ResourcesFolder()."symbols/symbols35.sym";}
    public static function pathToMapFonts()    { return configuration::ResourcesFolder()."fonts/fonts.list";}
    

    public static function mapfileExtension()  { return "map"; }

    public static function imageHeight() { return 600;}
    public static function imageWidth()  { return 700;}
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