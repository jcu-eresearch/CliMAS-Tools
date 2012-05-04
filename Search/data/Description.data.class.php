<?php

/* 
 * CLASS: FieldDescription
 *        
 * 
 *   
 */
class Description extends aData {
    //put your code here
    
    public static function create($variableName = null,$name = null, $description = null,$moreInformation = null , $dataSource = null) 
    {
        $D = new Description();
        
        $D->VariableName($variableName);
        $D->Name($name);
        $D->Description($description);
        $D->MoreInformation($moreInformation);
        $D->DataSource($dataSource);
        return $D;
    } 
    
    
    public function __construct() { 
        parent::__construct();
    }
    
    public function __destruct() {    
        parent::__destruct();
    }

    public function VariableName() {
        if (func_num_args() == 0) return $this->getProperty();
        $this->Data(func_get_arg(0));
        return $this->setProperty(func_get_arg(0));
    }

    public function Name() {
        if (func_num_args() == 0) return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }

    public function Description() {
        if (func_num_args() == 0) return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }
    
    public function MoreInformation() {
        if (func_num_args() == 0) return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }

    public function DataSource() {
        if (func_num_args() == 0) return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }
    
    
    public function toString() 
    {
        $result = "[{$this->VariableName()}] {$this->Name()}\n{$this->Description()}";
        return $result;
    }
    
    
    
    
}

?>
