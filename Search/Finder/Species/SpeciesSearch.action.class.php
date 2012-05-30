<?php

class SpeciesSearch extends Action implements iAction {

    public function __construct() {
        parent::__construct();
        $this->ActionName(__CLASS__);
        $this->FinderName("SpeciesFinder");
        $this->Description("Species");
    }


    public function __destruct() {
        parent::__destruct();
    }

    public function Execute()
    {
        $this->Result($this);
        return $this;
    }

    /**
     *
     * @return array ..  [SubsetName] => "ActionclassName"
     *
     * e.g.
     *                   [All]        => "SpeciesAllNames"
     *                   [By Taxa]    => "SpeciesByTaxa"
     *
     *
     */
    public function Subsets() {

        $result = array();

        $result[] = "SpeciesAllValues";
        $result[] = "SpeciesComputed";
        $result[] = "SpeciesTaxanomicNames";
        $result[] = "SpeciesSingle";

        $actions = new Actions();
        $actions->FromArray($result);

        return $actions;

    }

}

?>
