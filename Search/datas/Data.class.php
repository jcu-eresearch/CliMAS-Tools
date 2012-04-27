<?php

/* 
 * CLASS: Data
 *        
 * 
 *   
 */
class Data extends Object {
    //put your code here
    
    public function __construct() { 
        parent::__construct();
    }
    
    public function __destruct() {    
        parent::__destruct();
    }
    
    public function Data()
    { 
        if (func_num_args() == 0) return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }
    
    public static function isData($src)
    {
        return ($src instanceof self);
    }
    
    
    public static function cast($src)
    {
        $src instanceof Data;
        return $src;
    }
    
    
    
}

?>

