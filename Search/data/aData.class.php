<?php
/* 
 * Interface : Data
 *        
 * 
 *   
 */
interface iData {
    //put your code here
    public static function create();
    
    /*
     * What type of data is this ?
     */    
    public function Type();

    /*
     * Access to the actual data
     */
    public function Data();
    
    /*
     * Plain String version of the data
     */
    public function toString();
    
    
    
}

/* 
 * CLASS: aData
 *        
 * 
 *   
 */
class aData extends Object implements iData{
    //put your code here
    
    public static function create() 
    { 
        throw new Exception("{$this->Name()} Create has not been implemented");
    }
    
    
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

    public function Type() 
    {
        if (is_object($this->Data())) return get_class($this->Data());
        return gettype($this->Data());
    }

    public function toString() 
    {
        return print_r($this->Data(),true);
    }

    
    public static function isData($src)
    {
        return ($src instanceof self);
    }
    
    
    
}



?>

