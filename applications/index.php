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
            'Reports' => '/bifocal',
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
        <h2><a href="/bifocal">
            CliMAS Reports
        </a></h2><p>
            Create regionally-focussed reports on the future of climate change and biodiversity.
        </p><p>
            <a href="/bifocal">go to tool &raquo;</a>
        </p>
    </div>

</div>

<?php include 'ToolsFooter.php' ?>

<pre style="color: white; padding: 2em;">
<?php
    $species[] = 'Casuarius_casuarius';
    $species[] = '19814';

    foreach ($species as $species_id) {
        $MaxentThreshold = DatabaseMaxent::GetMaxentThresholdForSpeciesFromFile($species_id);
        echo "\n\nMaxentThreshold for id {$species_id} is " . $MaxentThreshold;
    }

echo "\n\n";

$bucket_count = 3;
$ramp = RGB::Ramp($MaxentThreshold, 1, $bucket_count, RGB::ReverseGradient(RGB::GradientYellowOrangeRed()));

print_r($ramp);

?>
</pre>

</body>
</html>









