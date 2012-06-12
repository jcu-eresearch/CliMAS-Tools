<?php
include_once 'Command.configuration.class.php';

include_once CommandConfiguration::UtilityClasses();
include_once CommandConfiguration::FinderClasses();

$cf = CommandConfiguration::CommandClassesFolder();

include_once $cf.'Command.class.php';
include_once $cf.'CommandAction.class.php';
include_once $cf.'CommandUtil.class.php';
include_once $cf.'Command.factory.class.php';

include_once $cf.'SpeciesMaxent/SpeciesMaxent.includes.php';
include_once $cf.'PackageDatafiles/PackageDatafiles.includes.php';

?>
