<?php

class TimeAllValues extends Object implements iAction {

    public function __construct() {
        parent::__construct();
        $this->Name(__CLASS__);
        $this->Description("All available values for Time");
    }


    public function __destruct() {
        parent::__destruct();
    }

    public function Execute()
    {
        $result = array();
        $result[1975] = 'Base conditions';
        $result[1990] = '1990';
        $result[2000] = '2000';
        $result[2010] = '2010';
        $result[2020] = '2020';
        $result[2030] = '2030';
        $result[2040] = '2040';
        $result[2050] = '2050';
        $result[2060] = '2060';
        $result[2070] = '2070';
        $result[2080] = '2080';

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
