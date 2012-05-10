<?php



class VariablesNames extends Object implements iAction {

    public function __construct() {
        parent::__construct();
        $this->Name(__CLASS__);
    }


    public function __destruct() {
        parent::__destruct();
    }


    public function Execute()
    {

        $result = array();
        $result[] = "Species";
        $result[] = "Climate Model";
        $result[] = "Emission Scenario";
        $result[] = "Time";

        return $result;

    }

}




?>
