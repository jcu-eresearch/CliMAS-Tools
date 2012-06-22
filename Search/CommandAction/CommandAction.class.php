<?php

/**
 *
 *  
 *  
 */
class CommandAction extends Object implements Serializable
{

    public function __construct() {        
        parent::__construct();
        $this->ActionName(__CLASS__);
        $this->ExecutionFlag(self::$EXECUTION_FLAG_READY);

        $this->initialised(false);
        
    }


    public function __destruct() {
        
    }


    public function Execute()
    {
        throw new Exception("COmmand Action has not been defined for ".$this->ActionName());
    }


    public function initialise()
    {
        
        
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
     * Command has been initialised OK
     * 
     * @return type 
     */
    public function initialised() {
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


    public function serialize() {

        $result = array();

        foreach ($this->PropertyNames() as $name)
        {
            $result[$name] = $this->getPropertyByName($name); // todo will not work so well if the value of a propetry is an object
        }

        return serialize($result);


    }

    public function unserialize($serialized) {

        $data = unserialize($serialized);

        foreach ($data as $name => $value)
                $this->setPropertyByName($name, $value);

    }

    public function ActionName()
    {
        if (func_num_args() == 0) return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }


    public function Description() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));

    }

    /**
     * READY    -- Needs to be started - ie find action execute  Change ExecutionFlag to RUNNING
     * RUNNING  -- Go and check QSUB status / status of action via its mechanisim - WIll Set to COMPLETE if it's all done
     * TIMEOUT  -- Maybe ?   it's taken to long ?
     * COMPLETE -- Has completed - results to be returned
     *
     * @return string Execution phase
     */
    public function ExecutionFlag()
    {
        if (func_num_args() == 0) return $this->getProperty();
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



    public function Status() {
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
     * Holds the ID from QSUB
     * - gets written to the command at the start of execution - 
     *  allows us to find the QSTAT details
     *
     * @return string QSUB ID
     */
    public function QueueID()
    {
        if (func_num_args() == 0) return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }

    public function AttachedCommand() {
        
    }



     /**
      * READY    -- Needs to be started - ie find action execute  Change ExecutionFlag to RUNNING
      */
     public static $EXECUTION_FLAG_READY = "EXECUTION_FLAG_READY";

     /**
      * RUNNING  -- Go and check QSUB status / status of action via its mechanisim - WIll Set to FINALISE if it's all done
      */
     public static $EXECUTION_FLAG_RUNNING = "EXECUTION_FLAG_RUNNING";

     /**
      * TIMEOUT  -- Maybe ?   it's taken to long ?
      */
     public static $EXECUTION_FLAG_TIMEOUT = "EXECUTION_FLAG_TIMEOUT";


     /**
      *  QUEUE_DONE -- Job has completed as far as the QSTAT is concered
      */
     public static $EXECUTION_FLAG_QUEUE_DONE = "EXECUTION_FLAG_QUEUE_DONE";


     /**
      *  FINALISE -- Has finished - but not yet complete
      */
     public static $EXECUTION_FLAG_FINALISE = "EXECUTION_FLAG_FINALISE";


     /**
      * COMPLETE -- Has completed - results to be returned
      */
     public static $EXECUTION_FLAG_COMPLETE = "EXECUTION_FLAG_COMPLETE";

    
    
}

?>
