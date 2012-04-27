<?php

/* 
 * CLASS: MapServerLayerClassStyle
 *        
 * 
 *   
 */
class MapServerLayerClassStyle extends Object {
    //put your code here
    
    public function __construct() { 
        
        parent::__construct();
        $this->setPropertyByName("Color", new RGB(RGB::transparent()));
        $this->Width(1);
        
    }
    
    public function __destruct() {    
        parent::__destruct();
    }


    /*
     * Overloads
     * RGB Object
     * 3 element Array  positional 0=Red, 1=Green, 2=Blue 
     * 3 element Array  Keyed      Red, Green, Blue 
     * 3 element Array  Keyed      R, G, B 
     * 
     */
    public function Color() {

        if (func_num_args() == 0) 
            $result = $this->getProperty();
        else
        {
            if ((func_get_arg(0) instanceof RGB))
                $result = $this->setProperty(func_get_arg(0));    
            else
            {
                
                $args = func_get_args();
                
                $result = RGB::create($args[0]);
            }
                
        }
        
        $result instanceof RGB;
        return $result;
        
    }
    

    public function Width() {
        if (func_num_args() == 0) return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
        
    }
    
    
}

?>
