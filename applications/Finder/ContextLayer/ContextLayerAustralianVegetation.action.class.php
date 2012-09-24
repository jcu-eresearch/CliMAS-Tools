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

        $d->Filename(configuration::ResourcesFolder()."rasters/fred.asc");
        
        if (!file_exists($d->Filename())) return null;
        
        $d->SpatialDatatype(spatial_util::$SPATIAL_TYPE_RASTER);
        $d->Attribute('');
        $d->Description("Australian Vegetation");

        $ramp = RGB::GradientGreenBeige();
        
        $ramp[0] = null;
        
        $d->ColourRamp($ramp);
        
        $d->HistogramBuckets(250);
        
        $this->Result($d);

        return $d;

        

    }



}



?>

