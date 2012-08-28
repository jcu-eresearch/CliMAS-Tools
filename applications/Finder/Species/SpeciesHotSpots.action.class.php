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
        
        if (!is_null($this->models()   )) $this->models   (array_util::Replace($this->models(),    "Models_",    ""));
        if (!is_null($this->scenarios())) $this->scenarios(array_util::Replace($this->scenarios(), "Scenarios_", ""));
        if (!is_null($this->times()    )) $this->times    (array_util::Replace($this->times(),     "Times_",     ""));
        if (!is_null($this->bioclims() )) $this->bioclims (array_util::Replace($this->bioclims(),  "Bioclims_",  ""));

        if (!is_null($this->taxa()))    $this->taxa(     array_util::Replace($this->taxa(),      "Taxa_",      ""));
        if (!is_null($this->family()))  $this->family(   array_util::Replace($this->family(),    "Family_",    ""));
        if (!is_null($this->genus()))   $this->genus(    array_util::Replace($this->genus(),     "Genus_",     ""));
        //if (!is_null($this->species())) $this->species(  array_util::Replace($this->species(),   "Species_",   ""));
        
        
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
        
        
        $this->ExecutionFlag(CommandAction::$EXECUTION_FLAG_COMPLETE);        
        $this->UpdateStatus("Hotspot Computation Ready");

        
        if (is_array($this->genus()))
        {
            $g = array();
            array_util::CopyTo($this->genus(), $g);

            foreach ($g as $genus) 
            {
                $this->genus($genus);
                parent::Execute();    
                
                
                
            }
            
        }
        else
        {
            parent::Execute();
        }
        
        
        $this->ExecutionFlag(CommandAction::$EXECUTION_FLAG_FINALISE);
        $this->UpdateStatus("Hotspot Computation Completed");

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

    public function QsubCollectionID() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }

    
    
    public function AttachedCommand() 
    {
        return $this;
    }
    
    
    public function ui_element_id()
    {
        if (func_num_args() == 0) return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }
    
    
    
}


?>
