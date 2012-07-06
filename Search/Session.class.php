<?php
class Session {
    
    private static $ActionsToBeMapped = "ActionsToBeMapped";

    /**
     * @param
     * @return type
     */
    public static function MapableResults()
    {

        $actionsToMap = self::get(self::$ActionsToBeMapped);
        if (count($actionsToMap) == 0)
        {
            self::AddMapableAction(configuration::DefaultMapableActionClassname());
            $actionsToMap = self::get(self::$ActionsToBeMapped);
        }

        $result = array();
        foreach ($actionsToMap as $actionToMap)
            $result[$actionToMap] = FinderFactory::Result($actionToMap);

        
        return $result;
    }

    /**
    * @param
    * @return type
    */
    public static function PostableFinderActionNames()
    {
        $toPost = self::get(self::$ActionsToBeMapped);

        if (is_null($toPost))
            return configuration::DefaultMapableActionClassname();

        return join(",",$toPost);
        
    }


    /**
     * @param
     * @return type
     */
    public static function ClearLayers()
    {
        $_SESSION[self::SessionName()][self::$ActionsToBeMapped] = array();
    }


    /**
     *
     * @param type $post_field
     * @return type
     */
    public static function UpdateFromPostedFinderActionNames($post_field)
    {

        $postedActionClassNames = array_util::Value($_POST, $post_field, null);
        if (is_null($postedActionClassNames)) return;

        self::ClearLayers(); // clear layers and only add the ones we are posting in this time

        // if we have layers posted to us then we need to add them to the session
        foreach (explode(",",$postedActionClassNames) as $postedActionClassName)
            self::AddMapableAction($postedActionClassName);

    }



    /**
     *
     * @param type $actionClassname
     * @return type
     */
    public static function AddMapableAction($actionClassname)
    {
        $actionClassname = trim($actionClassname);
        if ($actionClassname == "") return;

        $current = self::get(self::$ActionsToBeMapped);
        $current[$actionClassname] = $actionClassname;

        self::add(self::$ActionsToBeMapped, $current);

    }

    /**
     *
     * @return type
     */
    public static function SessionName()
    {
        return FindersConfiguration::$SESSION_NAME;
    }

    /**
     *
     * @return array
     */
    public static function AppSession()
    {
        if (!array_key_exists(self::SessionName(), $_SESSION))
                $_SESSION[self::SessionName()] = array();

        return $_SESSION[self::SessionName()];
    }

    /**
     *
     * @param type $key
     * @param type $default
     * @return type 
     */
    public static function get($key,$default = null)
    {
        if (!isset($_SESSION)) return null;
        
        
        if (!self::has($key)) return $default;

        $session_data = self::AppSession();

        return $session_data[$key];
    }


    public static function SessionVariablesForApplication()
    {
        return self::AppSession();
    }

    /**
     *
     * @param type $key
     * @param type $value
     */
    public static function addActionIds($actionName,$idsString)
    {
        if (is_null($actionName)) return null;
        if (is_null($idsString)) return null;

        self::add($actionName,$idsString);
    }

    /**
     *
     *
     * @param string $actionName
     * @return string SPace seperated list of id's that have been selected for this action
     */
    public static function getActionIds($actionName)
    {
        if (is_null($actionName)) return null;
        return self::get($actionName);
    }

    /**
     *
     * @param type $key
     * @param type $value
     */
    public static function add($key,$value)
    {
        $_SESSION[self::SessionName()][$key] = $value;
    }

    /**
     *
     * @param type $key
     */
    public static function remove($key)
    {
        if (self::has($key))
            unset($_SESSION[self::SessionName()][$key]);
    }

    /**
     *
     */
    public static function clear()
    {
        $_SESSION[self::SessionName()] = array();
    }


    /**
     *
     * @param type $key
     * @return type
     */
    public static function has($key)
    {
        $session_data = self::AppSession();
        return array_key_exists($key, $session_data);
    }



}
?>