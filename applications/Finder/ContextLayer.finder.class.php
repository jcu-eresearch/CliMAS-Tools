<?php
/**
 *        
 * Get lists of Context data requested
 *
 *
 *
 */
class ContextLayerFinder extends Finder  {

    public function __construct() { 
        parent::__construct($this);
        $this->Name("ContextLayer");
        $this->DefaultAction("AustralianVegetation");
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

