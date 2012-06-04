<?php

/**
 *
 *
 * @author Adam Fakes (James Cook University)
 */
class CommandOutput extends Output
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
        return configuration::ApplicationName()." :: SOme Command";
    }


    public function Head()
    {
        return "";

    }

    private function command()
    {
        $result = $this->Source();
        $result instanceof Command;
        return $result;

    }

    public function Content()
    {

        return "COMAND Object ???::: ".$this->command()->Description();

    }

    public function PreProcess()
    {
        
        

    }




}

?>
