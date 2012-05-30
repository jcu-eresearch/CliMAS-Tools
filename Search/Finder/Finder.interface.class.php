<?php

/**
 * Interface : iFinder
 *        
 * 
 *   
 */
interface iFinder {

    public function Filter($name,$value);
    
    public function Execute();
    
    public function Result();

    public function Description();

    public function FinderName();

    
}

interface iAction {

    public function Execute();

    public function Result();

    public function Description();

    public function ActionName();


}


/**
 * Base class for all finders 
 * - supports common processes for finders 
 * - Properties, Executing an Action and returning the Action result
 *
 */
class Finder extends Object implements iFinder  {

    public function __construct($child) {
        $this->Actions(get_class($child));
        parent::__construct();
        $this->FinderName(__CLASS__);

    }
    
    public function __destruct() {
        parent::__destruct();
        
    }

    /**
     * @method
     * @throws Exception - Execption forces subclasses to define this method
     */
    public function Description()
    {
        /**
         *@todo - LOG this
         */
        throw new Exception("###Finder Description has not be defined");
    }


    /**
     *  Find the requested action ($this-UseAction) and then run it. place result into $this->Result
     */
    public function Execute()
    {
        if ( is_null($this->UseAction()) ) $this->UseAction($this->DefaultAction()) ;
        $action = ActionFactory::Find($this, $this->UseAction()); // get the class that is defined by this Finder Action Combo

        $this->Result($action->Execute());
    }

    /**
     * 
     * @return mixed .. Result of action
     */
    public function Result()
    {
        if (func_num_args() == 0) return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    } 


    /**
     * Finder level filters available to Finders and Actions
     *
     * @param type $name
     * @param type $value
     * @return null|\array (Filter name not valid | All Filters | key/value pair of filter just added )
     * @throws Exception
     */
    public function Filter($name = null, $value = null)
    {

        if (is_null($name) && is_null($value)) return $this->getProperty(); // return all filters
        
        if (!is_null($value) && is_null($name)) 
            throw new Exception("Filter called with NULL key  and some value [{$value}]");

        $filter_array = $this->getProperty();
        if (!is_null($name) && is_null($value))   // return one of the tags
        {
            if (!array_key_exists($name, $filter_array)) return null;  // if the key does not exist then return null
            return $filter_array[$name];
        }

        // we have a key and we have a value so set the key value pair and update property
        if (!is_null($name) && !is_null($value)) 
        {
            $filter_array[$name] = $value;
            $this->setProperty($filter_array);
        }
        
        
        $result = array();
        $result[$name] = $value;
        
        return $result; // return key value pair
    }

    
    /**
     *
     * @return array  available actions for this Finder
     */
    public function Actions()
    {
        return ActionFactory::Available($this);
    }
    

    /**
     *
     * @return string Name of Action to Run
     */
    public function UseAction() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }

    /**
     *
     * @return string Name of default action for this finder
     */
    public function DefaultAction() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }

    public function FinderName() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }


}





/**
 * Base class for all Actions
 * - supports common processes for Actions
 * 
 *
 */
class Action extends Object implements iAction  {

    public function __construct() {
        parent::__construct();
        $this->ActionName(__CLASS__);

    }

    public function __destruct() {
        parent::__destruct();

    }

    /**
     * @method
     * @throws Exception - Execption forces subclasses to define this method
     */
    public function Description()
    {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }


    /**
     *  Find the requested action ($this-UseAction) and then run it. place result into $this->Result
     */
    public function Execute()
    {
        throw new Exception("Execute not defined for ".$this->ActionName());

    }

    /**
     *
     * @return mixed .. Result of action
     */
    public function Result()
    {
        if (func_num_args() == 0) return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }

    public function ActionName()
    {
        if (func_num_args() == 0) return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }


    public function FinderName()
    {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }



}




?>
