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
        $this->OutputName(__CLASS__);

    }

    public function __destruct() {
        parent::__destruct();

    }

    public function Title()
    {
        return configuration::ApplicationName()." :: POPUP";
    }


    public function Head()
    {
        return "";

    }

    public function Content()
    {

        return "GENERIC ::: ".$this->Source()->asFormattedString();

    }


}

?>
