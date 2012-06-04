<?php
session_start();
include_once 'configuration.class.php';
include_once configuration::UtilityClasses();
include_once configuration::RemoteCommandClasses();


// include interfaces & factories here
include_once 'Finder/Finder.includes.php';
include_once 'Data/Data.includes.php';
include_once 'DB/ToolsData.includes.php';


include_once 'Output/Output.includes.php';
include_once 'extras/extras.includes.php';
include_once 'MapServer/Mapserver.includes.php';
include_once 'Session.class.php';

?>
