<?php

class SpeciesTaxanomicNames extends Object implements iAction {

    public function __construct() {
        parent::__construct();
        $this->Name(__CLASS__);
        $this->Description("Taxanomic Names");


    }


    public function __destruct() {
        parent::__destruct();
    }

    public function Execute()
    {
        // TODO:: Will be array of Descriptions

        $result = array();
        $result["Birds"][] = "GOULFINC";
        $result["Birds"][] = "RAVEN";

        $this->Result($result);

        return $result;
    }

    public function Description() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }

    public function Result() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }


}

?>
