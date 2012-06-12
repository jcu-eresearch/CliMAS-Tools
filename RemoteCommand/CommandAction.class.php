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
class CommandAction extends Object implements iAction, iCommand,  Serializable
{

    public function __construct() {        
        parent::__construct();
        $this->ActionName(__CLASS__);
        $this->ExecutionFlag(Command::$EXECUTION_FLAG_READY);

    }


    public function __destruct() {
        
    }


    public function Execute()
    {
        throw new Exception("COmmand Action has not been defined for ".$this->ActionName());
    }


    /**
     * To check if the action has been completed - should do things like check files or other stuff
     *
     *
     * @throws Exception
     */
    public function CompleteTest()
    {
        throw new Exception("COmmand Action CompleteTest has not been defined for ".$this->ActionName());
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


    /**
     * Date and Time Command was Last Updated
     * @return string Updated Date & Time
     */
    public function LastUpdated()
    {
        if (func_num_args() == 0) return $this->getProperty();
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

    public function Status() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }

    public function serialize() {

        $result = array();

        foreach ($this->PropertyNames() as $name)
        {
            $result[$name] = $this->getPropertyByName($name); // todo will not work so well if the value of a propetry is an object
        }

        return serialize($result);


    }

    public function unserialize($serialized) {

        $data = unserialize($serialized) ;

        foreach ($data as $name => $value)
                $this->setPropertyByName($name, $value);

    }

    public function ActionName()
    {
        if (func_num_args() == 0) return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }

    public function AttachedCommand()
    {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }

    public function Description() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));

    }

    public function Result() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));

    }

    public function ExecutionFlag() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));

    }

    public function QueueID() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }


}

?>
