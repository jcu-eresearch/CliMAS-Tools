<?php

class ClimateModelSearch extends Action implements iAction {

    public function __construct() {
        parent::__construct();
        $this->ActionName(__CLASS__);
        $this->Description("Climate Model");
        $this->FinderName("ClimateModelFinder");
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
        return FinderFactory::Result("ClimateModelAllValues");
    }


}

?>
