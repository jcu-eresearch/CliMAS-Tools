<?php
/**
 * 
 * test remote up date
 * 
 * Allows for Support of "Properties"
 * Public properties are defined as Methods()
 *  - if no peremeters are passed to the method then it returns the current value
 *  - if a parameters is passed than this is set to the value of the propery
 * 
 * - All obejcts that are create have a uniqueID
 * 
 *  public function ReadWriteProperty() {
 *        if (func_num_args() == 0) return $this->getProperty();
 *        return $this->setProperty(func_get_arg(0));
 *  }
 *
 * 
 */
class Object  {


    protected $name = "AnObject";
    
    private $property = array();
    
    
    
    /**
     * Create an object 
     * 
     * @param type $id  - if we already have a tracking ID we can pass it in
     */
    public function __construct($id = null) {    
        
        
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

    /**
     * When extend from a subclass will return the SubClass Clas Name
     * 
     * @return string the class type 
     */
    public function _ClassName() {
        return get_class($this);
    }


    /**
     * set value of property
     * Called from Subclass
     * - property name is found by a debug backtrace to see what function / method name called this function
     * 
     * @param mixed $value Property value
     * @return mixed Property value 
     */
    protected function setProperty($value) {
        $bt = debug_backtrace(); 
        $property_name = $bt[1]['function'];
        if (!array_key_exists($property_name, $this->property)) $this->property[$property_name] = null; //** make the property exist if it does not
        $this->property[$property_name] = $value; //** set property value //** if they passed value the set that value
        return $this->property[$property_name];  //** return current vlaue of property
    }

    /*
     *  Setting a property value from somewhere else 
     *  within the class you should use  $this->PropertyName($value)
     *  - This method alows updating of multiple properties via loops
     *  - Also allows for dynamic creation of properties
     * 
     * 
     */
    protected function setPropertyByName($property_name,$value) {
        if (!array_key_exists($property_name, $this->property)) $this->property[$property_name] = null; //** make the property exist if it does not
        $this->property[$property_name] = $value; //** set property value //** if they passed value the set that value
        return $this->property[$property_name];  //** return current vlaue of property
    }

    /**
     * Get value  of property
     * 
     * @return mixed current value of propery
     */
    protected function getProperty() {
        $bt = debug_backtrace(); 
        $property_name = $bt[1]['function'];
        if (!array_key_exists($property_name, $this->property)) $this->property[$property_name] = null; //** make the property exist if it does not
        return $this->property[$property_name];  //** return current vlaue of property
    }

    /*
     *  getting a property value from somewhere else 
     *  within the class you should use  $this->PropertyName()
     *  - This method allows reading of multiple properties via loops
     * 
     */    
    public function getPropertyByName($property_name,$null_value = null)
    {
        if (!array_key_exists($property_name, $this->property)) return $null_value;
        return $this->property[$property_name];  //** return current value of property
    }


    /** 
     * Check object to see if property exists
     * 
     * @param string $property_name Name of property to check for
     * @return boolean Has this property 
     */
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
    
    /**
     * Unique ID of object or ID passed in at creation
     * 
     * @return mixed Object ID
     */
    public function ID() {
        return $this->getProperty();
    }
    
    
    /**
     * String version of objects properties
     * @return string  
     */
    public function __toString() {
        
        $result = $this->name."::\n";
        foreach ($this->property as $key => $value) 
        {            
            $result .= "* $key => $value\n";
        }
            
        
        return $result;
    }

    /**
     * 
     * @return array string arry of property names 
     */
    public function PropertyNames()
    {
        $result = array_flip(array_keys($this->property));
        return array_keys($result);
    }

    
    /**
     * 
     * @return array string arry of property values 
     */
    public function PropertyValues()
    {
        $result = array();
        foreach ($this->property as $key => $value) {
            $result[$key] = $value;
        }
        return $result;
    }



    /**
     * When debugging used to output current properies and values
     * 
     * @param type $web 
     */
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
    public function asFormattedString($format = null,$use_db_escapes = false)
    {
        $result = "";
        if (is_null($format))
            foreach ($this->property as $key => $value)
            {
                if (is_bool($value)) 
                {
                    $value =  ($value) ? self::$TRUE : self::$FALSE;    
                    if ($use_db_escapes) $value = util::dbq($value);
                    
                }
                
                if (is_array($value))
                {
                    $result =  join (",", $value);
                    if ($use_db_escapes) $result = util::dbq($result);
                }   
                else
                {
                    $result = $value;
                    if ($use_db_escapes && !is_numeric($value)) $result = util::dbq($result);
                }
                    

            }
            
        else
        {
            $result = $format;
            foreach ($this->property as $key => $value)
            {
                if (util::contains($value, "http//")) $value = str_replace ('http//', 'http://', $value);
                
                if (is_bool($value)) $value = ($value) ? self::$TRUE : self::$FALSE;
                
                if (is_array($value))  
                {
                    $value = join (",", $value);
                    if ($use_db_escapes) $value = util::dbq($value);
                }
                    
                if ($use_db_escapes)
                {
                    $result = str_replace("{".$key."}", (is_numeric($value) ? $value : util::dbq($value) ), $result);
                }
                else
                {
                    $result = str_replace("{".$key."}", $value, $result);    
                }
                
                
            }
                
        }
        
        return $result;
        
    }
    
    
    
    public function DataName()
    {
        return $this->name;
    }
    
    /**
     * Create copy of this object  into $dest
     * - create a now object and copy property values
     * 
     * @param Object $dest 
     */
    protected function copy(Object &$dest) 
    {                
        foreach ($this->property as $key => $value) 
            $dest->property[$key] = $value;        
    }
    

    
    /**
     * Inititalise the values of the properties of this object from a keyed array
     * @param array $src array(key1 => Value1, key2 => Value2, .....  )
     * @return null 
     */
    public function InitaliseByArray($src)
    {
        if (!is_array($src)) return null;

        foreach ($src as $key => $value)
            $this->setPropertyByName($key, $value);
    }
 
    
    /**
     * Text to see if $src is of Class object or a subclass of Object
     * 
     * @param mixed $src
     * @return bool
     */
    public static function isObject($src)
    {
        return ($src instanceof self);
    }


    /**
     * Cast $src to b an Object
     * 
     * @param Object $src
     * @return Object 
     */
    public static function cast($src)
    {
        $src instanceof Object;
        return $src;
    }


    public static $TRUE = "TRUE";
    public static $FALSE = "FALSE";

}
?>