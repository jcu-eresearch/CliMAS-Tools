<?php
include_once dirname(__FILE__).'/includes.php';
/**
 * Basic Menu to select between AP02- Tools
 *
 *
 */

if (array_util::Value($_GET, "clean") == 'xyz123') DatabaseFile::RemoveUsedFiles();

$pagetitle = "CliMAS tools";
$pagesubtitle = "Tools related to modelling climate change, climate suitability and biodiversity";

?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title><?php echo $pagetitle; ?></title>
    <link href="styles.css" rel="stylesheet" type="text/css">
</head>
<body>

<div class="header clearfix">
    <a href="http://tropicaldatahub.org/"><img class="logo"
        src="../images/TDH_logo_medium.png"></a>
    <h1><?php echo $pagetitle; ?></h1>
    <h2><?php echo $pagesubtitle; ?></h2>
</div>

<?php
    $navSetup = array(
        'tabs' => array(
            'tool index' => 'index.php',
            'Suitability' => 'SpeciesSuitability.php',
            'Biodiversity' => 'biodiversity.php',
            'Reports' => configuration::ReportsUrl(),
        ),
        'current' => 'index.php'
    );
    include 'NavBar.php';
?>

<div class="maincontent">

    <div style="display: inline-block; vertical-align: top; width: 28%; margin-right: 5%">
        <h2><a href="SpeciesSuitability.php">
            CliMAS Suitability
        </a></h2><p>
            See climate suitability maps for individual species, now and in the future.
        </p><p>
            <a href="SpeciesSuitability.php">go to tool &raquo;</a>
        </p>
    </div>

    <div style="display: inline-block; vertical-align: top; width: 28%; margin-right: 5%">
        <h2><a href="biodiversity.php">
            CliMAS Biodiversity
        </a></h2><p>
            See biodiversity (count of species suitable to an area) maps, now and in the future.
        </p><p>
            <a href="biodiversity.php">go to tool &raquo;</a>
        </p>
    </div>

    <div style="display: inline-block; vertical-align: top; width: 28%">
        <h2><a href="<?php configuration::ReportsUrl() ?>">
            CliMAS Reports
        </a></h2><p>
            Create regionally-focussed reports on the future of climate change and biodiversity.
        </p><p>
            <a href="<?php configuration::ReportsUrl() ?>">go to tool &raquo;</a>
        </p>
    </div>

</div>

<?php include 'ToolsFooter.php' ?>
<?php
/*
echo '<pre style="color: white; padding: 2em; opacity: 0.7;">';

$species_id = '19814';
$bucket_count = 3;
$UserLayer = '1990';

$grid_filename_asc = "/tmp/{$UserLayer}_{$species_id}.asc";

$MaxentThreshold = DatabaseMaxent::GetMaxentThresholdForSpeciesFromFile($species_id);
echo "\n\nMaxentThreshold for id {$species_id} is " . $MaxentThreshold;

echo "\n\n";

$ramp = RGB::Ramp($MaxentThreshold, 1, $bucket_count, RGB::ReverseGradient(RGB::GradientYellowOrangeRed()));

echo "Colour ramp is:\n";
foreach ($ramp as $start => $data) {
    echo $start . "\n";
}
// print_r($ramp);

$M = new MapServerWrapper();
$layer = $M->Layers()->AddLayer($grid_filename_asc);

$layer->HistogramBuckets($bucket_count);
$layer->ColorTable($ramp);

$min = $layer->Minimum();
$max = $layer->Maximum();

echo "layer min: {$min} \n";
echo "layer max: {$max} \n\n";

// write out our completed mapfile
$MF = new Mapfile($M);

echo $MF->Text();
echo '</pre>';
*/
?>
</body>
</html>









