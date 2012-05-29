<?php

/**
 * 
 *
 * @author Adam Fakes (James Cook University)
 */
class SpeciesSearchOutput extends Output
{


    public function __construct() {
        parent::__construct();
        $this->Name(__CLASS__);

    }

    public function __destruct() {
        parent::__destruct();

    }

    private function speciesSearch()
    {
        $result = $this->Source();
        $result  instanceof SpeciesSearch;
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

        $actions = $this->speciesSearch()->Subsets();

        // does and outputtter for each subset exist

        foreach ($actions->Actions()  as $action)
        {

        }




        $rowTemplate = '<input type="BUTTON" class="Button" id="{#key#}" onClick="action(\'{#key#}\')" value="{#value#}"><br>';
        
        $result = array_util::FromTemplate($actions->Descriptions(), $rowTemplate);
        return join("",$result);
    }

}


?>
