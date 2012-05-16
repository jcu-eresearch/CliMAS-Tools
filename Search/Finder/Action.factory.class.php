<?php
/**
 * Description of ActionFactory
 *
 * Actions belong to Finders
 *
 * Finding a action will invlove looking for class that are in a the a folder
 *
 * Finder/(FinderName)/(ActionName)
 *
 * eg. Finder/Species/Names   --  THis would ba  class to find names of spceices ClassName::  ActionSpeciesNames
 *
 * @author jc166922
 */
class ActionFactory {




    // find all actions for this Finder
    // but don't load them just look at their names
    // return array[ClassName] = Simple Name
    public static function Available($owner)
    {
        if ($owner instanceof aFinder ) return self::AvailableFromClass($owner);

        if (is_string($owner)) return self::AvailableFromString ($owner);

        return null;
    }

    private static function AvailableFromString($owner)
    {
        $F  = FinderFactory::Find($owner);

        $actions = self::AvailableFromClass($F);

        unset($F);

        return $actions;

    }


    private static function AvailableFromClass(aFinder $owner) {

        // ??? if null return all actions for all finders

        // simple name fore this Finder (remove "Finder")
        $finders_simple_name = str_replace("Finder","",get_class($owner));

        // something like  Finder/Species/Taxa
        $actions_folder = file::currentScriptFolder(__FILE__).configuration::osPathDelimiter().$finders_simple_name;

        if (!is_dir($actions_folder))
        {
            // no actions
            echo "<br>NO actions for {$owner->Name()}<br>";
            return null;
        }

        $action_class_files = file::arrayFilter(file::ClassFiles($actions_folder), ".action.class.php");

        $action_class_names = array_values(file::filenameOnly($action_class_files));
        $action_class_names = array_util::Replace($action_class_names, ".action.class", "");


        // turn class names into the keys and then strip out the $owner name and make it the cvalue
        $result = array();
        foreach ($action_class_names as $action_class_name)
            $result[$finders_simple_name."-".$action_class_name] = $finders_simple_name.$action_class_name;


        return $result;



    }



    public static function Find(aFinder $owner, $action_name = null)
    {
        if (is_null($action_name)) $action_name = $owner->DefaultAction();
        if (strtolower($action_name) == "default") $action_name = $owner->DefaultAction();


        // construct path to action class
        $finders_simple_name = str_replace("Finder","",get_class($owner));

        // something like  Finder/Species/Taxa
        $actions_folder = file::currentScriptFolder(__FILE__).configuration::osPathDelimiter().$finders_simple_name;

        $action_class_filename = $actions_folder.configuration::osPathDelimiter().$action_name.".action.class.php";

        // echo "action_class_filename = {$action_class_filename}<br>";

        if (!file_exists($action_class_filename))
        {
            //TODO;: logg that we had to fall back to a default action

            echo "Default fall back from ({$action_name})   Can't find class file {$action_class_filename} for {$finders_simple_name}/{$action_name}<br>";

            $action_name = $owner->DefaultAction();
            // return null;
            $action_class_filename = $actions_folder.configuration::osPathDelimiter().$action_name.".action.class.php";
        }


        include_once $action_class_filename;


        $action_class_name = $finders_simple_name.$action_name;

        // echo "action_class_name = {$action_class_name}<br>";

        if (!class_exists($action_class_name))  // check to see if we includes it properly
        {
            echo "Trying to get action class {$action_class_name} does not exist<br>";
            // TODO:: Exception or ??

            return null;   // Return Null
        }

        $result = new $action_class_name;
        $result instanceof iAction;

        return $result;

    }

    public static function Execute(aFinder $owner, $action_name = null)
    {

        $A = self::FinderAction($owner, $action_name);
        return $A->Execute();

    }


}

?>






