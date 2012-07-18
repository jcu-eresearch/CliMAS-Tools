<?php
include_once 'ParameterNames.class.php';
include_once 'configuration.class.php';

$conf = array();

/**
 *
 *  DEFAULT VALUES FOR CONFIG VARIABLES
 *  
 */
$conf[Parameter::$PathDelimiter]       = "/";
$conf[Parameter::$ExtensionDelimiter]  = ".";

$conf[Parameter::$APPLICATION_NAME]      = "TDH-TOOLS";

$conf[Parameter::$APPLICATION_FOLDER]    = "/app/";
$conf[Parameter::$UTILITIES_CLASSES]     = "/app/Utilities/includes.php";

$conf[Parameter::$DOWNLOAD_FOLDER_WEB]   = "http://SomeServer/eresearch/TDH-Tools/output/";
$conf[Parameter::$DOWNLOAD_FOLDER_REAL]  = "/app/output/";
$conf[Parameter::$RESOURCES_FOLDER]      = "/app/Resources/";
$conf[Parameter::$ICONS_FOLDER]          = "/app/Resources/icons/";

$conf[Parameter::$SOURCE_DATA_FOLDER]    = "/app/source/";

$conf[Parameter::$Descriptions_ClimateModels]      = "/app/Resources/descriptions/gcm.csv";
$conf[Parameter::$Descriptions_EmissionScenarios]  = "/app/Resources/descriptions/scenario.csv";
$conf[Parameter::$Descriptions_Years]              = "/app/Resources/descriptions/year.txt";

$conf[Parameter::$COMMAND_QUEUE_LOG      ] = "/app/logs/queue.log";
$conf[Parameter::$COMMAND_QUEUE_FOLDER   ] = "/app/queue/";
$conf[Parameter::$COMMAND_SCRIPTS_FOLDER ] = "/app/scripts/";
$conf[Parameter::$COMMAND_SCRIPTS_EXE    ] = "/app/Search/CommandActionExecute.php";


$conf[Parameter::$COMMAND_EXTENSION      ] = $conf[Parameter::$ExtensionDelimiter]."command";
$conf[Parameter::$COMMAND_SCRIPTS_PREFIX ] = "command_";
$conf[Parameter::$COMMAND_SCRIPTS_SUFFIX ] = $conf[Parameter::$ExtensionDelimiter]."sh";

$conf[Parameter::$MaxentJar ] = "/app/maxent.jar";

$conf[Parameter::$Maxent_Taining_Data_folder ]            = "/home/jc165798/Climate/PCMDI/01.Oz.5km.61.90/mxe/1975/";
$conf[Parameter::$Maxent_Future_Projection_Data_folder ]  = "/home/jc165798/Climate/CIAS/Australia/5km/bioclim_mxe/";
$conf[Parameter::$Maxent_Species_Data_folder ]            = "/home/ctbccr/TDH/";
$conf[Parameter::$Maxent_Species_Data_Output_Subfolder ]  = "output/";
$conf[Parameter::$Maxent_Species_Data_Occurance_Filename ] = "occur.csv";

$hostname = trim(exec("hostname --fqdn"));


/**
 *
 *  Develpopment values from workstation hostname  "afakes-eresearch"
 *  
 */

