<?php

class VariablesFinders extends Object implements iAction {

    public function __construct() {
        parent::__construct();
        $this->Name(__CLASS__);
    }


    public function __destruct() {
        parent::__destruct();
    }


    public function Execute()
    {

        $names = FinderFactory::Result("Variables", "Names");

        $result = array();
        foreach ($names as $key => $value)
            $result[$key] = $key.FindersConfiguration::$CLASS_NAME_SUFFIX_FINDER;

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
