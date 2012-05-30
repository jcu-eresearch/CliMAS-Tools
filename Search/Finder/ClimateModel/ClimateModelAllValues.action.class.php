<?php

class ClimateModelAllValues extends Action  {

    public function __construct() {
        parent::__construct();
        $this->ActionName(__CLASS__);
        $this->Description("A list of Climate Models");
        $this->FinderName("ClimateModelFinder");
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

}

?>
