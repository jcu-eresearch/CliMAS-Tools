<?php

class SpeciesTaxanomicNames extends Action implements iAction {

    public function __construct() {
        parent::__construct();
        $this->ActionName(__CLASS__);
        $this->FinderName("SpeciesFinder");
        $this->Description("By Taxanomic Name");

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

}

?>
