<?php
include_once dirname(__FILE__).'/includes.php';

$sdf = configuration::SourceDataFolder();

$species_common_name_list_file = "{$sdf}species_common_name_list.txt";

ErrorMessage::Marker("Create species_id folders for each in $species_common_name_list_file");

ErrorMessage::Marker("LOAD  $species_common_name_list_file");
$data = matrix::Load($species_common_name_list_file);

// matrix::display($data, " ", null, 15);

$data_folder = configuration::Maxent_Species_Data_folder();

$error = array();
foreach ($data as $index => $row) 
{
    // create symbolic links for each row  
    
    $ap02_species_folder_id = "{$data_folder}{$index}";
    
    
    if (is_dir($ap02_species_folder_id))
        exec("rm -r {$ap02_species_folder_id}/*"); // remove all files and folders for this id as it may be pointing to a different spot
    else
        file::mkdir_safe($ap02_species_folder_id);
    
        
    $ascii_folder = "/scratch/jc148322/AP02/{$row['clazz_common']}/models/{$row['folder']}/output/ascii/";
    
    if (is_dir($ascii_folder))
    {
        $ap02_output_folder = "{$ap02_species_folder_id}/output";
        file::mkdir_safe($ap02_output_folder);   // create oputput folder in AP02 accessible & writeable area
        
        // loop thru all gz files in  $ascii_folder  and create symboplic link in $ap02_output_folder
        
        $gz_files = file::folder_gz($ascii_folder, '/', true);
        
        ErrorMessage::Marker("Create link for {$ap02_output_folder}");
        foreach ($gz_files as $gz_basename => $gz_pathname) 
        {
            $ap02_link = $ap02_output_folder."/{$gz_basename}";
            exec("ln -s '{$gz_pathname}' '{$ap02_link}'");
        }
        
        
        ErrorMessage::Marker("Create link for {$ap02_output_folder} occur.csv");
        $occur_target    = "/scratch/jc148322/AP02/{$row['clazz_common']}/models/{$row['folder']}/occur.csv";
        $occur_link_name = "{$ap02_species_folder_id}/occur.csv";
        exec("ln -s '{$occur_target}' '{$occur_link_name}'");
        
        
    }
    else
    {
        ErrorMessage::Marker("##ERROR no ascii folder for {$row['clazz_common']} / {$row['species']} ");
        $error[$index] = $row;
        
    }
    
}

if (count($error) > 0 )
{
    ErrorMessage::Marker("##### MISSING ASCII FOLDERS #####");    
    
    
    matrix::display($error, " ", null, 15);
    ErrorMessage::Marker("##### MISSING ASCII FOLDERS #####");    
}



?>
