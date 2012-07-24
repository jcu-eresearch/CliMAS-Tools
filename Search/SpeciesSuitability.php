<?php
session_start();
include_once dirname(__FILE__).'/includes.php';

$scenarios = Descriptions::fromTable("scenarios");
$models = Descriptions::fromTable("models");
$times = Descriptions::fromTable("times"); 

//print_r($scenarios);

$M = new MapServerWrapper();

$caption = new VisualText("Species suitability", 10, "Red");
$M->Caption($caption);

foreach (Session::MapableResults() as $MapableResult)
    $M->Layers()->AddLayer($MapableResult);


$MF = Mapfile::create($M);

$_SESSION['map_path'] = $MF->save($M);

$GUI = MapserverGUI::create($_SESSION['map_path']);
if (is_null($GUI)) die ("Map Server GUI failed");

if ($GUI->hasInteractive()) $GUI->ZoomAndPan();

Session::add('MAP_EXTENT', $GUI->ExtentString()); // make available to session so we know where to look later


function icon($name)
{
    echo '<img title="'.$name.'" style="height: 30px; width: 30px;" border="0" src="'.configuration::IconSource().$name.'" />';
}


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
#selectable_species { list-style-type: none; margin: 0; padding: 0; width: 90%; }
#selectable_species li { margin: 3px; padding: 0.4em; font-size: 0.8em; height: 18px; }
s
#selectable_model .ui-selecting { background: #FECA40; }
#selectable_model .ui-selected { background: #F39814; color: white; }
#selectable_model { list-style-type: none; margin: 0; padding: 0; width: 90%;  }
#selectable_model li { margin: 3px; padding: 0.0em; font-size: 0.8em; height: 18px;}

#selectable_scenario .ui-selecting { background: #FECA40; }
#selectable_scenario .ui-selected { background: #F39814; color: white; }
#selectable_scenario { list-style-type: none; margin: 0; padding: 0; width: 90%; }
#selectable_scenario li { margin: 3px; padding: 0.4em; font-size: 0.8em; height: 18px; }

#selectable_time .ui-selecting { background: #FECA40; }
#selectable_time .ui-selected { background: #F39814; color: white; }
#selectable_time { list-style-type: none; margin: 0; padding: 0; width: 90%; }
#selectable_time li { margin: 3px; padding: 0.4em; font-size: 0.8em; height: 18px; }

#selectable_layers .ui-selecting { background: #FECA40; }
#selectable_layers .ui-selected { background: #F39814; color: white; }
#selectable_layers { list-style-type: none; margin: 0; padding: 0; width: 90%; }
#selectable_layers li { margin: 3px; padding: 0.4em; font-size: 0.8em; height: 90px; }


#species
{
    padding: 0px;
    margin: 0px;
    
}

#lhs
{
    float: none; 
    height: 600px; 
    width: 1000px; 
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
    height: 100%; 
    width: 300px; 
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
    height: 44px;
}




#MapTools
{
    float:left;
    height: 40px;
}

#FSspeedA
{
    float:left;
    height: 40px;
    width: 200px;
}

#speedA
{
    
    height: 100%;
    width: 90%;
}

#speedA option
{    
    width: 90%;
}

#remote-queue-id
{
    /* display: none;    -- TODO: hide this later*/
}


