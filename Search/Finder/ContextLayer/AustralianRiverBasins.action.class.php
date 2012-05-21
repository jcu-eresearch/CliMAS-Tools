<?php

/**
 *
 * Describe map of River Basins for Australia
 *
 */
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
        $this->Filename(configuration::ContextSpatialLayersFolder()."/Australia/RiverBasins1997/rbasin_polygon.shp");
        $this->SpatialDatatype(spatial_util::$SPATIAL_TYPE_LINE);
        $this->Attribute('BNAME');
        $this->Description("Describe map of River Basins for Australia");
        $this->Result($this);

        return $this;
    }

    public function Result() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }




}



?>

