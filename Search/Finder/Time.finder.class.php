<?php
/**
 *
 * Return lists of TIme Periods and standard / predefined subsets
 *
 */
class TimeFinder extends Finder  {

    public function __construct() {
        parent::__construct($this);
        $this->FinderName(__CLASS__);
        $this->DefaultAction("Search");
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
