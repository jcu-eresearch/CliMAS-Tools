<?php

class SpeciesMaxentCommand extends Command {
    //put your code here


    public function __construct() {
        parent::__construct();
        $this->ActionName("SpeciesMaxentAction");
        $this->LocationName(CommandConfiguration::$LOCATION_HPC);
        $this->Description("Run the Species Maxent Compututations on HPC");

    }

    public function __destruct() {
        parent::__destruct();
    }


    public function SpeciesIDs() {
        if (func_num_args() == 0) return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }

    public function EmissionScenarioIDs() {
        if (func_num_args() == 0) return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }

    public function ClimateModelIDs() {
        if (func_num_args() == 0) return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }

    public function TimeIDs() {
        if (func_num_args() == 0) return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }

}


?>
