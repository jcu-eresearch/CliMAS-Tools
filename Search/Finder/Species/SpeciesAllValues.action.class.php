<?php

class SpeciesAllValues extends Action {

    public function __construct() {
        parent::__construct();
        $this->ActionName(__CLASS__);
        $this->Description("All Species");
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

    private function checkLocal()
    {
        // read database here



    }


}

?>
