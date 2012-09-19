<?php
/**
 * Main page for Species Suitability tool
 * 
 * 
 *  
 */
session_start();
include_once dirname(__FILE__).'/includes.php';

$ramp = RGB::Ramp(0, 1, 100,RGB::ReverseGradient(RGB::GradientYellowOrangeRed()));


$scenarios = DatabaseClimate::GetScenarios();
$models    = DatabaseClimate::GetModels();
$times     = DatabaseClimate::GetTimes();


$scenarios = array_flip($scenarios);
$models    = array_flip($models);
$times     = array_flip($times);

unset($scenarios['CURRENT']);
unset($models['CURRENT']);
unset($models['ALL']);
unset($times['1990']);
unset($times['1975']);

$scenarios = array_flip($scenarios);
$models    = array_flip($models);
$times     = array_flip($times);



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

<style>
#selectable_layers .ui-selecting { background: #FECA40; }
#selectable_layers .ui-selected { background: #F39814; color: white; }
#selectable_layers { list-style-type: none; margin: 0; padding: 0; width: 90%; }
#selectable_layers li { margin: 3px; padding: 0.4em; font-size: 0.8em; height: 90px; }

.ui-autocomplete {
		max-height: 150px;
		overflow-y: auto;
		overflow-x: hidden;
		padding-right: 20px;
	}


.maincontent
{
    width: 100%;
    max-width: 1200px;
    height: 800px;
    overflow: hidden;

}


#ToolBar
{
    padding-top: 4px;
    padding-left: 4px;
    height: 44px;
    width: 1120px;
    float: none;
    clear: both;
}

#MapTools
{
    float:left;
    height: 40px;
}

#CurrentSpecies
{
    float: left;
    width: 200px;
    height: 40px;
}


#ScenarioBar
{
    padding-top: 4px;
    padding-left: 204px;
    height: 44px;
    width: 920px;
    float: none;
    clear: both;
}

#ModelBar
{
    float: left;
    height: 670px;
    width: 200px;
    overflow: hidden;
    overflow-y: auto;
}


#MapContainer
{
    float: left;
    height: 670px;
    width: 710px;
    overflow: hidden;

}

#ColorKeyContainer
{
    width: 100%;
    padding: 0px;
    margin: 0px;
    height: 24px;
    overflow:hidden;
    clear:both;

}

#ColorKey
{
    float: left;
}


#InfoBar
{
    float: left;
    height: 670px;
    width: 204px;
    overflow: hidden;
    overflow-y: auto;
}

#map_overview
{
    width:  200px;
    height: 160px;
    overflow: hidden;
}

#download
{
    width:  200px;
    height: 30px;
    overflow: hidden;
}


#information
{
    width:  200px;
    height: 440px;
    overflow: auto;
}



#TimeBar
{
    padding-top: 4px;
    padding-left: 204px;
    height: 44px;
    width: 920px;
    float: none;
    clear: both;
}



</style>
<script>

// GLOBAL VARIABLES
<?php
echo htmlutil::AsJavaScriptSimpleVariable(configuration::ApplicationFolderWeb(),'ApplicationFolderWeb');

echo htmlutil::AsJavaScriptSimpleVariable(configuration::Maxent_Species_Data_folder_web(),'Maxent_Species_Data_folder_web');

echo htmlutil::AsJavaScriptObjectArray(SpeciesFiles::speciesList(),"full_name","species_id","availableSpecies");

echo htmlutil::AsJavaScriptArray($scenarios,'scenarios');
echo htmlutil::AsJavaScriptArray($models,   'models');
echo htmlutil::AsJavaScriptArray($times,    'times');

echo htmlutil::AsJavaScriptSimpleVariable(configuration::IconSource(),'IconSource');

?>




function enableSelectorButtons()
{
    $('.select_scenario_button').button('enable');
    $('.select_model_button').button('enable');
    $('.select_time_button').button('enable');
    
    $('.select_scenario_input').button('enable');
    $('.select_model_input').button('enable');
    $('.select_time_input').button('enable');
}




function disableSelectorButtons()
{
    $('.select_scenario_button').button('disable');
    $('.select_model_button').button('disable');
    $('.select_time_button').button('disable');
    $('.select_scenario_input').button('disable');
    $('.select_model_input').button('disable');
    $('.select_time_input').button('disable');
}
var currentSpeciesName = '';
var currentSpeciesID = '';
var currentScenario = '';
var currentModel = '';
var currentTime = '';
var currentCombination = '';

function selectScenario(src)
{
    var id = src.id.toString();
    currentScenario = id.replace('select_scenario_','');
    
    setCurrentCombination();
}

function selectModel(src)
{
    var id = src.id.toString();
    currentModel = id.replace('select_model_','');
    setCurrentCombination();

}

function selectTime(src)
{
    var id = src.id.toString();
    currentTime = id.replace('select_time_','');
    
    setCurrentCombination();
}

