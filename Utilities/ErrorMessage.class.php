<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ErrorMessage
 *
 * @author jc166922
 */
class ErrorMessage extends Object {
    
    public static function Marker($message)
    {
        
        if (php_sapi_name() == "cli")
        {
            
            if (is_array($message))
            {
                $msg =  str_pad(datetimeutil::NowDateTime(), 20, " ")
                    .print_r($message)."\n"
                    ;
                
            }
            else
            {
                $msg =  str_pad(datetimeutil::NowDateTime(), 20, " ")
                    .trim($message)."\n"
                    ;
                
            }
            

            echo $msg;
            
        }
        
        
        
    }

    
    public static function Stacked($method_name,$line_num,$message,$log_error = true,  ErrorMessage $previous = null)
    {
        
        $E = new ErrorMessage($method_name,$line_num,$message,$log_error);
        $E->PreviousError($previous);
        return $E;
    }
    
    
    public function __construct($method_name = null,$line_num= null,$message= null,$log_error = true) 
    {    
        parent::__construct();
        
        $this->SourceMethod($method_name);
        $this->SourceLine($line_num);
        $this->Message($message);
        $this->DateTime(datetimeutil::NowDateTime());
        
//        // only LOg to DB if we want it to - might be a DB error that we can't log to DB
//        if ($log_error)
//        {
//            if (!is_string($message))
//                DBO::LogError($method_name."(".$line_num.")",print_r($message,true));    
//            else
//                DBO::LogError($method_name."(".$line_num.")",$message);
//            
//        }

        
        if (php_sapi_name() == "cli")
        {
            $msg =  str_pad($this->DateTime(), 20, " ")
                   .str_pad($this->SourceMethod(), 30, " ")
                   .str_pad($this->SourceLine(), 20, " ")
                   ."\n".$this->Message()."\n\n"
                   ;

            echo $msg;
            
        }
        
        
    }
    
    public function __destruct() {    
        parent::__destruct();
    }
    
    public function Message() {
        if (func_num_args() == 0) return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
        
    }
    
    public function SourceLine() {
        if (func_num_args() == 0) return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
        
    }
    
    public function SourceMethod() {
        if (func_num_args() == 0) return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
        
    }
    
    public function DateTime() {
        if (func_num_args() == 0) return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
        
    }
    
    public function PreviousError() {
        if (func_num_args() == 0) return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
        
    }
    
    
    
}

?>
