<?php

class SpeciesSearch extends Action implements iAction {

    public function __construct() {
        parent::__construct();
        $this->ActionName(__CLASS__);
        $this->FinderName("SpeciesFinder");
        $this->Description("Species");
    }


    public function __destruct() {
        parent::__destruct();
    }

    public function Execute()
    {

        $result = "";
        $actions = $this->Subsets();

        $selectedIds = "";
        foreach ($actions->ActionNames() as $actionName)
        {
            $selectedForAction = Session::getActionIds($actionName);
            if (!is_null($selectedForAction))
            {
                $selectedForAction = trim($selectedForAction);
                if ($selectedForAction != "")
                    $selectedIds .= " ".$selectedForAction;
            }
        }


        $descs = new Descriptions();

        // for all selected IDS create "description"
        foreach ( array_unique(explode(" ",trim($selectedIds))) as $selectedID)
        {
            $selectedID = trim($selectedID);

            if ($selectedID !="")
            {
                $desc = ToolsData::DescriptionForSpecies($selectedID);
                $descs->Add($desc);
            }

        }

        $this->Result($descs);
        return $result;
    }

    /**
     *
     * @return array ..  [SubsetName] => "ActionclassName"
     *
     * e.g.
     *                   [All]        => "SpeciesAllNames"
     *                   [By Taxa]    => "SpeciesByTaxa"
     *
     *
     */
    public function Subsets() {

        $result = array();

        $result[] = "SpeciesAllValues";
        $result[] = "SpeciesComputed";
        $result[] = "SpeciesTaxanomicNames";
        $result[] = "SpeciesSingle";

        $actions = new Actions();
        $actions->FromArray($result);

        return $actions;

    }

}

?>
