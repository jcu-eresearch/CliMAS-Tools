<?php

/**
 * @package Species\SpeciesComputed
 * 
 * Return list of Species that have been computed / cached.
 * 
 * - this will be tricky as some combination may an may not have been computed.
 * - given a n array of combinations return array of booleans
 *  
 */
class SpeciesComputed extends Action implements iAction {

    public function __construct() {
        parent::__construct();
        $this->ActionName(__CLASS__);
        $this->Description("Cached");
        $this->FinderName("SpeciesFinder");
    }


    public function __destruct() {
        parent::__destruct();
    }
    
    public function initialise($src = null) 
    {
        $this->SourceData($src);
        
        $this->SpeciesID(array_util::Value($src,'species'));
        
        return true;
    }    
    
    
    public function Execute()
    {

        // keyed file-dis of Quick Look images available for this species
        $data_quick_look = SpeciesData::GetAllModelledData($this->SpeciesID(), 'QUICK_LOOK');

        $data_ascii_grid = SpeciesData::GetAllModelledData($this->SpeciesID(), 'ASCII_GRID');
        
        $scenarioModels = array();
        
        $sub_result = array();
        
        foreach ($data_quick_look as $combination => $row) 
        {
            //  speciesID_scenario_model_time_QuickLookFileID_AsciiGridFileID
            $sub_result[$combination] =  $this->SpeciesID()."_".$combination."_".$row['file_unique_id']."_".$data_ascii_grid[$combination]['file_unique_id']."_".$row['full_name'];
            $scenarioModels[$row['scenario_name']."_".$row['model_name']] = "";
        }

        
        $sub_result['scenarioModels'] = implode("~", array_keys($scenarioModels));
        
        $sub_result['full_name'] = SpeciesData::SpeciesQuickInformation($this->SpeciesID());
        $sub_result['species_id'] = $this->SpeciesID();
        
        
        $this->Result($sub_result);
        return $sub_result;
    }

    
    
    
    public function SpeciesID() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }
    

    public function SourceData() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }
    
    
}

?>
