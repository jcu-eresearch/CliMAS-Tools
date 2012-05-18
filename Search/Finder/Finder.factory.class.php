<?php

/**
 * Find "Finder" classes,
 * - load Class file into memory (include)
 * - instantiate object
 * - Set Action
 *
 */
class FinderFactory {

    public static $EXTENTSION = ".finder.class.php";
    public static $EXTENTSION_ACTION = ".action.class.php";

    /**
     * Find, include, instaniate and return subclass object of Finder
     * @param string $srcClassname  .. Name of the Class to find
     * @return null|\Finder 
     */
    public static function Find($srcClassname)
    {
        
        $finderClassname = null;
        $actionClassname = null;

        $finderClassname = self::hasFinder($srcClassname);  // get finder for this $src
        
        // $finder  is not NULL it represnts a Simple Finder Name

        if (is_null($finderClassname))
        {
            // maybe $srcClassname is an action class name

            $finderClassname = self::FinderClassnameForActionClassname($srcClassname); // try to find vi an action lookup
            if (!is_null($finderClassname)) $actionClassname = $srcClassname; // found finder thru actionCLassName so $src must be an action
        }

        if (is_null($finderClassname))
        {
            echo "[{$finderClassname}]does not seem to be a Finder Class<br>"; // TODO:: Exception or ??
            return null;   // Return Null if we can't find a class
        }


        $finderFilename = file::currentScriptFolder(__FILE__).configuration::osPathDelimiter().self::SimpleFinderName($finderClassname).self::$EXTENTSION;

        if (!file_exists($finderFilename))
        {
            echo "Can't find file $finderFilename for a Finder [{$finderClassname}]"; // TODO:: Exception or Logg
            return null;   // Return Null if we can't find a class
        }


        include_once $finderFilename;  // INCLUDE this finder

        if (!class_exists($finderClassname))  // check to see if we includes it properly
        {
            echo "AFter including {$finderFilename}  class {$finderClassname} does not exist<br>";  //TODO: logg this
            return null;   // Return Null
        }


        $result = new $finderClassname();

        if (!($result instanceof Finder))
        {
            echo "class inside {$finderFilename} is not an instance of aFinder<br>";  //TODO: logg
            return null;   // Return Null
        }


        $result instanceof Finder;

        $result->UseAction(self::SimpleActionName($actionClassname));

        return $result;
        
    }

    
    /**
     * Find Finder and Action then execute Action
     * @param type $actionClassname
     * @return null|\Finder
     */
    public static function Execute($actionClassname)
    {

        $F = self::Find($actionClassname);
        if (is_null($F))
        {
            echo "FAILED: Execute {$actionClassname}";  //TODO: logg
            return null;   // Return Null
        }

        $F instanceof Finder;
        $F->Execute();
        return $F;
    }


    /**
     *
     * @param type $actionClassname
     * @return null|\mixed
     */
    public static function Result($actionClassname)
    {
        $F = self::Execute($actionClassname);
        if (is_null($F))
        {
            echo "FAILED: Result {$actionClassname}";  //TODO: logg
            return null;   // Return Null
        }

        return $F->Result();
    }


    /**
     *
     * @return array .. [Finder Classname]  => FinderName
     */
    public static function FinderClassnames()
    {
        $files = file::find_files(file::currentScriptFolder(__FILE__), self::$EXTENTSION);

        $files = array_util::Replace($files,file::currentScriptFolder(__FILE__)."/**","");
        $files = array_util::Replace($files,self::$EXTENTSION,"");

        $finders = array();
        foreach ($files as $FinderFolderName) 
            if (is_dir(file::currentScriptFolder(__FILE__).configuration::osPathDelimiter().$FinderFolderName))
                $finders[$FinderFolderName.FindersConfiguration::$CLASS_NAME_SUFFIX_FINDER] = $FinderFolderName;

        return $finders;
    }

    /**
     * If we have a finder of this name  $finderClassname the return it
     *
     * @param type $finderClassname
     * @return null|\string
     */
    public static function hasFinder($finderClassname)
    {
        return array_util::Value(self::FinderClassnames(), $finderClassname, null);
    }


    /**
     * @return array(JaggedArray) All actions for all finders [FinderClassname] => ( [ActionClassname]=>ActionName, ...)
     */
    public static function Finders2Actions()
    {

        $result = array();
        foreach (self::FinderClassnames() as $finderClassname => $finderName)
        {

            $actionFolder = file::currentScriptFolder(__FILE__).configuration::osPathDelimiter().$finderName;
            $files = file::folder_files($actionFolder, configuration::osPathDelimiter(), true);
            $files = file::arrayFilter($files, self::$EXTENTSION_ACTION);

            $action_names = array_util::Replace(array_keys($files),self::$EXTENTSION_ACTION,"");

            foreach ($action_names as $action_name)
               $result[$finderClassname][$finderName.$action_name] = $action_name;
        }

        return $result;


    }

    /**
     *
     * @return array [ActionClassname] => [(Parent) FinderClassname]
     */
    public static function ActionClassnamesToFinderClassnames()
    {
        $result = array();
        foreach (self::Finders2Actions() as $finderClassname => $actionClassnames)
            foreach ($actionClassnames as $actionClassname => $actionName)
               $result[$actionClassname] = $finderClassname;

        return $result;

    }


    /**
     *
     * @param type $actionClassname .. Action name to lookup to find it's "parent" Finder Class Name
     * @return type
     */
    public static function FinderClassnameForActionClassname($actionClassname)
    {
        return array_util::Value(self::ActionClassnamesToFinderClassnames(), $actionClassname, null);
    }

    /**
     *
     * @param type $finderClassName
     * @return array ActionsClassNames associated with this Finder
     */
    public static function ActionsForFinder($finderClassName)
    {
        return array_util::Value(self::Finders2Actions, $finderClassName, null);
    }


    /**
     *
     * @param type $finderName
     * @return string FinderClassname
     */
    public static function SimpleFinderName2FullFinderName($finderName)
    {
        return $finderName.FindersConfiguration::$CLASS_NAME_SUFFIX_FINDER;
    }


    /**
     *
     * @param type $finderClassName
     * @return string .. Finder Name
     */
    public static function SimpleFinderName($finderClassName)
    {
        return str_replace(FindersConfiguration::$CLASS_NAME_SUFFIX_FINDER, "", $finderClassName);
    }


    /**
     *
     * @param type $actionClassname
     * @return string Simple action name
     */
    public static function SimpleActionName($actionClassname)
    {
        $finderClassname = self::FinderClassnameForActionClassname($actionClassname);

        $finder_simple_name = self::SimpleFinderName($finderClassname);

        return str_replace($finder_simple_name, "", $actionClassname);
    }



}

?>
