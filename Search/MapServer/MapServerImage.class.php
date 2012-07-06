<?php
include_once 'Mapfile.class.php';
include_once 'MapserverGUI.class.php';
/**
 * CLASS: 
 *        
 * 
 *   
 */
class MapServerImage extends Object {
    //**put your code here
    
    public static function FromFile($spatialFilename,$attributeColumnName = null,$layerType = null) 
    { 
        if (!file_exists($spatialFilename)) return null;
        $M = new MapServerImage();
        
        $M->AddLayer($spatialFilename,$attributeColumnName,$layerType);  
        
        $M->save();
        return $M->MapImageLocation();
    }
    

    public static function RasterFromFile($spatialFilename) 
    { 
        return self::FromFile($spatialFilename,null,  MapServerConfiguration::$SPATIAL_TYPE_RASTER);
    }

    public static function VectorFromFile($spatialFilename,$attributeColumnName = null) 
    { 
        return self::FromFile($spatialFilename,$attributeColumnName,MapServerConfiguration::$SPATIAL_TYPE_LINE);
    }

    public static function PolylineFromFile($spatialFilename,$attributeColumnName = null) 
    { 
        return self::FromFile($spatialFilename,$attributeColumnName,MapServerConfiguration::$SPATIAL_TYPE_LINE);
    }
    
    public static function PolygonFromFile($spatialFilename,$attributeColumnName = null) 
    { 
        return self::FromFile($spatialFilename,$attributeColumnName,MapServerConfiguration::$SPATIAL_TYPE_POLYGON);
    }
    
    public static function PointsFromFile($spatialFilename,$attributeColumnName = null) 
    { 
        return self::FromFile($spatialFilename,$attributeColumnName,MapServerConfiguration::$SPATIAL_TYPE_POINT);
    }
    
    public static function RasterOnVector($backgroundSpatialFilename = null,$backgroundAttributeColumnName = null,$foregroundSpatialFilename = null,$extent = null) 
    { 
                
        $MSI = new MapServerImage();
        
        
        $MSI->Extent($extent);
        
        
        $simpleCaption = file::filenameOnly($foregroundSpatialFilename);
        $MSI->Caption($simpleCaption);
        
        $layerAdded = false;
        
        if (!is_null($backgroundSpatialFilename))
        {
            $background = $MSI->AddLayer($backgroundSpatialFilename,$backgroundAttributeColumnName,MapServerConfiguration::$SPATIAL_TYPE_LINE);
            $background instanceof MapServerLayerVector;
            $background->LabelItem(null);            
            $layerAdded = true;
        }
        
        if (!is_null($foregroundSpatialFilename))
        {
            $MSI->AddLayer($foregroundSpatialFilename,null, MapServerConfiguration::$SPATIAL_TYPE_RASTER);
            $layerAdded = true;
        }
        
        if (!$layerAdded) return null;

        $MSI->save();
        return $MSI->MapImageLocation();
        
    }
    
    
    
    private $wrapper = null;
    
    public function __construct() { 
        parent::__construct();
        $this->wrapper = new MapServerWrapper();
        
    }
    
    public function __destruct() {    
        parent::__destruct();
    }
    
    
    public function AddLayer($filename, $attributeColumnName = null, $layerType = null)
    {
        if (!file_exists($filename)) return null;
        
        
        switch ($layerType) {
            case MapServerConfiguration::$SPATIAL_TYPE_RASTER:
                
                $current = $this->wrapper->Layers()->AddLayer($filename);
                
                $current instanceof MapServerLayerRaster;
                $current->HistogramBuckets(10);
                $current->ColorTableByStats(RGB::GradientYellowOrangeRed());
                $current->ColorTableResetFirstElement();                        
                break;

            default:
                
                $current = $this->wrapper->Layers()->AddLayer($filename,$attributeColumnName, $layerType);
                
                if ($current instanceof MapServerLayerRaster)
                {
                    $current instanceof MapServerLayerRaster;
                    $current->HistogramBuckets(10);
                    $current->ColorTableByStats(RGB::GradientYellowOrangeRed());
                    $current->ColorTableResetFirstElement();                                            
                }
                else
                {
                    $current instanceof MapServerLayerVector;    
                }
                
                break;
        }
        
        
        $this->Extent($this->wrapper->Extent());
        
        
        return $current;
        
    }
            
    
    public function save() 
    {
        
        $this->wrapper->Caption($this->Caption());
        
        $MF = Mapfile::create($this->wrapper);
        
        $MF->Extent($this->Extent());
        
        $mapfile = $MF->write();
        
        $map_object = ms_newMapObj($mapfile);
        
        $image_folder = $map_object->web->imagepath;
        
        $this->setPropertyByName("ImageFolder", $image_folder);
        $this->setPropertyByName("MapfilePath", $mapfile);
        
        $GUI = MapserverGUI::create($mapfile);
        if (is_null($GUI)) die ("Map Server GUI failed");

        $this->setPropertyByName("MapImageLocation", $GUI->MapImageLocation());
        $this->setPropertyByName("MapLegendLocation", $GUI->MapLegendLocation());
        
        unset($GUI);
        unset($MF);
        
    }


    public function ReadWriteProperty() {
        if (func_num_args() == 0) return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
        
    }


    
    public function MapImageLocation() {
        return $this->getProperty();
    }
    
    public function MapLegendLocation() {
        return $this->getProperty();
    }

    public function MapfilePath() {
        return $this->getProperty();
    }

    public function ImageFolder() {
        return $this->getProperty();
    }
    
    public function Caption() {
        if (func_num_args() == 0) return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }
    
    public function Extent()
    {        
        if (func_num_args() == 0) return SpatialExtent::cast($this->getProperty());
        return SpatialExtent::cast($this->setProperty(func_get_arg(0)));
    }

}
?>