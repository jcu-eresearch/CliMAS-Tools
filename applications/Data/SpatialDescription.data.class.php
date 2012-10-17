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
        $this->DataName(__CLASS__);

        $this->SpatialDatatype();
        $this->Attribute();
        $this->ColourRamp();

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
    

    public function ColourRamp() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }
    
    public function HistogramBuckets() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }
    
}
?>