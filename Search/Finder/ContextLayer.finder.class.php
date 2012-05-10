
<?php

/* 
 * CLASS: ContextLayers
 *        
 * Get lists of Context data requested
 * 
 *
 * 
 * 
 *   
 */
class ContextLayerFinder extends aFinder  {

    public function __construct() { 
        parent::__construct($this);
        $this->Name(__CLASS__);
        $this->DefaultAction("AustralianStates");
    }
    
    public function __destruct() {
        parent::__destruct();
        
    }
    
}

?>

