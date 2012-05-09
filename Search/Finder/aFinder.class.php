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

    /*
     * All action methods must start with this 
     */
    private static $ACTION_METHOD_PREFIX = "Action";
    private static $ACTION_DEFAULT = "Default";


    public function __construct($child) {
        $this->Actions(get_class($child));
        parent::__construct();
        $this->Name(__CLASS__);


    }
    
    public function __destruct() {
        parent::__destruct();
        
    }

    
    public function Find()
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
    public function Actions($class_name = null)
    {

        ActionFactory::Actions($this);


//        $methods = array_flip(array_util::ElementsThatContain(get_class_methods($class_name),self::$ACTION_METHOD_PREFIX));
//        unset($methods["Actions"]); // remove this method
//        unset($methods["UseAction"]); // remove this method
//
//        return $methods; // return key value pair
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
     * Override this method in your classes
     *
     * YOu can always return the the result of another action as the DActionDefault
     *
     * e.g.
     *
     * public function ActionDefault() 
     * {
     *  return $this->ActionOther();
     * }
     *
     *
     */
    public function ActionDefault() {
        return null;
    }
    
}



?>
