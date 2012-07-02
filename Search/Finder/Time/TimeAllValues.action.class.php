<?php

class TimeAllValues extends Action {

    public function __construct() {
        parent::__construct();
        $this->ActionName(__CLASS__);
        $this->Description("All available values for Time");
        $this->FinderName('TimeFinder');
    }


    public function __destruct() {
        parent::__destruct();
    }

    public function Execute()
    {
        $result = ToolsData::Times();
        $this->Result($result);
        return $result;
    }

}

?>
