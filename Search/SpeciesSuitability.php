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
    height: 700px; 
    width: 940px; 
    overflow: hidden;    
    clear:both;
}

#MapContainer
{
    float: left; 
    
    height: 100%; 
    width: 620px; 
    overflow: hidden;
    
}

#MapLayers
{
    float: left; 
    height: 690px; 
    width: 316px; 
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
    height: 500px;
    overflow: scroll;
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
?>

var speciesDataHeight = 400;


function string2Array(str,delim)
{
    
    var result = new Array();
    if (str.indexOf("~") == -1 )
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

function get_id_list(selector)
{
    var delim = "~";
    var replace_from = '';
    var replace_to = '';
    var default_value = null;

    if (typeof arguments[1] != 'undefined') { replace_from  = arguments[1];}
    if (typeof arguments[2] != 'undefined') { replace_to    = arguments[2]}
    if (typeof arguments[3] != 'undefined') { default_value = arguments[3]}
    
    var ids = "";
    $(selector) .each( 
                    function() { 
                        if (ids == "")    
                            ids += this.id.replace(replace_from,replace_to); 
                        else
                            ids += delim + this.id.replace(replace_from,replace_to);
                    }
                );


    if (ids == "") return default_value;

    var result = ids.split(delim);

    return result;
    
}






function selectSelectableElement (selectableContainer, elementToSelect,unselectAll)
{

    if (unselectAll == true)
    {
        // add unselecting class to all elements in the styleboard canvas except current one
        jQuery("li", selectableContainer).each(function() {
        if (this != elementToSelect[0])
            jQuery(this).removeClass("ui-selected").addClass("ui-unselecting");
        });

    }

    // add ui-selecting class to the element to select
    elementToSelect.addClass("ui-selecting");

    // trigger the mouse stop event (this will select all .ui-selecting elements, and deselect all .ui-unselecting elements)
    selectableContainer.data("selectable")._mouseStop(null);
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
        flash_red("#species_header_for_" + speciesID);
        return;
    }
    

    var div = '';
        div += '<div id="species_container_for_'+speciesID+'" class="ui-widget-content ui-corner-all species_container" >';
        div +=   '<div id="species_header_for_'+speciesID+'"  class="ui-widget-header  ui-corner-all species_header" >'+ speciesName +'</div>';
        div +=   '<div id="species_data_for_'+speciesID+'"    class="ui-widget-content ui-corner-all species_data"   ></div>';
        div += '</div>';

    $("#species_data").append(div);
    
    
    $('#species_header_for_'+speciesID).button();
    
    $('#species_header_for_'+speciesID).click(function() { toggleSpeciesData(this); });
    
    var jData = { 
         cmdaction:'SpeciesComputed'
        ,species:speciesID
    }


   $.post("ExecuteCommandAjax.php", jData , function(data) { postAddSpecies(data); },"json");


}

function toggleSpeciesData(src)
{
    var dataID = src.id.toString().replace('species_header_for_','species_data_for_');  
    
    var options = {};
    $( "#" + dataID ).toggle( 'blind', options, 500 );
    
    
}

function postAddSpecies(data)
{    
    
    var speciesID = data['species_id'];
    
    var species_data_id = 'species_data_for_' + speciesID;
    
    
//    postAddSpeciesScenarios(data_div_id,data['scenarios']);
//    postAddSpeciesModels(data_div_id,data['models']);
//    postAddSpeciesTimes(data_div_id,data['times']);
    
    for (d in data)
    {
        switch(d)
        {
            case "scenarios":
                break;
            case "models":
                break;
            case "times":
                break;
            case "full_name":
                break;
            case "species_id":
                break;
            case "model_scenarios":
                $('#MutliLayerSelector').html(data[d]);
                break;
            
            default:
                postAddSpeciesLayers(species_data_id,d,data[d]);
        }
        
    }
    


    $('.species_layer_image').width("95%");
    
    $('.species-layer-from-user').height(230).css("overflow","hidden");

    $('.species_layer_image').click(function() { userSelectedLayer(this) });
    
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

        var img = '<img id="ascii_'+AsciiGridFileID +'" class="species_layer_image" src="'+imgsrc+'"   >';


        var li = $('<li class="species-layer-from-user" id="speciesLayer_'+layerID+'" >'+ img + '</li>');

        $('#' + data_div_id).append(li);


        $('#ascii_'+AsciiGridFileID).data("FullName",  FullName);
        $('#ascii_'+AsciiGridFileID).data("speciesID", speciesID);
        $('#ascii_'+AsciiGridFileID).data("scenario",  scenario);
        $('#ascii_'+AsciiGridFileID).data("model",     model);
        $('#ascii_'+AsciiGridFileID).data("time",      time);
        $('#ascii_'+AsciiGridFileID).data("AsciiGridFileID", AsciiGridFileID);
        $('#ascii_'+AsciiGridFileID).data("QuickLookFileID", QuickLookFileID);

    }
    
}



