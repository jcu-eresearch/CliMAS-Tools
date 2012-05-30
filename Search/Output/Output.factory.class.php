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

        $outputter->PreProcess();

        return $outputter;
        

    }


    private static function getOutputFor($srcClass)
    {
        // Output Classname

        //echo "srcClass = $srcClass<br>";

        $className = get_class($srcClass);

        $outputClassname = $className;
        if (!util::contains($outputClassname, "Output"))  $outputClassname = $outputClassname . "Output";

        //echo "outputClassname = $outputClassname<br>";

        if (!class_exists($outputClassname))
        if (!self::includeOutputClassFor($srcClass))
        {
            echo "{$outputClassname} still does not exist<br>";
        }

        $outputObject = new $outputClassname();
        $outputObject instanceof Output;

        return $outputObject;
        
    }


    private static function outputClassfilename($srcClass)
    {
        $fn = file::currentScriptFolder(__FILE__).configuration::osPathDelimiter().get_class($srcClass).self::$EXTENSION;

        if (file_exists($fn)) return $fn; // it's in the Outputfolder

        // otherwise see if we have it ina "Finder" folder
        // ie we will look at the class name and see if we can find a folder that has the saem finder name

        if ($srcClass instanceof Action)
        {
            $srcClass instanceof Action;
            $folderName =   file::currentScriptFolder(__FILE__).
                            configuration::osPathDelimiter().
                            str_replace(FindersConfiguration::$CLASS_NAME_SUFFIX_FINDER, "", $srcClass->FinderName());

            if (is_dir($folderName))
            {
                $fn = $folderName.configuration::osPathDelimiter().get_class($srcClass).self::$EXTENSION;
                if (file_exists($fn)) return $fn; // it's in a FINDER Outputfolder
            }
        }

        return null;
    }

    private static function outputClassfilenameExists($srcClass)
    {
        if (!self::hasOutputClassFor($srcClass))
        {
            echo "Can't find Output class file for ".get_class($srcClass)." ... ".self::outputClassfilename($srcClass)."<br>";

            // todo LOG ERROR - Throw new Exception("Can't find classfile for {$outputClassname} - Looking for {$outputClassFilename}");
            return false;
        }

        return true;
    }


    private static function includeOutputClassFor($srcClass)
    {
        if (self::outputClassfilenameExists($srcClass))
        {

            include_once self::outputClassfilename($srcClass);
            return true;
        }

        return false;

    }

    public static function hasOutputClassFor($srcClass)
    {
        return file_exists(self::outputClassfilename($srcClass));
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


    public static function Style(iOutput $src)
    {
        $o = self::Find($src);
        return $o->Style();
    }

    public static function Content(iOutput $src)
    {        
        if (is_null($src)) return null;

        $o = self::Find($src);
        return $o->Content();   
    }




}

?>
