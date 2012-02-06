<?php
include_once 'utilities/includes.php';
include_once 'configuration.class.php';

class TDH {

    private static $NAME = "Tropical Data Hub";
    
    private $database = null;
    
    public static function create() {
        $O = new TDH();
        return $O;
    }

    
    private $property = array();
    
    public function __construct() {
     
        $this->database = new database(   configuration::$DATABASE_NAME, 
                                          configuration::$DATABASE_SERVER, 
                                          configuration::$DATABASE_USER, 
                                          configuration::$DATABASE_PASSWORD);
        
    }
    
    public function __destruct() {
        
    }
    
    
    public function run() {
        
    }
    

    public function DB()
    {
        return $this->database;
    }
    

    
    
    private function GetSetData($property_name,$value = null)
    {
        if (is_null($value)) return $this->property[$property_name];
        $this->property[$property_name] = $value;
    }

    
    public function __toString() {
        
        return $this->VersionString();
    }
    
    
    public function VersionName()
    {
        return configuration::$VERSION_NAME;
    }

    public function VersionNumber()
    {
        return configuration::$VERSION_NUMBER;
    }
    
    public function VersionString()
    {
        return $this->Name()." ".configuration::$VERSION_NUMBER." (".configuration::$VERSION_NAME.")";
    }

    
    public function Name()
    {
        return self::$NAME;
    }
    
}

?>
