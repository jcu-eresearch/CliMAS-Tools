<?php
include_once '../Utilities/includes.php';

/* 
 * CLASS: Used to generate xxxx.map file 
 *        Process xxxx.map file
 *        Return (web and real) paths to image file
 * 
 * Within this class the main  properties are
 * 
 *   -- Layers  - holds an array of MapServerLayer
 *   
 */
class MapServer {

    private static $NAME = "MapServer";
    
    public static function create() {
        $O = new MapServer();
        return $O;
    }
 
    private $property = array();

    public function ImageType($value = null) {
    
        $property_name = "ImageType";
        
        $value = strtoupper($value);
        
        switch ($value) {
            case "PNG":
            case "JPG":
            case "GIF":

            default:
                throw new Exception("$property_name .. Unknown Image Type $value");
                break;
        }
        
        return $this->Property($property_name, $value);
        
    }

    
    
//    IMAGETYPE      PNG24
//                   West       South      East       North                
//    EXTENT         113.156360 -43.642890 153.638340 -10.687530
//    SIZE           800 500
//    SHAPEPATH      "data"
//    IMAGECOLOR     -1 -1 -1
//    FONTSET        "/www/projects/tdh/fonts/fonts.list"
//    SYMBOLSET      "/www/projects/tdh/symbols/symbols35.sym"
//
//    WEB
//        TEMPLATE  '/www/projects/tdh/picard/code/map.php'
//        IMAGEPATH '/www/tmp/'   ## full path to image folder
//        IMAGEURL  '/tmp/'       ## web  path ''   ''    ''
//    END 
    
    
    public function __construct() {
        
    }
    
    public function __destruct() {
        
    }
    
    private function Property($property_name,$value = null) {
        if (is_null($value)) return $this->property[$property_name];
        $this->property[$property_name] = $value;        
        return $value;
    }
    
    public function __toString() {
        return $this->VersionString();
    }
    
    public function Name() {
        return self::$NAME;
    }
    
}

?>
