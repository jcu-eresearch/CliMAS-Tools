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

$incoming_sh = configuration::ApplicationFolder()."applications/Incoming.sh";
    
echo "=====================================================================================\n";  
echo "Check on database access here\n";

/*

DROP TABLE IF EXISTS command_action;
CREATE TABLE command_action 
(
    id SERIAL NOT NULL PRIMARY KEY,
    objectID VARCHAR(50) NOT NULL,  -- objectID 
    data text,                      -- php serialised object
    execution_flag varchar(50),     -- execution state
    status varchar(200),            -- current status
    queueid varchar(50),            -- to identify where this job cam from, allows multiple environments to use same queue
    update_datetime TIMESTAMP NULL  -- the last time data was updated
);

GRANT ALL PRIVILEGES ON command_action TO ap02;
GRANT USAGE, SELECT ON SEQUENCE command_action_id_seq TO ap02;


// SPECIES OCCURENCE COUNT - will need to be rebuilt on occuence updates
 * easier to them look up species that have occurences
 * 
 * 
DROP TABLE IF EXISTS species_occurence;
create table species_occurence as  select species_id,count(*) from occurrences group by species_id;
GRANT ALL PRIVILEGES ON species_occurence TO ap02;



// get species name for a spoecies that has occurances
select s.id as species_id ,s.scientific_name,s.common_name,sp.count as occurance_count from species s, species_occurence sp  where s.id=sp.species_id and sp.count > 0 and scientific_name like '%Lethrinus lentjan%'


- Query for get Spewcies list where they have Occurances
select s.id as species_id ,s.scientific_name,s.common_name,sp.count as occurance_count from species s, species_occurence sp  where s.id=sp.species_id and sp.count > 0



FIND FAULTY FILES - files that donot have the correct number of parts
select f.file_unique_id,f.filetype,f.totalparts,count(*) as parts_count from files f, files_data fd  where f.file_unique_id = fd.file_unique_id group by f.file_unique_id,f.filetype,f.totalparts having count(*) != f.totalparts;


REMOVE FILES_DATA
delete from files_data where file_unique_id             = '500dfd4576c8e';
delete from files where file_unique_id                  = '500dfd4576c8e';
delete from modelled_climates where file_unique_id      = '500dfd4576c8e';
delete from modelled_species_files where file_unique_id = '500dfd4576c8e';





 */


echo "\n";
echo "Create CRON script {$incoming_sh} \n";
echo "\n";

file_put_contents($incoming_sh, "#!/bin/tcsh\n cd ".configuration::ApplicationFolder()."\n php -q ".configuration::ApplicationFolder()."applications/Incoming.php\n");

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
