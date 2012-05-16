<?php
/*
 * CLASS: SpeciesFinder
 *
 * Return lists of Species names and their associate ranges, thresholds, and subsets
 *
 */
class EmissionScenarioFinder extends aFinder  {

    public function __construct() {
        parent::__construct($this);
        $this->Name("EmissionScenario");
        $this->DefaultAction("List");
    }

    public function __destruct() {
        parent::__destruct();

    }

    public function Description()
    {
        return "EmissionScenario";
    }


}

?>
