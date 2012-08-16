<?php
include_once dirname(__FILE__).'/includes.php';

ErrorMessage::Marker("Median\n");

$folders = file::folder_folders(configuration::Maxent_Species_Data_folder(),  configuration::osPathDelimiter(),true);

if (!is_array($folders))
{
    $msg = "Folder is Not an Array ??";
    return new ErrorMessage(__FILE__, __METHOD__, $msg);
}

// create scripts - that will create medians for each species

$script_folder = configuration::CommandScriptsFolder()."medians/";
file::mkdir_safe(configuration::CommandScriptsFolder()."medians/");


foreach ($folders as $key => $value) 
{
    $script_filename = $script_folder."median_{$key}.sh";
    
$app_path = configuration::ApplicationFolder();

$SpeciesMedian_php = $app_path."applications/SpeciesMedian.php";


$s = <<<SCRIPT
#!/bin/bash
cd {$script_folder}
php {$SpeciesMedian_php} {$key}
SCRIPT;

    file_put_contents($script_filename, $s);
    exec("chmod u+x '$script_filename'");
    exec("qsub '$script_filename'");    
    sleep(1);
}        

?>
