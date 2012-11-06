<?php
/**
 * Hotspots / Species Richness Tool
 *
 *
 */
session_start();
include_once dirname(__FILE__).'/includes.php';
if (php_sapi_name() == "cli") return;

$cmd = htmlutil::ValueFromGet('cmd',''); // if we have a command_id on the url then they have returned.

$possibleNames = array(
    "Biowealth",
    "( ̲:̲̅:̲̅:̲̅[̲̅ ̲̅]̲̅:̲̅:̲̅:̲̲̅̅)",
    "Biomaps",
    "Biodiversity",
    "Wilson",
    "Species richness",
    "&Sigma;sp",
    "&Sigma;spp",
    "&Sigma;pecies",
    "&Sigma;Species"
);

$pagetitle = $possibleNames[ array_rand($possibleNames) ];
$pagetitle = "The Biodiversity Map Tool";
$pagesubtitle = "Visualising biodiversity across Australia";

?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title><?php echo $pagetitle; ?></title>
    <script type="text/javascript" src="js/jquery-1.7.2.min.js"></script>
    <script type="text/javascript" src="js/jquery-ui-1.8.21.custom.min.js"></script>
    <script type="text/javascript" src="js/jquery.pulse.min.js"></script>
    <script type="text/javascript" src="js/selectMenu.js"></script>
    <script type="text/javascript" src="js/Utilities.js"></script>

    <link type="text/css" href="css/start/jquery-ui-1.8.21.custom.css" rel="stylesheet" />
    <link type="text/css" href="css/selectMenu.css" rel="stylesheet" />
    <link type="text/css" href="styles.css"         rel="stylesheet" />
    <link type="text/css" href="HotSpots.css"       rel="stylesheet" />

    <script type="text/javascript" >
    <?php

        $exclude_word_list = file(configuration::SourceDataFolder()."exclude_word_list.txt");

        echo htmlutil::AsJavaScriptSimpleVariable(configuration::ApplicationFolderWeb(),'ApplicationFolderWeb');

        echo htmlutil::AsJavaScriptSimpleVariable(configuration::Maxent_Species_Data_folder_web().'richness/' ,'richness_folder');

        echo htmlutil::AsJavaScriptArrayFromFile(configuration::SourceDataFolder()."clazz_list.txt",'availableTaxa',true);

        echo htmlutil::AsJavaScriptArrayFromFile(configuration::SourceDataFolder()."family_list.txt",'availableFamily',true);

        echo htmlutil::AsJavaScriptArrayFromFile(configuration::SourceDataFolder()."genus_list.txt",'availableGenus',true,$exclude_word_list);

        $species_taxa_data = matrix::Load(configuration::SourceDataFolder()."species_to_id.txt", ",");
        echo htmlutil::AsJavaScriptObjectArray($species_taxa_data,"name","id","availableSpecies");

        echo htmlutil::AsJavaScriptSimpleVariable(configuration::IconSource(),'IconSource');

        echo htmlutil::AsJavaScriptSimpleVariable($cmd,'cmd');

        echo htmlutil::AsJavaScriptSimpleVariable(configuration::Maxent_Species_Data_folder_web(),'Maxent_Species_Data_folder_web');


     ?>


    </script>

    <script type="text/javascript" src="HotSpots.js"></script>

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
            'quick map' => 'biodiversity.php',
            'custom map' => 'HotSpots.php',
            'about the map tool' => 'biodiversity.php?page=about',
            'using the tool' => 'biodiversity.php?page=using',
            'the science' => 'biodiversity.php?page=science',
            'credits' => 'biodiversity.php?page=credits'
        ),
        'current' => 'HotSpots.php'
    );
    include 'NavBar.php';
?>

<div class="maincontent">

    <div class="formsection" id="tabs-1">
        <h2>Species List</h2>
        <p class="usagetip">
            Select species to include on your
            richness map. Select a class, family or genus to
            include all species from that taxon.
        </p>
        <form id="InputsSearchBar">
            <div id="InputTypesSet">
                <p>Search by:
                    <label><input type="radio" id="InputTypeTaxa"     name="InputTypes" />Class</label>
                    <label><input type="radio" id="InputTypeFamily"   name="InputTypes" />Family</label>
                    <label><input type="radio" id="InputTypeGenus"    name="InputTypes" />Genus</label>
                    <label><input type="radio" id="InputTypeSpecies"  name="InputTypes" />Species</label>
                </p>
                <!--  <input type="radio" id="InputTypeLocation" name="InputTypes" /><label for="InputTypeLocation">Location</label> -->
                <input type="input" id="InputText" name="InputText">
            </div>
        </form>

        <ul id="TaxaSelection"     class="UserInputs"></ul>
        <ul id="FamilySelection"   class="UserInputs"></ul>
        <ul id="GenusSelection"    class="UserInputs"></ul>
        <ul id="SpeciesSelection"  class="UserInputs"></ul>
        <ul id="LocationSelection" class="UserInputs"></ul>

        <div id="MapContainer" >

            <div id="MapTools">
                <button id="ToolClearAll"   onclick="ClearAll();"      >Clear All</button>
                <button id="ToolFullExtent" onclick="SetFullExtent();" >Reset Map</button>
                <input name="MapsTools" type="radio" id="ToolZoomOut"  onclick="SetZoom(this,-2.0);"                   /><label for="ToolZoomOut">Zoom Out</label>
                <input name="MapsTools" type="radio" id="ToolCentre"   onclick="SetZoom(this,1.0)"                     /><label for="ToolCentre" >Centre</label>
                <input name="MapsTools" type="radio" id="ToolZoomIn"   onclick="SetZoom(this,2.0)"   checked="checked" /><label for="ToolZoomIn" >Zoom In</label>
            </div>

            <iframe ID="GUI" src="HotSpotsMap.php?w=800&h=600" width="820" height="626" frameBorder="0" border="0" style="margin: 0px; overflow:hidden; float:none; clear:both;" onload="map_gui_loaded()"></iframe>
            <FORM id="MapInteractionData" METHOD=POST ACTION="<?php echo $_SERVER['PHP_SELF']?>"><INPUT TYPE="HIDDEN" ID="ZoomFactor" NAME="ZoomFactor" VALUE="2"><INPUT TYPE="HIDDEN" ID="UserLayer"  NAME="UserLayer"  VALUE=""><INPUT TYPE="HIDDEN" ID="SpeciesID"  NAME="SpeciesID"  VALUE=""></FORM>

        </div>
    </div>

    <div class="formsection" id="tabs-3">
        <h2>Select Emission Scenarios</h2>
        <p class="usagetip">
            Select emission scenarios you want to use for future climate modelling.
            Hold the Ctrl button on your keyboard while clicking to select more than one.
        </p>