function postAddSpeciesScenarios(data_div_id,data)
{

    var items = string2Array(data,'~');

    var li = null;
    for (s = 0; s < items.length ; s++)
    {
        li = $('<li class="species-from-user" id="scenarios_'+ s +'" class="ui-widget-content" >'+items[s] +'</li>');
        li.button();
        $('#'+data_div_id).append(li);
    }
  
 
}

function postAddSpeciesModels(data_div_id,data)
{
    var items = string2Array(data,'~');

    var li = null;
    for (s = 0; s < items.length ; s++)
    {
        li = $('<li class="species-from-user" id="models_'+ s +'" class="ui-widget-content" >'+items[s] +'</li>');
        li.button();
        $('#'+data_div_id).append(li);
    }
  
    
}

function postAddSpeciesTimes(data_div_id,data)
{
    var items = string2Array(data,'~');

    var li = null;
    for (s = 0; s < items.length ; s++)
    {
        li = $('<li class="species-from-user" id="times_'+ s +'" class="ui-widget-content" >'+items[s] +'</li>');
        li.button();
        $('#'+data_div_id).append(li);
    }
    
}






function userSelectedLayer(src)
{
    var id = src.id.toString();
    
    var ascii_grid_id = id.split('_')[1];  // get ascii  grid  file id 
    
    var speciesID = $('#' + id).data("speciesID");
    var FullName = $('#' + id).data("FullName");
    
    
    $("#CurrentSpecies").html(FullName);  
    
    $("#UserLayer").val(ascii_grid_id);    // file_id of grid file
    $("#SpeciesID").val(speciesID);    
    $('#GUI').contents().find('#MAP_FORM').submit();
    
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
                            
                            if ( $('#user_species_'+ui.item.value).length ) 
                            {
                                $('#user_species_'+ui.item.value).stop().css("background-color", "#FF0000").animate({ backgroundColor: "#FFFFFF"}, 1500);
                                return false;
                            }                                
                            else
                            {
                                //var li = $('<li class="species-from-user" id="user_species_'+ui.item.value+'" class="ui-widget-content" ><button class="species-remove-button" id="remove_user_species_'+ui.item.value+'" >remove</button>'+ui.item.label+'</li>');
                                //$('#selectable_species').append(li);
                                addSpecies(ui.item.value,ui.item.label);
                                $(this).val('');
//                                $('#remove_user_species_' + ui.item.value)
//                                    .button({
//                                                icons: { primary: "ui-icon-circle-close" },
//                                                text: false
//                                            })
//                                    .click( function() { removeUserSpecies(this,ui.item.value,ui.item.label); return false;} )
//                                    ;

                                return false;
                                
                            }

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

});
    
</script>
    
</head>
<body>
    <h1 class="pagehead"><a href="SpeciesSuitability.php"><img src="<?php echo configuration::IconSource()."Header_v1.png" ?>" /></a></h1>

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
                width="640" 
               height="560" 
          frameBorder="0" 
               border="0" 
                 style="margin: 0px; overflow:hidden; float:none; clear:both;" >
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
            <input id="species">
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


</body>
</html>
