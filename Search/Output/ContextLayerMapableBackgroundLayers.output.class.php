<?php

/**
 * 
 *
 * @author Adam Fakes (James Cook University)
 */
class ContextLayerMapableBackgroundLayersOutput extends Output
{

    public function __construct() {
        parent::__construct();
        $this->OutputName(__CLASS__);

    }

    public function __destruct() {
        parent::__destruct();

    }

    public function Head()
    {
        return "";

    }

    public function Content()
    {

        $src = $this->Source();
        $src instanceof ContextLayerMapableBackgroundLayers;


        $result = array();
        foreach ($src->Result() as $desc)
        {
            $desc instanceof SpatialDescription;

            $result[$desc->_ClassName()]= array();
            $result[$desc->_ClassName()]['Name'] = $desc->Name();
            $result[$desc->_ClassName()]['Description'] = $desc->Description();
            $result[$desc->_ClassName()]['Classname'] = $desc->_ClassName();
            
        }

        return $result;

    }


}

?>
