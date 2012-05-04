<?php

/* 
 * INTERFACE: iOutput
 *        
 * 
 *   
 */
interface iOutput {
    //put your code here

    /*
     * General uses will be for  classes that implement or extend iData   
     *                                     or implement or extend iFinder
     */
    
    public function Source();
    
    public function Title();
    
    public function Layout();
    
    public function Result(); 
    
}

class anOutput extends Object implements iOutput{
    //put your code here

    public function __construct() { 
        parent::__construct();        
        
    }
    
    public function __destruct() {
        parent::__destruct();
        
    }

    public function Title() {
        if (func_num_args() == 0) return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
        
    }
    
    public function Layout() 
    {
        throw new Exception("{$this->Name()} Layout has not been implemented");
    }

    public function Source() 
    {
        throw new Exception("{$this->Name()} Source has not been implemented");
    }
    
    public function Result()
    {
        throw new Exception("{$this->Name()} Result has not been imnplemented");
    } 
    
    
}



?>
