<?php

class FinderFactory {



    public static $EXTENTSION = ".finder.class.php";
    
    /*
     * Look intoi Finder Folder and instansiate   
     * 
     */
    public static function Find($src,$action = null)
    {

        if (!array_key_exists($src.FindersConfiguration::$CLASS_NAME_SUFFIX_FINDER, self::Available()))
        {

            echo "[$src".FindersConfiguration::$CLASS_NAME_SUFFIX_FINDER."] is NOT a Finder Class<br>"; // TODO:: Exception or ??
            return null;   // Return Null if we can't find a class
        }


        $requestFinderFilename = file::currentScriptFolder(__FILE__)."/{$src}".self::$EXTENTSION;

        if (!file_exists($requestFinderFilename))
        {
            echo "Can't find File $requestFinderFilename<br>"; // TODO:: Exception or Logg
            return null;   // Return Null if we can't find a class
        }

        include_once $requestFinderFilename;  // INCLUDE this finder

        $finderClassName = "{$src}".FindersConfiguration::$CLASS_NAME_SUFFIX_FINDER;

        if (!class_exists($finderClassName))  // check to see if we includes it properly
        {
            echo "class {$finderClassName} does not exist<br>";  //TODO: logg this
            return null;   // Return Null
        }

        $result = new $finderClassName;

        if (!($result instanceof aFinder))
        {
            echo "class inside {$requestFinderFilename} is not an instance of aFinder<br>";  //TODO: logg
            return null;   // Return Null
        }

        $result instanceof aFinder;

        $result->UseAction($action);

        return $result;
        
    }

    public static function Execute($src,$action = null)
    {
        $F = self::Find($src, $action);
        if (is_null($F))
        {
            echo "FAILED: Execute {$src->Name()}<br>";  //TODO: logg
            return null;   // Return Null
        }

        $F->Find();
        return $F;
    }


    public static function Description($src,$action = null)
    {
        $F = self::Find($src, $action);
        if (is_null($F))
        {
            echo "FAILED: Execute {$src->Name()}<br>";  //TODO: logg
            return null;   // Return Null
        }
        
        return $F->Description();
    }

    public static function Result($src,$action = null)
    {
        $F = self::Execute($src,$action);
        if (is_null($F))
        {
            echo "FAILED: Result {$src->Name()}<br>";  //TODO: logg
            return null;   // Return Null
        }

        return $F->Result();
    }



    public static function Available()
    {
   // look thru list of files in tis foilder and return a list of files that would be considerd to be "finders"
        $files = file::arrayFilter(file::folder_files(file::currentScriptFolder(__FILE__),null,true), self::$EXTENTSION);
        $names = array_util::Replace(array_keys($files), self::$EXTENTSION, "");
        

        $result = array();
        foreach ($names as $value)
            $result[$value.FindersConfiguration::$CLASS_NAME_SUFFIX_FINDER] = $value;

        return $result;
    }

}

?>
