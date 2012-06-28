<?php
include_once 'ParameterNames.class.php';
include_once 'configuration.class.php';

$conf = array();

$hostname = trim(exec("hostname --fqdn"));

include_once 'config.default';
include_once 'config.afakes';
include_once 'config.daniel';
include_once 'config.hpc';
include_once 'config.wallace';

    
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
