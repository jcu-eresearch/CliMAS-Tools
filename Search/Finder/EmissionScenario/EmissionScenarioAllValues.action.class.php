<?php

class EmissionScenarioAllValues extends Action implements iAction {

    public function __construct() {
        parent::__construct();
        $this->ActionName(__CLASS__);
        $this->Description("A list of Emission Scenarios");
        $this->FinderName("EmissionScenario");
    }


    public function __destruct() {
        parent::__destruct();
    }

    public function Execute()
    {
        $result = ToolsData::EmissionScenarios();

        $this->Result($result);
        return $result;
    }


}

?>
