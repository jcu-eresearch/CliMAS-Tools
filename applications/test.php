<?php
include_once dirname(__FILE__).'/includes.php';


//$t = SpeciesFiles::speciesList();
//print_r($t);


//$t = SpeciesFiles::GetAllModelledData(50);
//print_r($t);


$S = FinderFactory::Find("SpeciesComputed");
$S instanceof SpeciesComputed;

$S->SpeciesID(50);
$R = $S->Execute();

print_r($R );


?>


