<?php

/**
 * 
 *
 * @author Adam Fakes (James Cook University)
 */
class SpeciesComputedOutput extends Output
{


    public function __construct() {
        parent::__construct();
        $this->OutputName(__CLASS__);

    }

    public function __destruct() {
        parent::__destruct();

    }

    private function speciesAllValues()
    {
        $result = $this->Source();
        $result  instanceof SpeciesAllValues;
        return  $result;
    }


    public function Title()
    {
        return configuration::ApplicationName()."::Species Search";
    }


    public function Head()
    {
        $result = "";
        $result  = $this->speciesOutput()->Head();
        //$result .= htmlutil::includeLocalHeadCodeFromPathPrefix(file::currentScriptFolder(__FILE__),"Species",configuration::osPathDelimiter(),configuration::osExtensionDelimiter());

        return $result;
    }

    public function Content()
    {

        $result = "";
        $o = $this->speciesOutput();
        $result = $o->Content();
        return $result;

    }

    public function PreProcess()
    {
        $this->speciesAllValues()->Execute();

        $descs = $this->speciesAllValues()->Result();
        $descs instanceof Descriptions;

        $o = OutputFactory::Find($descs);

        $this->speciesOutput($o);

    }


    private function speciesOutput() {
        if (func_num_args() == 0) return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }

}


?>
