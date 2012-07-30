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
        $this->OutputName(__CLASS__);

        $this->MoreInformationLinkText('<img src="/eresearch/TDH-Tools/Resources/icons/more_info.png" width="20px" height="20px">');

        $this->Template($this->defaultTemplate());


    }

    public function __destruct() {
        parent::__destruct();

    }

    public function Head()
    {
        return htmlutil::includeLocalHeadCodeFromPathPrefix(file::currentScriptFolder(__FILE__),"Descriptions",configuration::osPathDelimiter(),configuration::osExtensionDelimiter());
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

        $link = '<a target="_moreinfo" href="'.$desc->URI().'">'.$this->MoreInformationLinkText().'</a>';
        $temp = str_replace("{URI}",$link , $temp);


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
<div id="{DataName}" class="DescriptionCell" >
    <span class="DescriptionLink">{URI}</span><div class="DescriptionName">{DataName}</div>
    <div class="DescriptionText">{Description}</div>
    <div id="Selector{DataName}" class="DescriptionSelector" onclick="descriptionSelect('{DataName}');" ></div>
    <div class="DescriptionMoreInformation">{MoreInformation}</div>
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

    public function MoreInformationLinkText() {
        if (func_num_args() == 0) return $this->getProperty();
        $value = func_get_arg(0);
        return $this->setProperty($value);
    }


}

?>
