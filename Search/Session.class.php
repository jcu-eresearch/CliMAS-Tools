<?php

class Session {
    
    private static $LAYERS = "LAYERS";

    public static function SessionName()
    {
        return FindersConfiguration::$SESSION_NAME;
    }

    public static function AppSession()
    {
        if (!array_key_exists(self::SessionName(), $_SESSION))
                $_SESSION[self::SessionName()] = array();

        return $_SESSION[self::SessionName()];
    }

    public static function get($key,$default = null)
    {
        if (!self::has($key)) return $default;

        $session_data = self::AppSession();

        return $session_data[$key];
    }

    public static function add($key,$value)
    {
        $_SESSION[self::SessionName()][$key] = $value;
    }

    public static function remove($key)
    {
        if (self::has($key))
            unset($_SESSION[self::SessionName()][$key]);
    }

    public static function clear()
    {
        $_SESSION[self::SessionName()] = array();
    }


    public static function has($key)
    {
        $session_data = self::AppSession();
        return array_key_exists($key, $session_data);
    }

    public static function PostableFinderActionNames()
    {

        $result = array();
        foreach (self::LayerFinderNames() as $index => $finderActionRow)
            $result[] = $finderActionRow[FindersConfiguration::$CLASS_NAME_SUFFIX_FINDER]."-".$finderActionRow[FindersConfiguration::$CLASS_NAME_SUFFIX_ACTION];

        return join(",",$result);
    }


    public static function ClearLayers()
    {
        $_SESSION[self::SessionName()][self::$LAYERS] = array();
    }



    public static function UpdateFromPostedFinderActionNames($post_field)
    {
        $current_posted_layers = array_util::Value($_POST, $post_field, null);
        if (is_null($current_posted_layers)) return;

        // clear layers and only add the ones we are posting in this time


        self::ClearLayers();

        // if we have layers posted to us then we need to add them to the session
        foreach (explode(",",$current_posted_layers) as $current_posted_layer)
        {
            if (trim($current_posted_layer) == "") continue;

            list($finderName,$actionName) = explode("-",$current_posted_layer);
            self::AddLayerFinderActionName($finderName, $actionName);
        }

    }


    public static function AddLayerFinderActionName($finder_name, $action_name)
    {

        $current_layers = self::get(self::$LAYERS);

        $add_layer = array();
        $add_layer[FindersConfiguration::$CLASS_NAME_SUFFIX_FINDER] = $finder_name;
        $add_layer[FindersConfiguration::$CLASS_NAME_SUFFIX_ACTION] = $action_name;

        $current_layers[] = $add_layer;

        self::add(self::$LAYERS, $current_layers);

    }


    /*
     * RETURN::  layers currently defined /  requested
     *
     * if there are no layers then return default context layer
     *
     */
    public static function LayerFinderNames()
    {
        $current_layers = self::get(self::$LAYERS);

        // If there are no layers then add the Deafult Context Layer
        // this would usually be the first timr thscreen opens
        if (is_null($current_layers))
        {
            $current_layers = array();

            $default_layer = array();
            $default_layer[FindersConfiguration::$CLASS_NAME_SUFFIX_FINDER] = configuration::DefaultLayerFinderName();
            $default_layer[FindersConfiguration::$CLASS_NAME_SUFFIX_ACTION] = configuration::DefaultLayerFinderActionName();

            $current_layers[] = $default_layer;

            self::add(self::$LAYERS, $current_layers);
        }

        //print_r($current_layers);

        return $current_layers;
    }


    public static function LayerFinderActionResults()
    {

        $result = array();
        foreach (self::LayerFinderNames() as $key => $layerDesc)
        {
            $result[$key] = FinderFactory::Result($layerDesc[FindersConfiguration::$CLASS_NAME_SUFFIX_FINDER], $layerDesc[FindersConfiguration::$CLASS_NAME_SUFFIX_ACTION]);
        }

        return $result;
    }


}



?>
