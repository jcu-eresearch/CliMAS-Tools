<?php
include_once 'MapServerLayer.class.php';
/* 
 * CLASS: MapServerLayers
 *        
 * To manage layers for ultimatley buliding the MapServer MapFile
 * 
 *   
 */
class MapServerLayers extends Object {

    private $parent = null;
    
    private $layers = null;
    private $extent = null;
    
    public function __construct(MapServerWrapper $parent) { 
        parent::__construct();
        
        $this->parent = $parent;    
        $this->layers = array(); // array of MapServerLayer
        
    }
    
    public function __destruct() {    
        parent::__destruct();
        
    }
    

    /*
     * Extent of all layers on map
     * - each layer will hold an extent of itself
     */
    
    /** @function Extent SpatialExtent **/
    public function Extent($update = false)
    {
        
        if (is_null($this->extent)) $update = true;
        
        if (!$update) 
        {
            return $this->extent;
        }
            
        
        // get extent for all layers
        $extent_matrix = array();
        foreach ($this->layers as $layer_name => $layer) 
        {
            $layer instanceof MapServerLayer;  // type hint
            $extent_matrix[configuration::NORTH()][$layer_name] = $layer->North();
            $extent_matrix[configuration::SOUTH()][$layer_name] = $layer->South();
            $extent_matrix[configuration::EAST() ][$layer_name] = $layer->East();
            $extent_matrix[configuration::WEST() ][$layer_name] = $layer->West();
        }
        
        // update map extent
        $this->extent = new SpatialExtent();
        $this->extent->North(max($extent_matrix[configuration::NORTH()]));
        $this->extent->South(min($extent_matrix[configuration::SOUTH()]));
        $this->extent->East (min($extent_matrix[configuration::EAST()]));
        $this->extent->West (max($extent_matrix[configuration::WEST()]));
        
        return $this->extent;
        
    }    
    
    
    public function AddLayer($filename,$column_name = null,$LayerType = null)
    {        
        
        if (!is_array($filename))
        {
            echo "<br>Adding one filename {$filename}";

            $L = MapServerLayer::create($this, $filename,$column_name,$LayerType);

            if (is_null($L)) 
            {
                echo "<br>Failed to load layer filename [{$filename}]";
                return null;
            }

            $this->layers[$L->LayerName()] = $L;
            $this->Extent(true);

            return $L;
        }
        else
        {

            $result = array();
            foreach ($filename as $key => $single_filename) 
            {


                if (is_array($column_name)) 
                    $use_column_name = $column_name;   // use same column name for each filename
                else
                    $use_column_name = $column_name[$key];  // macth column name to use to the filename key
                
                $L = $this->AddLayer($single_filename, $use_column_name, $LayerType);
                
                $result[$key] = $L;
            }   

            return $result;
            
        }
            
        return null;
        
    }

    public function AddPoint($filename,$column_name = null)
    {        
        $L = $this->AddLayer($filename, $column_name, MapServerLayer::$TYPE_POINT);
        return $L;
    }

    public function AddPolygon($filename,$column_name = null)
    {        
        $L = $this->AddLayer($filename, $column_name, MapServerLayer::$TYPE_POLYGON);
        return $L;
    }
    
    public function AddPolyline($filename,$column_name = null)
    {        
        $L = $this->AddLayer($filename, $column_name, MapServerLayer::$TYPE_LINE);
        return $L; 
    }
    
    public function AddRaster($filename)
    {        
        $L = $this->AddLayer($filename, null, MapServerLayer::$TYPE_RASTER);
        return $L;
    }
    
    
    public function RemoveLayer($name)
    {
        if (!array_key_exists($name, $this->layers)) return;
        unset($this->layers[$name]);
        
        $this->Extent(true);

    }

    public function ByName($name)
    {
        $result = null;
        if (array_key_exists($name, $this->layers)) 
                $result = $this->layers[$name];
                
         $result instanceof MapServerLayer;
         return $result;
    }
    
    
    public function Layers()
    {
        return $this->layers;
    }    
    
    public function LayerNames()
    {
        return array_keys($this->layers);
    }    
    
}

?>
