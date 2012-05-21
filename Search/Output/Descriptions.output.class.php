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

    }

    public function __destruct() {
        parent::__destruct();

    }

    public function Style()
    {
        return "";

    }

    public function Content()
    {

        $result = array();
        foreach ($this->mySource()->Descriptions() as $key => $value)
        {
            $result[$key] = $value;// OutputFactory::Content($value);
        }

        return $result;

    }


    private function mySource()
    {
        $result = $this->Source();
        $result instanceof Descriptions;
        return $result;
    }


}

?>
