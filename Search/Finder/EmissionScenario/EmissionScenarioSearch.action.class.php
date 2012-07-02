<?php

class EmissionScenarioSearch extends Action implements iAction {

    public function __construct() {
        parent::__construct();
        $this->ActionName(__CLASS__);
        $this->Description("Emission Scenario");
        $this->FinderName("EmissionScenario");

    }


    public function __destruct() {
        parent::__destruct();
    }

    public function Execute()
    {
        $this->Result($this);
        return $this;
    }

    public function Subsets() {

        $result = FinderFactory::Result("EmissionScenarioAllValues");

        

        return $result;
    }


}

?>
