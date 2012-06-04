<?php
/**
 *
 * Return lists of Species names and their associate ranges, thresholds, and subsets
 *
 */
class SpeciesFinder extends Finder  {

    public function __construct() {
        parent::__construct($this);
        $this->FinderName(__CLASS__);
        $this->DefaultAction("Search");
    }

    public function __destruct() {
        parent::__destruct();

    }

    public function Description()
    {
        return "Species";
    }


    /**
     * Gather selected Species id's user has selected
     * Unique string of Species ID's slected From various sources
     *
     * @return string space delimed string of ID's (may contain "Special ID's - (ALL) ...")
     */
    public function SelectedSpeciesIDs()
    {

        $speciesSearch = FinderFactory::Find("SpeciesSearch");
        $speciesSearch instanceof SpeciesSearch;
        $actions = $speciesSearch->Subsets();
        $actions instanceof Actions;

        // get species ID's that have been selected - froom the various Actions that user has been given
        $speciesIDsString = "";
        foreach ($actions->ActionNames() as $actionName)
        {
            $speciesIdForAction = Session::get($actionName,"");
            if ($speciesIdForAction != "")
                $speciesIDsString .= " ". Session::get($actionName,"");
        }

        $speciesIDsString = trim($speciesIDsString);

        if ($speciesIDsString == "") return null;

        $result = array();
        $speciesIDs = array_unique(explode(" ", $speciesIDsString));

        foreach ($speciesIDs as $value)
        {
            $value = trim($value);
            if ($value != "") $result[$value] = $value;
        }

        return join(" ",array_values($result)) ;

    }



}

?>
