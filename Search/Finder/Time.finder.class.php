<?php
/**
 *
 * Return lists of TIme Periods and standard / predefined subsets
 *
 */
class TimeFinder extends aFinder  {

    public function __construct() {
        parent::__construct($this);
        $this->Name("Time");
        $this->DefaultAction("List");
    }

    public function __destruct() {
        parent::__destruct();

    }

    public function Description()
    {
        return "Time";
    }


}

?>
