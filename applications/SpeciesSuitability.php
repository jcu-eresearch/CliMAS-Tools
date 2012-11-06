<?php
/**
 * Main page for Species Suitability tool
 *
 *
 *
 */
session_start();
include_once dirname(__FILE__).'/includes.php';

unset($_SESSION['map_path']);
Session::ClearLayers();

$scenarios = DatabaseClimate::GetScenarios();
$models    = DatabaseClimate::GetModels();
$times     = DatabaseClimate::GetTimes();

$scenarios = array_flip($scenarios);
$models    = array_flip($models);
$times     = array_flip($times);

unset($scenarios['CURRENT']);
foreach (explode(",","SRESA1B,SRESA1FI,SRESA2,SRESB1,SRESB2") as $remove)  unset($scenarios[$remove]);


unset($models['CURRENT']);
unset($models['ALL']);

$models['all'] = "Median";

unset($times['1990']);
unset($times['1975']);

$scenarios = array_flip($scenarios);
$models    = array_flip($models);
$times     = array_flip($times);

sort($scenarios);
sort($models);
sort($times);

if (array_key_exists('page', $_GET)) {
    $page = $_GET['page'];
} else {
    $page = null;
}

$pagetitle = "Biosuitability";
$pagetitle = "The Suitability Map Tool";
$pagesubtitle = "Vertebrate distributions based on climate suitability";

?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <link rel="shortcut icon" href="<?php echo configuration::IconSource(); ?>favicon-trans.png" />
    <title><?php echo $pagetitle; ?></title>
<script>
// GLOBAL VARIABLES
<?php
echo htmlutil::AsJavaScriptSimpleVariable(configuration::ApplicationFolderWeb(),'ApplicationFolderWeb');

echo htmlutil::AsJavaScriptSimpleVariable(configuration::Maxent_Species_Data_folder_web(),'Maxent_Species_Data_folder_web');


        $species_taxa_data = matrix::Load(configuration::SourceDataFolder()."species_to_id.txt", ",");
        echo htmlutil::AsJavaScriptObjectArray($species_taxa_data,"name","id","availableSpecies");

echo htmlutil::AsJavaScriptArray($scenarios,'scenarios');
echo htmlutil::AsJavaScriptArray($models,   'models');
echo htmlutil::AsJavaScriptArray($times,    'times');
echo htmlutil::AsJavaScriptSimpleVariable(configuration::IconSource(),'IconSource');
?>
</script>

<link type="text/css" href="css/start/jquery-ui-1.8.21.custom.css" rel="stylesheet" />
<link type="text/css" href="css/selectMenu.css"     rel="stylesheet" />
<link type="text/css" href="SpeciesSuitability.css" rel="stylesheet" />

<link href="styles.css" rel="stylesheet" type="text/css">

<link rel="stylesheet" href="http://cdn.leafletjs.com/leaflet-0.4/leaflet.css" />
<!--[if lte IE 8]>
    <link rel="stylesheet" href="http://cdn.leafletjs.com/leaflet-0.4/leaflet.ie.css" />
<![endif]-->
<script src="http://cdn.leafletjs.com/leaflet-0.4/leaflet.js"></script>

<script type="text/javascript" src="js/jquery-1.7.2.min.js"></script>
<script type="text/javascript" src="js/jquery-ui-1.8.21.custom.min.js"></script>
<script type="text/javascript" src="js/selectMenu.js"></script>
<script type="text/javascript" src="js/Utilities.js"></script>
<script type="text/javascript" src="SpeciesSuitability.js"></script>

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
            'map tool' => 'SpeciesSuitability.php',
            'about the map tool' => 'SpeciesSuitability.php?page=about',
            'using the tool' => 'SpeciesSuitability.php?page=using',
            'the science' => 'SpeciesSuitability.php?page=science',
            'credits' => 'SpeciesSuitability.php?page=credits'
        ),
        'current' => 'SpeciesSuitability.php' . ( ($page) ? ('?page=' . $page) : '' )
    );
    include 'NavBar.php';
?>

<?php if ($page == 'about') { // ============================================== ?>

<div class="maincontent">
    <?php include 'SpeciesSuitability-about.html'; ?>
</div>

<?php } else if ($page == 'using') { // ======================================= ?>

<div class="maincontent">
    <?php include 'SpeciesSuitability-using.html'; ?>
</div>


<?php } else if ($page == 'science') { // ===================================== ?>

<div class="maincontent">
    <?php include 'SpeciesSuitability-science.html'; ?>
</div>


<?php } else if ($page == 'credits') { // ===================================== ?>

<div class="maincontent">
    <?php include 'SpeciesSuitability-credits.html'; ?>
</div>

<?php } else { // ============================================================= ?>

<div class="maincontent">

    <div id="ToolBar">
            <input id="species" placeholder="Type in the species of interest">
    </div>

    <div id="leafletmap"></div>

    <div id="UserSelectionBar" class="formsection">

        <div class="onefield">
            <h2>Species data</h2>
            <a id="download_all" target="_blank" href="#" class="disabled">download species data</a>
        </div>

        <div class="onefield">
            <h2>View on map</h2>
            <select id="datastyle_selection" onchange="selectDataStyle(this)">
                <option  class="select_datastyle_input" name="DataStyleTools" id="select_datastyle_current" value="CURRENT" checked/>current</option>
                <option  class="select_datastyle_input" name="DataStyleTools" id="select_datastyle_future"  value="FUTURE" />future</option>
            </select>
        </div>

        <div class="onefield">
            <h2>Scenario</h2>
            <select id="scenario_selection" onchange="selectScenario(this)">
            <?php
                foreach ($scenarios as $scenario)
                echo '<option selected="selected" class="select_scenario_input" name="ScenarioTools" id="select_scenario_'.$scenario.'" value="'.$scenario.'" />'.$scenario.'</option>';
            ?>
            </select>
        </div>

        <div class="onefield">
            <h2>Model</h2>
            <select id="model_selection" onchange="selectModel(this)">
            <?php
                foreach ($models as $model)
                {
                    $modelname = $model;
                    if ($model == "all") $modelname  = "best estimate";

                    echo '<option  class="select_model_input" name="ModelTools" id="select_model_'.$model.'" value="'.$model.'" />'.$modelname.'</option>';
                }

            ?>
            </select>
        </div>

        <div class="onefield">
            <h2>Time</h2>
            <select id="time_selection" onchange="selectTime(this)">
            <?php
                foreach ($times as $time)
                    echo '<option selected="selected" class="select_time_input" name="TimeTools" id="select_time_'.$time.'" value="'.$time.'" />'.$time.'</option>';
            ?>
            </select>
        </div>

    </div>

    <br style="clear: both;"><br><br><hr>

    <div id="information">

        <iframe  class=""
                    ID="information_content"
                   src="SpeciesSuitabilityInformation.php"
                 width="760"
                height="300"
           frameBorder="0"
                border="0"
                onload="map_gui_loaded()"
               >
        </iframe>

    </div>

</div>

<div id="messages_container" style="height:0px; width:0px;"></div>

<?php } // ==================================================================== ?>
<?php include 'ToolsFooter.php' ?>

</body>
</html>
