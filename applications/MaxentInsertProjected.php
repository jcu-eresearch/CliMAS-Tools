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


$file_id = DatabaseMaxent::InsertSingleMaxentProjectedFile(
             $species_id
            ,$ascii_filename
            ,'ASCII_GRID'
            ,'Spatial data of projected species suitability:'.basename($ascii_filename)
            );

if ($file_id instanceof ErrorMessage)  
    return ErrorMessage::Stacked (__FILE__,__LINE__,"Trying to insert ASCII file [{$ascii_filename}]  species_id = $species_id ", true,$file_id);

    
$qlfn = SpeciesMaxentQuickLook::CreateImage($species_id,$ascii_filename);
if ($qlfn instanceof ErrorMessage) 
    return ErrorMessage::Stacked (__FILE__,__LINE__,"Failed to Create Quick Look from ASCII Grid File {$ascii_filename}  \nspecies_id = $species_id\n", true,$qlfn);


$file_id = DatabaseMaxent::InsertSingleMaxentProjectedFile(
             $species_id
            ,$qlfn
            ,'QUICK_LOOK'
            ,'Quick look image of projected species suitability:'.basename($qlfn)
            );


if ($file_id instanceof ErrorMessage)  
    return ErrorMessage::Stacked (__FILE__,__LINE__,"Failed to Insert Single Maxent Projected Quick Look File {$qlfn}  \nspecies_id = $species_id\n", true,$file_id);


//file::Delete($qlfn);
?>