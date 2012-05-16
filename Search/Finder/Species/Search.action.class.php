<?php

class SpeciesSearch extends Object implements iAction {

    public function __construct() {
        parent::__construct();
        $this->Name("Species");
        $this->Description("Species");
    }


    public function __destruct() {
        parent::__destruct();
    }

    public function Execute()
    {
        $result = "Species Search";
        return $result;
    }

    public function Description() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }


}

?>