if (stripos( $hostname, "afakes-eresearch") !== FALSE) 
{
    
    $conf[Parameter::$APPLICATION_FOLDER]    = "/www/eresearch/TDH-Tools/";
    $conf[Parameter::$UTILITIES_CLASSES]     = "/www/eresearch/TDH-Tools/Utilities/includes.php";
    $conf[Parameter::$DOWNLOAD_FOLDER_REAL]  = "/www/eresearch/TDH-Tools/output/";
    $conf[Parameter::$RESOURCES_FOLDER]      = "/www/eresearch/TDH-Tools/Resources/";
    $conf[Parameter::$SOURCE_DATA_FOLDER]    = "/data/dmf/TDH-Tools/source";

    $conf[Parameter::$Descriptions_ClimateModels]      = "/www/eresearch/TDH-Tools/Resources/descriptions/gcm.csv";
    $conf[Parameter::$Descriptions_EmissionScenarios]  = "/www/eresearch/TDH-Tools/Resources/descriptions/scenario.csv";
    $conf[Parameter::$Descriptions_Years]              = "/www/eresearch/TDH-Tools/Resources/descriptions/year.txt";

    $conf[Parameter::$DOWNLOAD_FOLDER_WEB]   = "http://localhost/eresearch/TDH-Tools/output/";
    $conf[Parameter::$ICONS_FOLDER]          = "http://localhost/eresearch/TDH-Tools/Resources/icons/";
    
    
    $conf[Parameter::$COMMAND_QUEUE_LOG      ] = "/data/dmf/TDH-Tools/logs/queue.log";
    $conf[Parameter::$COMMAND_QUEUE_FOLDER   ] = "/data/dmf/TDH-Tools/queue/";
    $conf[Parameter::$COMMAND_SCRIPTS_FOLDER ] = "/data/dmf/TDH-Tools/scripts/";
    $conf[Parameter::$COMMAND_SCRIPTS_EXE    ] = "/www/eresearch/TDH-Tools/Search/CommandActionExecute.php";
    
    $conf[Parameter::$MaxentJar ] = "/data/dmf/TDH-Tools/Search/Finder/Species/maxent.jar";

    $conf[Parameter::$Maxent_Taining_Data_folder ]            = "/home/jc165798/Climate/PCMDI/01.Oz.5km.61.90/mxe/1975";
    $conf[Parameter::$Maxent_Future_Projection_Data_folder ]  = "/home/jc165798/Climate/CIAS/Australia/5km/bioclim_mxe";
    $conf[Parameter::$Maxent_Species_Data_folder ]            = "/data/dmf/TDH-Tools/source/species/";
    $conf[Parameter::$Maxent_Species_Data_Output_Subfolder ]  = "output";
    $conf[Parameter::$Maxent_Species_Data_Occurance_Filename ] = "occur.csv";
    
}        

/**
 *
 *  Development values from workstation hostname "vbox-ubuntu" (Daniel's dev VM)
 *  
 */

if (stripos( $hostname, "vbox-ubuntu") !== FALSE) 
{
    $pathprefix = "/home/daniel/projects/ap02";
    
    $conf[Parameter::$APPLICATION_FOLDER]    = $pathprefix . "/TDH-Tools/";
    $conf[Parameter::$UTILITIES_CLASSES]     = $pathprefix . "/TDH-Tools/Utilities/includes.php";
    $conf[Parameter::$DOWNLOAD_FOLDER_REAL]  = $pathprefix . "/TDH-Tools/output/";
    $conf[Parameter::$RESOURCES_FOLDER]      = $pathprefix . "/TDH-Tools/Resources/";
    $conf[Parameter::$SOURCE_DATA_FOLDER]    = $pathprefix . "/TDH-Tools/source/";

    $conf[Parameter::$Descriptions_ClimateModels]      = $pathprefix . "/TDH-Tools/Resources/descriptions/gcm.csv";
    $conf[Parameter::$Descriptions_EmissionScenarios]  = $pathprefix . "/TDH-Tools/Resources/descriptions/scenario.csv";
    $conf[Parameter::$Descriptions_Years]              = $pathprefix . "/TDH-Tools/Resources/descriptions/year.txt";

    $conf[Parameter::$DOWNLOAD_FOLDER_WEB]   = "http://localhost/eresearch/TDH-Tools/output/";
    $conf[Parameter::$ICONS_FOLDER]          = "http://localhost/eresearch/TDH-Tools/Resources/icons/";
    
    
    $conf[Parameter::$COMMAND_QUEUE_LOG      ] = $pathprefix . "/TDH-Tools/logs/queue.log";
    $conf[Parameter::$COMMAND_QUEUE_FOLDER   ] = $pathprefix . "/TDH-Tools/queue/";
    $conf[Parameter::$COMMAND_SCRIPTS_FOLDER ] = $pathprefix . "/TDH-Tools/scripts/";
    $conf[Parameter::$COMMAND_SCRIPTS_EXE    ] = $pathprefix . "/TDH-Tools/Search/CommandActionExecute.php";
    
}   

