<?php
include_once 'includes.php';

$doit = array_util::Value($argv, 1,null);

if (is_null($doit)) echo "Test ONLY \n";


echo "Remove all COmmand Action queue Items\n";
if (!is_null($doit)) DatabaseCommands::CommandActionRemoveAll(true);


$db = new PGDB();

echo "Remove all rows from modelled_species_data \n";
if (!is_null($doit)) DBO::DeleteAll("modelled_species_data");

echo "Remove all rows from files_data \n";
if (!is_null($doit)) DBO::DeleteAll("files_data");

echo "Remove all rows from ap02_command_action \n";
if (!is_null($doit)) DBO::DeleteAll("command_action");

$cmd = "rm -r " . configuration::CommandScriptsFolder()."*";
echo "Remove all Scripts as per $cmd \n";
if (!is_null($doit)) exec($cmd);

$cmd = "rm -r " . configuration::Maxent_Species_Data_folder()."*";
echo "Remove all Data as per $cmd \n";
if (!is_null($doit)) exec($cmd);

$cmd = "rm -r " . configuration::TempFolder()."*";
echo "Remove all TEMP data $cmd \n";
if (!is_null($doit)) exec($cmd);


$cmd = "rm -r " . configuration::FilesDownloadFolder()."*";
echo "Remove all TEMP data $cmd \n";
if (!is_null($doit)) exec($cmd);

$cmd = "rm -r /home/jc166922/4*.e4*";
echo "Remove from home folder $cmd \n";
if (!is_null($doit)) exec($cmd);

$cmd = "rm -r /home/jc166922/4*.o4*";
echo "Remove from home folder $cmd \n";
if (!is_null($doit)) exec($cmd);


unset($db);
echo "\n";

?>