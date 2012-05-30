<?php

class TimeSearch extends Action {

    public function __construct() {
        parent::__construct();
        $this->ActionName(__CLASS__);
        $this->Description("Time");
        $this->FinderName('TimeFinder');
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
        return FinderFactory::Result("TimeAllValues");
    }





}

?>
