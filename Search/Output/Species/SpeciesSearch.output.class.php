<?php

/**
 * 
 *
 * @author Adam Fakes (James Cook University)
 */
class SpeciesSearchOutput extends Output
{


    private $toOutput = null;


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


    public function Title()
    {
        return configuration::ApplicationName()."::Species Search";
    }


    public function Head()
    {
        $result = "";

        foreach ($this->toOutput as $output)
        {
            $output instanceof Output;
            $result .= $output->Head();
        }
        
        $result .= htmlutil::includeLocalHeadCodeFromPathPrefix(file::currentScriptFolder(__FILE__),"Species",configuration::osPathDelimiter(),configuration::osExtensionDelimiter());
        
        return $result;
    }

    public function Content()
    {

        $result = "";

        foreach ($this->toOutput as $output)
        {
            $output instanceof Output;
            $result .= $output->Content();
        }

        return $result;

    }

    public function PreProcess()
    {
        $this->search()->Execute();

        $this->toOutput = array();
        $this->toOutput[] = OutputFactory::Find($this->search()->Subsets());
        $this->toOutput[] = OutputFactory::Find($this->search()->Result());

    }


}


?>
