<?php
ini_set('memory_limit','8096M');
include_once dirname(__FILE__).'/ParameterNames.class.php';
/**
 * Main Include for all configuration information and Classes
 *  
 * Use this file to support inclusion of classess, when application is installed in differewnt folders
 * 
 * Each configuration file should define what defines it.
 * - usually the start of the fille will test some part of the  Fully Qualified Host Name --  $hostname
 * 
 */

$af = dirname(__FILE__).'/';

$conf = array();

$hostname = trim(exec("hostname --fqdn"));

// include configuration file, with fallback to the default one.

if (file_exists("{$af}CONFIGURATION.cfg")) {
	include_once "{$af}CONFIGURATION.cfg";
} else {
	include_once "{$af}CONFIGURATION.cfg.default";
}

include_once dirname(__FILE__).'/configuration.class.php';

include_once configuration::UtilityClasses();
include_once $af.'Session.class.php';
include_once $af.'CommandAction/Command.includes.php';
include_once $af.'Finder/Finder.includes.php';
include_once $af.'Data/Data.includes.php';
include_once $af.'extras/extras.includes.php';
include_once $af.'MapServer/Mapserver.includes.php';
include_once $af.'DB/ToolsData.includes.php';
include_once $af.'Output/Output.includes.php';
include_once $af.'Finder/Species/SpeciesMaxent.action.class.php';
include_once $af.'Finder/Species/SpeciesMaxentQuickLook.class.php';
include_once $af.'Finder/Species/MaxentServerBuild.class.php';

?>