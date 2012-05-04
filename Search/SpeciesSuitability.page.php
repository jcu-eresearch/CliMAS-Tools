<?php 
include_once '../../configuration.class.php';
include_once '../../includes.php';
include_once configuration::UtilityClasses();

include_once '../../extras/Object.class.php';
include_once '../DataFinder.class.php';
include_once '../DataDisplay.class.php';
include_once 'ClimateDataFinder.class.php';
include_once 'ClimateDataDisplay.class.php';
include_once '../../MapServer/MapServerImage.class.php';

$F = new ClimateDataFinder();
$F->ParentFolder('/www/eresearch/source'); // TODO:: need to set as Singleton Object
$F->Species(array_util::Value($_GET,'species' ));
$F->LimitModel(array_util::Value($_GET,'model'   ));
$F->LimitScenario(array_util::Value($_GET,'scenario'));


$D = new ClimateDataDisplay();
$D->SpatialBackground("/www/eresearch/source/Australia_states/data/Australia_states.shp");
$D->Finder($F);
$D->Extent( MapServerWrapper::SpatialExtentFromString( urldecode(array_util::Value($_GET,'extent'))) );
$D->Layout();

?>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title><?php echo $D->Title(); ?></title>
    </head>
    <body>
        <h3><?php echo $D->Title(); ?></h3>
        <?php echo $D->Result(); ?>
    </body>
</html>
