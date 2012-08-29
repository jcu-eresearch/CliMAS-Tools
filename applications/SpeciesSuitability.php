<?php
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
    
    userSelectedLayer();
}

function currentQuickLook()
{
    var result =    Maxent_Species_Data_folder_web 
                  + currentSpeciesID + '/'
                  + 'output/'
                  + currentCombination 
                  + '.png';
              
    return result;
}

function currentAsciiGrid()
{
    var result =    Maxent_Species_Data_folder 
                  + currentSpeciesID + '/' +
                  + 'output/' + 
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


function setMapOverview()
{
    var html = '<img style="width: 100%; height="130%" src="'+ currentQuickLook() +'">';
    
    $('#map_overview').html(html);
    
}


function toggleSpeciesData(src)
{
    // gets the data area
    var dataID = src.id.toString().replace('species_header_for_','species_data_for_');


    var options = {};
    $( "#" + dataID ).toggle( 'blind', options, 500 );

    $(src).toggleClass("ui-selected");


}

function postAddSpecies(data)
{

    var species_id = data['species_id'];
    //postAddSpeciesScenarioModels(species_id,data);

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

function postAddSpeciesScenarioModels(species_id,scenarioModelsStr,data)
{

    // data - all the fileid's that we are goiong to look at '

    var div = "";
    var div_header = "";
    var div_content = "";
    var div_timeline = "";



    var timesStr = "";

    var timeStrValues = null;

    var firstScenarioModelTime = "";  // holds the id of the quikclook image for the first time zone from the scenario model

    var selected = "";

    var firstTimeName = '';
    var firstAsciiGridID = '';
    var firstFullname = '';

    var sm = '';

    var scenario = '';
    var model = '';


    var data =  string2Array(Value(data['data']), "~");


    for (s = 0; s < data.length;  s++)
    {
        var smd = string2Array(Value(data[s]),'!');

        var scenario_model = string2Array(smd[0],'_');

        var scenario = scenario_model[0];
        var model = scenario_model[1];

        postAddSpeciesDisplaySingleScenarioModel(species_id,scenario,model,smd);

    }

        scenario_model = scenarios_models[s]; // get the scenario model pair


        timeStrValues = null;
        timesStr = "";

        firstScenarioModelTime = ''; // QuickLookID
        firstTimeName = '';

        firstAsciiGridID = '';
        firstFullname = '';

        sm = string2Array(scenario_model, '_');

        scenario = sm[0];
        model = sm[1];


        // get the timeline from data for this scenario_model
        for (d in data)
        {




            if (d.indexOf(scenarioModelname) != -1)
            {
                // here should be a time elemnt of the current scenaro_model

                timeStrValues = string2Array(data[d], '_');


                selected = "";
                if (firstScenarioModelTime == '')
                {
                    firstTimeName = timeStrValues[3];
                    firstScenarioModelTime = timeStrValues[4]; // set first time point
                    firstAsciiGridID = timeStrValues[5];
                    firstFullname = timeStrValues[6];
                    selected = ' checked="checked"  ';
                }

                timesStr += '<input onClick="scenarioModelSelectedButtonSet(this)" class="radio_'+speciesID + "_"+scenarioModelname+'" '+selected+' type="radio" name="radio_'+speciesID + "_"+scenarioModelname+'" id="'+data[d]+'" /><label class="time_radio" for="'+data[d]+'">'+ timeStrValues[3]+'</label>';

            }


        }


        var loading_img_src = IconSource +'wait.gif';
        var loading_msg = '<div id="loading_'+firstAsciiGridID +'"><img style="margin-left: 70px; margin-top: 50px; width:100px; height=100px;" src="'+loading_img_src+'"></div>';


        var firstImageSrc = ApplicationFolderWeb + 'applications/file.php?id=' + firstScenarioModelTime;

        div_header = '<div id="'+speciesID + '_' +scenarioModelname+'_image_header" style="padding:3px; height: 24px; float:none; clear: both; background-color: black; color: white;" >' + sm[0] + "&nbsp;&nbsp;&nbsp;&nbsp;" + sm[1] + '</div>';

        div_content = '<div id="'+speciesID + '_' +scenarioModelname+'_image_container" style="margin-left: 80px; height: 220px; float:none; clear: both;  overflow: hidden; " >'+loading_msg+'<img  onload="layerImageLoaded(\''+firstAsciiGridID +'\')"   id="'+speciesID + '_' +scenarioModelname+'_'+ firstTimeName +'_image" style="width: 70%; height: 300px;" src="'+firstImageSrc+'"></div>';

        div_timeline = '<div id="'+speciesID + '_' +scenarioModelname+'_times" style="margin-left: 1px; height: 40px;float:none; clear: both;" >'+timesStr+'</div>';

        div  = '<div class="scenaro_model_container" style="width: 100%; height: 280px;" >';
        div += div_header + div_content + div_timeline;
        div += '</div>';


        $('#'+species_data_id).append(div);

        $('#'+speciesID + '_' +scenarioModelname+'_times').buttonset();

        $('#'+speciesID + '_' +scenarioModelname+'_image_header').addClass("ui-corner-all");



        $('.time_radio')
            .css("font-size","0.8em");

        var firstImageId = speciesID + '_' +scenarioModelname+'_'+ firstTimeName +'_image';
        // tie data to first image
        $('#' + firstImageId).data("speciesID",speciesID);
        $('#' + firstImageId).data("AsciiGridID",firstAsciiGridID);
        $('#' + firstImageId).data("FullName",firstFullname);


        $('#'+firstImageId).data("scenario",  sm[0]);
        $('#'+firstImageId).data("model",     sm[1]);
        $('#'+firstImageId).data("time",      firstTimeName);

        $('#' + firstImageId).click(function() {userSelectedLayer(this); return false;})






}


function postAddSpeciesDisplaySingleScenarioModel(species_id,scenario,model,smd)
{


        var times_available = string2Array(smd[1],',');

        for (t = 0; t < time_available.length;  t++)
        {

            var time_available = string2Array(times_available[t],'=');

            var time = time_available[0];
            var available = time_available[1];



        }

        var prefix = species_id+'_'+scenario+'_'+model;


        var loading_img_src = IconSource +'wait.gif';
        var loading_msg = '<div id="loading_'+firstAsciiGridID +'"><img style="margin-left: 70px; margin-top: 50px; width:100px; height=100px;" src="'+loading_img_src+'"></div>';


        div_header = '<div id="'+prefix+'_image_header" style="padding:3px; height: 24px; float:none; clear: both; background-color: black; color: white;" >' + scenario + "&nbsp;&nbsp;&nbsp;&nbsp;" + model + '</div>';

        div_content = '<div id="'+prefix+'_image_container" style="margin-left: 80px; height: 220px; float:none; clear: both;  overflow: hidden; " >'+loading_msg+'<img  onload="layerImageLoaded(\''+firstAsciiGridID +'\')"   id="'+speciesID + '_' +scenarioModelname+'_'+ firstTimeName +'_image" style="width: 70%; height: 300px;" src="'+firstImageSrc+'"></div>';

        div_timeline = '<div id="'+prefix+'_times" style="margin-left: 1px; height: 40px;float:none; clear: both;" >'+timesStr+'</div>';

        div  = '<div class="scenaro_model_container" style="width: 100%; height: 280px;" >';
        div += div_header + div_content + div_timeline;
        div += '</div>';


        $('#'+species_data_id).append(div);

        $('#'+speciesID + '_' +scenarioModelname+'_times').buttonset();

        $('#'+speciesID + '_' +scenarioModelname+'_image_header').addClass("ui-corner-all");



        $('.time_radio')
            .css("font-size","0.8em");





}



function scenarioModelSelectedButtonSet(src)
{

    var id = src.id.toString();

    var values = id.split("_");  // speciesID_scenario_model_time_QuickLookFileID_AsciiGridFileID

    var speciesID = values[0];
    var scenario  = values[1];
    var model     = values[2];
    var time      = values[3];
    var QuickLookFileID = values[4];
    var AsciiGridFileID = values[5];
    var FullName = values[6];

    // I want to replace the src element of speciesID + '_' +scenarioModelname+'_image  with   this  QuickLookFileID
    var newImageSrc = ApplicationFolderWeb + 'applications/file.php?id=' + QuickLookFileID;

    var imageHolder = speciesID + '_' + scenario + "_" + model +'_image_container';

    var newImageID = speciesID + '_' + scenario + "_" + model + '_'+ time + '_image';


    $('#' + imageHolder).children().hide();

    if (exists('#'+newImageID))
    {
        $('#' + newImageID).show(); // show this one
    }
    else
    {

        var loading_img_src = IconSource +'wait.gif';

        var loading_msg = '<div id="loading_'+AsciiGridFileID +'"><img style=" margin-left: 70px; margin-top: 50px;  width:100px; height=100px;" src="'+loading_img_src+'"></div>';

        var div = loading_msg + '<img  onload="layerImageLoaded(\''+AsciiGridFileID +'\')"    id="'+newImageID + '" style=" width: 70%; height: 300px;" src="'+newImageSrc+'">';

        // setup image onloaded  -
        $('#' + imageHolder).append(div);

        // tie data to image
        $('#' + newImageID).data("speciesID",speciesID);
        $('#' + newImageID).data("AsciiGridID",AsciiGridFileID);
        $('#' + newImageID).data("FullName",FullName);
        $('#' + newImageID).data("scenario",  scenario);
        $('#' + newImageID).data("model",     model);
        $('#' + newImageID).data("time",      time);


        $('#' + newImageID).click(function() {userSelectedLayer(this); return false;})

    }



}

function layerImageLoaded(AsciiGridFileID)
{
    // happens once the image is loaded
    $('#loading_' + AsciiGridFileID).remove();  // remove "Loading"
}



function userSelectedLayer()
{

    setMapOverview();

    // data needs to be posted at the mapserver
    $("#UserLayer").val(currentCombination);    // file_id of grid file - sets the fileid to be posted at map server
    $("#SpeciesID").val(currentSpeciesID);


    var offset = $('#GUI').offset();
    var guiHeight = $('#GUI').height();
    var guiWidth = $('#GUI').width();

    var map_loading_div = '<div id="MLD">Loading '
                        + currentSpeciesName
                        + '<br><img style="width:100%; height:40%;" src="'+IconSource +'Loading.gif"></div>';


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

        <div id="ColorKeyContainer" ><?php echo RGB::RampDisplay($ramp); ?></div>

        <FORM METHOD=POST ACTION="<?php echo $_SERVER['PHP_SELF']?>">
            <INPUT TYPE="HIDDEN" ID="ZoomFactor" NAME="ZoomFactor" VALUE="2">
            <INPUT TYPE="HIDDEN" ID="UserLayer"  NAME="UserLayer"  VALUE="">
            <INPUT TYPE="HIDDEN" ID="SpeciesID"  NAME="SpeciesID"  VALUE="">
        </FORM>

    </div>

    <div id="InfoBar" class="ui-widget-header ui-corner-all" >
        <div id="map_overview" class="ui-widget-content ui-corner-all" >
            
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
