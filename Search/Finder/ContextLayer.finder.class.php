
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
        $this->Name("ContextLayer");
        $this->DefaultAction("AustralianStates");
    }
    
    public function __destruct() {
        parent::__destruct();
        
    }

    public function Description()
    {
        return "Context Layers available to be added to maps";
    }


}

?>