function setCurrentCombination()
{
    if (currentScenario == '') return;
    if (currentModel == '') return;
    if (currentTime == '') return;
    
    currentCombination = currentScenario + '_' + currentModel + '_' + currentTime;    
    
    updateInformation();
    
    userSelectedLayer();
}

function updateInformation()
{
    $('#information_content').attr('src','SpeciesSuitabilityInformation.php?combination='+currentCombination);
    
}


function currentAsciiGrid()
{
    var result =    Maxent_Species_Data_folder_web 
                  + currentSpeciesID + '/'
                  + 'output/' 
                  + currentCombination + '.asc';
              
    return result;
}


function addSpecies(species_id,speciesName)
{

    var options = {};
    
    currentSpeciesName = speciesName;
    currentSpeciesID = species_id;
    
    enableSelectorButtons();
    
    $('.select_scenario_input').first().click();
    $('.select_model_input').first().click();
    $('.select_time_input').first().click();

    userSelectedLayer();


}

function clearMapOverview()
{
    $('#map_overview').html('');
}


function setMapOverview()
{
    
    var mapa = $("#GUI").contents().find("#mapa");
    
    if ($('#map_overview').html() == "") 
    {
        $('#map_overview').html('<img style="width: 100%; height="130%" src="'+ mapa.attr('src') +'">');    
        
        $('#download')
            .html('<a target="_download" id="download_current_data" href="'+currentAsciiGrid()+'">DOWNLOAD RASTER</a>')
            ;    

        $('#download_current_data')
            .button()
            .css('font-size','0.7em')
            .css('width','97%')
            .css('margin','1%')
            ;    

        
    }
    
    
}



function postAddSpecies(data)
{

    var species_id = data['species_id'];

    var parent_id =  'species_data_for_' +species_id;


    var scenarios = string2Array(Value(data['scenarios']),'~');
    var models    = string2Array(Value(data['models'])   ,'~');
    var times     = string2Array(Value(data['times'])    ,'~');
    var table     = string2Array(Value(data['table'])    ,'~');

    var scenario = '';
    var model    = '';
    var time     = '';
    var cell     = '';

    var tab = 0;


    var div = '';
    var button_text = '';
    for (s = 0; s < scenarios.length;  s++)
    {
        scenario = scenarios[s];

        for (m = 0; m < models.length;  m++)
        {

            model = models[m];

            for (t= 0; t < times.length;  t++)
            {

                time = times[t];
                cell = table[tab];

                button_text = '' + '';

                div = '<button id=""></button>';

                $('#'+parent_id).append(div);


                tab++;

            }

        }


    }

}


function userSelectedLayer()
{
    clearMapOverview();

    // data needs to be posted at the mapserver
    $("#UserLayer").val(currentCombination);    // file_id of grid file - sets the fileid to be posted at map server
    $("#SpeciesID").val(currentSpeciesID);


    var offset = $('#GUI').offset();
    var guiHeight = $('#GUI').height();
    var guiWidth = $('#GUI').width();

    var map_loading_div = '<div id="MLD">Loading '
                        + currentSpeciesName
                        + '<br><img style="width:100%; height:30%;" src="'+IconSource +'Loading.gif"></div>';


    $('#messages_container').append(map_loading_div);

    $('#MLD')
        .width(guiWidth * 0.8).height(90)
        .offset({ top: (offset.top + (guiHeight/2) - ($('#MLD').height() / 2)), left: (offset.left + (guiWidth/2) - ($('#MLD').width() / 2)) })
        .button()
        .fadeIn(200)
        ;

    $('#GUI').contents().find('#MAP_FORM').submit();

}


function GetZoom() {
    document.getElementById('ZoomFactor').value = parent.document.getElementById('ZoomFactor').value;
}


function map_gui_loaded()
{
    if (exists('#MLD'))
    {
        $('#MLD').fadeOut(200).remove();
        $('#MLD').remove();
    }

}



function setTools(src)
{
     $( "#" + src.id.toString() ).toggleClass( "ui-state-active", 100 );

}


function ReloadDiv(divID)
{
    document.getElementById(divID).src = document.getElementById(divID).src
}



function ReloadGUI()
{
    ReloadDiv('GUI') ;
}


function SetZoom(caller,zoom_value) {
    document.getElementById('ZoomFactor').value = zoom_value;
}

function SetFullExtent() {
    ReloadGUI();
}



