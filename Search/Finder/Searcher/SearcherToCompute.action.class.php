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

        // here we may run off and check to see if we already have the data here.
        // currently we are creating a command but this might be an action if we already have the data

        $haveData = false;

        if($haveData)
        {

        }
        else
        {
            // this should just create a command to be executed, and don't woirry abaout getting status back.'
            CommandUtil::Queue($this->getCommand());
        }

        $result = "Command sent";

        $this->Result($result);
        return $result;
    }

    private function getCommand()
    {
        /*
         * [ActionsToBeMapped] => Array ( [ContextLayerAustralianStates] => ContextLayerAustralianStates [ContextLayerAustralianRiverBasins] => ContextLayerAustralianRiverBasins )
         * [MAP_EXTENT] => 124.05662341892 -22.264512887509 137.84288405521 -13.654570387491
         */

        $speciesCommand = new SpeciesMaxent();
        $speciesCommand->SpeciesIDs(FinderFactory::GetMethodResult("SpeciesFinder","SelectedSpeciesIDs"));
        $speciesCommand->EmissionScenarioIDs(Session::get("EmissionScenarioSearch", ""));
        $speciesCommand->ClimateModelIDs(Session::get("ClimateModelSearch", ""));
        $speciesCommand->TimeIDs(Session::get("TimeSearch", ""));

        $speciesCommand->Result("");

        return $speciesCommand;
        
    }

}

?>
