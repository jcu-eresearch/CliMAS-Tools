<?php
include_once 'includes.php';
/*
 * Take command line args of a path to the ASC file we want to create quiuck look for and then insert that file into db
 * with appropriate model scenario time and species data
 * 
 * - needs to be command line as we are going to run this after generationm on the HPC nodes
 * 
 */
$prog = array_util::Value( $argv, 0);
$species_id = array_util::Value( $argv, 1);
$ascii_filename = array_util::Value($argv, 2);

if (is_null($species_id)) usage($prog);
if (is_null($ascii_filename)) usage($prog);

if (!file_exists($ascii_filename))
{
    echo "{$prog}:: File not found: $ascii_filename\n";
    exit(1);
}

function usage($prog)
{
   echo "usage: {$prog} species MaxentOutputFilename\n" ;
   exit(1);
}

// MAIN
// --------------------------------------------------------------

$db = new PGDB();

$file_id = 
    $db->InsertSingleMaxentProjectedFile(
             $species_id
            ,$ascii_filename
            ,'ASCII_GRID'
            ,'Spatial data of projected species suitability:'.basename($ascii_filename)
            );


$qlfn = SpeciesMaxent::CreateQuickLookImage($species_id,$ascii_filename);

$file_id = 
    $db->InsertSingleMaxentProjectedFile(
             $species_id
            ,$qlfn
            ,'QUICK_LOOK'
            ,'Quick look image of projected species suitability:'.basename($qlfn)
            );

unset($db);

file::Delete($qlfn);
?>