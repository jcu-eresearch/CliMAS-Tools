<?php
include_once 'MapServerLayer.class.php';
class MapServerLayerRaster extends MapServerLayer {
    
    
    public function __construct(MapServerLayers $parent, $filename) 
    {
        $layer_name = file::filenameOnly($filename);
        parent::__construct($parent,$filename,$layer_name);

        $this->setPropertyByName("LayerType", MapServerConfiguration::$SPATIAL_TYPE_RASTER  );

        $this->setPropertyByName("isRaster", true);
        
        $this->setPropertyByName("BandCount", 1);   //TODO:: sneed to support atleast 3 bands for RGB output.
        $this->Band(1);
        
        $this->setPropertyByName("NoDataValue", "-9999");  // TODO:  will use GDAL to retive no data value for layer
        
        $this->getStatistics();
        
        $this->ClassItem("[pixel]");
        $this->LabelItem("");
        
        $this->ColorUniqueValues(false);
        
        $default = $this->Classes()->DefaultClass();
        $default->Style()->Color(RGB::ColorBlue());
        
        $this->HistogramBuckets(10); // default 10 buckets
        
        
    }

    public function __destruct() {    
        parent::__destruct();
    }
    
    private function getStatistics()
    {
        $basic = spatial_util::RasterStatisticsBasic($this->Filename(), $this->Band());
        $this->setPropertyByName("Minimum", $basic[spatial_util::$STAT_MINIMUM]);
        $this->setPropertyByName("Maximum", $basic[spatial_util::$STAT_MAXIMUM]);
        $this->setPropertyByName("Mean",    $basic[spatial_util::$STAT_MEAN]);
        $this->setPropertyByName("StdDev",  $basic[spatial_util::$STAT_STDDEV]);
        
    }

    /***
     * Set Color Table using Image statistics 
     * 
     */
    public function ColorTableByStats($indexed_color_gradient = null)
    {
        if (is_null($indexed_color_gradient)) $indexed_color_gradient = RGB::GradientBlueGreenRed();
        $table = RGB::Ramp($this->Minimum() , $this->Maximum(),$this->HistogramBuckets(),$indexed_color_gradient);
        $this->ColorTable($table);   
    }

    
    public function ColorTableResetFirstElement($color = null)
    {
        
        if (is_null($color)) $color = RGB::transparent();
        $table = $this->ColorTable();
        $first_key = array_util::FirstValueKey($table);
        $table[$first_key] = $color;
        $this->ColorTable($table);   
    }
    
    public function HistogramBuckets()
    {
        if (func_num_args() == 0) return $this->getProperty();                
        $value = func_get_arg(0); 
        return  $this->setProperty($value);
    }
    
    public function ColorUniqueValues()
    {
        if (func_num_args() == 0) return $this->getProperty();        
        $value = func_get_arg(0); 
        return  $this->setProperty($value);
    }

    public function NoDataValue() { return $this->getProperty(); }
    
    public function ColorHistogram() {return $this->getProperty(); }
    
    public function Minimum() { return $this->getProperty();}
    
    public function Maximum() { return $this->getProperty(); }

    public function Mean() { return $this->getProperty();  }

    public function StdDev() {  return $this->getProperty(); }

    /***
     * Attribute Column Name to be used to Class Features
     */
    public function ClassItem()
    {
        if (func_num_args() == 0) return $this->getProperty();        
        
        // if width set to out of bounds chnage to default values
        $value = func_get_arg(0); 
        return  $this->setProperty($value);
            
    }
    
    /***
     * Attribute Column Name to be used to Label Features
     */
    public function LabelItem()
    {
        if (func_num_args() == 0) return $this->getProperty();        
        
        // if width set to out of bounds chnage to default values
        $value = func_get_arg(0); 
        return  $this->setProperty($value);
            
    }
    
    /***
     * Attribute Column Name to be used to Label Features
     */
    public function Band()
    {
        if (func_num_args() == 0) return $this->getProperty();        
        
        // if width set to out of bounds chnage to default values
        $value = func_get_arg(0); 
        return  $this->setProperty($value);
            
    }
    
    /***
     * Attribute Column Name to be used to Label Features
     */
    public function BandCount()
    {
        return $this->getProperty();        
    }
    
    
    /***
     * Array of Value to RGB Object
     * 
     * key = Pixel Value
     * value = RGB Onject with color for pixel
     * 
     */
    public function ColorTable()
    {
        if (func_num_args() == 0) return $this->getProperty();        
        
        // if width set to out of bounds chnage to default values
        $value = func_get_arg(0); 
        return  $this->setProperty($value);
            
    }
    
    
    
}
?>