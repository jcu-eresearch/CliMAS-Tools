<?php

class ContextLayerAustralianStates extends Object implements iAction {

    public function __construct() {
        parent::__construct();
        $this->Name(__CLASS__);
    }


    public function __destruct() {
        parent::__destruct();
    }

    public function Execute()
    {

        $this->Filename("/www/eresearch/source/context/Australia/AustralianStates.shp");
        $this->SpatialDatatype(spatial_util::$SPATIAL_TYPE_LINE);
        $this->Attribute('ISLAND_NAM');
        return $this;

    }

    public function Filename() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }

    public function SpatialDatatype() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }

    public function Description() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }

    public function Attribute() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }


}



?>

