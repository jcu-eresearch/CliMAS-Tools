<?php
/**
 * 
 *  
 */
interface iData {

    public function DataName();

    public function Type();

    public function Data();

    public static function isA($src);

    public static function cast($src);

}

/**
 * 
 *   
 */
class Data extends Object implements iData{

    /**
     * @param $data
     */
    public function __construct($data = null) {
        parent::__construct();
        $this->Data($data);
    }
    
    public function __destruct() {    
        parent::__destruct();
    }


    /**
     * @property
     * @return type
     */
    public function DataName()
    {
        if (func_num_args() == 0) return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }


    /**
     * Called with (null) return value of property<br>
     * Called with ($arg)    set value of property to $arg<br>
     *
     * @return mixed current property value
     *
     */
    public function Type() 
    {
        if (func_num_args() == 0) return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }

    /**
     * Called with (null) return value of property<br>
     * Called with ($arg)    set value of property to $arg<br>
     *
     * @return mixed current property value
     *
     */
    public function Data()
    {
        if (func_num_args() == 0) return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }


    /**
     * @return BOOL - TRUE     $src is a Data object
     *                      or $src is a Data subclass object
     */
    public static function isA($src)
    {
        return ($src instanceof Data);
    }

    /**
     * @return Data
     */
    public static function cast($src)
    {
        if (self::isA($src))
        {
            $result = $src;
            $result instanceof Data;
            return $result;
        }

        // otherwise create a data and put the values they gave into it
        $D = new aData($src);
        return $D;
    }

    
}
?>