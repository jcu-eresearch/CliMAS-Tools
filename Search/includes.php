<?php
include_once 'ParameterNames.class.php';
include_once 'configuration.class.php';

$conf = array();

$hostname = trim(exec("hostname --fqdn"));

if (file_exists("config.default"))  include_once 'config.default';

if (file_exists("config.daniel"))   include_once 'config.daniel';
if (file_exists("config.hpc"))      include_once 'config.hpc';
if (file_exists("config.wallace"))  include_once 'config.wallace';
if (file_exists("config.tdh2"))     include_once 'config.tdh2';
if (file_exists("config.tdh2-hpc")) include_once 'config.tdh2-hpc';
if (file_exists("config.afakes"))   include_once 'config.afakes';

    
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
