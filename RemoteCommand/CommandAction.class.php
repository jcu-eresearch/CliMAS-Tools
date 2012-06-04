<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CommandAction
 *
 * @author jc166922
 */
class CommandAction extends Action {

    public function __construct() {
        parent::__construct();
        $this->ActionName(__CLASS__);
        $this->FinderName("");
    }


    public function __destruct() {
        parent::__destruct();
    }


    public function Execute()
    {
        throw new Exception("COmmand Action has not been defined for ".$this->ActionName());
    }


    public function Command(Command $cmd = null) {
        if (is_null($cmd))
        {
            $result = $this->getProperty();
            $result instanceof Command;
            return $result;
        }

        $result = $this->setProperty($cmd);
        $result instanceof Command;
        return $result;
    }

}

?>
