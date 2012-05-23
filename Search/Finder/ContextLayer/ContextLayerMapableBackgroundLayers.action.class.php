<?php
/**
 *
 * 
 *  
 */

class ContextLayerMapableBackgroundLayers extends Object implements iAction {

    public function __construct() {
        parent::__construct();
        $this->Name(__CLASS__);

    }


    public function __destruct() {
        parent::__destruct();
    }

    /**
     *
     * @return type
     */
    public function Execute()
    {

        //TODO will be get descirptiosn from clasas
        $result = array();
        $result['ContextLayerAustralianRiverBasins'] = "Australian River Basins";
        $result['ContextLayerAustralianStates'] = "Australian State borders";

        $this->Result($result);

        return $this;
    }

    public function Result() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }


    public function Description() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }


}



?>

