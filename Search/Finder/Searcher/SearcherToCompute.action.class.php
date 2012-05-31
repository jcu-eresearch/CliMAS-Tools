<?php

class SearcherToCompute extends Action {

    public function __construct() {
        parent::__construct();
        $this->ActionName(__CLASS__);
        $this->FinderName("SearcherFinder");

    }

    public function __destruct() {
        parent::__destruct();
    }

    /*
     *
     * @return array ActionClassname Search Actions for Finders
     */
    public function Execute()
    {
        // look into session and find what Species, Scenario, TIme and MOdles are selected

//        $o = $this->descriptionsOutput();
//        $o->DescriptionTemplate('<a href="{Value}">{Value}</a>');

        print_r(Session::SessionVariablesForApplication());

        /*
         * [ActionsToBeMapped] => Array ( [ContextLayerAustralianStates] => ContextLayerAustralianStates [ContextLayerAustralianRiverBasins] => ContextLayerAustralianRiverBasins )
         * [MAP_EXTENT] => 124.05662341892 -22.264512887509 137.84288405521 -13.654570387491
         * [SpeciesAllValues] => GOULFINC CASSOWARY
         * [SpeciesComputed]
         * [SpeciesTaxanomicNames]
         * [EmissionScenarioSearch] => RCP45 RCP3PD RCP6
         * [TimeSearch] => 2015 2035
         * [ClimateModelSearch] => ccsr-miroc32med
         * [SpeciesSearch] => )
         */

        // need to write these to a file for the HPC to run
        // or write the script file here and copy to server ?

        // TODO:  here we can save the variable set and username so they can pick it up.

        // get sepcies

        $toCompute = array();
        $toCompute['Species'] = $this->getSpeciesIDs();
        $toCompute['EmissionScenario'] = $this->getIDs("EmissionScenarioSearch");
        $toCompute['ClimateModel'] = $this->getIDs("ClimateModelSearch");
        $toCompute['Time'] = $this->getIDs("TimeSearch");

        $this->compute($toCompute);

        $this->Result($toCompute);

        return $toCompute;
    }


    /**
     *
     *
     *
     */
    private function compute($toCompute)
    {
        $result = RemoteCommand::ComputeAnySpecies($toCompute);
        return $result;
    }


    private function getSpeciesIDs()
    {

        $speciesSearch = FinderFactory::Find("SpeciesSearch");
        $speciesSearch instanceof SpeciesSearch;
        $actions = $speciesSearch->Subsets();
        $actions instanceof Actions;

        // get species ID's that have been selected - fropm the various Actions that user has been given
        $speciesIDsString = "";
        foreach ($actions->ActionNames() as $actionName)
        {
            $speciesIdForAction = Session::get($actionName,"");
            if ($speciesIdForAction != "")
                $speciesIDsString .= " ". Session::get($actionName,"");
        }

        $speciesIDsString = trim($speciesIDsString);

        if ($speciesIDsString == "") return null;

        $result = array();
        $speciesIDs = array_unique(explode(" ", $speciesIDsString));

        foreach ($speciesIDs as $value)
        {
            $value = trim($value);
            if ($value != "") $result[$value] = $value;
        }
        

        return $result;

    }


    private function getIDs($sessionName)
    {
        $IDs = Session::get($sessionName,"");
        $IDs = trim($IDs);

        if ($IDs == "") return null;

        foreach (explode(" ",$IDs) as $key => $value)
        {
            $result[$value] = $value;
        }

        return $result;

    }


}

?>
