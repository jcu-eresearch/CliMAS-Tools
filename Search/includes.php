<?php
session_start();
include_once 'Object.class.php';
include_once 'configuration.class.php';
include_once configuration::UtilityClasses();

// include interfaces here
include_once 'Finder/Finder.includes.php';
include_once 'data/data.includes.php';
include_once 'output/Output.includes.php';
include_once 'extras/extras.includes.php';
include_once 'MapServer/Mapserver.includes.php';

?>