/**
 *
 *  values if code running on HPC
 *  
 */

if (stripos( $hostname, "default.domain") !== FALSE) 
{
    
    
    $conf[Parameter::$APPLICATION_FOLDER]    = "/home/jc166922/TDH-Tools/";
    
    $conf[Parameter::$UTILITIES_CLASSES]     = "/home/jc166922/TDH-Tools/Utilities/includes.php";
    $conf[Parameter::$DOWNLOAD_FOLDER_REAL]  = "/home/jc166922/TDH-Tools/output/";
    $conf[Parameter::$RESOURCES_FOLDER]      = "/home/jc166922/TDH-Tools/Resources/";
    $conf[Parameter::$SOURCE_DATA_FOLDER]    = "/home/jc166922/TDH-Tools/source/";
    
    $conf[Parameter::$Descriptions_ClimateModels]      = "/home/jc166922/TDH-Tools/Resources/descriptions/gcm.csv";
    $conf[Parameter::$Descriptions_EmissionScenarios]  = "/home/jc166922/TDH-Tools/Resources/descriptions/scenario.csv";
    $conf[Parameter::$Descriptions_Years]              = "/home/jc166922/TDH-Tools/Resources/descriptions/year.txt";

    
    $conf[Parameter::$DOWNLOAD_FOLDER_WEB]   = "http://localhost/eresearch/TDH-Tools/output/";
    $conf[Parameter::$ICONS_FOLDER]          = "http://localhost/eresearch/TDH-Tools/Resources/icons/";
    
    
    $conf[Parameter::$COMMAND_QUEUE_LOG      ] = "/home/jc166922/TDH-Tools/logs/queue.log";
    $conf[Parameter::$COMMAND_QUEUE_FOLDER   ] = "/home/jc166922/TDH-Tools/queue/";
    $conf[Parameter::$COMMAND_SCRIPTS_FOLDER ] = "/home/jc166922/TDH-Tools/scripts/";
    $conf[Parameter::$COMMAND_SCRIPTS_EXE    ] = "/home/jc166922/TDH-Tools/Search/CommandActionExecute.php";
    
    $conf[Parameter::$MaxentJar ] = "/home/jc166922/TDH-Tools/Search/Finder/Species/maxent.jar";
    
    $conf[Parameter::$Maxent_Taining_Data_folder ]            = "/home/jc165798/Climate/PCMDI/01.Oz.5km.61.90/mxe/1975";
    $conf[Parameter::$Maxent_Future_Projection_Data_folder ]  = "/home/jc165798/Climate/CIAS/Australia/5km/bioclim_mxe";
    $conf[Parameter::$Maxent_Species_Data_folder ]            = "/home/jc166922/TDH-Tools/source/species/";
    $conf[Parameter::$Maxent_Species_Data_Output_Subfolder ]  = "output";
    $conf[Parameter::$Maxent_Species_Data_Occurance_Filename ] = "occur.csv";
    
    
}        

/**
 *
 *  Values when running on wallaceinitiative.jcu.edu.au
 *  
 *   This has multiple configs for different installs
 * 
 */
