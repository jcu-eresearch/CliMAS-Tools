<?php
include_once dirname(__FILE__).'/includes.php';
/**
 * Basic Menu to select between AP02- Tools
 *
 *
 */

if (array_util::Value($_GET, "clean") == 'xyz123') DatabaseFile::RemoveUsedFiles();

$pagetitle = "Biodiversity tools";
$pagesubtitle = "Tools related to modelling climate change, climate suitability and biodiversity";

?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title><?php echo $pagetitle; ?></title>
    <link href="styles.css" rel="stylesheet" type="text/css">

    <script type="text/javascript" >
    <?php
     ?>

    </script>

        <script type="text/javascript" src="SpeciesScenarioTimeline.js"></script>

    <style>
    </style>

    <script>

    $(document).ready(function(){
    });

    </script>

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
        ),
        'current' => 'index.php'
    );
    include 'NavBar.php';
?>

<div class="maincontent">

    <div style="display: inline-block; vertical-align: top; width: 28%; margin-right: 5%">
        <h2><a href="SpeciesSuitability.php">
            Species Suitability
        </a></h2><p>
            See climate suitability maps for individual species, now and in the future.
        </p><p>
            <a href="SpeciesSuitability.php">go to tool &raquo;</a>
        </p>
    </div>

    <div style="display: inline-block; vertical-align: top; width: 28%; margin-right: 5%">
        <h2><a href="biodiversity.php">
            Biodiversity
        </a></h2><p>
            See biodiversity (count of species suitable to an area) maps, now and in the future.
        </p><p>
            <a href="biodiversity.php">go to tool &raquo;</a>
        </p>
    </div>

    <div style="display: inline-block; vertical-align: top; width: 28%">
        <h2><a href="../bifocal">
            BIFOCCAL Reports
        </a></h2><p>
            Create regionally-focussed reports on the future of climate change and biodiversity.
        </p><p>
            <a href="../bifocal">go to tool &raquo;</a>
        </p>
    </div>

</div>

<?php include 'ToolsFooter.php' ?>

</body>
</html>
