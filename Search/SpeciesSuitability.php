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

<link href="styles.css" rel="stylesheet" type="text/css">

<style>
#selectable_species .ui-selecting { background: #FECA40; }
#selectable_species .ui-selected { background: #F39814; color: white; }
#selectable_species { list-style-type: none; margin: 0; padding: 0; width: 95%; }
#selectable_species li { margin: 3px; padding: 0.4em; font-size: 0.8em; height: 18px; }

#selectable_model .ui-selecting { background: #FECA40; }
#selectable_model .ui-selected { background: #F39814; color: white; }
#selectable_model    { list-style-type: none; margin: 0; padding: 0; width: 95%;  }
#selectable_model li { margin: 3px; padding: 0.0em; font-size: 0.8em; height: 24px; width: 45%; overflow:hidden;}

#selectable_scenario .ui-selecting { background: #FECA40; }
#selectable_scenario .ui-selected { background: #F39814; color: white; }
#selectable_scenario    { list-style-type: none; margin: 0; padding: 0; width: 95%; }
#selectable_scenario li { margin: 3px; padding: 0.4em; font-size: 0.8em; height: 24px; width: 45%; overflow:hidden;}

#selectable_time .ui-selecting { background: #FECA40; }
#selectable_time .ui-selected { background: #F39814; color: white; }
#selectable_time    { list-style-type: none; margin: 0; padding: 0; width: 95%; }
#selectable_time li { margin: 3px; padding: 0.4em; font-size: 0.8em; height: 24px; width: 45%; overflow:hidden;}

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


.green
{
    background-color: green;
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
    float: right; 
}

#MutliLayerSelector
{
    width: 100%;
    padding: 0px; 
    margin: 0px; 
    height: 30px; 
    overflow:hidden; 
    clear:both;
}

#species
{
    padding: 0px;
    margin: 0px;
    
}

#lhs
{
    float: none; 
    height: 800px;
    width: 1200px; 
    overflow: hidden;    
    clear:both;
}

#MapContainer
{
    float: left; 
    
    height: 100%; 
    width: 710px; 
    overflow: hidden;
    
}

#MapLayers
{
    float: left; 
    height: 100%; 
    width: 450px; 
    overflow: hidden;    
}


#rhs
{
    float: left; 
    height: 0px; 
    width: 0px; 
    overflow: hidden;    
    
}


.ToolButtonSelected
{
    background-color: red;
}


#ToolBar 
{
    padding-top: 4px;
    padding-left: 4px;
    height: 44px;
}


#SpeciesBar
{
    padding-top: 4px;
    padding-left: 4px;
    height: 6.5%;
    width:100%;
    
}

#species_data
{
    height: 92%;
    width:100%;
    overflow-x: hidden;
    overflow-y: auto;
}




.species_container
{
    
}

.species_header
{
    height: 40px;
    font-size: 0.8em;
    width:100%;
    
}

.species_data
{
    height: 660px;
    overflow: auto;
}


#MapTools
{
    float:left;
    height: 40px;
}


.SpeciesRangeImageContainer
{
    width: 240px;
    height: 200px;
    float: left;
}

.SpeciesRangeImage
{
    width: 100%;
    height: 100%;
    
}


</style>
<script>

// GLOBAL VARIABLES
<?php 

echo htmlutil::AsJavaScriptSimpleVariable(configuration::ApplicationFolderWeb(),'ApplicationFolderWeb');
echo htmlutil::AsJavaScriptSimpleVariable(CommandAction::$EXECUTION_FLAG_RUNNING,'EXECUTION_FLAG_RUNNING');
echo htmlutil::AsJavaScriptSimpleVariable(CommandAction::$EXECUTION_FLAG_COMPLETE,'EXECUTION_FLAG_COMPLETE');
echo htmlutil::AsJavaScriptObjectArray(SpeciesData::speciesList(),"full_name","species_id","availableSpecies");    
echo htmlutil::AsJavaScriptSimpleVariable(configuration::IconSource(),'IconSource');

?>

var speciesDataHeight = 400;


function string2Array(str,delim)
{
    
    var result = new Array();
    if (str.indexOf(delim) == -1 )
    {
        result[0] = str;
    }
    else
    {
        result = str.split(delim);
    }
    
    return result;
}


function GetZoom() {
    document.getElementById('ZoomFactor').value = parent.document.getElementById('ZoomFactor').value;
}
    

function exists(selector)
{
    if ( $(selector).length ) return true;
    return false;
}


function flash_selector(selector,from_color,to_color)
{
    $(selector).stop().css("background-color", from_color).
    animate({ backgroundColor: to_color}, 800,function() { $(selector).css("background-color", ""); });

}

