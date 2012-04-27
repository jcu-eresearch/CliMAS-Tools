<?php
class Object  {
    
    protected $name = "AnObject";
    
    private $property = null;
    
    public function __construct($id = null) {    
        
        $this->property = array();
        $bt = debug_backtrace(); 
        $this->name = $bt[1]['class'];
        
        if (is_null($id))
            $this->setPropertyByName("ID",  uniqid('',true));
        else
            $this->setPropertyByName("ID",  $id);
    }

    public function __destruct() {        
        $this->property = null;
    }
    
    protected function setProperty($value) {
        $bt = debug_backtrace(); 
        $property_name = $bt[1]['function'];
        if (!array_key_exists($property_name, $this->property)) $this->property[$property_name] = null; // make the property exist if it does not
        $this->property[$property_name] = $value; // set property value // if they passed value the set that value
        return $this->property[$property_name];  // return current vlaue of property
    }

    protected function setPropertyByName($property_name,$value) {
        if (!array_key_exists($property_name, $this->property)) $this->property[$property_name] = null; // make the property exist if it does not
        $this->property[$property_name] = $value; // set property value // if they passed value the set that value
        return $this->property[$property_name];  // return current vlaue of property
    }
    
    
    protected function getProperty() {
        $bt = debug_backtrace(); 
        $property_name = $bt[1]['function'];
        if (!array_key_exists($property_name, $this->property)) $this->property[$property_name] = null; // make the property exist if it does not
        return $this->property[$property_name];  // return current vlaue of property
    }

    protected function hasProperty($property_name) {
        
        if (!array_key_exists($property_name, $this->property))  return true;
        
        return false;
    }
    
    
    protected function readOnly() {
        $bt = debug_backtrace(); 
        $property_name = $bt[1]['function'];
        if (!array_key_exists($property_name, $this->property)) $this->property[$property_name] = null; // make the property exist if it does not
        return $this->property[$property_name];  // return current vlaue of property
    }
    
    public function ID() {
        return $this->getProperty();
    }
    
    
    
    
    public function __toString() {
        
        $result = $this->name."::";
        foreach ($this->property as $key => $value) 
            $result .= "$key => $value, ";
        
        return $result;
    }

    public function Debug($web = false)
    {
        $eol = ($web) ? "<br>\n" : "\n";
        
        foreach ($this->property as $key => $value) 
        {
            echo $eol.$this->Name().":: $key => $value";
        }
        
        echo $eol."------------------------------------".$eol;
        $tmp = print_r($this,true);
        echo str_replace("\n", $eol, $tmp);
        echo $eol."------------------------------------".$eol;
        
        
    }

    /*
     * $format should contain a string with
     * {property_name}  ... {property_name} ... {property_name} 
     * 
     * each property you want to appear in the string enclosed in braces
     * these will then be replaced with the actual property values
     */
    public function asFormattedString($format = null)
    {
        $result = "";
        if (is_null($format))
            $result = join (",", $this->property);
        else
        {
            $result = $format;
            foreach ($this->property as $key => $value)
            {
                if (is_bool($value)) $value = ($value) ? configuration::$TRUE : configuration::$FALSE;
                $result = str_replace("{".$key."}", $value, $result);
            }
                
        }
        
        return $result;
        
    }
    
    
    public function Name()
    {
        return $this->name;
    }
    
    protected function copy(Object &$dest) 
    {                
        foreach ($this->property as $key => $value) 
            $dest->property[$key] = $value;        
    }
    
    
}

?>
