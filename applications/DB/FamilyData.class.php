<?php
/**
 * Description of FamilyData
 *
 *
 */
class FamilyData extends Object
{

    public static function GetList($class)
    {
        $families = array();

        $datapath = configuration::SourceDataFolder() . 'Taxa' .
                configuration::osPathDelimiter() . $class
                ;

        if (!file::reallyExists($datapath)) return $families; // bail if no data

        $family_paths = file::folder_folders($datapath, null, true);

        $families = array_keys($family_paths);

        return $families;
    }



}

?>


