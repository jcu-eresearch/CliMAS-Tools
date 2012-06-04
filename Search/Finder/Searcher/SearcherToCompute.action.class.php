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


        $speciesCommand = new SpeciesMaxentCommand();
        $speciesCommand->SpeciesIDs(FinderFactory::GetMethodResult("SpeciesFinder","SelectedSpeciesIDs"));
        $speciesCommand->EmissionScenarioIDs(Session::get("EmissionScenarioSearch", ""));
        $speciesCommand->ClimateModelIDs(Session::get("ClimateModelSearch", ""));
        $speciesCommand->TimeIDs(Session::get("TimeSearch", ""));

        CommandFactory::Queue($speciesCommand);
        
        $updated = CommandFactory::QueueStatus($speciesCommand);

        $result = $updated->Result();



        $this->Result($result);
        return $result;
    }

}

?>
