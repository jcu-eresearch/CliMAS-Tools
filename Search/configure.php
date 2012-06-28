<?php
include_once 'includes.php';

echo "\n=====================================================================================\n";
echo "==== current configuration settings for this host {$hostname}\n";
echo "=====================================================================================\n";
print_r($conf);
echo "=====================================================================================\n";

echo "\n";

if (!is_dir(configuration::ApplicationFolder()))
{
    echo "ApplicationFolder does not exist ".configuration::ApplicationFolder()."\n";
    exit(1);
}

if (!is_dir(configuration::TempFolder()  ))
{
    echo "TempFolder does not exist: ".configuration::TempFolder()."\n";
    exit(1);
}


if (!file_exists(configuration::UtilityClasses()))
{
    echo "UtilityClasses NOT FOUND: ".configuration::UtilityClasses()."\n";
    exit(1);
}


if (!is_dir(configuration::FilesDownloadFolder()))
{
    echo "FilesDownloadFolder does not exist: ".configuration::FilesDownloadFolder()."\n";
    exit(1);
}


if (!is_dir(configuration::ResourcesFolder()))
{
    echo "ResourcesFolder does not exist: ".configuration::ResourcesFolder()."\n";
    exit(1);
}

if (!file_exists(configuration::Descriptions_ClimateModels() ))
{
    echo "Descriptions for ClimateModels does not exist: ".configuration::Descriptions_ClimateModels()."\n";
    exit(1);
}

if (!file_exists(configuration::Descriptions_EmissionScenarios() ))
{
    echo "Descriptions for EmissionScenarios does not exist: ".configuration::Descriptions_EmissionScenarios()."\n";
    exit(1);
}

if (!file_exists(configuration::Descriptions_Years() ))
{
    echo "configuration::Descriptions_Years() does not exist: ".configuration::Descriptions_Years()."\n";
    exit(1);
}

if (!is_dir(configuration::SourceDataFolder() ))
{
    echo "Source Data Folder does not exist: ".configuration::SourceDataFolder()."\n";
    exit(1);
}

if (!is_dir(configuration::ContextSpatialLayersFolder()))
{
    echo "Context Spatial Layers Folder does not exist: ".configuration::ContextSpatialLayersFolder()."\n";
    exit(1);
}


if (!is_dir(configuration::CommandQueueFolder()  ))
{
    echo "Command Queue Folder does not exist: ".configuration::CommandQueueFolder()."\n";
    exit(1);
}

if (!is_dir(configuration::CommandScriptsFolder()  ))
{
    echo "Command Scripts Folder()  does not exist: ".configuration::CommandScriptsFolder() ."\n";
    exit(1);
}

if (!file_exists(configuration::CommandScriptsExecutor()  ))
{
    echo "Command Scripts Executor does not exist: ".configuration::CommandScriptsExecutor()."\n";
    exit(1);
}

if (!file_exists(configuration::MaxentJar()  ))
{
    echo "Maxent.Jar does not exist: ".configuration::MaxentJar()."\n";
    exit(1);
}

if (!is_dir(configuration::Maxent_Taining_Data_folder()  ))
{
    echo "WARNING:: Maxent_Taining_Data_folder does not exist: ".configuration::Maxent_Taining_Data_folder()."\n";    
}

if (!is_dir(configuration::Maxent_Future_Projection_Data_folder()  ))
{
    echo "WARNING:: Maxent_Future_Projection_Data_folder does not exist: ".configuration::Maxent_Future_Projection_Data_folder()."\n";
}

if (!is_dir(configuration::Maxent_Species_Data_folder()  ))
{
    echo "WARNING:: Maxent_Species_Data_folder() does not exist: ".configuration::Maxent_Species_Data_folder()."\n";
}

    
echo "=====================================================================================\n";  
echo "Check on database access here\n";
echo "\n";
echo "\nCRON (on host that will be processing the queue)";
echo "\n";
echo "\n  command:   crontab -e   (i  ) \n";
echo "\n  (for Vi)   i = insert \n";
echo "\n             paste  [ * * * * * php -q ".configuration::ApplicationFolder()."Search/Incoming.php  ]";
echo "\n             ESC";
echo "\n             :w<enter>";
echo "\n             ESC";
echo "\n             :q<enter>";
echo "\n";
echo "=====================================================================================\n";
echo "Seems to be OK, check warnings if any\n";
echo "\n";


?>
