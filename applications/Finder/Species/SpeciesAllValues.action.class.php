<?php

/**
 * @package Search\Species\SpeciesAllValues
 * 
 * Read database and return Descriptions of all Species
 *  
 */
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
        
        $descs = new Descriptions();
        $descs instanceof Descriptions;
        
        $speciesSearchResult = SpeciesData::SpeciesList("");
        
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



}

?>
