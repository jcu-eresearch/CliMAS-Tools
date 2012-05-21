<?php

/**
 * 
 *
 * @author Adam Fakes (James Cook University)
 */
class GenericOutput extends Output
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

        return "GENERIC ::: ".$this->Source()->asFormattedString();

    }


}

?>
