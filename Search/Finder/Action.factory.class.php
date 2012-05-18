<?php
/**
 *
 * Actions belong to Finders
 *
 * Finding an action invloves looking for Finder Classes and the associated FOlder and looking for actions Class files
 *
 * The format of a Action class name is ... Finder/(ActionName)
 *
 * eg. SpeciesNames   --  Species finder "parent"  with an action of Names
 *
 * @author Adam Fakes (James Cook University)
 */
class ActionFactory {

    /**
     *
     * find all actions for this Finder but don't load them just look at their names
     *
     * @param Finder $owner . 
     * @return null|\array ([ClassName] => SimpleName)
     */
    public static function Available($owner)
    {
        if ($owner instanceof aFinder ) return self::AvailableFromClass($owner);

        if (is_string($owner)) return self::AvailableFromString ($owner);

        return null;
    }

    /**
     * @param string $owner
     * @return array
     */
    private static function AvailableFromString($owner)
    {
        $F  = FinderFactory::Find($owner);
        // TODO:: check $F to be a Finder

        $actions = self::AvailableFromClass($F);

        unset($F);

        return $actions;

    }


    /**
     * list of Actions availe using and instantiated object
     * @param aFinder $owner
     * @return null|array Available actions for $owner
     */
    private static function AvailableFromClass(aFinder $owner) {

        //** simple name fore this Finder (remove "Finder")
        $finders_simple_name = str_replace(FindersConfiguration::$CLASS_NAME_SUFFIX_FINDER,"",get_class($owner));

        // something like  Species/Taxa
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
            $result[$action_class_name] = $finders_simple_name.$action_class_name;

        return $result;

    }



    /**
     *
     * @param aFinder $owner
     * @param type $action_name
     * @return null|\iAction
     */
    public static function Find(aFinder $owner, $action_name = null)
    {
        if (is_null($action_name)) $action_name = $owner->DefaultAction();
        if (strtolower($action_name) == "default") $action_name = $owner->DefaultAction();


        //** construct path to action class
        $finders_simple_name = str_replace("Finder","",get_class($owner));

        //** something like  Finder//**Taxa
        $actions_folder = file::currentScriptFolder(__FILE__).configuration::osPathDelimiter().$finders_simple_name;

        $action_class_filename = $actions_folder.configuration::osPathDelimiter().$action_name.".action.class.php";

        //** echo "action_class_filename = {$action_class_filename}<br>";

        if (!file_exists($action_class_filename))
        {
            //**TODO;: logg that we had to fall back to a default action

            echo "Default fall back from ({$action_name})   Can't find class file {$action_class_filename} for {$finders_simple_name}/**{$action_name}<br>";

            $action_name = $owner->DefaultAction();
            //** return null;
            $action_class_filename = $actions_folder.configuration::osPathDelimiter().$action_name.".action.class.php";
        }


        include_once $action_class_filename;

        $action_class_name = $finders_simple_name.$action_name;

        if (!class_exists($action_class_name))  //** check to see if we includes it properly
        {
            echo "Trying to get action class {$action_class_name} does not exist<br>";
            //TODO:: Exception or / Log
            return null; 
        }

        $result = new $action_class_name();
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






