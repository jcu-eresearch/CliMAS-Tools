<?php

/* 
 * Interface : iFinder
 *        
 * 
 *   
 */
interface iFinder {
    //put your code here

    public function Filter($name,$value);
    
    public function Find();
    
    public function Result(); 
    
}

class aFinder extends Object implements iFinder  {
    //put your code here

    public function __construct() { 
        parent::__construct();        
        
    }
    
    public function __destruct() {
        parent::__destruct();
        
    }

    
    public function Find()
    {
        throw new Exception("{$this->Name()} Find has not been imnplemented");
    }
    
    
    public function Result()
    {
        if (func_num_args() == 0) return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    } 
    

    
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
    

    
    /*
     * Actions is a list of available actions for this filter
     * each filter action will be added to this list./
     */
    protected $actions = array();
    
    public function Actions()
    {
        return $this->actions; // return key value pair
    }

    /*
     * Class that extent Finder will add actions that that finder supports
     * - wo9uld have been nice to go down another level - but too much  structure muight be an issue
     */
    protected function addAction($action_name)
    {
        $this->actions[$action_name] = $action_name;
    }
    
    
    /*
     * An action is something this fider will do with the Filters
     */
    protected $useAction = null;
    public function UseAction($action_name)
    {
        if (!array_key_exists( $action_name,$this->actions)) return null;
        $this->useAction = $action_name;
        return $this->useAction; // return key value pair
    }
    
    
}



?>
