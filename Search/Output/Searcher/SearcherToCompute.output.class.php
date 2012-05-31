<?php

/**
 * 
 *
 * @author Adam Fakes (James Cook University)
 */
class SearcherToComputeOutput extends Output
{

    private $output = "";

    public function __construct() {
        parent::__construct();
        $this->OutputName(__CLASS__);

    }

    public function __destruct() {
        parent::__destruct();

    }

    private function searcherToCompute()
    {
        $result = $this->Source();
        $result instanceof SearcherToCompute;
        return $result;
    }

    public function Title()
    {
        return configuration::ApplicationName()."::Outputs";
    }


    public function Head()
    {
//        $result = $this->descriptionsOutput()->Head();
//        return $result;

    }

    public function Content()
    {
        $result = "{$this->output}";
        return $result;
    }

    public function PreProcess()
    {
        $this->searcherToCompute()->Execute();

        $result = $this->searcherToCompute()->Result();

        $this->output = OutputFactory::Find($result);

        // based on the results from searcherToCompute
        // we either show data here or we message saying we are proessing
        // pass and id.

    }


}


?>
