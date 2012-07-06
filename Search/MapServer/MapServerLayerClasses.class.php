<?php
include_once 'MapServerLayerClass.class.php';
/**
 * CLASS: MapServerLayerClasses
 *        
 * To manage Classes for Layer
 * 
 *   
 */
class MapServerLayerClasses extends Object {
    
    private $classes = null;
    private $parent = null;
    
    public function __construct(MapServerLayer $parent) { 
        parent::__construct();
        
        $this->parent = $parent;    
        $this->classes = array(); // array of MapServerLayer
        
        $this->addDefaultClass();
        
    }
    
    public function __destruct() {    
        parent::__destruct();
        
    }

    public function ClassNames()
    {
        return array_keys($this->classes);
    }
    
    public function AddClass(MapServerLayerClass $src)
    {        
        if (is_null($src)) return null;
        $this->classes[$src->ClassName()] = $src;
        return $src->ClassName();
    }
    
    public function RemoveClass($name)
    {
        if (!array_key_exists($name, $this->classes)) return;
        unset($this->classes[$name]);
    }
    
    public function ByName($name)
    {
        if (!array_key_exists($name, $this->classes)) return null;
        
        $result = $this->classes[$name];
        $result instanceof MapServerLayerClass;
        
        return $result;
    }

    public function DefaultClass()
    {
        $result = util::first_element($this->classes);
        $result instanceof MapServerLayerClass;
        return $result;
    }

    private function addDefaultClass()
    {
        $default = new MapServerLayerClass($this->parent,$this->parent->LayerName());        
        $default->addLabel();
        
        $default->Label()->Size(6);
        
        $default->Style()->Color(RGB::ColorBlack());
        $this->AddClass($default);        
    }
    
}
?>