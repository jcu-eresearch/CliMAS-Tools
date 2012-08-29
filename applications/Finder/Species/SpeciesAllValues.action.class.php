<?php //

///**
// * @package Search\Species\SpeciesAllValues
// * 
// * Read database and return Descriptions of all Species
// *  
// */
//class SpeciesAllValues extends Action {
//
//    public function __construct() {
//        parent::__construct();
//        $this->ActionName(__CLASS__);
//        $this->Description("All Species");
//        $this->FinderName("SpeciesFinder");
//
//    }
//
//
//    public function __destruct() {
//        parent::__destruct();
//    }
//
//    public function Execute()
//    {
//        
//        $descs = new Descriptions();
//        $descs instanceof Descriptions;
//        
//        $speciesSearchResult = SpeciesFiles::SpeciesList("");
//        
//        foreach ($speciesSearchResult as $index => $row) 
//        {
//            
//            $species_id = $row['species_id'];
//            $info = SpeciesData::SpeciesQuickInformation($species_id);
//            if (! ($info instanceof ErrorMessage))
//            {
//                $desc = new Description();
//                $desc->DataName(urlencode($row['scientific_name']));
//                $desc->Description($info);
//                $desc->MoreInformation('');
//                $desc->URI(ToolsDataConfiguration::ALAFullTextSearch().urlencode($row['scientific_name']) );
//
//                $descs->Add($desc);                
//            }
//        }
//
//
//        $this->Result($descs);
//        return $descs;
//    }
//
//
//
//}

?>
