<?php
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
        $result = array();;
        $result[] = "GOULFINC";

        $this->Result($result);

        return $result;
    }


}

?>
