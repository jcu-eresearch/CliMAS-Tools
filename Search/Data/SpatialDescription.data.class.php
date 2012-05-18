<?php

/**
 * 
 *        
 * 
 *   
 */
class SpatialDescription extends Description {

    public function __construct() { 
        parent::__construct();
        $this->Name(__CLASS__);
    }
    
    public function __destruct() {    
        parent::__destruct();
    }

    /**
     * @property
     * @return type
     */
    public function SpatialDatatype() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }

    /**
     * @property
     * @return type
     */
    public function Attribute() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }
    
    
}

?>
