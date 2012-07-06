<?php
include_once 'MapServerLayer.class.php';
class MapServerLayerVector extends MapServerLayer {
    
    
    public function __construct(MapServerLayers $parent, $filename,$column_name, $LayerType = null) 
    {   
        
        // get column name
        if (is_null($column_name)) 
        {
            // need to pickup first column that has some type of category
            $column_name = NULL;
            
            $names = spatial_util::VectorLayers($filename); // use first layer in spatial vector file
            
            $attribs = spatial_util::VectorAttributeNames($filename,$names[0],true);
            
            $string_attribs = array_util::ElementsThatContain($attribs, spatial_util::$FIELD_TYPE_STRING);
            
            $column_name = array_util::Value(array_keys($string_attribs), 0, NULL);
        }
        
        
        
        
        $layer_name = file::filenameOnly($filename).(is_null($column_name) ? "" : "_".$column_name);
        
        parent::__construct($parent,$filename,$layer_name);
        
        $this->setPropertyByName("isVector", true);
        
        // if we have said what the layer type is then try to guess
        if (is_null($LayerType))
            $this->setPropertyByName("LayerType", $this->translateVectorType());
        else
            $this->setPropertyByName("LayerType", $LayerType);

        
        $this->setPropertyByName("AttributeColumnName", $column_name);

        $this->ClassItem($this->AttributeColumnName());
        $this->LabelItem($this->AttributeColumnName());

        $default = $this->Classes()->DefaultClass();
        $default->addLabel();
        $default->Style()->Color(RGB::ColorBlack());
        $default->Style()->Width(1);
        
        
        
    }
    
    private function translateVectorType()
    {
        $type = spatial_util::VectorType($this->Filename());        
        $this->setPropertyByName("RawVectorType", $type);  // for debaug purpose
        
        
        $result = "";
        switch ($type) {
            case "Line String":
               $result = MapServerConfiguration::$SPATIAL_TYPE_LINE;
                break;

            case "Polygon":
                $result = MapServerConfiguration::$SPATIAL_TYPE_POLYGON;
                break;
            
            case "Point":
                $result = MapServerConfiguration::$SPATIAL_TYPE_POINT;;
                break;

            default:
                break;
        }
        
        return $result;
        
    }
    
    
    public function __destruct() {    
        parent::__destruct();
    }
    
    /**
     * Attribute Column Name to be used to Class Features
     */
    public function ClassItem()
    {
        if (func_num_args() == 0) return $this->getProperty();        
        
        // if width set to out of bounds chnage to default values
        $value = func_get_arg(0); 
        return  $this->setProperty($value);
            
    }
    
    /**
     * Attribute Column Name to be used to Label Features
     */
    public function LabelItem()
    {
        if (func_num_args() == 0) return $this->getProperty();        
        
        // if width set to out of bounds chnage to default values
        $value = func_get_arg(0); 
        return  $this->setProperty($value);
            
    }

    
    public function AttributeColumnName() 
    {
        return $this->getProperty();        
    }    
    
    
    
    
}
?>