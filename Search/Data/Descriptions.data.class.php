<?php
/**
 * 
 *  A List of single Description Objects
 *   
 */
class Descriptions extends Data {
    

    private $descriptions = array();

    public function __construct() {
        parent::__construct();
        $this->Name(__CLASS__);

    }
    
    public function __destruct() {

        parent::__destruct();
    }

    public function Descriptions() {
        return $this->descriptions;
    }


    public function Add(Description $value)
    {
        $this->descriptions[$value->Name()] = $value;
        return $value->Name();
    }

    public function Remove($key)
    {
        if (!$this->has($key)) return false;

        unset($this->descriptions[$key]);
        return true;
    }

    public function Get($key,$null_value = null)
    {
        if (!$this->has($key)) return $null_value;
        return $this->descriptions[$key];
    }

    public function has($key)
    {
        return (array_key_exists($key, $this->descriptions));
    }

    public static function isA($src)
    {
        return $src instanceof Descriptions;
    }
 
    public static function cast($src)
    {
        $result = $src;
        $result instanceof Descriptions;
        return $result;
    }

}

?>
