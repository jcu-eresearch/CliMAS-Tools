<?php
/**
 * 
 *        
 * 
 *   
 */
class Description extends Data {
    
    
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
    public function Filename() {
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
    public function Description() {
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
    public function Source() {
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
    public function MoreInformation() {
        if (func_num_args() == 0) return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }
    
}

?>
