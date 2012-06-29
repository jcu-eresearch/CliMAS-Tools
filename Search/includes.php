<?php
include_once dirname(__FILE__).'/ParameterNames.class.php';

$conf = array();

$hostname = trim(exec("hostname --fqdn"));

if (file_exists("config.default"))  include_once 'config.default';

if (file_exists("config.daniel"))   include_once 'config.daniel';
if (file_exists("config.hpc"))      include_once 'config.hpc';
if (file_exists("config.wallace"))  include_once 'config.wallace';
if (file_exists("config.tdh2"))     include_once 'config.tdh2';
if (file_exists("config.tdh2-hpc")) include_once 'config.tdh2-hpc';
if (file_exists("config.afakes"))   include_once 'config.afakes';

include_once dirname(__FILE__).'/configuration.class.php';

$af = dirname(__FILE__).'/';

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




?>
