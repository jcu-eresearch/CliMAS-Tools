<?php

/**
 *   
 * @package CommandAction\Species\SpeciesMaxent
 * 
 * Wrapper for Maxent.jar and assocoiated projections
 * 
 * 
 */
include_once 'SpeciesRichness.action.class.php';

class SpeciesHotSpots extends SpeciesRichness {
    
    
    public function __construct() {
        parent::__construct();

        $this->NiceID(str_replace(".", "_", $this->ID()));
        
        
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
        
        // clean up parameters
        $this->cleanParameters();
        $this->clazz($this->taxa()); // the UI send Taxa we need to pass the Riuchness as Clazz
        
        $this->initialised(true);
        $this->UpdateStatus('Hotspot Calculation initialised');
        
        
        return true;
        
    }
    
    
    private function cleanParameters() 
    {
        
        if (!is_null($this->scenarios())) $this->scenarios(array_util::Replace($this->scenarios(), "Scenarios_", ""));
        if (!is_null($this->times()    )) $this->times    (array_util::Replace($this->times(),     "Times_",     ""));

        if (!is_null($this->taxa()))    $this->taxa(     array_util::Replace($this->taxa(),      "Taxa_",      ""));
        if (!is_null($this->family()))  $this->family(   array_util::Replace($this->family(),    "Family_",    ""));
        if (!is_null($this->genus()))   $this->genus(    array_util::Replace($this->genus(),     "Genus_",     ""));
        if (!is_null($this->species())) $this->species(  array_util::Replace($this->species(),   "Species_",   ""));
        
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
        
        $this->ExecutionFlag(CommandAction::$EXECUTION_FLAG_RUNNING);
        $this->UpdateStatus("Richness Computation Ready");
        
        ErrorMessage::Marker("After Richness Computation Ready " .print_r($this->times(),true));
        
        parent::Execute();
        
        
        // create metadata text for this process / job
        
        $this->createMetadata();
        $this->packageData();
        
        
        $this->ExecutionFlag(CommandAction::$EXECUTION_FLAG_COMPLETE);
        $this->UpdateStatus("Richness Computation Completed");

    }
    
    
    /**
     *Create metadata for this jhob and write toi a file that will be package later
     *  
     */
    private function createMetadata()
    {
        
        $file_template =
                configuration::ResourcesFolder().'metadata/RichnessHeader.txt'.
                configuration::ResourcesFolder().'metadata/RichnessBody.txt'.
                configuration::ResourcesFolder().'metadata/RichnessFooter.txt'
                ;
        
        //{DateTime}
        //{UserTaxaList}
        //{UserFamilyList}
        //{UserGenusList}
        //{UserSpeciesList}
        //{ComputedSpeciesList}
        
        
        
        
        // string replacement of {VariableName} strings from src array
        
        
    }
    

    /**
     * Package data into zip file for download
     *  
     */
    private function packageData()
    {
     
        
        
        
    }
    
    
    /**
     * Override Update status so if the Species Richness status is updated it comes through here
     * - which wiull then up date the status on the database
     * @param type $msg 
     */
    protected function UpdateStatus($msg)
    {
        
        $this->Status($msg);
        
        if (php_sapi_name() == "cli")
        {
            ErrorMessage::Marker($msg);
            self::Queue($this);    
        }
        else 
        {
            self::Queue($this);    
        }
        
        
    }

    
    
    public function NiceID() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));        
    }

    
    
    public function taxa() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }


    public function FinderName() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }

    
    
    public function ui_element_id()
    {
        if (func_num_args() == 0) return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }
    
    
    
}


?>
