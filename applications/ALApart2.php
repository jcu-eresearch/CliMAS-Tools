<?php
include_once dirname(__FILE__).'/includes.php';


ErrorMessage::Marker("Create Common Name / Species Name List");

$sdf = configuration::SourceDataFolder();


$source_folder_translation = array();

$source_folder_translation['AMPHIBIA'] = 'amphibians';
$source_folder_translation['MAMMALIA'] = 'mammals';
$source_folder_translation['REPTILIA'] = 'reptiles';



$species_common_name_list_file = "{$sdf}species_common_name_list.txt";
file::Delete($species_common_name_list_file);

$species_common_name_list = array();
exec("find {$sdf} | grep 'common_names.txt'",$species_common_name_list);

$common_names_species_names = array();

$index = 0;

foreach ($species_common_name_list as $path_to_common_names_file) 
{
    
    $path_to_common_names_file  = trim($path_to_common_names_file);
    if ($path_to_common_names_file  == "" ) continue;
    
    
    // take each common name file and create a single list of common name to species name.

    $species_folder_name = util::fromLastSlash(str_replace('/common_names.txt', '', $path_to_common_names_file));
    
    list($clazz,$family,$genus,$species) = explode("/",  str_replace('/common_names.txt',"", str_replace("/scratch/jc166922/tdh1/source/species_data/","",$path_to_common_names_file)));    
    $species = str_replace("_"," ", $species);
    
    ErrorMessage::Marker("species = {$species} path_to_common_names_file = $path_to_common_names_file\n");
    
    foreach (file($path_to_common_names_file) as $common_name)
    {
        
        $common_name = trim($common_name);
        if ($common_name == "") continue;
        
        $common_names_species_names[$index]['folder'] = $species_folder_name;
        $common_names_species_names[$index]['clazz'] = $clazz;
        $common_names_species_names[$index]['clazz_common'] = $source_folder_translation[$clazz];
        $common_names_species_names[$index]['family'] = $family;
        $common_names_species_names[$index]['genus'] = $genus;
        $common_names_species_names[$index]['species'] = $species;
        $common_names_species_names[$index]['common_name'] = $common_name;
        $common_names_species_names[$index]['full_name'] = "{$common_name} ({$species})";
        
                                                               // amphibians/models/Pseudophryne_major/output/ascii/
        $common_names_species_names[$index]['data_folder'] =   $source_folder_translation[$clazz]."/".'models/'.$species_folder_name.'/output/ascii/';
        
        ErrorMessage::Marker("Adding $common_name ..  $species\n");
        
        $index++;
        
    }
    
    
    
}

ksort($common_names_species_names);

ErrorMessage::Marker("Writing to ".count($common_names_species_names)." entries to {$species_common_name_list_file} \n");

matrix::Save($common_names_species_names,$species_common_name_list_file);

if (file_exists($species_common_name_list_file))
{
    ErrorMessage::Marker("WROTE ".count($common_names_species_names)." entries to {$species_common_name_list_file} \n");    
}
else
{
    ErrorMessage::Marker("### Error writing to {$species_common_name_list_file} \n");    
}



// file_put_contents($species_common_name_list_file, implode("\n",$common_names_species_names)."\n" );



?>
