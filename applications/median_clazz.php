<?php
include_once dirname(__FILE__).'/includes.php';

$app_path = configuration::ApplicationFolder();
$SpeciesMedian_php = $app_path."applications/SpeciesMedian.php";

$script_folder = configuration::CommandScriptsFolder()."medians/";
file::mkdir_safe(configuration::CommandScriptsFolder()."medians/");

$clazz = "MAMMALIA";

ErrorMessage::Marker("Median for Clazz [{$clazz}] \n");

$ids = SpeciesData::SpeciesForClazz($clazz);

foreach ($ids as $species_id) 
{
    
    $script_filename = $script_folder."median_{$species_id}.sh";

    $s = "  #!/bin/bash \ncd {$script_folder}\n
            php {$SpeciesMedian_php} {$key}\n
            
            ";    
    file_put_contents($script_filename, $s);
    
    exec("chmod u+x '$script_filename'");
    exec("qsub '$script_filename'");
    
    sleep(1);
}        

?>
