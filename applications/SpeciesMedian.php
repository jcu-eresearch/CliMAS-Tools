<?php
include_once dirname(__FILE__).'/includes.php';

$prog = array_util::Value( $argv, 0);
$species_id = array_util::Value($argv, 1);


if (is_null($species_id)) usage($prog);

function usage($prog)
{
   echo "usage: {$prog} species_id [--time=yyyy] [--scenario=name] [--LoadASCII=true]  [LoadQuickLooks=true] \n" ;
   exit(1);
}

$scenario = util::CommandScriptsFoldermandLineOptionValue($argv, "scenario");
$time = util::CommandScriptsFoldermandLineOptionValue($argv, "time");

$LoadASCII = util::CommandScriptsFoldermandLineOptionValue($argv, "LoadASCII",true);
$LoadQuickLooks = util::CommandScriptsFoldermandLineOptionValue($argv, "LoadQuickLooks",true);

ErrorMessage::Marker("Create Median for species {$species_id} {$scenario} {$time}\n");

$result = SpeciesData::ScenarioTimeMediansForSpecies($species_id,$scenario,$time,$LoadASCII,$LoadQuickLooks);
if ($result instanceof ErrorMessage) 
{
    ErrorMessage::Stacked (__FILE__,__LINE__,"Error Creating medians for {$species_id}", true,$result);
    exit(1);
}
?>