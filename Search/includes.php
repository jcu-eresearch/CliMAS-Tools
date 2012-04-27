<?php
session_start();
include_once 'configuration.class.php';

// include interfaces here
include_once 'Finders/DataFinder.class.php';
include_once 'Finders/DataDisplay.class.php';


include_once configuration::UtilityClasses();
include_once 'extras/Object.class.php';
include_once 'extras/RGB.class.php';
include_once 'extras/SpatialExtent.class.php';
include_once 'datas/VisualText.class.php';

include_once 'MapServer/MapServerWrapper.class.php';

?>
