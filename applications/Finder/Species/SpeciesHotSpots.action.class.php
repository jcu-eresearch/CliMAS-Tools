<?php

/**
 *   
 * @package CommandAction\Species\SpeciesMaxent
 * 
 * Wrapper for Maxent.jar and assocoiated projections
 * 
 * 
 */
class SpeciesHotSpots extends CommandAction {
    
    
    
    public function __construct() {
        parent::__construct();
        $this->CommandName(__CLASS__);
        $this->FinderName("SpeciesFinder");
        
    }

    public function __destruct() {
        parent::__destruct();
    }

    
    /**
     * This is run from the web server side 
     * - so NO processing here 
     * - check to see if this has already been run / outputs or other items have already been processed 
     * 
     * @param src array 
     *  species   => space delimited string of values
        scenario  => space delimited string of values
        model     => space delimited string of values
        time      => space delimited string of values
     * 
     */
    public function initialise($src = null) 
    {

        $this->ExecutionFlag(CommandAction::$EXECUTION_FLAG_READY);      // if we already have the dat - no reason to go to the server        
        
        if (!is_null($src))
        {
            // init properties
            foreach ($src as $property_name => $value) 
            {
                $this->setPropertyByName($property_name, $value);
            }
            
        }
        
        $this->Status('Hotspot Calculation initialised');
        $this->ProgressPercent(1);
        $this->initialised(true);
        
        return true;
    }
    
    
    
    /**
     * Runnning on GRID 
     * - Look at inititalisation variables 
     * - get Species occurances for any species that we don't have
     * - get current progress, if other process have already done parts of this call.
     * - launch other jobs thru QSUB that will be able to run paralell jobs for maxent i.e. Future Projections
     * 
     * @return type 
     */
    public function Execute()
    {
        
        $this->QsubCollectionID(substr(uniqid(),0,10));
        
        //
        // We have to call the grid because at least one output is missing
        //
        
        $this->Status("Retreiving Data to build hotspot" );
        self::Queue($this);

        $this->calculateMedian();
        
        
        $this->Status("Hotspot Compution Completed");
        $this->ExecutionFlag(CommandAction::$EXECUTION_FLAG_COMPLETE);        
        self::Queue($this);

    }
    
    private function calculateMedian()
    {

        // collect list of filenames that will be used
        
        // group filenames into   Scenario_Time =>  Climate Model 1, Climate Model 2 ....
        
        
    }
    
    
    public function Result() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }

    
    public function cmdaction() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }
    
    public function inputType() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }

    public function inputID() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }

    public function inputName() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }
    
    public function models() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }
    
    public function scenarios() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }
    
    
    public function times() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }
    
    
    public function bioclims() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }
    

    public function FinderName() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }

    public function QsubCollectionID() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }

    public function ProgressPercent() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }
    
    
    public function AttachedCommand() 
    {
        return $this;
    }
    
    
    /**
     * Make sure all outputs for a model run exist
     * 
     * @param type $species
     * @param type $scenario
     * @param type $model
     * @param type $time
     * @return null 
     */
    public function GetModelledData($combination)
    {

        list($scenario, $model, $time) = explode("_",$combination);            
        
        $asc_file_id        = null;
        $quickLook_file_id  = null;
        
        switch (strtolower($this->inputType())) {
            case "taxa":
                break;

            case "family":
                break;
            
            case "genus":
                break;
            
            case "species":
                $asc_file_id        = SpeciesData::GetModelledData($this->inputID(), $scenario, $model, $time,'ASCII_GRID');
                $quickLook_file_id  = SpeciesData::GetModelledData($this->inputID(), $scenario, $model, $time,'QUICK_LOOK');
                break;

            default:
                break;
        }
        
        if (is_null($asc_file_id) || is_null($quickLook_file_id) ) return null;
        
        return $asc_file_id;
    }

    
}


?>
