<?php

class FinderFactory {


    public static $EXTENTSION = ".finder.class.php";
    
    /*
     * Look intoi Finder Folder and instansiate   
     * 
     */
    public static function Find($src,$action = null)
    {

        if (!array_key_exists($src, self::Available()))
        {
            echo "[$src] is NOT  a Finder Class<br>"; // TODO:: Exception or ??
            return null;   // Return Null if we can't find a class
        }


        $requestFinderFilename = file::currentScriptFolder(__FILE__)."/{$src}.finder.class.php";

        if (!file_exists($requestFinderFilename))
        {
            echo "Can't find File $requestFinderFilename<br>"; // TODO:: Exception or Logg
            return null;   // Return Null if we can't find a class
        }

        include_once $requestFinderFilename;  // INCLUDE this finder

        $finderClassName = "{$src}Finder";

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
        return array_flip(array_util::Replace(array_keys($files), self::$EXTENTSION, ""));
    }

}

?>
