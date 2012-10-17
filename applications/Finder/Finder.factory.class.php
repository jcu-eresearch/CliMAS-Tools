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
        if (is_null( $srcClassname))  
            return new ErrorMessage(__METHOD__, __LINE__, "srcClassname is Null");
        
        if (self::isFinder($srcClassname))  
        {
            $finder = self::Finder($srcClassname);
            if ($finder instanceof Finder) return $finder;
            
            if ($finder instanceof ErrorMessage)
                return ErrorMessage::Stacked(__METHOD__, __LINE__, "srcClassname is Null",true,$finder);
        }

        
        $action = self::Action($srcClassname);
        
        if ($action instanceof ErrorMessage) return new ErrorMessage(__METHOD__, __LINE__, "No class found called {$srcClassname}");
        
            
        return $action;

        
    }



    private static function Finder($finderClassname)
    {
        
        if (is_null($finderClassname)) return new ErrorMessage(__METHOD__, __LINE__, "finderClassname is Null");
        
        if (!self::isFinder($finderClassname))  return null;
        
        if (!self::FinderFilenameExists($finderClassname)) return new ErrorMessage(__METHOD__, __LINE__, "Can't find file for $finderClassname ");

        
        // INCLUDE this finder
        include_once self::FinderFilename($finderClassname);  

        
        if (!self::FinderClassExists($finderClassname))  return new ErrorMessage(__METHOD__, __LINE__, "Included $finderClassname butr Class not available ");


        // Instantiate an object from the Class
        $result = new $finderClassname();

        if (!($result instanceof Finder)) return new ErrorMessage(__METHOD__, __LINE__, "Instantiate an object from the Class ({$finderClassname}) but it was not a Finder");

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

        if (is_null($actionClassname))  return new ErrorMessage(__METHOD__,__LINE__,"actionClassname is null");            
        
        $actionClassname = trim($actionClassname);
        if ($actionClassname == "")  return new ErrorMessage(__METHOD__,__LINE__,"actionClassname is EMPTY");            

        $actionFilename = array_util::Value(self::Actions(), $actionClassname, null);
        if (is_null($actionFilename))  return new ErrorMessage(__METHOD__,__LINE__,"actionFilename is NULL");
            

        include_once $actionFilename;  // INCLUDE this action Class

        
        $result = new $actionClassname();
        
        if ($result instanceof iAction )
        {
            $result instanceof iAction;
            return $result;
        }

        if ($result instanceof CommandAction )
        {
            $result instanceof CommandAction;
            return $result;
        }
        
        return new ErrorMessage(__METHOD__,__LINE__,"{$actionClassname} is NOT iAction or CommandAction ");

        
    }

    
    /**
     * Find Finder and Action then execute Action
     * @param type $actionClassname
     * @return null|\Finder
     */
    public static function Execute($actionClassname)
    {
        if (is_null($actionClassname))  return new ErrorMessage(__METHOD__,__LINE__,"ActionClassname is null");                    

        $A = self::Action($actionClassname);
        if ($A instanceof ErrorMessage) return ErrorMessage::Stacked(__METHOD__, __LINE__, "Can't execute",true,$A);

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

        if (is_null($actionClassname))  return new ErrorMessage(__METHOD__,__LINE__,"ActionClassname is null");                    

        $A = self::Execute($actionClassname);
        if ($A instanceof ErrorMessage) return ErrorMessage::Stacked(__METHOD__, __LINE__, "Can't get Result",true,$A);        

        return $A->Result();
    }



    public static function Description($actionClassname)
    {
        if (is_null($actionClassname))  return new ErrorMessage(__METHOD__,__LINE__,"ActionClassname is null");                    

        $A = self::Action($actionClassname);
        if ($A instanceof ErrorMessage) return ErrorMessage::Stacked(__METHOD__, __LINE__, "Can't get Description",true,$A);        

        return $A->Description();

    }

    public static function Descriptions($actionClassnames)
    {
        if (is_null($actionClassnames))  return new ErrorMessage(__METHOD__,__LINE__,"actionClassnames is null");
        
        if (!is_array($actionClassnames)) self::Description($actionClassnames);

        $result = array();
        foreach ($actionClassnames as $actionClassname)
        {
            $desc = self::Description($actionClassname);
            
            if ( !($desc instanceof ErrorMessage)) 
                $result[$actionClassname] = $desc;
        }
            

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
        
        if (is_null($className))  return new ErrorMessage(__METHOD__,__LINE__,"$className is null");                            
        if (is_null($methodName))  return new ErrorMessage(__METHOD__,__LINE__,"$methodName is null");                            
        
        $object = self::Find($className);
        if ($object instanceof ErrorMessage) return ErrorMessage::Stacked (__METHOD__,__LINE__, "", true, $object);


        if (!method_exists($object, $methodName)) 
            new ErrorMessage(__METHOD__,__LINE__,"Method [{$methodName}] does not exists inside class [{$className}]");
                

        return $object->$methodName($params);


    }

    /**
     * Possible that this class may not be loaded so make sure it is before decoding
     * 
     * @param type $data  to be decoded
     * @param type $decode_using_base64
     * @return \ErrorMessage 
     */
    public static function UnserialiseAndLoadUndefinedClass($data,$decode_using_base64 = true)
    {

        if ($decode_using_base64)
            $object = @unserialize(base64_decode($data));    
        else
            $object = @unserialize($data);
        
        
        
        if (!$object ||  $object instanceof __PHP_Incomplete_Class)
        {

            $print_r_str = print_r($object,true);
            $split = explode("\n",$print_r_str);
            if (count($split) < 3) return new ErrorMessage(__METHOD__,__LINE__,"Failed to load appropriate class for object ".print_r($object,true));

            $bits = explode("=>",$split[2]);
            if (count($split) < 2) return new ErrorMessage(__METHOD__,__LINE__,"Failed to load appropriate class for object ".print_r($object,true));

            $className = trim($bits[1]);

            $class = self::Find($className);

            if ($class instanceof ErrorMessage) return $class;

            // the Class should be loaded and available

            if($decode_using_base64)
                $object = @unserialize(base64_decode($data)); // try to unserialize again
            else
                $object = @unserialize($data); // try to unserialize again
            
        }

        
        return $object;
        
    }
    
    

}

?>
