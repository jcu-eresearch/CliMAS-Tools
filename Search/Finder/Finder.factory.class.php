<?php

/**
 * Find "Finder" classes,
 * - load Class file into memory (include)
 * - instantiate object
 * - Set Action
 *
 */
class FinderFactory {

    public static $EXTENTSION_FINDER = ".finder.class.php";
    public static $EXTENTSION_ACTION = ".action.class.php";

    /**
     * Find, include, instaniate and return subclass object of Finder
     * @param string $srcClassname  .. Name of the Class to find
     * @return null|\Finder 
     */
    public static function Find($srcClassname)
    {

        if (self::isFinder($srcClassname)) return self::Finder ($srcClassname);

        $action = self::Action($srcClassname);
        if (!is_null($action)) return $action;

        return null;
    }



    private static function Finder($finderClassname)
    {
        if (!self::isFinder($finderClassname)) return null; //TODO: LOG

        if (!self::FinderFilenameExists($finderClassname)) return null; //TODO: LOG

        include_once self::FinderFilename($finderClassname);  // INCLUDE this finder

        if (!self::FinderClassExists($finderClassname)) return null; //TODO: LOG


        // Instantiate an object from the Class
        $result = new $finderClassname();

        if (!($result instanceof Finder)) return null; //TODO: LOG
        $result instanceof Finder;

        return $result;


    }

    public static function isFinder($finderClassname)
    {
        return util::contains($finderClassname, FindersConfiguration::$CLASS_NAME_SUFFIX_FINDER);
    }


    public static function FinderName($finderClassname)
    {
        if (!self::isFinder($finderClassname)) return null;
        return str_replace(FindersConfiguration::$CLASS_NAME_SUFFIX_FINDER, "", $finderClassname);
    }


    public static function FinderFilename($finderClassname)
    {
        if (!self::isFinder($finderClassname)) return null;
        return file::currentScriptFolder(__FILE__).configuration::osPathDelimiter().self::FinderName($finderClassname).self::$EXTENTSION_FINDER;
    }

    public static function FinderFilenameExists($finderClassname)
    {
        return file_exists(self::FinderFilename($finderClassname));
    }


    public static function FinderClassExists($finderClassname)
    {
        return class_exists($finderClassname);
    }


    public static function Actions()
    {
        // Actin class names from folders
        $files = file::find_files(file::currentScriptFolder(__FILE__), self::$EXTENTSION_ACTION);
        
        $actions = array();
        foreach ($files as $file) 
        {
            $key = str_replace(self::$EXTENTSION_ACTION, "", $file);
            $key = str_replace(file::currentScriptFolder(__FILE__).configuration::osPathDelimiter(), "", $key);
            $key = util::fromLastSlash($key);

            $actions[$key] = $file;
        }

        return $actions;
        
    }

    public static function ActionsContaining($str)
    {
        return array_util::ElementsThatContain(self::Actions(), $str);
    }


    public static function ActionsNamed($str)
    {
        $actions = array_util::ElementsThatContain(self::Actions(), $str);

        $result = array();
        foreach ($actions as $actionClassname => $actionFilename)
        {            
            if (util::endsWith($actionClassname, $str))
               $result[$actionClassname] = $actionFilename;
        }

        return $result;
    }


    public static function Action($actionClassname)
    {

        if (is_null($actionClassname)) return null; // todo: log
        
        $actionClassname = trim($actionClassname);
        if ($actionClassname == "") return null; // todo: log


        $actionFilename = array_util::Value(self::Actions(), $actionClassname, null);

        if (is_null($actionFilename)) return null; // todo: log

        include_once $actionFilename;  // INCLUDE this action Class

        $result = new $actionClassname();

        if (!($result instanceof iAction)) return null; //TODO: LOG

        $result instanceof iAction;

        return $result;
        
    }

    
    /**
     * Find Finder and Action then execute Action
     * @param type $actionClassname
     * @return null|\Finder
     */
    public static function Execute($actionClassname)
    {
        if (is_null($actionClassname)) return null;

        $A = self::Action($actionClassname);
        if (is_null($A))
        {
            echo "FAILED: Execute {$actionClassname}";  //TODO: logg
            return null;   // Return Null
        }

        $A instanceof iAction;
        $A->Execute();
        return $A;
    }


    /**
     *
     * @param type $actionClassname
     * @return null|\mixed
     */
    public static function Result($actionClassname)
    {

        if (is_null($actionClassname)) return null;

        $A = self::Execute($actionClassname);
        if (is_null($A))
        {
            echo "FAILED: Result {$actionClassname}";  //TODO: logg
            return null;   // Return Null
        }

        return $A->Result();
    }



    public static function Description($actionClassname)
    {
        if (is_null($actionClassname)) return null;

        $action = self::Action($actionClassname);
        if (is_null($action)) return null; //TODO log

        return $action->Description();

    }

    public static function Descriptions($actionClassnames)
    {
        if (is_null($actionClassnames)) return null;
        if (!is_array($actionClassnames)) self::Description($actionClassnames);

        $result = array();
        foreach ($actionClassnames as $actionClassname)
            $result[$actionClassname] = self::Description($actionClassname);

        return $result;
    }


    /**
     * Lookfor Finder or Action Class, LOad it, Instatiate it then return value from method
     *
     * @param string $className Finder or Action Name
     * @param type $methodName  Method of that Finder or Action
     * @return mixed Returned valie of method
     */
    public static function GetMethodResult($className,$methodName,$params = null)
    {
        $object = self::Find($className);
        if (is_null($object)) return null;

        if (!method_exists($object, $methodName)) return null;


        return $object->$methodName($params);


    }



}

?>
