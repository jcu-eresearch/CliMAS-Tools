<?php

/**
 *        
 * Return lists of Variable names and their associate ranges, thresholds, and subsets
 *
 */
class SearcherFinder extends Finder  {

    public function __construct() {
        parent::__construct($this);
        $this->Name("Searcher");
        $this->DefaultAction("Names");
    }

    public function __destruct() {
        parent::__destruct();

    }

    public function Description()
    {
        return "Searchers";
    }


}

?>
