<?php

/**
 * Description of Description
 *
 * @author Adam Fakes (James Cook University)
 */
class ActionsOutput extends Output
{

    public function __construct() {
        parent::__construct();
        $this->OutputName(__CLASS__);

        $this->Template($this->defaultTemplate());


    }

    public function __destruct() {
        parent::__destruct();

    }

    public function Head()
    {
        return htmlutil::includeLocalHeadCodeFromPathPrefix(file::currentScriptFolder(__FILE__),"Actions",configuration::osPathDelimiter(),configuration::osExtensionDelimiter());
    }

    public function Title()
    {
        return configuration::ApplicationName()."::Actions";
    }


    public function Content()
    {
        $actions = $this->Source();
        $actions instanceof Actions;

        $tmp = "";
        foreach ($actions->ActionObjects() as $action)
        {
            $action instanceof iAction;
            //echo "{$action->Description()}";

            $tmp .= $this->formattedAction($action);
        }

        return $tmp;

    }

    private function formattedAction(iAction $action)
    {
        

        $result = $action->asFormattedString($this->processMiniTemplates($action));
        return $result;
    }

    private function processMiniTemplates(iAction $action)
    {
        $temp = $this->Template(); // read for descriptrions

        foreach ($action->PropertyNames() as $propertyName)
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
<div id="{ActionName}" class="ActionCell" >
    <div class="ActionName">{ActionName}</div>
    <div class="ActionDescription">{Description}</div>
    <div class="ActionButton"><input type="button" value="{Description}" name="button{ActionName}" id="button{ActionName}" onclick="action('{ActionName}')" ></div>
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
        $value = str_replace("{Value}", "{Name}", $value);
        $value = str_replace("{value}", "{Name}", $value);

        return $this->setProperty($value);
    }

    public function ButtonTemplate() {
        if (func_num_args() == 0) return $this->getProperty();

        $value = func_get_arg(0);
        $value = str_replace("{Value}", "{Name}", $value);
        $value = str_replace("{value}", "{Name}", $value);

        return $this->setProperty($value);
    }



}

?>
