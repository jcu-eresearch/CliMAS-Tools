<?php
/**
 *
 * Return lists of Species names and their associate ranges, thresholds, and subsets
 *
 */
class SpeciesFinder extends Finder  {

    public function __construct() {
        parent::__construct($this);
        $this->FinderName(__CLASS__);
        $this->DefaultAction("");
    }

    public function __destruct() {
        parent::__destruct();

    }

    public function Description()
    {
        return "Species";
    }




}

?>
