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

    public function _ClassName() {
        return get_class($this);
    }


    protected function setProperty($value) {
        $bt = debug_backtrace(); 
        $property_name = $bt[1]['function'];
        if (!array_key_exists($property_name, $this->property)) $this->property[$property_name] = null; //** make the property exist if it does not
        $this->property[$property_name] = $value; //** set property value //** if they passed value the set that value
        return $this->property[$property_name];  //** return current vlaue of property
    }

    protected function setPropertyByName($property_name,$value) {
        if (!array_key_exists($property_name, $this->property)) $this->property[$property_name] = null; //** make the property exist if it does not
        $this->property[$property_name] = $value; //** set property value //** if they passed value the set that value
        return $this->property[$property_name];  //** return current vlaue of property
    }

    protected function getProperty() {
        $bt = debug_backtrace(); 
        $property_name = $bt[1]['function'];
        if (!array_key_exists($property_name, $this->property)) $this->property[$property_name] = null; //** make the property exist if it does not
        return $this->property[$property_name];  //** return current vlaue of property
    }

    public function getPropertyByName($property_name,$null_value = null)
    {
        if (!array_key_exists($property_name, $this->property)) return $null_value;
        return $this->property[$property_name];  //** return current value of property
    }


    protected function hasProperty($property_name) {
        
        if (!array_key_exists($property_name, $this->property))  return true;
        
        return false;
    }
    
    
    protected function readOnly() {
        $bt = debug_backtrace(); 
        $property_name = $bt[1]['function'];
        if (!array_key_exists($property_name, $this->property)) $this->property[$property_name] = null; //** make the property exist if it does not
        return $this->property[$property_name];  //** return current vlaue of property
    }
    
    public function ID() {
        return $this->getProperty();
    }
    
    
    public function __toString() {
        
        $result = $this->name."::";
        foreach ($this->property as $key => $value) 
            $result .= "$key => $value\n<br>";
        
        return $result;
    }

    public function PropertyNames()
    {
        $result = array_flip(array_keys($this->property));
        unset($result['Tags']);
        return array_keys($result);
    }

    public function PropertyValues()
    {
        $result = array();
        foreach ($this->property as $key => $value) {
            $result[$key] = $value;
        }
        unset($result['Tags']);
        return $result;
    }



    public function Debug($web = false)
    {
        $eol = ($web) ? "<br>\n" : "\n";
        
        foreach ($this->property as $key => $value) 
        {
            echo $eol.$this->DataName().":: $key => $value";
        }
        
        echo $eol."------------------------------------".$eol;
        $tmp = print_r($this,true);
        echo str_replace("\n", $eol, $tmp);
        echo $eol."------------------------------------".$eol;
        
        
    }

    /**
     * $format should contain a string with
     * {property_name}  ... {property_name} ... {property_name}
     *
     * each property you want to appear in the string enclosed in braces
     * these will then be replaced with the actual property values
     *
     * @param type $format
     * @return type
     */
    public function asFormattedString($format = null)
    {
        $result = "";
        if (is_null($format))
            foreach ($this->property as $key => $value)
            {
                if (is_bool($value)) $value = ($value) ? self::$TRUE : self::$FALSE;
                if (is_array($value))
                    $result = join (",", $value);
                else
                    $result = $value;

            }
            
        else
        {
            $result = $format;
            foreach ($this->property as $key => $value)
            {
                if (is_bool($value)) $value = ($value) ? self::$TRUE : self::$FALSE;
                
                if (is_array($value))  $value = join (",", $value);
                
                $result = str_replace("{".$key."}", $value, $result);
            }
                
        }
        
        return $result;
        
    }
    
    
    public function DataName()
    {
        return $this->name;
    }
    
    protected function copy(Object &$dest) 
    {                
        foreach ($this->property as $key => $value) 
            $dest->property[$key] = $value;        
    }
    
    

    /**
     * Overloads
     * ()            = return Tags array
     * ($key)        = return Value of this Tag
     * ($key,$value) = Add new Tag ($key) and set it's value ot ($value)
     * ($array)      = Foreach the array and add tags as per  $array's key/**values
     *
     */
    public function Tags()
    {

        if (func_num_args() == 0) return $this->getProperty(); //** return all tags

        if (func_num_args() == 1 && !is_array(func_get_arg(0)))
        {
            //** single
            $tag_array = $this->getProperty();
            if (!array_key_exists(func_get_arg(0), $tag_array)) return null;  //** if the key does not exist then return null
            return $tag_array[func_get_arg(0)];
        }

        //** they sent an array as the first parameter - so assum they want to add this array as tags
        if (func_num_args() == 1 && is_array(func_get_arg(0)))
        {
            $tag_array = $this->getProperty();
            foreach (func_get_arg(0) as $key => $value)
            {
                $tag_array[$key] = $value;
                $this->setProperty($tag_array);
            }
        }


        //** two parameters - so add new tag with Key and Value
        if (func_num_args() >= 2)
        {
            $tag_array = $this->getProperty();
            $tag_array[func_get_arg(0)] = func_get_arg(1);
            $this->setProperty($tag_array);
        }

        
    }

    

    public function InitaliseByArray($src)
    {
        if (!is_array($src)) return null;

        foreach ($src as $key => $value)
            $this->setPropertyByName($key, $value);
    }
 
    
    public static function isObject($src)
    {
        return ($src instanceof self);
    }


    public static function cast($src)
    {
        $src instanceof Object;
        return $src;
    }



    public static $TRUE = "TRUE";
    public static $FALSE = "FALSE";


}

?>
