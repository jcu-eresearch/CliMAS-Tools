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
        $result = "Emission Scenario Search";
        return $result;
    }

    public function Description() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }


}

?>
