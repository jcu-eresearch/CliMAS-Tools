<?php

class SpeciesAllValues extends Action implements iAction {

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
        $result = array();;
        $result[] = "GOULFINC";
        $result[] = "RAVEN";

        $this->Result($result);

        return $result;
    }

    private function checkLocal()
    {
        // read database here



    }


}

?>
