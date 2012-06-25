<?php

class SpeciesAllValues extends Action {

    public function __construct() {
        parent::__construct();
        $this->ActionName(__CLASS__);
        $this->Description("All Species");
        $this->FinderName("SpeciesFinder");

    }


    public function __destruct() {
        parent::__destruct();
    }

    public function Execute()
    {

//        $descs = new Descriptions();
//
//        
//        
//        $desc = self::DescriptionForSpecies($key);
//        $desc->Filename($folder);
//        $descs->Add($desc);
        
        $descs = new Descriptions();
        $descs instanceof Descriptions;
        
        
        $sp = new SpeciesData();
        $speciesSearchResult = $sp->speciesList("Mangrove");
        unset($sp);
        
        
        
        foreach ($speciesSearchResult as $index => $row) 
        {
            $desc = new Description();
            $desc->DataName(urlencode($row['scientific_name']));
            $desc->Description($row['common_name']." ({$row['scientific_name']})");
            
            $desc->MoreInformation($row['common_name']." ({$row['scientific_name']})");
            $desc->URI(ToolsDataConfiguration::ALAFullTextSearch().urlencode($row['scientific_name']) );

            $descs->Add($desc);
            
        }
        


        $this->Result($descs);
        return $descs;
    }

    private function checkLocal()
    {
        // read database here



    }


}

?>
