<?php

class EmissionScenarioSearch extends Object implements iAction {

    public function __construct() {
        parent::__construct();
        $this->Name("EmissionScenario");
        $this->Description("Emission Scenario");
    }


    public function __destruct() {
        parent::__destruct();
    }

    public function Execute()
    {
        $this->Result($this);
        return $this;
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
