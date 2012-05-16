<?php

class SearcherNames extends Object implements iAction {

    public function __construct() {
        parent::__construct();
        $this->Name(__CLASS__);
    }


    public function __destruct() {
        parent::__destruct();
    }

    public function Execute()
    {
        // look into the finders and return only those that have a 
        // Search Action

        $result = array();

        $finders = FinderFactory::Available();

        foreach ($finders as $key => $value)
        {
            $finder = FinderFactory::Find($value);

            if (array_key_exists(FindersConfiguration::$ACTION_TYPE_SEARCH, $finder->Actions()))
            {
                $action = ActionFactory::Find($finder, FindersConfiguration::$ACTION_TYPE_SEARCH);
                $result[$action->Name()] = $action->Description();
            }

        }

        return $result;
    }

    public function Description() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }



}

?>
