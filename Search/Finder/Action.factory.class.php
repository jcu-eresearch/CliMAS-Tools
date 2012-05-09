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


    // find all actions for this Class
    public static function Actions(aFinder $owner)
    {

        // ??? if null return all actions for all finders

        $simple_class_name = str_replace("Finder","",get_class($owner));
        $actions_folder = file::currentScriptFolder(__FILE__).configuration::osPathDelimiter().$simple_class_name;
        
        if (!is_dir($actions_folder))
        {
            // no actions
            echo "<br>NO actions for {$owner->Name()}<br>";
            return null;
        }

        $action_class_files = file::arrayFilter(file::arrayFilter(file::ClassFiles($actions_folder), ".action.class.php"),$simple_class_name );
        
        // must contain


        print_r($action_class_files);


    }


}

?>






