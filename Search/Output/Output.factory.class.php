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
        if (is_null($src)) return null;


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


    private static function forObject(Object $src)
    {


        $src instanceof Object;

        $outputter = null;
        $outputter instanceof Output;

        

        $outputter = self::getOutputFor(get_class($src) ); // Try toi find a Actaul Outputter for this object

        if ($src instanceof Action) $src instanceof Action;



        if (!is_null($outputter))
        {
            $outputter->Source($src);
            $outputter->PreProcess();
            return $outputter;
        }

        

        if (is_null($outputter))
        {
            // try to get Outputter for Parent Object - kinda fail UP ??
            $parent_class_name = get_parent_class($src);

            if ($parent_class_name)
            {

                $outputter = self::getOutputFor($parent_class_name);
                if (!is_null($outputter))
                {
                    $outputter->Source($src);
                    $outputter->PreProcess();


                    return $outputter;
                }

            }
                
        }


        if (is_null($outputter))  // still could not find an outputter for this Object
        {
            $outputter = new GenericOutput(); // otherwise return a Gneneric Outputtter 
            $outputter->Source($src);
        }


        return null;
    }


    private static function getOutputFor($srcClassname)
    {

        $outputClassname = (util::contains($srcClassname, "Output")) ? $srcClassname : "{$srcClassname}Output";

        // is it already in memory
        if (!class_exists($outputClassname))
        {

            $classFilename = self::outputClassfilename($srcClassname);

            if (is_null($classFilename))
            {
                //TODO just log this "<br>Could not find an output class filename named[{$classFilename}] for Class named [{$srcClassname}]"; //Todo Log this
                return null;
            }

            include_once $classFilename;   // So the class filename  $classFilename exists so now include it

            // test to see after include we do actually have the class in memory
            if (!class_exists($outputClassname))
            {
                echo "<br>{$outputClassname} still does not exist<br>"; //Todo Log this
                return null;
            }

        }

        $outputObject = new $outputClassname();
        $outputObject instanceof Output;

        return $outputObject;
        
    }


    /**
     * Find filename of Class file that holds an output class for thisd ClassName
     *
     * @param string $classname  Name of Class to find and output for
     * @return string|null
     */
    private static function outputClassfilename($classname)
    {

        // look files class files  that have .output.php in the filename

        $fn = file::currentScriptFolder(__FILE__).configuration::osPathDelimiter().$classname.self::$EXTENSION;

        if (file_exists($fn)) 
        {
            return $fn; // it's in the Outputfolder
        }


        // otherwise see if we have it ina "Finder" folder

        $folders = file::folder_folders(file::currentScriptFolder(__FILE__),configuration::osPathDelimiter() , true);
        

        foreach ($folders as $folderPath) 
        {
            $fn = $folderPath.configuration::osPathDelimiter().$classname.self::$EXTENSION;
            if (file_exists($fn))
            {
                return $fn; // it's in a FINDER Outputfolder
            }
                

        }

        return null;
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