function flash_red(selector)
{
    flash_selector(selector,"#FF0000","#AA0000");
}


function removeUserSpecies(obj,species_id,species_name)
{
    $('#user_species_'+species_id).remove();
 
}

function addSpecies(speciesID,speciesName)
{

    var options = {};
    
    if (exists(".species_data"))
        $( ".species_data" ).hide( 'blind', options, 1 ); // hide all then add


    // check to see if this species already exists if so just move to that one
    if (exists("#species_container_for_" + speciesID))
    {
        $( "#species_data_for_" + speciesID ).show( 'blind', options, 200 ); // hide all then add
        return;
    }

    var div = '';
        div += '<div id="species_container_for_'+speciesID+'"    class="ui-widget-content ui-corner-all species_container" >';
        div +=   '<div id="species_header_for_'+speciesID+'"     class="ui-widget-header  ui-corner-all species_header" >'+ speciesName +'</div>';
        div +=   '<div id="species_data_for_'+speciesID+'"       class="ui-widget-content ui-corner-all species_data"   >';
        div +=   '</div>';
        div += '</div>';

    $("#species_data").append(div);
    
    $('#species_header_for_'+speciesID).button().click(function() { toggleSpeciesData(this); });
    
    var jData = { 
         cmdaction:'SpeciesComputed'
        ,species:speciesID
    }


   $.post("ExecuteCommandAjax.php", jData , function(data) { postAddSpecies(data); },"json");


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
    
    var speciesID = data['species_id'];
    
    var species_data_id = 'species_data_for_' + speciesID;
    
    postAddSpeciesScenarioModels(species_data_id,speciesID,data['scenarioModels'],data);
    
    

    
}

function postAddSpeciesScenarioModels(species_data_id,speciesID,scenarioModelsStr,data)
{
    
    // data - all the fileid's that we are goiong to look at '
    
    var scenarioModels =  string2Array(scenarioModelsStr, "~");

    var div = "";
    var div_header = "";
    var div_content = "";
    var div_timeline = "";

    var scenarioModelName = "";

    var timesStr = "";

    var timeStrValues = null;

    var firstScenarioModelTime = "";  // holds the id of the quikclook image for the first time zone from the scenario model

    var selected = "";

    var firstTimeName = ''; 
    var firstAsciiGridID = ''; 
    var firstFullname = '';



    for (s = 0; s < scenarioModels.length;  s++)
    {
        
        scenarioModelName = scenarioModels[s];
        
        
        timeStrValues = null;
        timesStr = "";
        
        firstScenarioModelTime = ''; // QuickLookID
        firstTimeName = ''; 
        
        firstAsciiGridID = '';
        firstFullname = '';
        
        // get the timeline from data for this scenario_model
        for (d in data)
        {

            
            if (d.indexOf(scenarioModelName) != -1)
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

                timesStr += '<input onClick="scenarioModelSelectedButtonSet(this)" class="radio_'+speciesID + "_"+scenarioModelName+'" '+selected+' type="radio" name="radio_'+speciesID + "_"+scenarioModelName+'" id="'+data[d]+'" /><label class="time_radio" for="'+data[d]+'">'+ timeStrValues[3]+'</label>';
                
            }
        
        
        }
        
        
        var loading_img_src = IconSource +'Loading.gif';

        var loading_msg = '<div id="loading_'+firstAsciiGridID +'"><img width="100%" height="20%" src="'+loading_img_src+'"></div>';
        
        
        var firstImageSrc = ApplicationFolderWeb + 'Search/file.php?id=' + firstScenarioModelTime;
        
        div_header = '<div style="height: 20px; float:none; clear: both; background-color: black; color: white;" >' + scenarioModelName + '</div>';

        div_content = loading_msg + 
                      '<div id="'+speciesID + '_' +scenarioModelName+'_image_container" style="height: 220px; float:none; clear: both;  overflow: hidden; " ><img  onload="layerImageLoaded(\''+firstAsciiGridID +'\')"   id="'+speciesID + '_' +scenarioModelName+'_'+ firstTimeName +'_image" style="width: 70%; height: 300px;" src="'+firstImageSrc+'"></div>';

        div_timeline = '<div id="'+speciesID + '_' +scenarioModelName+'_times" style="height: 40px;float:none; clear: both;" >'+timesStr+'</div>';

        div  = '<div class="scenaro_model_container" style="width: 100%; height: 280px;" >';
        div += div_header + div_content + div_timeline;
        div += '</div>';
        
        
        $('#'+species_data_id).append(div);

        $('#'+speciesID + '_' +scenarioModelName+'_times').buttonset();

        $('.time_radio')
            .css("font-size","0.8em");

        var firstImageId = speciesID + '_' +scenarioModelName+'_'+ firstTimeName +'_image';
        // tie data to first image
        $('#' + firstImageId).data("speciesID",speciesID);
        $('#' + firstImageId).data("AsciiGridID",firstAsciiGridID);
        $('#' + firstImageId).data("FullName",firstFullname);

        $('#' + firstImageId).click(function() {userSelectedLayer(this); return false;})




        


    }

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
    
    // I want to replace the src element of speciesID + '_' +scenarioModelName+'_image  with   this  QuickLookFileID
    var newImageSrc = ApplicationFolderWeb + 'Search/file.php?id=' + QuickLookFileID;   
    
    var imageHolder = speciesID + '_' + scenario + "_" + model +'_image_container';

    var newImageID = speciesID + '_' + scenario + "_" + model + '_'+ time + '_image';

    
    $('#' + imageHolder).children().hide();

    if (exists('#'+newImageID)) 
    {
        $('#' + newImageID).show(); // show this one
    }
    else
    {        
        
        var loading_img_src = IconSource +'Loading.gif';

        var loading_msg = '<div id="loading_'+AsciiGridFileID +'"><img width="100%" height="20%" src="'+loading_img_src+'"></div>';

        var div = loading_msg + '<img  onload="layerImageLoaded(\''+AsciiGridFileID +'\')"    id="'+newImageID + '" style="width: 70%; height: 300px;" src="'+newImageSrc+'">';
        
        // setup image onloaded  - 
        $('#' + imageHolder).append(div);
        
        // tie data to image
        $('#' + newImageID).data("speciesID",speciesID);
        $('#' + newImageID).data("AsciiGridID",AsciiGridFileID);
        $('#' + newImageID).data("FullName",FullName);
        
        $('#' + newImageID).click(function() {userSelectedLayer(this); return false;})
        
    }
    
    
    
}