if (stripos( $hostname, "wallaceinitiative.jcu.edu.au") !== FALSE) 
{
    
    
    if (stripos( __FILE__, "climate_2012/tdhtools") !== FALSE)
    {
        $conf[Parameter::$APPLICATION_FOLDER]    = "/local/climate_2012/tdhtools/";
        $conf[Parameter::$UTILITIES_CLASSES]     = "/local/climate_2012/tdhtools/Utilities/includes.php";
        $conf[Parameter::$RESOURCES_FOLDER]      = "/local/climate_2012/tdhtools/Resources/";
        $conf[Parameter::$SOURCE_DATA_FOLDER]    = "/local/climate_2012/tdhtools/source/";
        
        $conf[Parameter::$Descriptions_ClimateModels]      = "/local/climate_2012/tdhtools/Resources/descriptions/gcm.csv";
        $conf[Parameter::$Descriptions_EmissionScenarios]  = "/local/climate_2012/tdhtools/Resources/descriptions/scenario.csv";
        $conf[Parameter::$Descriptions_Years]              = "/local/climate_2012/tdhtools/Resources/descriptions/year.txt";

        $conf[Parameter::$DOWNLOAD_FOLDER_REAL]  = "/local/climate_2012/output/";
        
        
        $conf[Parameter::$DOWNLOAD_FOLDER_WEB]   = "http://wallaceinitiative.jcu.edu.au/climate_2012/output/";
        $conf[Parameter::$ICONS_FOLDER]          = "http://wallaceinitiative.jcu.edu.au/climate_2012/tdhtools/Resources/icons/";

        $conf[Parameter::$COMMAND_QUEUE_LOG      ] = "/local/climate_2012/tdhtools/logs/queue.log";
        $conf[Parameter::$COMMAND_QUEUE_FOLDER   ] = "/local/climate_2012/tdhtools/queue/";
        $conf[Parameter::$COMMAND_SCRIPTS_FOLDER ] = "/local/climate_2012/tdhtools/scripts/";
        $conf[Parameter::$COMMAND_SCRIPTS_EXE    ] = "/local/climate_2012/tdhtools/Search/CommandActionExecute.php";
        
        $conf[Parameter::$MaxentJar ] = "/local/climate_2012/tdhtools/Search/Finder/Species/maxent.jar";
        
        $conf[Parameter::$Maxent_Taining_Data_folder ]            = "/home/jc165798/Climate/PCMDI/01.Oz.5km.61.90/mxe/1975";
        $conf[Parameter::$Maxent_Future_Projection_Data_folder ]  = "/home/jc165798/Climate/CIAS/Australia/5km/bioclim_mxe";
        $conf[Parameter::$Maxent_Species_Data_folder ]            = "/home/ctbccr/TDH/";
        $conf[Parameter::$Maxent_Species_Data_Output_Subfolder ]  = "output";
        $conf[Parameter::$Maxent_Species_Data_Occurance_Filename ] = "occur.csv";
        
        
        
    }    
    
    if (stripos( __FILE__, "climate_2012/demo") !== FALSE)         
    {
        $conf[Parameter::$APPLICATION_FOLDER]    = "/local/climate_2012/demo/";
        $conf[Parameter::$UTILITIES_CLASSES]     = "/local/climate_2012/demo/Utilities/includes.php";
        $conf[Parameter::$DOWNLOAD_FOLDER_REAL]  = "/local/climate_2012/demo/output/";
        $conf[Parameter::$RESOURCES_FOLDER]      = "/local/climate_2012/demo/Resources/";
        $conf[Parameter::$SOURCE_DATA_FOLDER]    = "/local/climate_2012/demo/source/";

        $conf[Parameter::$Descriptions_ClimateModels]      = "/local/climate_2012/demo/Resources/descriptions/gcm.csv";
        $conf[Parameter::$Descriptions_EmissionScenarios]  = "/local/climate_2012/demo/Resources/descriptions/scenario.csv";
        $conf[Parameter::$Descriptions_Years]              = "/local/climate_2012/demo/Resources/descriptions/year.txt";

        
        $conf[Parameter::$DOWNLOAD_FOLDER_WEB]   = "http://wallaceinitiative.jcu.edu.au/climate_2012/demo/output/";
        $conf[Parameter::$ICONS_FOLDER]          = "http://wallaceinitiative.jcu.edu.au/climate_2012/demo/Resources/icons/";        

        $conf[Parameter::$COMMAND_QUEUE_LOG      ] = "/local/climate_2012/demo/logs/queue.log";
        $conf[Parameter::$COMMAND_QUEUE_FOLDER   ] = "/local/climate_2012/demo/queue/";
        $conf[Parameter::$COMMAND_SCRIPTS_FOLDER ] = "/local/climate_2012/demo/scripts/";
        $conf[Parameter::$COMMAND_SCRIPTS_EXE    ] = "/local/climate_2012/demo/Search/CommandActionExecute.php";

        $conf[Parameter::$MaxentJar ] = "/local/climate_2012/demo/Search/Finder/Species/maxent.jar";
        
        $conf[Parameter::$Maxent_Taining_Data_folder ]            = "/home/jc165798/Climate/PCMDI/01.Oz.5km.61.90/mxe/1975";
        $conf[Parameter::$Maxent_Future_Projection_Data_folder ]  = "/home/jc165798/Climate/CIAS/Australia/5km/bioclim_mxe";
        $conf[Parameter::$Maxent_Species_Data_folder ]            = "/home/ctbccr/TDH/";
        $conf[Parameter::$Maxent_Species_Data_Output_Subfolder ]  = "output";
        $conf[Parameter::$Maxent_Species_Data_Occurance_Filename ] = "occur.csv";
        
        
    }    
    
    if (stripos( __FILE__, "climate_2012/testtdhtools") !== FALSE)
    {
        $conf[Parameter::$APPLICATION_FOLDER]    = "/local/climate_2012/testtdhtools/";
        $conf[Parameter::$UTILITIES_CLASSES]     = "/local/climate_2012/testtdhtools/Utilities/includes.php";
        $conf[Parameter::$RESOURCES_FOLDER]      = "/local/climate_2012/testtdhtools/Resources/";
        $conf[Parameter::$SOURCE_DATA_FOLDER]    = "/local/climate_2012/testtdhtools/source/";
        
        $conf[Parameter::$Descriptions_ClimateModels]      = "/local/climate_2012/testtdhtools/Resources/descriptions/gcm.csv";
        $conf[Parameter::$Descriptions_EmissionScenarios]  = "/local/climate_2012/testtdhtools/Resources/descriptions/scenario.csv";
        $conf[Parameter::$Descriptions_Years]              = "/local/climate_2012/testtdhtools/Resources/descriptions/year.txt";

        $conf[Parameter::$DOWNLOAD_FOLDER_REAL]  = "/local/climate_2012/output/";
        
        
        $conf[Parameter::$DOWNLOAD_FOLDER_WEB]   = "http://wallaceinitiative.jcu.edu.au/climate_2012/output/";
        $conf[Parameter::$ICONS_FOLDER]          = "http://wallaceinitiative.jcu.edu.au/climate_2012/testtdhtools/Resources/icons/";

        $conf[Parameter::$COMMAND_QUEUE_LOG      ] = "/local/climate_2012/testtdhtools/logs/queue.log";
        $conf[Parameter::$COMMAND_QUEUE_FOLDER   ] = "/local/climate_2012/testtdhtools/queue/";
        $conf[Parameter::$COMMAND_SCRIPTS_FOLDER ] = "/local/climate_2012/testtdhtools/scripts/";
        $conf[Parameter::$COMMAND_SCRIPTS_EXE    ] = "/local/climate_2012/testtdhtools/Search/CommandActionExecute.php";
        
        $conf[Parameter::$MaxentJar ] = "/local/climate_2012/testtdhtools/Search/Finder/Species/maxent.jar";
        
        $conf[Parameter::$Maxent_Taining_Data_folder ]            = "/home/jc165798/Climate/PCMDI/01.Oz.5km.61.90/mxe/1975";
        $conf[Parameter::$Maxent_Future_Projection_Data_folder ]  = "/home/jc165798/Climate/CIAS/Australia/5km/bioclim_mxe";
        $conf[Parameter::$Maxent_Species_Data_folder ]            = "/home/ctbccr/TDH/";
        $conf[Parameter::$Maxent_Species_Data_Output_Subfolder ]  = "output";
        $conf[Parameter::$Maxent_Species_Data_Occurance_Filename ] = "occur.csv";
    }
    
    $af = configuration::ApplicationFolder();
    include_once configuration::ApplicationFolder().'Utilities/includes.php';
    
    include_once $af.'Search/Session.class.php';
    include_once $af.'Search/CommandAction/Command.includes.php';
    include_once $af.'Search/Finder/Finder.includes.php';
    include_once $af.'Search/Data/Data.includes.php';
    include_once $af.'Search/extras/extras.includes.php';
    include_once $af.'Search/MapServer/Mapserver.includes.php';
    include_once $af.'Search/DB/ToolsData.includes.php';
    include_once $af.'Search/Output/Output.includes.php';
    
    include_once $af.'Search/Finder/Species/SpeciesMaxent.action.class.php';
    
    

?>
