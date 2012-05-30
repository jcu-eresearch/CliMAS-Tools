<?php

class SearcherFinders extends Action implements iAction {

    public function __construct() {
        parent::__construct();
        $this->ActionName(__CLASS__);
        $this->FinderName("SearcherFinder");
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



}

?>