function postAddSpeciesLayers(data_div_id,combination,row_str)
{
 
    var values = row_str.split("_");  // speciesID_scenario_model_time_QuickLookFileID_AsciiGridFileID

    var speciesID = values[0];
    var scenario  = values[1];
    var model     = values[2];
    var time      = values[3];
    var QuickLookFileID = values[4];
    var AsciiGridFileID = values[5];
    var FullName = values[6];

    var layerID = speciesID + "_" + combination;

    if(typeof QuickLookFileID != 'undefined')
    {
        
        var imgsrc = ApplicationFolderWeb + 'Search/file.php?id=' + QuickLookFileID;
        

        var loading_msg = '<div id="loading_'+AsciiGridFileID +'"><div style="width: 100%; height: 80%; background-color: white;">'+combination+'</div></div>';

        var img = '<div id="species_layer_image_container_'+AsciiGridFileID +'" class="species_layer_image_container">'+loading_msg+'<img onload="layerImageLoaded(\''+AsciiGridFileID +'\')" id="ascii_'+AsciiGridFileID +'" class="species_layer_image" src="'+imgsrc+'"   ></div>';
        
        $('#' + data_div_id).append(img);
        
        var loading_img_src = IconSource +'Loading.gif';
        
        $('#loading_'+AsciiGridFileID)
                .height(30)
                .css("background-image",'url("'+loading_img_src+'")')
                .css("background-size",'100% 100%')
                ;
        
        
        // we have to wait for images to load so set the image height to 0
        // set the container for each layer to "small"
        $('#ascii_'+AsciiGridFileID ).height(0).width(0);
        $('#loading_'+AsciiGridFileID ).css("font-size","0.9em").css("font-weight","bold");


        // the container holds "loading" message and may be others that are available before image is loaded
        $('#species_layer_image_container_'+AsciiGridFileID)
                .width("95%")
                .height(30)
                .css("overflow","hidden")
                .css("margin-bottom","8px");


        $('#ascii_'+AsciiGridFileID).data("FullName",  FullName);
        $('#ascii_'+AsciiGridFileID).data("speciesID", speciesID);
        $('#ascii_'+AsciiGridFileID).data("scenario",  scenario);
        $('#ascii_'+AsciiGridFileID).data("model",     model);
        $('#ascii_'+AsciiGridFileID).data("time",      time);
        $('#ascii_'+AsciiGridFileID).data("AsciiGridFileID", AsciiGridFileID);
        $('#ascii_'+AsciiGridFileID).data("QuickLookFileID", QuickLookFileID);

    }
    
}


function layerImageLoaded(AsciiGridFileID)
{
    // happens once the image is loaded
    $('#loading_' + AsciiGridFileID).remove();  // remove "Loading"
}