#remote-result
{
    float: none; 
    clear: both;
    
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


.species-remove-button
{
    height: 20px;
    width: 20px;
}


</style>
<script>

    function GetZoom() {
        document.getElementById('ZoomFactor').value = parent.document.getElementById('ZoomFactor').value;
    }
    


<?php 

echo htmlutil::AsJavaScriptSimpleVariable(configuration::ApplicationFolderWeb(),'ApplicationFolderWeb');

echo htmlutil::AsJavaScriptSimpleVariable(CommandAction::$EXECUTION_FLAG_RUNNING,'EXECUTION_FLAG_RUNNING');
echo htmlutil::AsJavaScriptSimpleVariable(CommandAction::$EXECUTION_FLAG_COMPLETE,'EXECUTION_FLAG_COMPLETE');

echo htmlutil::AsJavaScriptObjectArray(SpeciesData::speciesList(),"full_name","species_id","availableSpecies");    

?>


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


/**
 * Process data from a run to the server
 * 
 * data - Data converted back from JSON
 * 
 */
function postRun(data)
{
    
    var disp = "";
    for (d in data)
    {
        
        switch(d) {
            case "queueID":
                $('#remote-queue-id').html(data[d]); // keep the queue id
            break;

            case 'status':
                $('#remote-status').html(data[d]); // status passed back
            break;

            case 'content':
                $('#remote-content').html(data[d]); // content passed back
            break;

            // if job has completed then set button back so they can run another 
            case "ExecutionFlag":
                var execution_flag = data[d];
                if (execution_flag == EXECUTION_FLAG_COMPLETE)
                {
                    $('#run_process').html('<span class="ui-button-text">RUN</span>');
                    $('#run_process').button();
                    $('#run_process').unbind('click');
                    $('#run_process').click ( function() { startProcess(); return false;} );                        
                }
                
            break;

            default:
                disp += ", " + d + " = " + data[d];
        }        
        
    }

    $('#remote-result').html(disp); // extra data gets put here
    
    // if the process need to queue the job off to the HPC then set button to get updates
    var queueID = $.trim($('#remote-queue-id').html());
   
    if (queueID != "")
        {
            $('#run_process').html('<span class="ui-button-text">UPDATE</span>');
            $('#run_process').button();
            $('#run_process').unbind('click');
            $('#run_process').click ( function() { updateProcess(); return false;} );
        }
    
}

/**
 *  Queue a command on HPC and get results the SpeciesMaxent action
 *  
 *  server shoud return "queueID" that can the be used to obtain updates
 * 
 */
function startProcess()
{
    
    // collect ther data we need from the selections
    
      var species_ids = get_id_list('.species-from-user','user_species_','',new Array());
        var model_ids = get_id_list('.user_model.ui-selected','user_model_','',new Array());
     var scenario_ids = get_id_list('.user_scenario.ui-selected','user_scenario_','',new Array());
         var time_ids = get_id_list('.user_time.ui-selected','user_time_','',new Array());


    var species_str   = jQuery.trim( species_ids.join(' '));
    var model_str     = jQuery.trim(   model_ids.join(' '));
    var scenario_str  = jQuery.trim(scenario_ids.join(' '));
    var time_str      = jQuery.trim(    time_ids.join(' '));


    var jData = { 
         cmdaction:'SpeciesMaxent'
        ,species:species_str
        ,model:model_str
        ,scenario:scenario_str
        ,time:time_str
    }

   $.post("QueueCommandAjax.php", jData , function(data) { postRun(data); },"json");
   
   
}

/**
 * What we are going to run
 */
function toProcessDisplay()
{
    
      var species_ids = get_id_list('.species-from-user','user_species_','','');
        var model_ids = get_id_list('.user_model.ui-selected','user_model_','','');
     var scenario_ids = get_id_list('.user_scenario.ui-selected','user_scenario_','','');
         var time_ids = get_id_list('.user_time.ui-selected','user_time_','','');

    
    var total_jobs = species_ids.length * model_ids.length * scenario_ids.length * time_ids.length;
    var d = '&nbsp;&nbsp;'+total_jobs + ' items &nbsp;&nbsp;';
    
    $('#process-display').html(d);
    
    return total_jobs;
    
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

function updateProcess()
{

   var jData = { queueID: $('#remote-queue-id').html()}
   
  
   $.post("UpdateCommandAjax.php", jData , function(data) { postRun(data); },"json");
}

function removeUserSpecies(obj,species_id,species_name)
{
    $('#user_species_'+species_id).remove();
 
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



$(document).ready(function(){

    $( "#species" ).autocomplete({ 
                        source: availableSpecies,
                        select: function (event, ui) 
                        {
                            
                            if ( $('#user_species_'+ui.item.value).length ) 
                            {
                                $('#user_species_'+ui.item.value).stop().css("background-color", "#FF0000").animate({ backgroundColor: "#FFFFFF"}, 1500);
                                
                                $(this).val(''); 
                                return false;
                            }                                
                            else
                            {
                                var li = $('<li class="species-from-user" id="user_species_'+ui.item.value+'" class="ui-widget-content" ><button class="species-remove-button" id="remove_user_species_'+ui.item.value+'" >remove</button>'+ui.item.label+'</li>');
                                $('#selectable_species').append(li);
                                addSpecies(ui.item.value,ui.item.label);
                                $(this).val(''); 
                                $('#remove_user_species_' + ui.item.value)
                                    .button({
                                                icons: { primary: "ui-icon-circle-close" },
                                                text: false
                                            })
                                    .click( function() { removeUserSpecies(this,ui.item.value,ui.item.label); return false;} )
                                    ;

                                return false;
                                
                            }

                        }
                     });
    



    $( "#selectable_species" ).selectable();
    $( "#selectable_model" ).selectable();
    $( "#selectable_scenario" ).selectable();
    $( "#selectable_time" ).selectable();

    

    $( "#process-display" ).button();

    $( "#ToolFullExtent" ).button({ text: false, icons: { primary: "ui-icon-image"  } });
    $( "#ToolZoomOut"    ).button({ text: false, icons: { primary: "ui-icon-zoomout"} }).click(function() { setTools(this); });
    $( "#ToolCentre"     ).button({ text: false, icons: { primary: "ui-icon-plus"   } }).click(function() { setTools(this); });
    $( "#ToolZoomIn"     ).button({ text: false, icons: { primary: "ui-icon-zoomin" } }).click(function() { setTools(this); });

    $( "#ToolFullExtent" ).height(30);
    $( "#ToolZoomOut"    ).height(30);
    $( "#ToolCentre"     ).height(30);
    $( "#ToolZoomIn"     ).height(30);


    $('select#speedA').selectmenu();

    $( "#run_process" )
            .button()
            .click ( function() { startProcess(); return false;} );
            
    $( "#accordion" ).accordion();

    $( "#accordion" ).accordion( "option", "autoHeight", true );
    $( "#accordion" ).accordion( "option", "fillSpace", true );
    

    $( ".parts" ).css("padding","0px");

    $("#species").css("padding","4px").css("margin","3px").addclass("ui-widget-header");


});


function addSpecies(speciesID,speciesName)
{

    var jData = { 
         cmdaction:'SpeciesComputed'
        ,species:speciesID
    }


   $.post("ExecuteCommandAjax.php", jData , function(data) { postAddSpecies(data); },"json");


}


function postAddSpecies(data)
{    
    for (d in data)
    {
        postAddSpeciesLayers(d,data[d]);
    }
    
    $('.species_layer_image').width("95%");
    
    $('.species-layer-from-user').height(160).css("overflow","hidden");

    $('.species_layer_image').click(function() { userSelectedLayer(this) });

    
}




function postAddSpeciesLayers(combination,row_str)
{
    

    var values = row_str.split("_");  // speciesID_scenario_model_time_QuickLookFileID_AsciiGridFileID

    var speciesID = values[0];
    var scenario  = values[1];
    var model     = values[2];
    var time      = values[3];
    var QuickLookFileID = values[4];
    var AsciiGridFileID = values[5];

    var layerID = speciesID + "_" + combination;


    var imgsrc = ApplicationFolderWeb + 'Search/file.php?id=' + QuickLookFileID;

    var img = '<img id="ascii_'+AsciiGridFileID+'" class="species_layer_image" src="'+imgsrc+'"   >';

    var li = $('<li class="species-layer-from-user" id="speciesLayer_'+layerID+'" >'+ img + '</li>');
    
    $('#selectable_layers').append(li);
    
    
}


function userSelectedLayer(src)
{
    var id = src.id.toString();
    
    // get ascii  grid  file id 
    
    var ascii_grid_id = id.split('_')[1];
    $("#UserLayer").val(ascii_grid_id);    // file_id of grid file
    $('#GUI').contents().find('#MAP_FORM').submit();
    
}


function setTools(src)
{
     $( "#" + src.id.toString() ).toggleClass( "ui-state-active", 200 );

}

function GetExtentText() 
{
    var iframe = document.getElementById('GUI');
    var innerDoc = iframe.contentDocument || iframe.contentWindow.document;   
    return innerDoc.getElementById('extent').value
}

function ReloadDiv(divID) 
{
    document.getElementById(divID).src = document.getElementById(divID).src
}

function SetContent(url, divID) 
{
    document.getElementById(divID).src = url;    
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


function zoomOut()
{
    var iframe = document.getElementById('GUI');
    var innerDoc = iframe.contentDocument || iframe.contentWindow.document;
    
    var map_form = innerDoc.getElementById("MAP_FORM");
    
    innerDoc.getElementById('ZoomFactor').value  = -2.0;
    
    innerDoc.getElementById('mapa').click();
    
}


    
</script>
    
</head>
<body>
    <h1 class="pagehead"><a href="SpeciesSuitability.php"><img src="<?php echo configuration::IconSource()."Header_v1.png" ?>" /></a></h1>

<div class="maincontent">

    
<div id="lhs" class="ui-widget-content" >

    <div id="MapContainer" class="ui-widget-content" >

        <div id="ToolBar" class="ui-widget-header ui-corner-all" >
            <div id="MapTools">
                <button id="ToolFullExtent" onclick="SetFullExtent();" >Full Extent</button>
                <input name="MapsTools" type="radio" id="ToolZoomOut"  onclick="SetZoom(this,-2.0);"                   /><label for="ToolZoomOut">Zoom Out</label>
                <input name="MapsTools" type="radio" id="ToolCentre"   onclick="SetZoom(this,1.0)"                     /><label for="ToolCentre" >Centre</label>
                <input name="MapsTools" type="radio" id="ToolZoomIn"   onclick="SetZoom(this,2.0)"   checked="checked" /><label for="ToolZoomIn" >Zoom In</label>

            </div>

        </div>
        
        <iframe ID="GUI" src="SearchMap.php" width="640" height="440" frameBorder="0" border="0" style="overflow:hidden; float:left;" ></iframe>

        <FORM METHOD=POST ACTION="<?php echo $_SERVER['PHP_SELF']?>">
            <INPUT TYPE="HIDDEN" ID="ZoomFactor" NAME="ZoomFactor" VALUE="2">
            <INPUT TYPE="HIDDEN" ID="UserLayer"  NAME="UserLayer"  VALUE="">
        </FORM>

    </div>
    <div id="MapLayers" class="ui-widget-content ui-corner-all" >
        
        <div id="accordion">
            <h3><a href="#">Species</a></h3>
            <div id="accordionSpecies" class="parts">
                
                <div id="SpeciesBar" class="ui-widget-header ui-corner-all" >
                    <input id="species">
                </div>
                
                <ol id="selectable_species">
                </ol>	        
            </div>

            <h3><a href="#">Layers</a></h3>
            <div  class="parts">
                For selected species
                <ol id="selectable_layers">

                </ol>	        
            </div>
            
            
            <h3><a href="#">Climate Models</a></h3>
            <div  class="parts">
                
                <ol id="selectable_model">
                    <?php 
                    foreach ($models->asSimpleArray() as $key => $value) 
                    { 
                    ?>        
                        <li id="user_model_<?php  echo $key; ?>" class="ui-widget-content user_model"><?php  echo $key; ?><span style="font-size: 80%;"></span></li>
                    <?php                     
                    }
                    ?>

                </ol>	        
            </div>

            <h3><a href="#">Emission Scenarios</a></h3>
            <div  class="parts">
                
                FOR THIS SPECIES
                
                <ol id="selectable_scenario">
                    <?php 
                    foreach ($scenarios->asSimpleArray() as $key => $value) 
                    { 
                    ?>        
                        <li id="user_scenario_<?php  echo $key; ?>" class="ui-widget-content user_scenario"> <?php  echo $key; ?>&nbsp;&nbsp;&nbsp;<span style="font-size: 80%;">(<?php  echo $value; ?>)</span></li>
                    <?php                     
                    }
                    ?>
                </ol>	

                <p>
                With respect to the emission scenarios, Representative Concentration Pathways (RCPs) has been adopted by the IPCC to replace the Special Report on Emissions Scenarios (SRES) used in the AR4 report (Solomon, Qin et al. 2007); RCPs are to be used in the AR5 IPCC report due in 2014.
                </p>

            </div>

            <h3><a href="#">Time periods</a></h3>
            <div  class="parts">
                
                FOR THIS SPECIES
                
                <ol id="selectable_time">
                    <?php 
                    foreach ($times->asSimpleArray() as $key => $value) 
                    { 
                    ?>        
                        <li id="user_time_<?php  echo $key; ?>" class="ui-widget-content user_time"> <?php  echo $key; ?></li>
                    <?php                     
                    }
                    ?>
                </ol>	

            </div>
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
