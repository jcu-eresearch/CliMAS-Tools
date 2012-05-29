<?php

/**
 * 
 *
 * @author Adam Fakes (James Cook University)
 */
class EmissionScenarioSearchOutput extends Output
{


    public function __construct() {
        parent::__construct();
        $this->Name(__CLASS__);

    }

    public function __destruct() {
        parent::__destruct();

    }

    private function search()
    {
        $result = $this->Source();
        $result  instanceof EmissionScenarioSearch;
        return  $result;
    }


    public function Title()
    {
        return configuration::ApplicationName()."::Species Search";
    }


    public function Head()
    {
        return "";

    }

    public function Content()
    {

        $values = $this->search()->Subsets();
        $rowTemplate = '<input type="BUTTON" class="Button" id="{#key#}" onClick="action(\'{#key#}\')" value="{#value#}"><br>';
        
        $result = array_util::FromTemplate($values, $rowTemplate);
        return join("",$result);
    }

}


?>
