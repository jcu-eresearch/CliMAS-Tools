<?php
/**
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
    

    /***
     * Extent of all layers on map
     * - each layer will hold an extent of itself
     */    
    public function Extent($update = false)
    {
        
        if (is_null($this->extent)) $update = true;
        
        if (!$update) 
        {
            return $this->extent;
        }
        
        //** get extent for all layers
        $extent_matrix = array();
        foreach ($this->layers as $layer_name => $layer) 
        {
            $layer instanceof MapServerLayer;  
            $extent_matrix[MapServerConfiguration::NORTH()][$layer_name] = $layer->North();
            $extent_matrix[MapServerConfiguration::SOUTH()][$layer_name] = $layer->South();
            $extent_matrix[MapServerConfiguration::EAST() ][$layer_name] = $layer->East();
            $extent_matrix[MapServerConfiguration::WEST() ][$layer_name] = $layer->West();
        }
        
        //** update map extent
        $this->extent = new SpatialExtent();
        $this->extent->North(max($extent_matrix[MapServerConfiguration::NORTH()]));
        $this->extent->South(min($extent_matrix[MapServerConfiguration::SOUTH()]));
        $this->extent->East (min($extent_matrix[MapServerConfiguration::EAST()]));
        $this->extent->West (max($extent_matrix[MapServerConfiguration::WEST()]));
        
        return $this->extent;
        
    }    
    
    public function AddLayer($src)
    {

        if (is_null($src)) return null;

        // load mulitple objects
        if (is_array($src))
        {
            $result = array();
            foreach ($src as $key => $element)
                $result[$key] = $this->AddLayer($element);

            return $result;
        }


        if (Object::isObject($src))
        {
            $this->AddLayerFromObject($src);
        }
            

        //** It's a string so most likely a Filename
        if (is_string($src))
        {
            if ($src == "") return null;
            
            if (file_exists($src))
            {
                $L = MapServerLayer::create($this, $src); //** default details

                if (is_null($L))
                {
                    return null;
                }

                $this->layers[$L->LayerName()] = $L;
                $this->Extent(true);

                return $L;
            }
        }



            
        return null;
        
    }

    private function AddLayerFromObject($src)
    {
        $src instanceof Object;
        
        
        $filename = $src->getPropertyByName("Filename", null);
        $attribute = $src->getPropertyByName("Attribute", null);
        $SpatialDatatype = $src->getPropertyByName("SpatialDatatype", null);
        
        
        
        if (!file_exists($filename))
        {
            return null;
        }

        
        $L = MapServerLayer::create($this, $filename,$attribute,$SpatialDatatype);
        if (is_null($L)) return null;
        
        $L instanceof MapServerLayer;
        
        if ($SpatialDatatype == spatial_util::$SPATIAL_TYPE_RASTER)
        {
            $L instanceof MapServerLayerRaster;
            
            $L->ColorTable($src->getPropertyByName("ColourRamp", null));
            $L->HistogramBuckets($src->getPropertyByName("HistogramBuckets", 2));
            
        }
        

        $this->layers[$L->LayerName()] = $L;

        $this->Extent(true);

        return $L;

    }



    public function AddPoint($filename,$column_name = null)
    {        
        $L = $this->AddLayer($filename, $column_name, MapServerConfiguration::$SPATIAL_TYPE_POINT);
        return $L;
    }

    public function AddPolygon($filename,$column_name = null)
    {        
        $L = $this->AddLayer($filename, $column_name, MapServerConfiguration::$SPATIAL_TYPE_POLYGON);
        return $L;
    }
    
    public function AddPolyline($filename,$column_name = null)
    {        
        $L = $this->AddLayer($filename, $column_name, MapServerConfiguration::$SPATIAL_TYPE_LINE);
        return $L; 
    }
    
    public function AddRaster($filename)
    {        
        $L = $this->AddLayer($filename, null, MapServerConfiguration::$SPATIAL_TYPE_RASTER);
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