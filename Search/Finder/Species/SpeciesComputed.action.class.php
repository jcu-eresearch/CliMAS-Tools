<?php

/**
 * @package Search\Species\SpeciesComputed
 * 
 * Return list of Species that have been computed / cached.
 * 
 * - this will be tricky as some combination may an may not have been computed.
 * - given a n array of combinations return array of booleans
 *  
 */
class SpeciesComputed extends Action implements iAction {

    public function __construct() {
        parent::__construct();
        $this->ActionName(__CLASS__);
        $this->Description("Cached");
        $this->FinderName("SpeciesFinder");
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
