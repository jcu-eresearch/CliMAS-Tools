<?php

/**
 * 
 *
 * @author Adam Fakes (James Cook University)
 */
class SpeciesAllValuesOutput extends Output
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
        
        $result .= '<div id="AllSpecies">';
        $result .= $o->Content();
        $result .= "</div>";
        
        return $result;

    }

    public function PreProcess()
    {
        $this->speciesAllValues()->Execute();

        $descs = $this->speciesAllValues()->Result();
        $descs instanceof Descriptions;
        

        $o = OutputFactory::Find($descs);
        $o instanceof DescriptionsOutput;
        $o->Template($this->template());

        $this->speciesOutput($o);

    }

    
        private function template()
{
$tmp = <<<TEMPLATE
<div id="{DataName}" class="DescriptionCell" >
    <span class="DescriptionLink">{URI}</span>
    <div class="DescriptionText">{Description}</div>
    <div id="Selector{DataName}" class="DescriptionSelector" onclick="descriptionSelect('{DataName}');" ></div>
    <div class="DescriptionMoreInformation">{MoreInformation}</div>
</div>

TEMPLATE;

//$tmp = <<<TEMPLATE
//<div id="{DataName}" class="DescriptionCell" >
//    <span class="DescriptionLink">{URI}</span>
//    <div class="DescriptionName">{DataName}</div>
//    <div class="DescriptionText">{Description}</div>
//    <div id="Selector{DataName}" class="DescriptionSelector" onclick="descriptionSelect('{DataName}');" ></div>
//    <div class="DescriptionMoreInformation">{MoreInformation}</div>
//</div>
//
//TEMPLATE;


        return $tmp;
    
        }
    

    private function speciesOutput() {
        if (func_num_args() == 0) return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }

}


?>
