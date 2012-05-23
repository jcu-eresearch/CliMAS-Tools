<?php

/**
 * 
 *        
 * 
 *   
 */
class OutputFactory  {


    public static $EXTENSION = ".output.class.php";


    /**
     * Search output foldert for class something like
     * 
     * Xxxxxx.output.class
     * 
     */
    public static function Find($src) 
    {

        if ($src instanceof Object) return self::forObject($src);

        switch (gettype($src)) {

            case "array":
                return self::forArray($src);
                break;

            case "boolean":
                return self::forBoolean($src);
                break;

            case "integer":
                return self::forInteger($src);
                break;

            case "double":
                return self::forDouble($src);
                break;

            case "string":
                return self::forString($src);
                break;

            case "object":
                return self::forSimpleObject($src);
                break;

            case "resource":
                return self::forResource($src);
                break;

            case "NULL":
                return self::forNULL($src);
                break;

            case "unknown type":
            default:
                return self::forAnything($src);

            break;
        }

        
    }


    private static function forObject($src)
    {
        $src instanceof Object;

        $outputter = self::getOutputFor($src);

        if (!is_null($outputter))
        {
            $outputter instanceof Output;
            $outputter->Source($src);
        }
        else
        {
            // return a Gneneric Outputtter
            $outputter = new GenericOutput();
            $outputter->Source($src);
            
        }

        return $outputter;

        return null;

    }


    private static function getOutputFor($srcClass)
    {
        // Output Classname
        
        $outputClassname = get_class($srcClass)."Output";
        if (!class_exists($outputClassname))
        {
            $outputClassFilename = file::currentScriptFolder(__FILE__).configuration::osPathDelimiter().get_class($srcClass).self::$EXTENSION;

            if (!file_exists($outputClassFilename))
            {
                // todo LOG ERROR - Throw new Exception("Can't find classfile for {$outputClassname} - Looking for {$outputClassFilename}");
                return null;
            }

            include_once $outputClassFilename;

        }


        $outputObject = new $outputClassname();
        $outputObject instanceof Output;
        return $outputObject;
        
    }


    // all of there should return a class  - will make OUtput CFLases for all of these
    private static function forArray($src)
    {

        $sub_result = array();
        foreach ($src as $key => $sub_array)
        {
            $sub_result[$key] = self::Find($sub_array);
        }

        return htmlutil::table($sub_result);
    }

    private static function forBoolean($src)
    {
        return ($src) ? "TRUE" : "FALSE" ;
    }

    private static function forInteger($src)
    {
        return "{$src}"; // here we could apply a default formatting ??
    }

    private static function forDouble($src)
    {
        return "{$src}"; // here we could apply a default formatting ??
    }

    private static function forString($src)
    {
        return str_replace("\n", "<br>\n", $src);
    }


    private static function forSimpleObject($src)
    {
        return print_r($src,true);
    }
            
    private static function forResource($src)
    {
        return print_r($src,true);
    }

    private static function forNULL($src)
    {
        return "NULL";
    }

    private static function forAnything($src)
    {
        return "UNKNOWN DATA TYPE::: ".print_r($src,true);
    }


    public static function Style($src)
    {
        $o = self::Find($src);
        return $o->Style();
    }

    public static function Content($src)
    {        
        


        $o = self::Find($src);
        return $o->Content();   
    }


}

?>
