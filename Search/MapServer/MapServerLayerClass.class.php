<?php
include_once 'MapServerLayerClassStyle.class.php';
include_once 'MapServerLayerClassLabel.class.php';
/**
 * CLASS:  MapServerLayerClass
 *        
 * 
 *   
 */
class MapServerLayerClass extends Object {

    private $layer = null;
    
    public function __construct(MapServerLayer $layer, $name) { 
        parent::__construct();
        
        $this->$layer = $layer;
        
        $name = util::CleanStr($name, null, " _!@#$%^&*()_+\\{}[]<>?");
        if ( is_numeric(substr($name, 0, 1))) $name = "A".$name; //** make sure does not start with Number
        
        $this->ClassName($name);
        $this->Style(new MapServerLayerClassStyle());
        
        
        
    }
    
    public function __destruct() {    
        parent::__destruct();
    }


    public function addLabel()
    {
        $this->Label(new MapServerLayerClassLabel());
    }
    
    public function ClassName() {
        
        $result = null;
        if (func_num_args() == 0) 
            $result = $this->getProperty();
        else
            $result = $this->setProperty(func_get_arg(0));

        return $result ;
    }
    
    
    /***
     * Map Server can hndle Multiple Styles and Multiple Labels 
     * - WISH:: Add array for Styles and Labels
     */
    public function Style() {
        
        $result = null;
        if (func_num_args() == 0) 
            $result = $this->getProperty();
        else
        {
            if (func_get_arg(0) instanceof MapServerLayerClassStyle)
                $result = $this->setProperty(func_get_arg(0));
        }
        
        $result instanceof MapServerLayerClassStyle;
        return $result ;
        
    }
    
    
    public function Label() {
        
        $result = null;
        if (func_num_args() == 0) 
            $result = $this->getProperty();
        else
        {
            if (func_get_arg(0) instanceof MapServerLayerClassLabel)
                $result = $this->setProperty(func_get_arg(0));
        }
        
        $result instanceof MapServerLayerClassLabel;
        return $result ;

    }
    

    public function Expression() {
        
        $result = null;
        if (func_num_args() == 0) 
            $result = $this->getProperty();
        else
        {
            $result = $this->setProperty(func_get_arg(0));
        }
                
        return $result ;

    }
    
    
}
?>