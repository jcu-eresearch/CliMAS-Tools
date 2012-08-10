<?php
include_once dirname(__FILE__).'/includes.php';

echo "Median\n";

$folders = file::folder_folders(configuration::Maxent_Species_Data_folder(),  configuration::osPathDelimiter(),true);

if (!is_array($folders))
{
    $msg = "Foldesr is Not an Array ??";
    echo "ERROR:: $msg\n";
    return new ErrorMessage(__FILE__, __METHOD__, $msg);
}

// create scripts - that will create medians for each species

$script_folder = configuration::CommandScriptsFolder()."medians/";
file::mkdir_safe(configuration::CommandScriptsFolder()."medians/");


foreach ($folders as $key => $value) 
{
    $script_filename = $script_folder."median_{$key}.sh";
    
$s = <<<SCRIPT
#!/bin/bash
cd {$script_folder}
php /scratch/jc166922/tdh1/tdhtools/applications/SpeciesMedian.php {$key}

SCRIPT;

    file_put_contents($script_filename, $s);
    exec("chmod u+x '$script_filename'");
    exec("qsub '$script_filename'");    
    sleep(3);
}        

?>
