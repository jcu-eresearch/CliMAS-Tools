<?php
/**
 * CLASS:  DataVisualText
 *        
 * 
 *   
 */
class VisualText extends Data {
    
    
    public function __construct($text = "DataVisualText",$point_size = 12,$colour = null) { 
        parent::__construct();
        $this->DataName(__CLASS__);
        $this->Text($text);
        $this->PointSize($point_size);
        $this->Colour($colour);
    }
    
    public function __destruct() {    
        parent::__destruct();
    }


    public function Text() {
        if (func_num_args() == 0) return $this->getProperty();
        
        $this->Data(func_get_arg(0));
        
        return $this->setProperty(func_get_arg(0));
    }

    public function PointSize() {
        if (func_num_args() == 0) return $this->getProperty();
        
        $value = func_get_arg(0);
        if ($value <=   3) $value = 3;    //** TODO:: place in configuration 
        if ($value >= 300) $value = 300;  //** TODO:: place in configuration 
        
        return $this->setProperty(func_get_arg(0));        
    }
    
    /**
     * If param pased in is not an RGB then set colour to Black
     */

    public function Colour() {
        
        if (func_num_args() == 0) 
            return RGB::cast($this->getProperty());
            
        $colour = RGB::create(func_get_arg(0)); //** create RGB object and then set it
        
        return RGB::cast($this->setProperty($colour));
    }
    
    
    public function toString() 
    {
        return $this->Text();
    }

    
    public static function isVisualText($src)
    {
        return ($src instanceof self);
    }
    
    public static function cast($src)
    {
        $src instanceof VisualText;
        return $src;
    }
    
    
    
}
?>