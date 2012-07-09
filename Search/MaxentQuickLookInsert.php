<?php
include_once 'includes.php';
/*
 * Take command line args of a path to the ASC file we want to create quiuck look for and then insert that file into db
 * with appropriate model scenario time and species data
 * 
 * - needs to be command line as we are going to run this after generationm on the HPC nodes
 * 
 */



$filename = '/home/jc166922/test/RCP3PD_gfdl-cm20_2015.asc';


$db = new PGDB();

$file_id = $db->InsertFile(SpeciesMaxent::CreateQuickLookImage($filename), "fred_RCP3PD_gfdl-cm20_2015", "QuickLook");

$temp_file = $db->ReadFile2Filesystem($file_id);



$db->RemoveFile($file_id);

unset($db);

exec("display {$temp_file} &");



?>
