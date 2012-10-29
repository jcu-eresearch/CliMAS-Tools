<?php

$inc_folder = dirname(__FILE__);
include_once "{$inc_folder}/includes.php";
if (php_sapi_name() != "cli") return;


$execute = false;

$execute_flag = array_util::Value($argv, 1);
if (is_null($execute_flag)) $execute_flag = 'NO_EXECUTE';


if ($execute_flag !== 'EXECUTE')
{
    ErrorMessage::Marker("####### DRY RUN ONLY .... no files will be changed #######");
    ErrorMessage::Marker("Please run as  'php {$argv[0]} EXECUTE'  to actually execute and do something ");    
    $execute = false;
}
else
{
    ErrorMessage::Marker("####### EXECUTING DATA BUILD #######");
    $execute = true;
}



$JSON_KEY = 'JSON';
$clazz_translation = array();
$clazz_translation['AMPHIBIA'] = 'amphibians';
$clazz_translation['MAMMALIA'] = 'mammals';
$clazz_translation['REPTILIA'] = 'reptiles';

$real_data_folder = "/scratch/jc148322/AP02/";   // folder with real data 

$AP02_data_folder  = configuration::Maxent_Species_Data_folder()."richness/ByGenus/";

ErrorMessage::Marker("RICHNESS QuickLook");

$folders = file::folder_folders($AP02_data_folder, configuration::osPathDelimiter(), true);

print_r($folders);

$count = 1;
foreach ($folders as $folder_basename => $folder_pathname) 
{
    ErrorMessage::Marker("RICHNESS QuickLook [{$folder_basename}] {$count}/".count($folders));
    $cmd = "php {$inc_folder}/HotSpotsCreateQuickLook.php --cmd='find {$folder_pathname}/*.asc.gz | sort ' --output={$folder_pathname} --title='Predicted Species Richness for the genus {$folder_basename}'";
    ErrorMessage::Marker("{$cmd}");
    if ($execute) exec($cmd);    
    
    $count++;
    
}


?>
