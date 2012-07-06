<?php
include_once 'Mapserver.includes.php';
/**
 * 
 *   
 */
class MapServerLayerClassStyle extends Object {
    
    public function __construct() { 
        parent::__construct();
        $this->Color(RGB::transparent());
        $this->Width(1);
    }
    
    public function __destruct() {    
        parent::__destruct();
    }
    
    
    /***
     * Something that will be abale to converted into and RGB object
     * 
     */
    public function Color() {
        if (func_num_args() == 0)  return $this->getProperty();

        $result = $this->setProperty(RGB::create(func_get_args()));
        $result instanceof RGB;
        return $result;        
    }
    

    public function Width() {
        if (func_num_args() == 0) return $this->getProperty();
        return $this->setProperty(func_get_arg(0));   
    }
    
    
}
?>