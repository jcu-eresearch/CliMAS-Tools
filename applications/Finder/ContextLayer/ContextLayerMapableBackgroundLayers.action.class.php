<?php
/**
 *
 * 
 *  
 */
class ContextLayerMapableBackgroundLayers extends Action implements iAction {

    public function __construct() {
        parent::__construct();
        $this->ActionName(__CLASS__);
        $this->FinderName("ContextLayerFinder");
    }


    public function __destruct() {
        parent::__destruct();
    }

    /**
     *
     * @return type
     */
    public function Execute()
    {

        $d = new Descriptions();

        $d->Add( FinderFactory::Result("ContextLayerAustralianRiverBasins"));
        $d->Add( FinderFactory::Result("ContextLayerAustralianStates"));
        $d->Add( FinderFactory::Result("ContextLayerAustralianVegetation"));
        
        $this->Result($d);

        return $d;
    }

}
?>