$(document).ready(function(){

    $( "#species" ).autocomplete({
                        source: availableSpecies,
                        select: function(event, ui)
                        {
                            addSpecies(ui.item.value,ui.item.label);
                            $(this).val(ui.item.label);
                            return false;
                        }
                     });


    $( "#ToolFullExtent")
        .button({ text: false, icons: { primary: "ui-icon-image"  } })
        .height(40)
        ;

    $( "#ToolZoomOut")
        .button({ text: false, icons: { primary: "ui-icon-zoomout"} })
        .click(function() { setTools(this); })
        .height(30)
        ;

    $( "#ToolCentre" )
        .button({ text: false, icons: { primary: "ui-icon-plus"   } })
        .click(function() { setTools(this); })
        .height(30)
        ;


    $( "#ToolZoomIn")
        .button({ text: false, icons: { primary: "ui-icon-zoomin" } })
        .click(function() { setTools(this); })
        .height(30)
        ;

    $("#species")
        .css("padding","4px")
        .css("margin","3px")
        .css("width","700px")
        .css("height","70%")
        .css('font-size','1.2em')
        .blur(function() { $(this).val(currentSpeciesName); return false; })
        .focus(function() { $(this).val(''); return false; })
        ;

    $('#ScenarioBar').buttonset();
    $('#ModelBar').buttonset();
    $('#TimeBar').buttonset();    

    $('.select_model_button')
        .css('width','98%')
        ;


    $('.select_scenario_button').click(function () { selectScenario(this) });
    $('.select_model_button').click(function () { selectModel(this) });
    $('.select_time_button').click(function () { selectTime(this) });
    
    $('.select_scenario_input').click(function () { selectScenario(this) });
    $('.select_model_input').click(function () { selectModel(this) });
    $('.select_time_input').click(function () { selectTime(this) });


    disableSelectorButtons();


});

</script>

</head>
<body>
    <h1 class="pagehead"><a href="index.php"><img src="<?php echo configuration::IconSource()."Suitability.png" ?>" border="0" /></a></h1>

<div class="maincontent">


    <div id="ToolBar" class="ui-widget-header ui-corner-all" >
        <div id="MapTools">
            <button id="ToolFullExtent" onclick="SetFullExtent();" >Reset Map</button>
            <input name="MapsTools" type="radio" id="ToolZoomOut"  onclick="SetZoom(this,-2.0);"                   /><label for="ToolZoomOut">Zoom Out</label>
            <input name="MapsTools" type="radio" id="ToolCentre"   onclick="SetZoom(this,1.0)"                     /><label for="ToolCentre" >Centre</label>
            <input name="MapsTools" type="radio" id="ToolZoomIn"   onclick="SetZoom(this,2.0)"   checked="checked" /><label for="ToolZoomIn" >Zoom In</label>
            <input id="species" value="Species">
        </div>
    </div>

    <div id="ScenarioBar" class="ui-widget-header ui-corner-all" >
        <?php
            foreach ($scenarios as $scenario)
                echo '<input class="select_scenario_input" name="ScenarioTools" type="radio" id="select_scenario_'.$scenario.'"  /><label class="select_scenario_button" for="select_scenario_'.$scenario.'">'.$scenario.'</label>';
        ?>
    </div>

    <div id="ModelBar" class="ui-widget-header ui-corner-all" >
        <?php
            foreach ($models as $model)
                echo '<input class="select_model_input"  type="radio" id="select_model_'.$model.'" name="ModelTools"    /><label class="select_model_button" for="select_model_'.$model.'">'.$model.'</label>';
        ?>
    </div>

    <div id="MapContainer" class="ui-widget-content" >

        <iframe class="ui-widget-content ui-corner-all"s
                   ID="GUI"
                  src="SpeciesSuitabilityMap.php"
                width="730"
               height="640"
          frameBorder="0"
               border="0"
                 style="margin: 0px; overflow:hidden; float:none; clear:both;"
                onload="map_gui_loaded()"
                 >
        </iframe>

        <div id="ColorKeyContainer" >
             <?php 
                $below_threshold = array();
                $below_threshold['below threshold'] = '#000000';
                echo RGB::RampDisplay($ramp,null,null,$below_threshold); 
             ?></div>

        <FORM METHOD=POST ACTION="<?php echo $_SERVER['PHP_SELF']?>">
            <INPUT TYPE="HIDDEN" ID="ZoomFactor" NAME="ZoomFactor" VALUE="2">
            <INPUT TYPE="HIDDEN" ID="UserLayer"  NAME="UserLayer"  VALUE="">
            <INPUT TYPE="HIDDEN" ID="SpeciesID"  NAME="SpeciesID"  VALUE="">
        </FORM>

    </div>

    <div id="InfoBar" class="ui-widget-header ui-corner-all" >
        <div id="map_overview" class="ui-widget-content ui-corner-all" ></div>
        <div id="download" class="ui-widget-content ui-corner-all" ></div>
        <div id="information" class="ui-widget-content ui-corner-all" >
            
            <iframe class="ui-widget-content ui-corner-all"s
                     ID="information_content"
                    src="SpeciesSuitabilityInformation.php"
                  width="198"
                 height="430"
            frameBorder="0"
                 border="0"
                  style="margin: 0px; overflow:hidden; float:none; clear:both;"
                 onload="map_gui_loaded()"
                    >
            </iframe>
            
            
        </div>
    </div>
    
    
    <div id="TimeBar" class="ui-widget-header ui-corner-all" >
        <?php
            foreach ($times as $time)
                echo '<input class="select_time_input"  name="TimeTools" type="radio" id="select_time_'.$time.'"  /><label class="select_time_button" for="select_time_'.$time.'">'.$time.'</label>';
        ?>
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
