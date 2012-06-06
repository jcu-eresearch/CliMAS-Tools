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

        $this->Refresh(5);

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
        // refresh page every 60 second
        //$result = '<meta http-equiv="refresh" content="60; url='.$_SERVER['PHP_SELF'].' " />';
        //return $result;

    }

    public function Content()
    {
        $result  = "Computing Species<br>";

        $result .= "Command output = ".$this->output."<br>";

        return $result;
    }

    public function PreProcess()
    {

        $this->searcherToCompute()->Execute(); // execute the Compute Function

        $stcResult = $this->searcherToCompute()->Result();


        // expecting the result to be a command or an action

        if ($stcResult instanceof Command) // command
        {
            $this->output = $this->resultsForCommandQueue($stcResult);
            return;
        }



        if ($stcResult instanceof Action)
        {
            $stcResult instanceof Action;
            $actionOutput = OutputFactory::Find($stcResult);

            $this->output = $actionOutput;
        }

    }


    private function resultsForCommandQueue($stcResult)
    {
        $stcResult instanceof Command;

        $cmdOutput = OutputFactory::Find($stcResult);

        $result = "Command with id ".$stcResult->ID(). $cmdOutput->Content();

        return $result;

    }


    private function resultsForAction($stcResult)
    {
        $stcResult instanceof Action;
        $actionOutput = OutputFactory::Find($stcResult);

        return $actionOutput;

    }


}


?>
