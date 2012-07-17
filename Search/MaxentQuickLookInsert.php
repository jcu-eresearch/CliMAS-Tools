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
$filename = array_util::Value($argv, 2);

if (is_null($species_id)) usage($prog);
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

$quicklook_image_filename = SpeciesMaxent::CreateQuickLookImage($species_id,$filename);

$desc = "QuickLook_".$species_id."_".str_replace('.asc','',basename($filename));
list($scenario, $model, $time) =  explode("_",str_replace('.asc','',basename($filename)));  // maxent filename format  scenario_model_time    RCP3PD_cccma-cgcm31_2085.asc

$file_id = $db->InsertSingleMaxentOutput($species_id,$quicklook_image_filename,$desc,$scenario, $model, $time);

if (is_null($file_id)) 
    echo "{$prog}: FAILED to insert QuickLook file from grid file: {$filename}\n";
    
$db->RemoveSingleMaxentOutput($species_id,$scenario, $model, $time );  // if we have made it here then we have run the model and projections so really need to remove old and insert new
    
$desc = "{$scenario}_{$model}_{$time}";
$file_id = $db->InsertSingleMaxentModelledOutput($species_id,$desc,$scenario, $model, $time);

if (is_null($file_id)) 
    echo "{$prog}: FAILED to insert Ascii Grid file: $species_id $desc $scenario $model $time\n";


unset($db);
file::Delete($quicklook_image_filename);
?>