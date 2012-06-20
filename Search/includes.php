<?php
session_start();
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
    $conf[Parameter::$SOURCE_DATA_FOLDER]    = "/www/eresearch/TDH-Tools/source/";

    $conf[Parameter::$Descriptions_ClimateModels]      = "/www/eresearch/TDH-Tools/Resources/descriptions/gcm.csv";
    $conf[Parameter::$Descriptions_EmissionScenarios]  = "/www/eresearch/TDH-Tools/Resources/descriptions/scenario.csv";
    $conf[Parameter::$Descriptions_Years]              = "/www/eresearch/TDH-Tools/Resources/descriptions/year.txt";

    $conf[Parameter::$DOWNLOAD_FOLDER_WEB]   = "http://localhost/eresearch/TDH-Tools/output/";
    $conf[Parameter::$ICONS_FOLDER]          = "http://localhost/eresearch/TDH-Tools/Resources/icons/";
    
    
    $conf[Parameter::$COMMAND_QUEUE_LOG      ] = "/data/dmf/TDH-Tools/logs/queue.log";
    $conf[Parameter::$COMMAND_QUEUE_FOLDER   ] = "/data/dmf/TDH-Tools/queue/";
    $conf[Parameter::$COMMAND_SCRIPTS_FOLDER ] = "/data/dmf/TDH-Tools/scripts/";
    $conf[Parameter::$COMMAND_SCRIPTS_EXE    ] = "/www/eresearch/TDH-Tools/Search/CommandActionExecute.php";
    
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
        
    }    
    
}        
    
    $af = configuration::ApplicationFolder();
    include_once configuration::ApplicationFolder().'Utilities/includes.php';

    include_once $af.'Search/Session.class.php';
    include_once $af.'Search/Finder/Finder.includes.php';
    include_once $af.'Search/Data/Data.includes.php';
    include_once $af.'Search/DB/ToolsData.includes.php';
    include_once $af.'Search/Output/Output.includes.php';
    include_once $af.'Search/extras/extras.includes.php';
    include_once $af.'Search/MapServer/Mapserver.includes.php';
    include_once $af.'Search/CommandAction/Command.includes.php';
    
    

?>
