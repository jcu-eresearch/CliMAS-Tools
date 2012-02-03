<?php
include_once 'utilities/includes.php';

class Template {

    private static $NAME = "Template";
    
    
    public static function create() {
        $O = new Template();
        return $O;
    }

    
    private $property = array();
    
    public function __construct() {
        
    }
    
    public function __destruct() {
        
    }
    
    public function run() {
        
    }
    

    public function Name($value = null)
    {
        return $this->GetSetData("Name",$value);
    }
    
    
    private function GetSetData($property_name,$value = null)
    {
        if (is_null($value)) return $this->property[$property_name];
        $this->property[$property_name] = $value;
    }

    
    public function __toString() {
        
        return self::$NAME;
    }
}

?>
