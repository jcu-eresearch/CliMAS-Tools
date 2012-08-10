<?php
include_once dirname(__FILE__).'/includes.php';

$prog = array_util::Value( $argv, 0);
$species_id = array_util::Value($argv, 1);

if (is_null($species_id)) usage($prog);

function usage($prog)
{
   echo "usage: {$prog} species_id \n" ;
   exit(1);
}

$result = SpeciesData::ScenarioTimeMediansForSpecies($species_id);

if($result instanceof ErrorMessage)
{
    echo $result;
    exit(1);
}


?>
