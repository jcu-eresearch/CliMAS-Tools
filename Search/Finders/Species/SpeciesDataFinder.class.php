<?php

/* 
 * CLASS: SpeciesDataFinder
 *        
 * Look at the Species Range output folders and pull out Models, Scenarios and Years
 * 
 * 
 * Threshold value of wich we don't want to see  i e set to transparent
 *
 * * Filename            Column Name
 * maxentResults.csv - "Equate entropy of thresholded and original distributions logistic threshold"
 *   
 */
class SpeciesDataFinder extends Object implements DataFinder  {
    
    
    public function __construct() { 
        parent::__construct();        
        
    }
    
    public function __destruct() {
        parent::__destruct();
    }
    
    
    public function Find() 
    {
        
    }
    
    private $result = null;
    public function Result() 
    {
        if (!is_null($this->result)) return $this->result;
        $this->result = $this->ScenarioModelMatrix();
        return $this->result;
    }

    public function ClearResult() 
    {
        $this->result = null;
    }

    /*
     * Use to limit the file list to only files that contain this model name
     */
    public function Filter() {
        if (func_num_args() == 0) return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }

    public function FilteredCount() {
        if (func_num_args() == 0) return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }
    
    public function TotalCount() {
        if (func_num_args() == 0) return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }

    /*
     * 
     * 
     */
    public function FilterTypes() 
    {
        
    }
    
    
    
}

?>
