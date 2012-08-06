<?php
include_once dirname(__FILE__).'/includes.php';

$S = FinderFactory::Find('SpeciesHotSpots');

$id = array_util::Value($argv, 1);

if (is_null($id))
{
    
    DBO::DeleteAll('command_action');
    $S = FinderFactory::Find('SpeciesHotSpots');
    $S instanceof SpeciesHotSpots;

    $S->inputType('species');
    $S->inputName('Pacific Black Duck (Anas (Anas) superciliosa)');
    $S->inputID(50);
    $S->models(explode(",","ccsr-miroc32hi,ccsr-miroc32med"));
    $S->scenarios(explode(",","RCP3PD,RCP45,RCP6,RCP85"));
    $S->times(explode(",","2015,2025,2035,2045,2055,2065,2075,2085"));
    $S->bioclims(explode(",","1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19"));

    $S->initialise();
    
    $queue_result = DatabaseCommands::CommandActionQueue($S);
    
    $id = $S->ID();
}

$cmd = DatabaseCommands::CommandActionRead($id);

print_r($cmd);

?>
