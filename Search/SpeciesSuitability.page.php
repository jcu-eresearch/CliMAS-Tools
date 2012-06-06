<?php 
include_once 'includes.php';

$F = new ClimateDataFinder();
$F->ParentFolder('/eresearch/'); //TODO:: need to set as Singleton Object
$F->Species(array_util::Value($_GET,'species' ));
$F->LimitModel(array_util::Value($_GET,'model'   ));
$F->LimitScenario(array_util::Value($_GET,'scenario'));

$D = new ClimateDataDisplay();
$D->SpatialBackground("/eresearch/Australia_states/Australia_states.shp");
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