function userSelectedLayer(src)
{
    var id = src.id.toString();
    
    var ascii_grid_id = $('#' + id).data("AsciiGridID");;  // get ascii  grid  file id 
    var speciesID = $('#' + id).data("speciesID");
    var FullName = $('#' + id).data("FullName");


    $("#CurrentSpecies").html(FullName);  
    
    $("#UserLayer").val(ascii_grid_id);    // file_id of grid file - sets the fileid to be posted at map server
    $("#SpeciesID").val(speciesID);    


    var offset = $('#GUI').offset();
    var guiHeight = $('#GUI').height();
    var guiWidth = $('#GUI').width();
    
    var loading_img_src = IconSource +'Loading.gif';
    
    var map_loading_div = '<div id="MLD">Loading ' 
                        + FullName 
                        + '<br><img style="width:100%; height:40%;" src="'+loading_img_src+'"></div>';
    
    
    $('#messages_container').append(map_loading_div);
    
    $('#MLD').width(guiWidth * 0.8).height(90);
    
    
    $('#MLD').offset({ top: (offset.top + (guiHeight/2) - ($('#MLD').height() / 2)), left: (offset.left + (guiWidth/2) - ($('#MLD').width() / 2)) }) 

    $('#MLD').button();

    $('#MLD').fadeIn(200);

    $('#GUI').contents().find('#MAP_FORM').submit();
    
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
                            $(this).val('Species');
                            return false;
                        }
                     });
    

    $( "#ToolFullExtent" ).button({ text: false, icons: { primary: "ui-icon-image"  } });
    $( "#ToolZoomOut"    ).button({ text: false, icons: { primary: "ui-icon-zoomout"} }).click(function() { setTools(this); });
    $( "#ToolCentre"     ).button({ text: false, icons: { primary: "ui-icon-plus"   } }).click(function() { setTools(this); });
    $( "#ToolZoomIn"     ).button({ text: false, icons: { primary: "ui-icon-zoomin" } }).click(function() { setTools(this); });

    $( "#ToolFullExtent" ).height(40);
    $( "#ToolZoomOut"    ).height(30);
    $( "#ToolCentre"     ).height(30);
    $( "#ToolZoomIn"     ).height(30);

            
    $( "#accordion" ).accordion();
    $( "#accordion" ).accordion({ autoHeight: false });
    $( "#accordion" ).accordion({ fillSpace: true });

    $("#species").css("padding","4px");
    $("#species").css("margin","3px");
    $("#species").css("width","93%");
    $("#species").blur(function() { $(this).val('Species'); return false; });
    $("#species").focus(function() { $(this).val(''); return false; });
    

});
    
</script>
    
</head>
<body>
    <h1 class="pagehead"><a href="SpeciesSuitability.php"><img src="<?php echo configuration::IconSource()."Header_v1.png" ?>" border="0" /></a></h1>

<div class="maincontent">

    
<div id="lhs" class="ui-widget-content" >

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
        
        <iframe class=""  
                   ID="GUI" 
                  src="SearchMap.php" 
                width="100%" 
               height="660" 
          frameBorder="0" 
               border="0" 
                 style="margin: 0px; overflow:hidden; float:none; clear:both;" 
                onload="map_gui_loaded()"
                 >
        </iframe>
        
        <div id="ColorKeyContainer" >
            <?php echo RGB::RampDisplay($ramp); ?>
        </div>

        <div id="MutliLayerSelector" >
            Buttons to select what items of multiple;
        </div>
        
        
        <FORM METHOD=POST ACTION="<?php echo $_SERVER['PHP_SELF']?>">
            <INPUT TYPE="HIDDEN" ID="ZoomFactor" NAME="ZoomFactor" VALUE="2">
            <INPUT TYPE="HIDDEN" ID="UserLayer"  NAME="UserLayer"  VALUE="">
            <INPUT TYPE="HIDDEN" ID="SpeciesID"  NAME="SpeciesID"  VALUE="">
        </FORM>

    </div>
    <div id="MapLayers" class="ui-widget-content ui-corner-all" >
        <div id="SpeciesBar" class="ui-widget-header ui-corner-all" >
            <input id="species" value="Species">
        </div>
        <div id="species_data" class="ui-widget-content ui-corner-all" >
        </div>            

    </div>

</div>
    
    
<div id="rhs">

    <div id="run_process">START GENERATION</div>

    <div id="process-display"></div>

    <div id="remote-queue-id"></div>
    <div id="remote-status"></div>
    <div id="remote-content"></div>
    <div id="remote-result"></div>

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
