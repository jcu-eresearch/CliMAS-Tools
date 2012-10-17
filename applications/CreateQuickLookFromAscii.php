<?php
include_once dirname(__FILE__).'/includes.php';

$prog = array_util::Value( $argv, 0);
$species_id = array_util::Value($argv, 1);
$filename = array_util::Value($argv, 2);

if (is_null($species_id)) usage($prog);
if (is_null($filename)) usage($prog);

function usage($prog)
{
   echo "usage: {$prog} species_id filename\n" ;
   exit(1);
}

$qlfn = SpeciesMaxentQuickLook::CreateImage($species_id, $filename); // build quick look from asc 
if ($qlfn instanceof ErrorMessage)
{
    echo $qlfn;
    exit(1);
}
?>