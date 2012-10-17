<?php
/**
 *
 * Return lists of Emission Scenarios
 * - will need to find what has already been calculated
 * - what has to be calculated
 *
 */
class EmissionScenarioFinder extends Finder  {

    public function __construct() {
        parent::__construct($this);
        $this->FinderName(__CLASS__);
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
