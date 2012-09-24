<?php
/**
 *
 *  Describe map of State political boundaries for Australia
 *
 */
class ContextLayerAustralianVegetation extends Action implements iAction {

    public function __construct() {
        parent::__construct();
        $this->ActionName(__CLASS__);
        $this->FinderName("ContextLayerFinder");
    }


    public function __destruct() {
        parent::__destruct();
    }

    /**
     * 
     *  @return SpatialDescription [Political boundaries for Australia]
     */
    public function Execute()
    {
        
        $d = new SpatialDescription();

        $d->DataName("ContextLayerAustralianVegetation");
                                                                
        $d->Filename(configuration::ResourcesFolder()."Rasters/vegetation.tif");
        $d->SpatialDatatype(spatial_util::$SPATIAL_TYPE_RASTER);
        $d->Description("Australian Vegetation");

        $this->Result($d);

        return $d;

        

    }



}



?>

