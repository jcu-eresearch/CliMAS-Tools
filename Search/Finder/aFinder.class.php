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



    public function __construct($child) {
        $this->Actions(get_class($child));
        parent::__construct();
        $this->Name(__CLASS__);


    }
    
    public function __destruct() {
        parent::__destruct();
        
    }


    /*
     * Find the requested action and then run it.
     * place result into $this->Result
     */
    public function Find()
    {
        if ( is_null($this->UseAction()) ) $this->UseAction($this->DefaultAction()) ;
        $action = ActionFactory::Find($this, $this->UseAction()); // get the class that is defined by this Finder / Action Combo

        $this->Result($action->Execute());
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
     */    
    public function Actions()
    {

        return ActionFactory::Available($this); //  a list of actions found for this finder
    }
    
    /*
     * An action is something this finder will do with the Filters
     */
    public function UseAction() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }

    /*
     * A default action will be done if no Action was passed to FinderFactory
     *
     */
    public function DefaultAction() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }
    
}



?>
