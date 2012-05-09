<?php

/*
 * Interface : iFinder
 *
 *
 *
 */
interface iAction {
    //put your code here

    public function Execute();

    public function Result();

}

class aAction extends Object implements iAction  {


    public function __construct() {
        parent::__construct();

    }

    public function __destruct() {
        parent::__destruct();

    }


    public function Execute()
    {

        if ( is_null($this->UseAction()) ) $this->UseAction(self::$ACTION_DEFAULT) ;

        $action_method = self::$ACTION_METHOD_PREFIX.$this->UseAction();

        if (!method_exists($this, $action_method))
        {
            echo "A method for [{$this->UseAction()}] defined as {$action_method}  does not exist";
            return null;
        }

        $this->Result($this->$action_method());

    }


    public function Result()
    {
        if (func_num_args() == 0) return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }

}



?>
