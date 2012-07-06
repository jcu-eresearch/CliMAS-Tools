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

$incoming_sh = configuration::ApplicationFolder()."Search/Incoming.sh";
    
echo "=====================================================================================\n";  
echo "Check on database access here\n";




/*

DROP TABLE IF EXISTS ap02_command_action;
CREATE TABLE ap02_command_action 
(
    id SERIAL NOT NULL PRIMARY KEY,
    objectID VARCHAR(50) NOT NULL, -- objectID 
    data text, -- php serialised object
    execution_flag varchar(50), -- execution state
    status varchar(200), -- current status
    queueid varchar(50), -- to identify where this job cam from, allows multiple environments to use same queue
    update_datetime TIMESTAMP NULL -- the last time data was updated
);

GRANT ALL PRIVILEGES ON ap02_command_action TO ap02;
GRANT USAGE, SELECT ON SEQUENCE ap02_command_action_id_seq TO ap02;


DROP TABLE IF EXISTS images;
CREATE TABLE images 
(
  id SERIAL NOT NULL PRIMARY KEY
    ,lookup          varchar(300) not null
    ,data            text
    ,filesize        float
    ,mimetype        varchar(100)
    ,encoder         varchar(50)
    ,decoder         varchar(50)
    ,update_datetime  timestamp without time zone 
);

GRANT ALL PRIVILEGES ON images TO ap02;
GRANT USAGE, SELECT ON SEQUENCE images_id_seq TO ap02;



DROP TABLE IF EXISTS modelled_species_data;
CREATE TABLE modelled_species_data 
(
  id SERIAL NOT NULL PRIMARY KEY
    ,scientific_name   varchar(300)
    ,common_name       varchar(300)
    ,model_name        varchar(50)
    ,scenario_name     varchar(50)
    ,time_name         varchar(10)
    ,data_category     varchar(100)
    ,get_data_query    varchar(999)
    ,maxent_threshold  varchar(50)
    ,files_id          varchar(100)
    ,update_datetime   timestamp without time zone 
);

GRANT ALL PRIVILEGES ON modelled_species_data TO ap02;
GRANT USAGE, SELECT ON SEQUENCE modelled_species_data_id_seq TO ap02;


DROP TABLE IF EXISTS files_data;
CREATE TABLE files_data 
(
    id SERIAL NOT NULL PRIMARY KEY
    ,file_unique_id   varchar(60)
    ,mimetype         varchar(100)
    ,file_description varchar(500)
    ,partnum          float
    ,totalparts       float
    ,total_filesize   float
    ,data             text
    ,update_datetime  timestamp without time zone 
);

GRANT ALL PRIVILEGES ON files_data TO ap02;
GRANT USAGE, SELECT ON SEQUENCE files_data_id_seq TO ap02;



 */


echo "\n";
echo "Create CRON script {$incoming_sh} \n";
echo "\n";

file_put_contents($incoming_sh, "cd ".configuration::ApplicationFolder()."; php -q ".configuration::ApplicationFolder()."Search/Incoming.php\n");

if (!file_exists($incoming_sh))
{
    echo "FAILED: to create CRON script {$incoming_sh} \n";
    exit(1);
}

echo "\n make executable  chmod u+x {$incoming_sh}";
exec("chmod u+x {$incoming_sh}");

echo "\n";

echo "\nCRON (on host that will be processing the queue)";
echo "\n";
echo "\n  copy |* * * * * {$incoming_sh}|";
echo "\n  crontab -e\n";
echo "\n  i = insert\n";
echo "\n  paste";
echo "\n  ESC";
echo "\n  :w<enter>";
echo "\n  ESC";
echo "\n  :q<enter>";
echo "\n";
echo "=====================================================================================\n";
echo "Seems to be OK, check warnings if any\n";
echo "\n";


?>
