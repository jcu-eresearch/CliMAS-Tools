<?php

/**
 * CLASS: 
 *        
 * 
 *   
 */
class TemplateClass extends Object {
    //**put your code here
    
    public function __construct() { 
        
        parent::__construct();
    }
    
    public function __destruct() {    
        parent::__destruct();
    }

    public function ReadWriteProperty() {
        if (func_num_args() == 0) return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
        
    }

    /**
     * Set the value of this Property in constructor
     * via $this->setPropertyByName("ReadOnlyProperty", "SomeValue")
     * 
     */
    public function ReadOnlyProperty() {
        return $this->getProperty();
    }


    
}

?>
