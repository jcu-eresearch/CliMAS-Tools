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

        $result = array();

        $searchActions = FinderFactory::findActionByString("Search");

        foreach ($searchActions as $actionClassname => $actionName)
        {
            $result[$actionClassname] = str_replace($actionName, "", $actionClassname);
        }
            

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