<!--
        <div class="SelectionToolBar">
            <button id="SelectAllScenarios"  >select all</button>
            <button id="SelectNoneScenarios" >deselect all</button>
            <button id="SelectDefaultScenarios" >Defaults</button>
            <button id="SelectSomeScenarios_RCP" >select RCP*</button>
        </div>
-->
        <ul id="ScenariosSelection" class="selectable">
        <?php
            $liFormat = '<li id="Scenarios_{DataName}"><h4>{DataName}</h4><p>{Description}</p></li>';
            echo DatabaseClimate::GetScenarioDescriptions('RCP')->asFormattedString($liFormat);
        ?>
        </ul>
        <ul class="references">
            <li>references <i>(links to exernal site)</i>&nbsp;&nbsp;</li>
            <?php echo DatabaseClimate::GetScenarioDescriptions('RCP')->asFormattedString('<li><a target="_ref" href="{URI}">{DataName}</a></li>'); ?>
        </ul>

    </div>


    <div class="formsection" id="tabs-4">
        <h2>Select Time Points</h2>
        <p class="usagetip">
            Select years you want to use for future climate modelling.
            Hold the Ctrl button on your keyboard while clicking to select more than one.
        </p>
<!--
        <div class="SelectionToolBar ui-widget-header">
            <button id="SelectAllTimes"  >select all</button>
            <button id="SelectNoneTimes" >deselect all</button>
            <button id="SelectDefaultTimes" >Defaults</button>
        </div>
-->
        <ul id="TimesSelection" class="selectable" >
        <?php
            $liFormat = '<li id="Times_{DataName}" class=" " ><h4>{DataName}</h4><p>{Description}</p> </li>';
            echo DatabaseClimate::GetFutureTimesDescriptions()->asFormattedString($liFormat);
        ?>
        </ul>

    </div>


    <div class="formsection" id="tabs-6">

        <h2>Start Calculation</h2>

        <button id="CreateProcess">Submit for Calculation</button>

<!--
        <div id="CountPanels">
            <div id="CountPanel1" class="CountPanel">
                <div class="Header ui-widget-header">
                    <h1 id="CountInputTotals" >0</h1><h2>User<br>selections</h2>
                </div>
                <div class="Value">
                    <h3 id="CountTaxa" class="Count">0</h3><h4>Taxa</h4>
                </div>
                <div class="Value">
                    <h3 id="CountFamily" class="Count">0</h3><h4>Family</h4>
                </div>
                <div class="Value">
                    <h3 id="CountGenus" class="Count">0</h3><h4>Genus</h4>
                </div>
                <div class="Value">
                    <h3 id="CountSpecies" class="Count" >0</h3><h4>Species</h4>
                </div>
            </div>

            <div id="CountPanel2" class="CountPanel">
                <div class="Header ui-widget-header">
                    <h1 id="CountFutureTotals" >0</h1><h2>Future<br>Datasets</h2>
                </div>

                <div class="Value">
                    <h3 id="CountTimes" >0</h3><h4>Times</h4>
                </div>

            </div>
            <div id="CountPanel4" class="CountPanel">
                <div class="GrandTotal ui-widget-header">
                    <h1 id="CountGrandTotal" >0</h1><h2 >Datasets to<br>Examine</h2>
                </div>

                <br>
                <i>enter a name for your job here</i>
                <input name="job_description" id="job_description" size="20" value="job description" style="width: 100%;">
                <br>
                <div> </div>
            </div>

        </div>
-->

        <br style="clear: both; float: none;">
        <div id="RunningProcessesToolBar" class="ui-widget-header">
            <button id="UpdateProcess">Update Status</button>
        </div>
        <ul id="RunningProcessesTable">

        </ul>

    </div>

</div>
<div id="working" style="display:none;"></div>



<?php    include_once 'ToolsFooter.php'; ?>


</body>
</html>
