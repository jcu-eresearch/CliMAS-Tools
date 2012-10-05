<?php
include_once dirname(__FILE__).'/includes.php';


ErrorMessage::Marker("ALA Species and Taxa data");


$sdf = configuration::SourceDataFolder();

$species_list_file = "{$sdf}species_list.txt";
file::Delete($species_list_file);

$species_data_folder = "{$sdf}species_data/";
file::mkdir_safe("$species_data_folder");

ErrorMessage::Marker("Remove Species Data");
exec("rm -r -f {$species_data_folder}/*");


$clazz_list_filename = "{$sdf}clazz_list.txt";
file::Delete($clazz_list_filename);

$family_list_filename = "{$sdf}family_list.txt";
file::Delete($family_list_filename);

$genus_list_filename = "{$sdf}genus_list.txt";
file::Delete($genus_list_filename);

$error_list = "{$sdf}errors.txt";


$species_list = array();

exec("ls -1 /scratch/jc148322/AP02/amphibians/models/",$species_list );
exec("ls -1 /scratch/jc148322/AP02/mammals/models/"   ,$species_list );
exec("ls -1 /scratch/jc148322/AP02/reptiles/models/",  $species_list );

file_put_contents($species_list_file, implode("\n", $species_list)."\n");
ErrorMessage::Marker("Species List written to {$species_list_file}");


$clazz = array();
$genus = array();
$family = array();
$species = array();

$count = 1;

foreach ($species_list as $species) 
{
    ErrorMessage::Marker("Process ... $species  {$count} / " . count($species_list) );
    
    $d = SpeciesData::ALASpeciesTaxa(str_replace("_", " ", $species));    
    if (is_null($d)) 
    {
        ErrorMessage::Marker("\n### ERROR $species NOT FOUND\n");
        file_put_contents($error_list,$species, 0, FILE_APPEND);
        continue;
    }
    
    $species_folder_name = str_replace(' ', '_', $d['species']);
    
    $folder = "{$species_data_folder}{$d['clazz']}/{$d['family']}/{$d['genus']}/{$species_folder_name}/";
    
    $folder = str_replace(" ", "_", $folder);
    
    file::mkdir_safe("{$folder}");

    ErrorMessage::Marker("Create folder {$folder}");
    
    $EXTRA_CHARS = '@##$%^&*()_+={}[]\|:";\'\\<>,.?/`~';
    
    
    $d['clazz']   = util::CleanStr($d['clazz']  ,NULL,$EXTRA_CHARS,"");
    $d['family']  = util::CleanStr($d['family'] ,NULL,$EXTRA_CHARS,"");
    $d['genus']   = util::CleanStr($d['genus']  ,NULL,$EXTRA_CHARS,"");
    $d['species'] = util::CleanStr($d['species'],NULL,$EXTRA_CHARS,"");
    $d['common_names'] = array_util::CleanStrings($d['common_names'], $EXTRA_CHARS, true, '');
    
    $d['common_names'] = array_util::Trim($d['common_names']);
    

    
    
    
    file_put_contents("{$species_data_folder}{$d['clazz']}/clazz_guid.txt", $d['clazz_guid']."\n");

    file_put_contents("{$species_data_folder}{$d['clazz']}/{$d['family']}/family_guid.txt", $d['family_guid']."\n");

    file_put_contents("{$species_data_folder}{$d['clazz']}/{$d['family']}/{$d['genus']}/genus_guid.txt", $d['genus_guid']."\n");
     
    file_put_contents("{$species_data_folder}{$d['clazz']}/{$d['family']}/{$d['genus']}/{$species_folder_name}/species_guid.txt", $d['species_guid']."\n");

    file_put_contents("{$species_data_folder}{$d['clazz']}/{$d['family']}/{$d['genus']}/{$species_folder_name}/scientific_name.txt", $d['species']."\n");
    
    
    // species commmon names
     
    if (is_array($d['common_names']))
    {
        file_put_contents("{$species_data_folder}{$d['clazz']}/{$d['family']}/{$d['genus']}/{$species_folder_name}/common_names.txt", implode("\n",$d['common_names'])."\n");
    }
    
    
    $clazz[$d['clazz']] = $d['clazz'];
    $genus[$d['genus']] = $d['genus'];
    $family[$d['family']] = $d['family'];
    
    
    $count++;
    
}

file_put_contents($clazz_list_filename,implode("\n",$clazz)."\n");
file_put_contents($family_list_filename,implode("\n",$family)."\n");
file_put_contents($genus_list_filename,implode("\n",$genus)."\n");


?>
