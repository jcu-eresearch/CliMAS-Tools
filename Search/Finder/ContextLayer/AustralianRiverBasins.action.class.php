<?php

class ContextLayerAustralianRiverBasins extends SpatialDescription implements iAction {

    public function __construct() {
        parent::__construct();
        $this->Name(__CLASS__);
    }


    public function __destruct() {
        parent::__destruct();
    }

    public function Execute()
    {
        $this->Name("Australian River Basins");
        $this->Filename(configuration::ContextSpatialLayersFolder()."//**RiverBasins1997/**rbasin_polygon.shp");
        $this->SpatialDatatype(spatial_util::$SPATIAL_TYPE_LINE);
        $this->Attribute('BNAME');
        return $this;
    }



}



?>

