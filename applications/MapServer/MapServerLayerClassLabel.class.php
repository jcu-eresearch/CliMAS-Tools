<?php
/***
 *        
 * 
 *   
 */
class MapServerLayerClassLabel extends Object {
    
    public function __construct() { 
        
        parent::__construct();

        $this->Color(RGB::ColorBlack());
        $this->ShadowColor(RGB::ColorBlack());
        
        $this->ShadowSizeX(0);
        $this->ShadowSizeY(0);
        
        $this->Type("TRUETYPE");
        $this->Font("arial"); // TODO REad Default Font Name this needs to exsist in the Fontset Set File
        $this->Size(9);
        $this->AntiAlias(true);
        $this->Position(self::$POSITION_UPPER_LEFT);
        $this->Partials(false);
        $this->MinDistance(100);
        $this->Buffer(4);
        $this->Display(true);
        
    }
    
    public function __destruct() {    
        parent::__destruct();
    }

    public function Display() 
    {

        if (func_num_args() == 0) 
            $result = $this->getProperty();
        else
            $result = $this->setProperty(func_get_arg(0));    
        
        return $result;        
    }

    
    
    public function Color() {

        if (func_num_args() == 0) 
            $result = $this->getProperty();
        else
        {
            if ((func_get_arg(0) instanceof RGB))
                $result = $this->setProperty(func_get_arg(0));    
            else
                $result = RGB::create(func_get_args());
        }
        
        $result instanceof RGB;
        return $result;
        
    }

    public function ShadowColor() {

        if (func_num_args() == 0) 
            $result = $this->getProperty();
        else
        {
            if ((func_get_arg(0) instanceof RGB))
                $result = $this->setProperty(func_get_arg(0));    
            else
                $result = RGB::create(func_get_args());
        }
        
        $result instanceof RGB;
        return $result;
        
    }
    

    public function ShadowSizeX() 
    {

        if (func_num_args() == 0) 
            $result = $this->getProperty();
        else
        {
            $result = $this->setProperty(func_get_arg(0));    
            $this->setPropertyByName("ShadowSizeString","{$this->ShadowSizeX()} {$this->ShadowSizey()}" );
        }
        
        return $result;        
    }
    
    public function ShadowSizeY() 
    {
        if (func_num_args() == 0) 
            $result = $this->getProperty();
        else
        {
            $result = $this->setProperty(func_get_arg(0));    
            $this->setPropertyByName("ShadowSizeString","{$this->ShadowSizeX()} {$this->ShadowSizey()}" );
        }
            
        
        return $result;        
    }

    public function ShadowSizeString() 
    {
        return $this->getProperty();
    }
   
    public function Type() 
    {

        if (func_num_args() == 0) 
            $result = $this->getProperty();
        else
            $result = $this->setProperty(func_get_arg(0));    
        
        return $result;        
    }
    
    public function Font() 
    {

        if (func_num_args() == 0) 
            $result = $this->getProperty();
        else
            $result = $this->setProperty(func_get_arg(0));    
        
        return $result;        
    }
    
    
    public function Size() 
    {

        if (func_num_args() == 0) 
            $result = $this->getProperty();
        else
            $result = $this->setProperty(func_get_arg(0));    
        
        return $result;        
    }

    
    
    
    public function AntiAlias() 
    {

        if (func_num_args() == 0) 
            $result = $this->getProperty();
        else
            $result = $this->setProperty(func_get_arg(0));    
        
        return $result;        
    }
    
    
    public function Position() 
    {

        if (func_num_args() == 0) 
            $result = $this->getProperty();
        else
            $result = $this->setProperty(func_get_arg(0));    
        
        return $result;        
    }
    
    public function Partials() 
    {

        if (func_num_args() == 0) 
            $result = $this->getProperty();
        else
            $result = $this->setProperty(func_get_arg(0));    
        
        return $result;        
    }
    
    public function MinDistance() 
    {

        if (func_num_args() == 0) 
            $result = $this->getProperty();
        else
            $result = $this->setProperty(func_get_arg(0));    
        
        return $result;        
    }

    public function Buffer() 
    {

        if (func_num_args() == 0) 
            $result = $this->getProperty();
        else
            $result = $this->setProperty(func_get_arg(0));    
        
        return $result;        
    }
    
    
    public static $POSITION_UPPER_LEFT    = "ul";
    public static $POSITION_UPPER_CENTER  = "uc";
    public static $POSITION_UPPER_RIGHT   = "ur";
    public static $POSITION_CENTER_LEFT   = "cl";
    public static $POSITION_CENTER_CENTER = "cc";
    public static $POSITION_CENTER_RIGHT  = "cr";
    public static $POSITION_LOWER_LEFT    = "ll";
    public static $POSITION_LOWER_CENTER  = "lc";
    public static $POSITION_LOWER_RIGHT   = "lr";
    
    public static $STATUS_OFF = "OFF";
    public static $STATUS_ON  = "ON";
    
    
}
?>