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


// Host (and other criteria) based configuration files


if (file_exists("{$af}config.default"))  include_once $af.'config.default';

if (file_exists("{$af}config.daniel"))   include_once $af.'config.daniel';
if (file_exists("{$af}config.tdh1"))     include_once $af.'config.tdh1';
if (file_exists("{$af}config.tdh1-hpc")) include_once $af.'config.tdh1-hpc';
if (file_exists("{$af}config.tdh2"))     include_once $af.'config.tdh2';
if (file_exists("{$af}config.tdh2-hpc")) include_once $af.'config.tdh2-hpc';

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