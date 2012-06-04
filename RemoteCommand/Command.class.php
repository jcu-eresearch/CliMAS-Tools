<?php

interface iCommand
{

    public function CommandName();

    public function Description();

    public function ActionName();

    public function Parameters();

    public function Result();

    public function LocationName();


}


class Command extends Object implements iCommand,  Serializable {

    public function __construct() {
        parent::__construct();
    }

    public function __destruct() {
        parent::__destruct();
    }
    

    public function serialize()
    {

        $result = array();

        foreach ($this->PropertyNames() as $name)
            $result[$name] = $this->getPropertyByName($name); // todo will not work so well if the value of a propetry is an object

        return serialize($result);

    }

    public function unserialize($serialized)
    {

        $data = unserialize($serialized) ;

        foreach ($data as $name => $value)
        {
            $this->setPropertyByName($name, $value);
        }

    }


    /**
     * What action to run when executed
     * @return String Action Name
     */
    public function ActionName() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }

    /**
     * Name of this command, could be usefull for Logging
     * To be used as name of QSUB jobs
     *
     * @return String Command Name
     */
    public function CommandName() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }


    public function Description() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }


    /**
     * Data from Issuing system
     *
     * @return array Parameters to be made avaiable to action
     */
    public function Parameters() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }

    /**
     *
     *
     * @return mixed will be the result of an Action
     */
    public function Result() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }


    /**
     * THis is where this command will be run,
     * From this we can figure out
     *  - how to get it there
     *  - how to updates & status
     *  - how to return it
     *
     * @return String Name of "Place" this command will be run
     */
    public function LocationName() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }




}



?>
