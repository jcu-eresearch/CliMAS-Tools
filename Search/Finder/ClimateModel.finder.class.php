<?php
/*
 * CLASS: SpeciesFinder
 *
 * Return lists of Species names and their associate ranges, thresholds, and subsets
 *
 */
class ClimateModelFinder extends aFinder  {

    public function __construct() {
        parent::__construct($this);
        $this->Name("ClimateModel");
        $this->DefaultAction("List");
    }

    public function __destruct() {
        parent::__destruct();

    }

    public function Description()
    {
        return "Climate Model";
    }


}

?>
