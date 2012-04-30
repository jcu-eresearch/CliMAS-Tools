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
        
        $tags = array();
        $this->setPropertyByName("Tags", $tags );
        
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
                
                if (is_array($value))  $value = join (",", $value);
                
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
    
    
    
    public function Tags($key = null,$value = null)
    {
        if (!is_null($value) && is_null($key)) 
            throw new Exception("Tags called with NULL key  and some value [{$value}]");

        
        if (is_null($key) && is_null($value)) return $this->getProperty(); // return all tags

        
        $tag_array = $this->getProperty();
        
        if (!is_null($key) && is_null($value))   // return one of the tags
        {
            if (!array_key_exists($key, $tag_array)) return null;  // if the key does not exist then return null
            return $tag_array[$key];
        }
            

        // we have a key and we have a value so set the key value pair and update property
        if (!is_null($key) && !is_null($value)) 
        {
            $tag_array[$key] = $value;
            $this->setProperty($tag_array);
        }
        
        
        $result = array();
        $result[$key] = $value;
        
        return $result; // return key value pair
    }
    
    
    
}

?>
