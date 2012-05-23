<?php

class TimeSearch extends Object implements iAction {

    public function __construct() {
        parent::__construct();
        $this->Name(__CLASS__);
        $this->Description("Time search");
        $this->AllValues("TimeAllValues");
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

    /**
     *
     * @return string - Action Class - Return list of all Time Slices
     */
    public function AllValues() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }




}

?>
