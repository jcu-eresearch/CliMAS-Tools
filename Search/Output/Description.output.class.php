<?php

/**
 * Description of Description
 *
 * @author Adam Fakes (James Cook University)
 */
class DescriptionOutput extends Output
{

    public function __construct() {
        parent::__construct();
        $this->Name(__CLASS__);

    }

    public function __destruct() {
        parent::__destruct();

    }

    public function Head()
    {
        return "Descriptions Style";

    }

    public function Content()
    {

        return "Descriptions COntent";

    }




}

?>
