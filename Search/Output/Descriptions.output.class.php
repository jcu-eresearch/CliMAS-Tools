<?php

/**
 * Description of Description
 *
 * @author Adam Fakes (James Cook University)
 */
class DescriptionsOutput extends Output
{

    public function __construct() {
        parent::__construct();
        $this->Name(__CLASS__);

        $this->Template($this->defaultTemplate());


    }

    public function __destruct() {
        parent::__destruct();

    }

    public function Head()
    {
        $css = "";
        $fn = file::currentScriptFolder(__FILE__)."/Descriptions.css";

        if (file_exists($fn))
            $css = "\n".'<style type="text/css">\n'.  file_get_contents($fn)."\n</style>\n";
            

        return $css;

    }

    public function Title()
    {
        return configuration::ApplicationName()."::Descrptions";
    }


    public function Content()
    {
        $descs = $this->Source();
        $descs instanceof Descriptions;

        $tmp = "";
        foreach ($descs->Descriptions() as $desc)
        {
            $tmp .= $this->formattedDesc($desc);
        }
            

        return $tmp;

    }

    private function formattedDesc(Description $desc)
    {
        $result = $desc->asFormattedString($this->processMiniTemplates($desc));
        return $result;
    }

    private function processMiniTemplates(Description $desc)
    {
        $temp = $this->Template(); // read for descriptrions

        foreach ($desc->PropertyNames() as $propertyName)
        {
            $miniTemplate = $this->getPropertyByName($propertyName."Template");

            if (!is_null($miniTemplate))
                $temp = str_replace("{".$propertyName."}", $miniTemplate, $temp);
        }

        return $temp;

    }


    public function Template() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }


    public function defaultTemplate()
    {

        

$tmp = <<<TEMPLATE
<div class="DescriptionCell">
    <div class="DescriptionName">{Name}</div>
    <div class="DescriptionText">{Description}</div>
    <div class="DescriptionMoreInformation">{MoreInformation}</div>
    <div class="DescriptionLink">{URI}</div>
</div>

TEMPLATE;

        return $tmp;

    }

    public function NameTemplate() {
        if (func_num_args() == 0) return $this->getProperty();

        $value = func_get_arg(0);
        $value = str_replace("{Value}", "{Name}", $value);
        $value = str_replace("{value}", "{Name}", $value);

        return $this->setProperty($value);
    }

    public function DescriptionTemplate() {
        if (func_num_args() == 0) return $this->getProperty();

        $value = func_get_arg(0);
        $value = str_replace("{Value}", "{Description}", $value);
        $value = str_replace("{value}", "{Description}", $value);

        return $this->setProperty($value);
    }

    public function URITemplate() {
        if (func_num_args() == 0) return $this->getProperty();
        $value = func_get_arg(0);
        $value = str_replace("{Value}", "{URI}", $value);
        $value = str_replace("{value}", "{URI}", $value);

        return $this->setProperty($value);
    }

    public function MoreInformationTemplate() {
        if (func_num_args() == 0) return $this->getProperty();
        $value = func_get_arg(0);
        $value = str_replace("{Value}", "{MoreInformation}", $value);
        $value = str_replace("{value}", "{MoreInformation}", $value);

        return $this->setProperty($value);
    }

}

?>
