<?php
/**
 *
 *  Describe map of State political boundaries for Australia
 *
 */



class  ContextLayerAustralianStates extends Action implements iAction {

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

        $d->DataName("ContextLayerAustralianStates");
                                                                //Australia/States/AustralianStates.shp
        $d->Filename(configuration::ContextSpatialLayersFolder()."Australia/States/AustralianStates.shp");
        $d->SpatialDatatype(spatial_util::$SPATIAL_TYPE_LINE);
        $d->Attribute('ISLAND_NAM');
        $d->Description("Australian state boundaries");

        $this->Result($d);

        return $d;

    }



}



?>

