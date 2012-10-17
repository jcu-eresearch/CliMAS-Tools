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

        $species_id = $this->SpeciesID();
        
        
        $folder = SpeciesFiles::species_data_folder($species_id);
        
        
        $scenarios = array();
        $models = array();
        $times = array();
        
        $table = array();
        
        foreach (DatabaseClimate::GetScenarios() as $scenario) 
        {
            if ($scenario == "CURRENT") continue;
            
            $scenarios[$scenario] = '';
            
            foreach (DatabaseClimate::GetModels() as $model) 
            {
                
                if ($model == "CURRENT") continue;
                
                $models[$model] = '';
                
                foreach (DatabaseClimate::GetTimes() as $time) 
                {
                    if ($time < 2000) continue;

                    $times[$time] = '';
                    
                    $combination  = "{$scenario}_{$model}_{$time}";
                    $asc_filename = $folder."{$combination}.asc";
                    $png_filename = $folder."{$combination}.png";
                    
                    
                    if (file_exists($png_filename) && file_exists($asc_filename))
                        $table[] = $combination;
                    else
                        $table[] = "null"; // we don't have files for this time point
                    
                    
                }
                
            }
            
        }
        
        
        $result = array();
        
        $result['scenarios'] = implode("~",array_keys($scenarios));
        $result['models']    = implode("~",array_keys($models));
        $result['times']     = implode("~",array_keys($times));
        $result['table']     = implode("~",$table);
        
        $result['species_id'] = $this->SpeciesID();
        
        $this->Result($result);
        
        return $result;
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
