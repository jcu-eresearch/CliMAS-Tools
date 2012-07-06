<?php
include_once dirname(__FILE__).'/ParameterNames.class.php';

$af = dirname(__FILE__).'/';

$conf = array();

$hostname = trim(exec("hostname --fqdn"));

if (file_exists("{$af}config.default"))  include_once $af.'config.default';

//if (file_exists("{$af}config.daniel"))   include_once $af.'config.daniel';
//if (file_exists("{$af}config.hpc"))      include_once $af.'config.hpc';
//if (file_exists("{$af}config.wallace"))  include_once $af.'config.wallace';
if (file_exists("{$af}config.tdh2"))     include_once $af.'config.tdh2';
if (file_exists("{$af}config.tdh2-hpc")) include_once $af.'config.tdh2-hpc';
//if (file_exists("{$af}config.afakes"))   include_once $af.'config.afakes';
include_once dirname(__FILE__).'/configuration.class.php';
include_once $af."../Utilities/includes.php";
include_once $af.'Session.class.php';
include_once $af.'CommandAction/Command.includes.php';
include_once $af.'Finder/Finder.includes.php';
include_once $af.'Data/Data.includes.php';
include_once $af.'extras/extras.includes.php';
include_once $af.'MapServer/Mapserver.includes.php';
include_once $af.'DB/ToolsData.includes.php';
include_once $af.'Output/Output.includes.php';
include_once $af.'Finder/Species/SpeciesMaxent.action.class.php';
?>