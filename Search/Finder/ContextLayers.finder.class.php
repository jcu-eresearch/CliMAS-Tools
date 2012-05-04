
<?php

/* 
 * CLASS: ContextLayers
 *        
 * Get lists of Context data requested
 * 
 *
 * 
 * 
 *   
 */
class FinderContextLayers extends aFinder  {
    
    
    public function __construct() { 
        parent::__construct();        
        $this->addAction(self::$SPATIAL_TYPE_POLYLINE);
        
    }
    
    public function __destruct() {
        parent::__destruct();
        
    }
    
    
    public function Find()
    {
        
        switch ($this->useAction) {
            case self::$SPATIAL_TYPE_POLYLINE:
                return $this->Polylines();
                break;

            default:
                break;
        }
        
    }

    /*
     * Find shapefiles where Filename contains the filter key
     * 
     * use filter list as Filter In
     * 
     */
    public function Polylines()
    {
        // here we can use the filters to find more specific Polylines

        $result = array();
        $result[] = "/www/eresearch/source/context/Australia/AustralianStates.shp";
        $this->Result($result);
        
    }
    
    public static $FILE_NAME = "FILENAME";
    public static $ATTRIBUTE_COLUMN_NAME = "ATTRIBUTE_COLUMN_NAME";
    
    public static $SPATIAL_TYPE_POLYLINE = "POLYLINE";
    public static $SPATIAL_TYPE_POLYGON  = "POLYGON";
    public static $SPATIAL_TYPE_POINT    = "POINT";
    public static $SPATIAL_TYPE_RASTER   = "RASTER";
    
    
}

?>

