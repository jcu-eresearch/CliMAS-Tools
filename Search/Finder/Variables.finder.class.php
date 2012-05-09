<?php

/*
 * CLASS: VariableNamesFinder
 *        
 * Return lists of Variable names and their associate ranges, thresholds, and subsets
 *
 */
class VariablesFinder extends aFinder  {


    public function __construct() {
        parent::__construct($this);
        $this->Name(__CLASS__);

    }

    public function __destruct() {
        parent::__destruct();

    }


    public function ActionDefault()
    {
        return $this->ActionNames();
    }

    public function ActionNames()
    {
        $result = array();
        $result[] = "Species";
        $result[] = "Climate Model";
        $result[] = "Emission Scenario";
        $result[] = "Time";

        return $result;
    }



    public function ActionValues()
    {






        return $result;

    }


}

?>
