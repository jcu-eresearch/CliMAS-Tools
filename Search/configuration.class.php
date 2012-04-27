<?php
/*
 * System configuration and default values
 */
class configuration {

    private static $PATH_ROOT = "/www/eresearch/TDH-Tools";
    
    public static function ApplicationName() {return "SRD::";}
    
    public static $SEARCH_PHP = "/www/eresearch/TDH-Tools/Search/Search.php";

    public static function UtilityClasses() {return "/www/eresearch/TDH-Tools/Utilities/includes.php";}
    
    public static function SourceDataFolder() {return "/www/eresearch/source";}
    
    public static function osPathDelimiter()  { return "/"; }
    public static function osExtensionDelimiter() { return "."; }
    
    // This folder must be configured to be accessible by the Apache
    public static function pathToMapfiles()   { return "/www/eresearch/MapserverMapfiles"; }
    public static function pathToImages()     { return "/www/eresearch/MapserverImages"; }
    public static function pathToImagesWeb()  { return     "/eresearch/MapserverImages"; } // HTML img src version

    public static function mapfileExtension()  { return "map"; } 
    
    
    public static function pathToApplicationRoot()  { return self::$PATH_ROOT; }
    public static function pathToMapSymbols()  { return self::$PATH_ROOT."/Resources/symbols/symbols35.sym"; }
    public static function pathToMapFonts()    { return self::$PATH_ROOT."/Resources/fonts/fonts.list";   }
    
    
    public static function imageHeight() { return 500;}
    public static function imageWidth()  { return 800;}
    public static function imageType()   { return "PNG24";}

    public static function imageMaxHeight() { return 100000;}
    public static function imageMaxWidth()  { return 100000;}
    
    public static function imageMinHeight() { return 100;}
    public static function imageMinWidth()  { return 100;}
    
    
    public static function imageBackgroundRGB()   
    { 
        return RGB::transparent();;
    }

    
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
 
    
    public static $TRUE  = "TRUE";
    public static $FALSE = "FALSE";
}

?>
