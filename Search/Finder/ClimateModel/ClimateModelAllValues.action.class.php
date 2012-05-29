<?php

class ClimateModelAllValues extends Object implements iAction {

    public function __construct() {
        parent::__construct();
        $this->Name("ClimateModelAllValues");
        $this->Description("A list of Climate Models");
    }

    public function __destruct() {
        parent::__destruct();
    }

    public function Execute()
    {
        $result = ToolsData::ClimateModels();
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
