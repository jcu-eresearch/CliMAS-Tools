<?php

/**
 * 
 *
 * @author Adam Fakes (James Cook University)
 */
class SpeciesSearchOutput extends Output
{


    public function __construct() {
        parent::__construct();
        $this->OutputName(__CLASS__);

    }

    public function __destruct() {
        parent::__destruct();

    }

    private function search()
    {
        $result = $this->Source();
        $result  instanceof SpeciesSearch;
        return  $result;
    }


    private function actions() {
        if (func_num_args() == 0)
        {
            $result = $this->getProperty();
            $result instanceof Actions;
            return $result;
        }

        $result = $this->setProperty(func_get_arg(0));
        $result instanceof Actions;
        return $result;

    }

    private function actionsOutput() {
        if (func_num_args() == 0)
        {
            $result = $this->getProperty();
            $result instanceof ActionsOutput;
            return $result;
        }

        $result = $this->setProperty(func_get_arg(0));
        $result instanceof ActionsOutput;
        return $result;

    }



    public function Title()
    {
        return configuration::ApplicationName()."::Species Search";
    }


    public function Head()
    {
        $result = $this->actionsOutput()->Head();
        return $result;

    }

    public function Content()
    {

        $o = $this->actionsOutput();
        //$o->DescriptionTemplate('<a href="{Value}">{Value}</a>');

        return $o->Content();

    }

    public function PreProcess()
    {

        $this->actions($this->search()->Subsets());
        $this->actionsOutput(OutputFactory::Find($this->actions()));


    }


}


?>
