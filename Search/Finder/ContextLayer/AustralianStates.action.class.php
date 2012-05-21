<?php
/**
 *
 *  Describe map of State political boundaries for Australia
 *
 */
class ContextLayerAustralianStates extends SpatialDescription implements iAction {

    public function __construct() {
        parent::__construct();
        $this->Name(__CLASS__);
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
        $this->Name("Australian States");
        $this->Filename(configuration::ContextSpatialLayersFolder()."/Australia/States/AustralianStates.shp");
        $this->SpatialDatatype(spatial_util::$SPATIAL_TYPE_LINE);
        $this->Attribute('ISLAND_NAM');
        $this->Description("Describe map of State Political boundaries for Australia");

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

