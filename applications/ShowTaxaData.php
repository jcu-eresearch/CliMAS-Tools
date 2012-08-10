<?php
include_once dirname(__FILE__).'/includes.php';


//print_r(SpeciesData::Kingdom()); 
//print_r(SpeciesData::Phylum() );
//print_r(SpeciesData::Clazz()  );
//print_r(SpeciesData::Orderz() );
//print_r(SpeciesData::Family() );
//print_r(SpeciesData::Genus()  );
//print_r(SpeciesData::Species()); 


//$t = SpeciesData::SpeciesForGenus('Ocyphaps');
//$t = SpeciesData::SpeciesForFamily('Sylviidae'); 
//$t = SpeciesData::SpeciesForKingdom('ANIMALIA'); 

//$t = SpeciesData::GenusForKingdom('ANIMALIA'); 
//$t = SpeciesData::GenusForFamily('Sylviidae'); 

//$t = SpeciesData::SpeciesIDsForGenus('Ocyphaps');


//$t = SpeciesData::FamilyForKingdom('ANIMALIA'); 


$t = SpeciesData::SpeciesWithOccuranceData();
print_r($t);
?>
