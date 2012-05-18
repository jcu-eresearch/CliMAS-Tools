<?php

class ContextLayerAustralianStates extends SpatialDescription implements iAction {

    public function __construct() {
        parent::__construct();
        $this->Name(__CLASS__);
    }


    public function __destruct() {
        parent::__destruct();
    }

    public function Execute()
    {
        $this->Name("Australian States");
        $this->Filename(configuration::ContextSpatialLayersFolder()."//**States/**AustralianStates.shp");
        $this->SpatialDatatype(spatial_util::$SPATIAL_TYPE_LINE);
        $this->Attribute('ISLAND_NAM');
        return $this;

    }



}



?>

