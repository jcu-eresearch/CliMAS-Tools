<?php
/**
 *
 *  A List of single Action Class names
 *
 */
class Actions extends Data {


    private $actions = array();

    public function __construct() {
        parent::__construct();
        $this->Name(__CLASS__);

    }

    public function __destruct() {

        parent::__destruct();
    }

    public function Actions() {
        return $this->actions;
    }


    public function FromArray($src)
    {
        if (is_null($src)) return null;
        if (!is_array($src)) return null;

        foreach ($src as $value)
        {
            $this->Add($value);
        }

    }


    public function Add($value)
    {
        $this->actions[$value] = $value;
        return $value;
    }

    public function Remove($key)
    {
        if (!$this->has($key)) return false;

        unset($this->actions[$key]);
        return true;
    }

    public function Get($key,$null_value = null)
    {
        if (!$this->has($key)) return $null_value;
        return $this->actions[$key];
    }

    public function Descriptions()
    {
        $result = array();
        foreach ($this->actions as $value)
            $result[$value] = FinderFactory::Description($value);

        return $result;
    }


    public function has($key)
    {
        return (array_key_exists($key, $this->actions));
    }

    public static function isA($src)
    {
        return $src instanceof Actions;
    }

    public static function cast($src)
    {
        if (!self::isA($src)) return null;

        $result = $src;
        $result instanceof Actions;
        return $result;
    }

}

?>
