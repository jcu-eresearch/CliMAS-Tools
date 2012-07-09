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
$species = array_util::Value( $argv, 1);
$filename = array_util::Value($argv, 2);

if (is_null($species)) usage($prog);
if (is_null($filename)) usage($prog);

if (!file_exists($filename))
{
    echo "{$prog}:: File not found: $filename\n";
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

$quicklook_image_filename = SpeciesMaxent::CreateQuickLookImage($filename);


$file_id = $db->InsertFile($quicklook_image_filename, $species."_".str_replace('.asc','',basename($filename)), "QuickLook");

unset($db);

if (is_null($file_id)) 
    echo "{$prog}: FAILED to insert file from grid file: {$filename}\n";
    

file::Delete($quicklook_image_filename);

echo "{$prog}:{$file_id}\n";

list($scenario, $model, $time) =  explode("_",str_replace('.asc','',basename($filename)));    
SpeciesMaxent::InsertModelledData($species,$scenario, $model, $time,$file_id);

//echo "{$prog}: insert_result {$insert_result}\n";


?>
