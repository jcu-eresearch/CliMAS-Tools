<?php
include_once dirname(__FILE__).'/includes.php';
/**
 *
 * Create Median for all species that belong to a CLAZZ (Taxa)
 *  
 * CReate Scripts that are then submitted to the PBS queue
 * 
 * 
 */

$app_path = configuration::ApplicationFolder();
$SpeciesMedian_php = $app_path."applications/SpeciesMedian.php";

$script_folder = configuration::CommandScriptsFolder()."medians/";
file::mkdir_safe($script_folder);

$clazz = util::CommandScriptsFoldermandLineOptionValue($argv, 'clazz', null);

if (is_null($clazz))
{
    echo "usage: \n";
    echo "  {$argv[0]} --clazz=(Taxa Name) \n";
    echo "\n";
    echo "  e.g. {$argv[0]} --clazz=MAMMALIA \n";
    
    exit(0);
}

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
