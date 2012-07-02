<?php

/**
 * @package Search\Species\SpeciesSearch
 * TODO: 
 * 
 * Find species by Taxanomic tree
 * 
 *  
 */
class SpeciesTaxanomicNames extends Action implements iAction {

    public function __construct() {
        parent::__construct();
        $this->ActionName(__CLASS__);
        $this->FinderName("SpeciesFinder");
        $this->Description("By Taxanomic Name");

    }


    public function __destruct() {
        parent::__destruct();
    }

    public function Execute()
    {
        $result = ToolsData::ComputedSpecies();


        $this->Result($result);
        return $result;
    }

}

?>
