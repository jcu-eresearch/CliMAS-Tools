<?php
include_once 'MapServerLayers.class.php';
class MapServerWrapper extends Object {
    
    private $layers = null;

    public function __construct($id = null) {
        parent::__construct($id);

        $this->setPropertyByName("MapfilePathname", $this->getMapfilePathname());
        
        $this->setPropertyByName("OutputImageType",MapServerConfiguration::imageTypePNG());
        $this->setPropertyByName("OutputImageWidth",MapServerConfiguration::imageWidth());
        $this->setPropertyByName("OutputImageHeight",MapServerConfiguration::imageHeight());
        $this->setPropertyByName("OutputImageBackgroundColour", RGB::transparent() );
        $this->setPropertyByName("Shapepath", "");
        
        $this->layers = new MapServerLayers($this);
        
    }
    
    /***
     * Path to map file, unique mapfilename that can be re-accessed.
     */
    public function MapfilePathname() 
    {
        return $this->getProperty(); 
    }

    private function getMapfilePathname()
    {

        $result = MapServerConfiguration::pathToMapfiles().configuration::osPathDelimiter().
                  $this->ID().configuration::osExtensionDelimiter().
                  MapServerConfiguration::mapfileExtension();
        
        return $result;
    }
    
    
    public function Layers() 
    {
        $result = $this->layers;
        $result instanceof MapServerLayers;
        
        return $result;
    }
    
    public function Extent() 
    {
        
        return $this->Layers()->Extent();
    }
    
    
    
    /***
     * Set image type - type of image to be displayed
     * 
     */
    public function OutputImageType() 
    {
        if (func_num_args() == 0) return $this->getProperty();
        
        $type = func_get_arg(0); 
        
        //** if they ask for an unknown type reset it to PNG
        if (!array_key_exists($type, MapServerConfiguration::imageTypes()))  $type = MapServerConfiguration::imageTypePNG ();
        return  $this->setProperty($type);
    }

    public function Shapepath() 
    {
        return $this->getProperty();
    }
    
    
    
    public function OutputImageWidth() 
    {
        if (func_num_args() == 0) return $this->getProperty();        
        
        //** if width set to out of bounds chnage to default values
        $value = func_get_arg(0); 
        
        if (is_null($value)) $value = -1;
        
        if ($value < MapServerConfiguration::imageMinWidth()) $value = MapServerConfiguration::imageMinWidth();
        if ($value > MapServerConfiguration::imageMaxWidth()) $value = MapServerConfiguration::imageMaxWidth();
        
        return  $this->setProperty($value);
    }

    
    public function OutputImageHeight() 
    {
        if (func_num_args() == 0) return $this->getProperty();        
        
        $value = func_get_arg(0); 
        
        if (is_null($value)) $value = -1;
        
        if ($value < MapServerConfiguration::imageMinHeight()) $value = MapServerConfiguration::imageMinHeight();
        if ($value > MapServerConfiguration::imageMaxHeight()) $value = MapServerConfiguration::imageMaxHeight();
        
        return  $this->setProperty($value);
    }

    
    /***
     * Background color of image
     * - overloads
     *   - instance of RGB
     *   - int Red, int Green, int Blue
     * 
     */
    public function OutputImageBackgroundColour() 
    {
        if (func_num_args() == 0) return $this->getProperty();        
        
        $value = func_get_arg(0); 
        if ( !($value instanceof RGB))  
        {
            $value = new RGB(); //** not RGB class so set as new
            if (func_num_args() == 3) 
            {
                  //** assume they meant to say RGB
                  $value->Red(func_get_arg(0));
                $value->Green(func_get_arg(1));
                 $value->Blue(func_get_arg(2));
            }
        }
        
        
        $this->setProperty($value);
        
        $result = $this->getProperty();  
        $result instanceof RGB;         //** type hint - really so that netbeans can hint properly
        
        return  $result;
    }
    
    public function Caption() {
        if (func_num_args() == 0) return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }

    
    /***
     * Convert string to Spatial Extent - $src format  "{West} {South} {East} {North}"
     * 
     * @param $default: if not a well foprmatted string then return current extent
     * 
     */
    public static function SpatialExtentFromString($src)
    {
        
        $split = explode(" ",$src);
        if (count($split) != 4) return $this->Extents();
        
        $result = new SpatialExtent();
         $result->West($split[0]);
        $result->South($split[1]);
         $result->East($split[2]);
        $result->North($split[3]);
        
        return $result;
        
    }

    
    
}
?>