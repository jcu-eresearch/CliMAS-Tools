<?php

class TimeSearch extends Object implements iAction {

    public function __construct() {
        parent::__construct();
        $this->Name("Time");
        $this->Description("Time");
    }


    public function __destruct() {
        parent::__destruct();
    }

    public function Execute()
    {
        $result = "Time Searcher";
        return $result;
    }

    public function Description() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }


}

?>
