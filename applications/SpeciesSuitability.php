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



?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Species Suitability</title>

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

<script type="text/javascript" src="js/jquery-1.7.2.min.js"></script>
<script type="text/javascript" src="js/jquery-ui-1.8.21.custom.min.js"></script>
<script type="text/javascript" src="js/selectMenu.js"></script>
<script type="text/javascript" src="js/Utilities.js"></script>
<script type="text/javascript" src="SpeciesSuitability.js"></script>
<link type="text/css" href="css/start/jquery-ui-1.8.21.custom.css" rel="stylesheet" />
<link type="text/css" href="css/selectMenu.css"     rel="stylesheet" />
<link type="text/css" href="SpeciesSuitability.css" rel="stylesheet" />

<link href="styles.css" rel="stylesheet" type="text/css">

<style>

</style>

</head>
<body>
    <h1 class="pagehead"><a href="index.php"><img src="<?php echo configuration::IconSource()."Suitability.png" ?>" border="0" /></a></h1>

<div class="maincontent">


    <div id="ToolBar" class="ui-widget-header ui-corner-all" >
            <input id="species" value="Type in the species of interest">
    </div>


    <div id="UserSelectionBar" class="ui-widget-content ui-corner-all" >
        
        <h2 style="">DATA</h2>
        <select id="datastyle_selection" onchange="selectDataStyle(this)">
            <option  class="select_datastyle_input" name="DataStyleTools" id="select_datastyle_current" value="CURRENT" checked/>CURRENT</option>
            <option  class="select_datastyle_input" name="DataStyleTools" id="select_datastyle_future"  value="FUTURE" />FUTURE</option>
        </select>
        
        
        <h2 style="">SCENARIO</h2>
        <select id="scenario_selection" onchange="selectScenario(this)">
        <?php
            foreach ($scenarios as $scenario)
            echo '<option  class="select_scenario_input" name="ScenarioTools" id="select_scenario_'.$scenario.'" value="'.$scenario.'" />'.$scenario.'</option>';
        ?>            
        </select>

        <h2 style="">MODEL</h2>
        <select id="model_selection" onchange="selectModel(this)">
        <?php
            foreach ($models as $model)
            {
                $modelname = $model;
                if ($model == "all") $modelname  = "Median";
                
                echo '<option  class="select_model_input" name="ModelTools" id="select_model_'.$model.'" value="'.$model.'" />'.$modelname.'</option>';
            }
                
        ?>            
        </select>
        
        <h2 style="">TIME</h2>
        <select id="time_selection" onchange="selectTime(this)">
        <?php
            foreach ($times as $time)
                echo '<option  class="select_time_input" name="TimeTools" id="select_time_'.$time.'" value="'.$time.'" />'.$time.'</option>';
        ?>            
        </select>
        
        <div id="download_all">
        </div>
        
    </div>

    <div id="MapContainer" class="ui-widget-content" >

        <iframe class="ui-widget-content ui-corner-all"s
                   ID="GUI"
                  src="SpeciesSuitabilityMap.php"
                width="730"
               height="660"
          frameBorder="0"
               border="0"
                 style="margin: 0px; overflow:hidden; float:none; clear:both;"
                onload="map_gui_loaded()"
                 >
        </iframe>


        <FORM METHOD=POST ACTION="<?php echo $_SERVER['PHP_SELF']?>">
            <INPUT TYPE="HIDDEN" ID="ZoomFactor" NAME="ZoomFactor" VALUE="2">
            <INPUT TYPE="HIDDEN" ID="UserLayer"  NAME="UserLayer"  VALUE="">
            <INPUT TYPE="HIDDEN" ID="SpeciesID"  NAME="SpeciesID"  VALUE="">
            <INPUT TYPE="HIDDEN" ID="MaxentThreshold"  NAME="MaxentThreshold"  VALUE="">
        </FORM>

    </div>

    <div id="MapTools" class="ui-widget-content ui-corner-all">
        <button class="MapTool" id="ToolFullExtent" onclick="SetFullExtent();" >Reset Map</button>
        <input  class="MapTool" name="MapsTools" type="radio" id="ToolZoomOut"  onclick="SetZoom(this,-2.0);"                   /><label for="ToolZoomOut">Zoom Out</label>
        <input  class="MapTool" name="MapsTools" type="radio" id="ToolCentre"   onclick="SetZoom(this,1.0)"                     /><label for="ToolCentre" >Centre</label>
        <input  class="MapTool" name="MapsTools" type="radio" id="ToolZoomIn"   onclick="SetZoom(this,2.0)"   checked="checked" /><label for="ToolZoomIn" >Zoom In</label>
    </div>
    
    <br style="clear: both; float:none;">
    
    <div id="information" class="ui-widget-content ui-corner-all" >

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

<div class="credits">
    <a href="http://www.jcu.edu.au/ctbcc/">
        <img src="../images/ctbcc_sm.png" alt="Centre for Tropical Biodiversity and Climate Change">
    </a>
    <a href="http://www.tyndall.ac.uk/">
        <img src="../images/themenews_logo.jpg" alt="Tyndall Centre for Climate Change Research">
    </a>
    <a href="http://www.jcu.edu.au">
        <img src="../images/jcu_logo_sm.png" alt="JCU Logo">
    </a>
    <a href="http://eresearch.jcu.edu.au/">
        <img src="../images/eresearch.png" alt="eResearch Centre, JCU">
    </a>
</div>


<div class="footer">
    <p class="contact">
        please contact Jeremy VanDerWal
        (<a href="mailto:jeremy.vanderwal@jcu.edu.au">jeremy.vanderwal@jcu.edu.au</a>)
        with any queries.
    </p>
</div>

<div id="messages_container" style="height:0px; width:0px;"></div>

</body>
</html>
