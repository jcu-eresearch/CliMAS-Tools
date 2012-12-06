<?php
include_once 'includes.php';
if (php_sapi_name() != "cli") return;

$species_id = util::CommandScriptsFoldermandLineOptionValue($argv, 'species', null);
$folder = util::CommandScriptsFoldermandLineOptionValue($argv, 'folder', null);

if (is_null($species_id) && is_null($folder))
{
    
    echo "usage {$argv[0]} calculate ASCIIgrid stats for";
    echo "\n";
    echo "       --species=n    this species N \n";
    echo "       --folder=true  all species in Data folder \n";
    exit(1);
}

if (!is_null($species_id)) 
{
    SpeciesData::CalculateAsciigridStatictis($species_id);
    exit();
}

if (!is_null($folder)) 
{
    $folder = configuration::Maxent_Species_Data_folder();
    
    $folders  = file::folder_folders($folder,null,true);
    
    foreach ($folders  as $species_id => $aFolder) {
        if (!is_numeric($species_id)) continue;
        ErrorMessage::Marker("Statsistics for {$species_id} {$aFolder}");
        SpeciesData::CalculateAsciigridStatictis($species_id);
    }
    
    
    exit();
}



?>
