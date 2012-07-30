<?php
include_once 'includes.php';
/*
 * Take command line args of Species ID
 * insert all maxent results into database
 * 
 * - needs to be command line as we are going to run this after generationm on the HPC nodes
 * 
 */
$prog = array_util::Value( $argv, 0);
$species_id = array_util::Value( $argv, 1);
if (is_null($species_id)) usage($prog);
function usage($prog)
{
   echo "usage: {$prog} species \n" ;
   exit(1);
}
// MAIN
// --------------------------------------------------------------
DatabaseMaxent::InsertAllMaxentResults($species_id);

?>        