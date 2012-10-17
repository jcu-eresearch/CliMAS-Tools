<?php
include_once 'Mapserver.includes.php';
/**
 * Description of SpatialExtent
 *
 * Mutli value item
 * - Spatial Extent of a Map.
 * 
 * @author Adam Fakes (James Cook University)
 */
class MapServerLayer extends Object {
    
    public static function create(MapServerLayers $parent, $filename,$column_name = null,$LayerType = null)
    {
        if (is_null($parent))  throw new Exception();  //**TODO:Parent (MapServerLayers) is Null
        
        if (!file_exists($filename)) return null; //** todo:: Fail messsage


        if (is_null($LayerType)) 
        {
            if (spatial_util::isRaster($filename))
                $LayerType = MapServerConfiguration::$SPATIAL_TYPE_RASTER;
            else
                $LayerType = spatial_util::VectorType($filename);
        }
        
        if (is_null($LayerType))
            throw new Exception("LayerType Unknown");  
        
        
        $L = null;
        
        switch (strtoupper($LayerType)) 
        {
            
            case MapServerConfiguration::$SPATIAL_TYPE_RASTER:
                $L = new MapServerLayerRaster($parent,$filename);
                break;
            
            case MapServerConfiguration::$SPATIAL_TYPE_LINE:
            case MapServerConfiguration::$SPATIAL_TYPE_POINT:
            case MapServerConfiguration::$SPATIAL_TYPE_POLYGON:
                
                if (spatial_util::isRaster($filename))
                    throw new Exception("Layer was passed as Vector but it is really a raster : {$filename}");  
                
                $L = new MapServerLayerVector($parent,$filename,$column_name,$LayerType);
                break;

            
            default:
                $L = new MapServerLayerVector($parent,$filename,$column_name,  MapServerConfiguration::$SPATIAL_TYPE_LINE); //** default shape typ[e is a line
                break;
        }
        
        
        
        return $L;
        
    }


    private $extent = null;
    protected $parent = null;
    
    protected $classes = null;
    
    
    /***
     *   String = Spatial filename 
     */
    public function __construct(MapServerLayers $parent, $filename,$layerName) {
        parent::__construct();        

        $this->parent = $parent;

        
        $this->classes = new MapServerLayerClasses($this);
        
        $this->setPropertyByName("isRaster", false);
        $this->setPropertyByName("isVector", false);
            
        $this->setPropertyByName("LayerName", $layerName);
        
        $this->setPropertyByName("Filename", $filename);

        $this->setPropertyByName("Status", self::$STATUS_ON);

        $this->setPropertyByName("LayerType", "SpatialLayer");
        
        
        $this->extent = SpatialExtent::createFromFilename($filename);

        
        
    }
    
    

    public function __destruct() {
        parent::__destruct();
    }

    public function Caption() {
        if (func_num_args() == 0) return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }
    
    
    
    public function isValid()
    {
        if (is_null($this->extent))  return false;        
        if (file_exists($this->Filename())) return false;
        
        //** Column name must extist 
        
        return true;
    }
   

    /***
     * Classes (for Display)
     */
    public function Classes() 
    {
        $result = $this->classes;
        $result instanceof MapServerLayerClasses;
        return $result;
    }    
    
    
    /***
     * NAME
     */
    public function LayerName() 
    {
        return $this->getProperty();
    }    
    
    /***
     * DATA
     */
    public function Filename()
    {
        return $this->getProperty();
    }

    /***
     * STATUS
     */
    public function Status()
    {
        return $this->getProperty();
    }

    /***
     * TYPE
     */
    public function LayerType()
    {
        return $this->getProperty();
    }
    
    
    public function isVector()
    {
        return $this->getProperty();
    }

    public function isRaster()
    {
        return $this->getProperty();
    }
    
    
    public function North()
    {
        return (is_null($this->extent)) ? null : $this->extent->North();
    }
    
    public function South()
    {
        return (is_null($this->extent)) ? null : $this->extent->South();
    }
    
    public function East()
    {
        return (is_null($this->extent)) ? null : $this->extent->East();
    }
    
    public function West()
    {
        return (is_null($this->extent)) ? null : $this->extent->West();
    }
    
    
    public function __toString() {
        return parent::asFormattedString("{Filename}");
        
    }
    
    public static $STATUS_ON  = "ON";
    public static $STATUS_OFF = "OFF";

    
    
}
?>