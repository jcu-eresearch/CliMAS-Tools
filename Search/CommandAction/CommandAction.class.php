<?php

/**
 * Extend this class to create jobs / actions to run in the GRID 
 * - Allows you to create a action to run without having to worry about 
 *   the infrastucture of status check and passing data into and out of the HPC 
 * 
 * 
 * Command Actions can be initialise, queued, executed and returned to the swebserver
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


    /**
     * If the subclass does define then it will be obvious as the system will throw an Exception
     * - 
     * Override this method to makethe CommandAction do what you need to to do
     * 
     * 
     * @throws Exception 
     */
    public function Execute()
    {
        throw new Exception("COmmand Action has not been defined for ".$this->ActionName());
    }


    
    
    /**
     * Override on subclass to allow inbound initialisation 
     * usually from web server to set-up object before sending to the grid.
     * 
     * - Here you can also check to see if the job really needs to be submitted
     * 
     * 
     * @throws Exception 
     */
    public function initialise()
    {
        // after succesfull initialisation - $this->initialised(true);
        throw new Exception("COmmand Action has not been defined for ".$this->ActionName());
        
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


    /**
     *
     * How to convert Object to a form that can be transmitted to another system
     * Uses startd PHP serialisation, (this could also be XML, or JSON)
     * 
     * @return type 
     */
    public function serialize() {

        $result = array();

        foreach ($this->PropertyNames() as $name)
        {
            $result[$name] = $this->getPropertyByName($name); // todo will not work so well if the value of a propetry is an object
        }

        return serialize($result);


    }

    /**
     *Return object property states after be sent
     * 
     * @param type $serialized 
     */
    public function unserialize($serialized) {

        $data = unserialize($serialized);

        foreach ($data as $name => $value)
                $this->setPropertyByName($name, $value);

    }

    /**
     * Action name 
     * @return type 
     */
    public function ActionName()
    {
        if (func_num_args() == 0) return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }


    /**
     * What does thi action do an why?
     * - could be used to store human readable information - to be displayed or written to reports
     *
     * @return type 
     */
    public function Description() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));

    }

    /**
     * Refer to Sstatic variables  self::$EXECUTION_FLAG_
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
     * Where partial & complete output froms Obect will be placed.
     * - this is read by the Output system to be displayed / written
     *
     * @return mixed will be the result of an Action
     */
    public function Result() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }



    /**
     * Human readable status to send back to user.
     * 
     * @return type 
     */
    public function Status() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }

    /**
     * CommandProcessor updates this each time it puts the command back to disk
     * 
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
