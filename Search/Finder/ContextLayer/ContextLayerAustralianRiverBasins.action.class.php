<?php

/**
 *
 * Describe map of River Basins for Australia
 *
 */
class ContextLayerAustralianRiverBasins extends Action implements iAction {

    public function __construct() {
        parent::__construct();
        $this->ActionName(__CLASS__);
        $this->FinderName("ContextLayerFinder");

    }

    public function __destruct() {
        parent::__destruct();
    }

    public function Execute()
    {

        $d = new SpatialDescription();

        $d->DataName("ContextLayerAustralianRiverBasins");
        $d->Filename(configuration::ContextSpatialLayersFolder()."Australia/RiverBasins1997/rbasin_polygon.shp");
        $d->SpatialDatatype(spatial_util::$SPATIAL_TYPE_LINE);
        $d->Attribute('BNAME');
        $d->Description("Australian River Basins");

        $this->Result($d);

        return $d;
    }



}



?>

