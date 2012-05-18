<?php

/**
 * 
 *        
 * 
 *   
 */
class FinderActionDescription extends Description {
    
    
    public function __construct() { 
        parent::__construct();
        $this->Name(__CLASS__);

    }
    
    public function __destruct() {    
        parent::__destruct();
    }

    /**
     * Called with (null) return value of property<br>
     * Called with ($arg)    set value of property to $arg<br>
     *
     * @return mixed current property value
     *
     */
    public function FinderAction() {
        if (func_num_args() == 0) return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }

    /**
     * Called with (null) return value of property<br>
     * Called with ($arg)    set value of property to $arg<br>
     *
     * @return mixed current property value
     *
     */
    public function FinderName() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }

    /**
     * Called with (null) return value of property<br>
     * Called with ($arg)    set value of property to $arg<br>
     *
     * @return mixed current property value
     *
     */
    public function ActionName() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }
    
    
}

?>
