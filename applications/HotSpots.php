<?php
session_start();
include_once dirname(__FILE__).'/includes.php';

$ramp = RGB::Ramp(0, 1, 100,RGB::ReverseGradient(RGB::GradientYellowOrangeRed()));

?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Species Suitability</title>

    <link type="text/css" href="css/start/jquery-ui-1.8.21.custom.css" rel="stylesheet" />
    <link type="text/css" href="css/selectMenu.css" rel="stylesheet" />
    <script type="text/javascript" src="js/jquery-1.7.2.min.js"></script>
    <script type="text/javascript" src="js/jquery-ui-1.8.21.custom.min.js"></script>
    <script type="text/javascript" src="js/selectMenu.js"></script>
    <script type="text/javascript" src="js/Utilities.js"></script>

    <link href="styles.css" rel="stylesheet" type="text/css">
    <link href="HotSpots.css" rel="stylesheet" type="text/css">
    
    <script type="text/javascript" >
    <?php     
        echo htmlutil::AsJavaScriptSimpleVariable(configuration::ApplicationFolderWeb(),'ApplicationFolderWeb');
        echo htmlutil::AsJavaScriptObjectArray(SpeciesData::speciesList(),"full_name","species_id","availableSpecies");    
        echo htmlutil::AsJavaScriptSimpleVariable(configuration::IconSource(),'IconSource');
     ?>
    </script>
    
    <script type="text/javascript" src="HotSpots.js"></script>

</head>
<body>
<?php    include_once 'ToolsHeader.php'; ?>

<div class="thecontent">
    
    <div id="toolbar" class="ui-widget-header ui-corner-all" >
        <div class="ToolBarItem" >
            Tool 1  
        </div>
        <div class="ToolBarItem" >
            Tool 2 
        </div>
        <div class="ToolBarItem" >
            Tool 3  
        </div>
        <div class="ToolBarItem" >
            Tool 4  
        </div>
        <div class="ToolBarItem" >
            Tool 5  
        </div>
    </div>

    <div id="Middle" class="ui-widget-content ui-corner-all" >
        
        <div class="ui-widget-content ui-corner-all" >
            <div id="MapContainer" class="ui-widget-content" >

                <div id="ToolBar" class="ui-widget-header ui-corner-all" >
                    <div id="MapTools">
                        <button id="ToolFullExtent" onclick="SetFullExtent();" >Reset Map</button>
                        <input name="MapsTools" type="radio" id="ToolZoomOut"  onclick="SetZoom(this,-2.0);"                   /><label for="ToolZoomOut">Zoom Out</label>
                        <input name="MapsTools" type="radio" id="ToolCentre"   onclick="SetZoom(this,1.0)"                     /><label for="ToolCentre" >Centre</label>
                        <input name="MapsTools" type="radio" id="ToolZoomIn"   onclick="SetZoom(this,2.0)"   checked="checked" /><label for="ToolZoomIn" >Zoom In</label>
                        <span id="CurrentSpecies"></span>
                    </div>

                </div>

                <iframe class       = ""  
                        ID          = "GUI" 
                        src         = "HotSpotsMap.php" 
                        width       = "100%" 
                        height      = "100%" 
                        frameBorder = "0" 
                        border      = "0" 
                        style       = "margin: 0px; overflow:hidden; float:none; clear:both;" 
                        onload      = "map_gui_loaded()"
                </iframe>

                <FORM METHOD=POST ACTION="<?php echo $_SERVER['PHP_SELF']?>">
                    <INPUT TYPE="HIDDEN" ID="ZoomFactor" NAME="ZoomFactor" VALUE="2">
                    <INPUT TYPE="HIDDEN" ID="UserLayer"  NAME="UserLayer"  VALUE="">
                    <INPUT TYPE="HIDDEN" ID="SpeciesID"  NAME="SpeciesID"  VALUE="">
                </FORM>

            </div>
                
        </div>

    </div>


</div>
    
    
    
<div class="maincontent">
    
<div id="lhs" class="ui-widget-content" >


    <div id="MapLayers" class="ui-widget-content ui-corner-all" >
        <div id="SpeciesBar" class="ui-widget-header ui-corner-all" >
            <input id="species" value="">
        </div>
        <div id="species_data" class="ui-widget-content ui-corner-all" >
        </div>            

    </div>

</div>
    
</div>
    
<?php    include_once 'ToolsFooter.php'; ?>    

</body>
</html>
