
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
class ContextLayerFinder extends aFinder  {
    
    
    public function __construct() { 
        parent::__construct($this);
        $this->Name(__CLASS__);

    }
    
    public function __destruct() {
        parent::__destruct();
        
    }

    /*
     * Find shapefiles where Filename contains the filter key
     * 
     * use filter list as Filter In
     * 
     */
    public function ActionAustraliaStates()
    {
        // here we can use the filters to find more specific Polylines

        $result = array();
        $result[] = "/www/eresearch/source/context/Australia/AustralianStates.shp";
        return $result;
        
    }

    public function ActionDefault()
    {
        // here we can use the filters to find more specific Polylines
        return $this->ActionAustraliaStates();
    }


    public static $FILE_NAME = "FILENAME";
    public static $ATTRIBUTE_COLUMN_NAME = "ATTRIBUTE_COLUMN_NAME";
    
    public static $SPATIAL_TYPE_POLYLINE = "POLYLINE";
    public static $SPATIAL_TYPE_POLYGON  = "POLYGON";
    public static $SPATIAL_TYPE_POINT    = "POINT";
    public static $SPATIAL_TYPE_RASTER   = "RASTER";
    
    
}

?>

