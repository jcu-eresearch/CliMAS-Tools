<?php
class logger
{    

    public static $display = true;
    public static $logged_called = true;
    public static $html = false;
    
    public static function text($text,$delim = "\n")
    {
        $bt = debug_backtrace();   
        if (!is_array($text))
        {
            $text = trim($text);
            $str = datetimeutil::now()."|"."{$bt[1]['class']}.{$bt[1]['function']}|".$text."\n";
            if (self::$display) 
                echo (self::$html) ? str_replace("\n", "<br>\n", $str) : $str;
            
            
            file_put_contents(self::filename(),$str , FILE_APPEND);            
        }
        else
        {
            foreach ($text as $key => $single_text)
            {
                $single_text = trim($single_text);
                $str = datetimeutil::now()."|"."{$bt[1]['class']}.{$bt[1]['function']}| $key => $single_text{$delim}";
                if (self::$display) 
                    echo (self::$html) ? str_replace("\n", "<br>\n", $str) : $str;
                
                file_put_contents(self::filename(),$str , FILE_APPEND);            
            }
        }
    }

    public static function error($text)
    {
        $bt = debug_backtrace();   
        if (!is_array($text))
        {
            $text = trim($text);
            $str = datetimeutil::now()."|"."{$bt[1]['class']}.{$bt[1]['function']}| ERROR:: ".$text."\n";
            if (self::$display) 
                echo (self::$html) ? str_replace("\n", "<br>\n", $str) : $str;
                
            
            file_put_contents(self::filename(),$str , FILE_APPEND);
            file_put_contents(self::filename_error(),$str , FILE_APPEND);
            
        }
        else
        {
            foreach ($text as $key => $single_text)
            {
                $single_text = trim($single_text);
                $str = datetimeutil::now()."|"."{$bt[1]['class']}.{$bt[1]['function']}| ERROR:: $key => $single_text\n";
                if (self::$display) 
                    echo (self::$html) ? str_replace("\n", "<br>\n", $str) : $str;
                    
                file_put_contents(self::filename(),$str , FILE_APPEND);
                file_put_contents(self::filename_error(),$str , FILE_APPEND);

            }
        }
    }
    
    
    public static function called($level =1)
    {
        if (self::$logged_called)
        {
            $bt = debug_backtrace();
            
            $class = "{$bt[1]['class']}";
            $func = "{$bt[1]['function']}";
            
            $args = array();
            foreach ($bt[1]['args'] as $key => $value) 
            {
                if (is_array($value))
                    $args[] = "Array [".count($value)."]";
                else
                    $args[] = $value;
            }
            
            
            $str = datetimeutil::now()."|"."{$class}.{$func}|"."(" .join(",",$args). ")\n";

            if (self::$display) 
                echo (self::$html) ? str_replace("\n", "<br>\n", $str) : $str;

            file_put_contents(self::filename(),$str , FILE_APPEND);            
        }
    }
    
    
    
    public static function header($text)
    {
       $bt = debug_backtrace();
        
       $str =   "\n".
                datetimeutil::now()."|"."{$bt[1]['class']}.{$bt[1]['function']}\n";
                "\n-------------------\n".
                $text.
                "\n-------------------\n";
       
        if (self::$display) 
            echo (self::$html) ? str_replace("\n", "<br>\n", $str) : $str;
       
        file_put_contents(self::filename(),$str, FILE_APPEND);
    }
    
    
    public static function clear()
    {
        file::reallyDelete(self::filename());
        file::reallyDelete(self::filename_error());

    }

    public static function variable($variable)
    {
        
        $bt = debug_backtrace();
        $str =  datetimeutil::now()."|"."{$bt[1]['class']}.{$bt[1]['function']}|".
                "\n-------------------\n".
                print_r($variable,true).
                "\n-------------------\n";
        
        if (self::$display) 
            echo (self::$html) ? str_replace("\n", "<br>\n", $str) : $str;
            
        
        file_put_contents(self::filename(),$str, FILE_APPEND);
    }
    
    public static function filename()
    {
        return "/tmp/jc166922_php_log.txt";
    }

    public static function filename_error()
    {
        return "/tmp/jc166922_php_log.err";
    }
    
    
    public static function copy($to_filename)
    {
        if (self::$display) echo "copying log file to $to_filename \n"; 
        copy(self::filename(), $to_filename);        
    }
    
    public static function show()
    {
        echo file_get_contents(self::filename());
    }
    
    public static function show_logger_filename()
    {
        if (self::$display) 
            echo "-------------------------------------------------------\n";
            echo "Current Logger output going to ...".self::filename()."\n";
            echo "-------------------------------------------------------\n";
    }
    
}

?>