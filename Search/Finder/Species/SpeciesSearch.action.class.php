<?php

class SpeciesSearch extends Object implements iAction {

    public function __construct() {
        parent::__construct();
        $this->Name(__CLASS__);
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

    public function Description() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }

    public function Result() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
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
