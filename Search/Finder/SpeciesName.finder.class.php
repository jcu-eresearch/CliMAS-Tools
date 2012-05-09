<?php

/* 
 * CLASS: SpeciesFinder
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
class SpeciesNameFinder extends aFinder  {
    
    
    public function __construct() { 
        parent::__construct();        
        $this->Name(__CLASS__);
        
        
    }
    
    public function __destruct() {
        parent::__destruct();
        
        
        
    }

    
}

?>
