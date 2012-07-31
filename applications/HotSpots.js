// GLOBAL VARIABLES

var SpeciesEntryBoxMessage = "Enter a species name here";

function GetZoom() {
    document.getElementById('ZoomFactor').value = parent.document.getElementById('ZoomFactor').value;
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
    
    $('#species_header_for_'+speciesID)
        .click(function() { toggleSpeciesData(this); })
        .css("color","black")
        .button()
        ;
    
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
        
        var sm = string2Array(scenarioModelName, '_') 

        
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
        
        
        var loading_img_src = IconSource +'wait.gif';

        var loading_msg = '<div id="loading_'+firstAsciiGridID +'"><img style="margin-left: 70px; margin-top: 50px; width:100px; height=100px;" src="'+loading_img_src+'"></div>';
        
        
        var firstImageSrc = ApplicationFolderWeb + 'applications/file.php?id=' + firstScenarioModelTime;
        
        div_header = '<div id="'+speciesID + '_' +scenarioModelName+'_image_header" style="padding:3px; height: 24px; float:none; clear: both; background-color: black; color: white;" >' + sm[0] + "&nbsp;&nbsp;&nbsp;&nbsp;" + sm[1] + '</div>';

        div_content = '<div id="'+speciesID + '_' +scenarioModelName+'_image_container" style="margin-left: 80px; height: 220px; float:none; clear: both;  overflow: hidden; " >'+loading_msg+'<img  onload="layerImageLoaded(\''+firstAsciiGridID +'\')"   id="'+speciesID + '_' +scenarioModelName+'_'+ firstTimeName +'_image" style="width: 70%; height: 300px;" src="'+firstImageSrc+'"></div>';

        div_timeline = '<div id="'+speciesID + '_' +scenarioModelName+'_times" style="margin-left: 1px; height: 40px;float:none; clear: both;" >'+timesStr+'</div>';

        div  = '<div class="scenaro_model_container" style="width: 100%; height: 280px;" >';
        div += div_header + div_content + div_timeline;
        div += '</div>';
        
        
        $('#'+species_data_id).append(div);

        $('#'+speciesID + '_' +scenarioModelName+'_times').buttonset();

        $('#'+speciesID + '_' +scenarioModelName+'_image_header').addClass("ui-corner-all");



        $('.time_radio')
            .css("font-size","0.8em");

        var firstImageId = speciesID + '_' +scenarioModelName+'_'+ firstTimeName +'_image';
        // tie data to first image
        $('#' + firstImageId).data("speciesID",speciesID);
        $('#' + firstImageId).data("AsciiGridID",firstAsciiGridID);
        $('#' + firstImageId).data("FullName",firstFullname);
       

        $('#'+firstImageId).data("scenario",  sm[0]);
        $('#'+firstImageId).data("model",     sm[1]);
        $('#'+firstImageId).data("time",      firstTimeName);

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



function userSelectedLayer(src)
{
    var id = src.id.toString();
    
    var ascii_grid_id = $('#' + id).data("AsciiGridID");;  // get ascii  grid  file id 
    var speciesID = $('#' + id).data("speciesID");
    var FullName = $('#' + id).data("FullName");

    // put text details of currently selected layer around the map 
    $('#MultiLayerSelector').html($('#' + id).data("scenario") + "&nsbp;&nsbp;" +  $('#' + id).data("model") + "&nsbp;&nsbp;" +  $('#' + id).data("time"));
    $("#CurrentSpecies").html(FullName);  

    // data needs to be posted at the mapserver 
    $("#UserLayer").val(ascii_grid_id);    // file_id of grid file - sets the fileid to be posted at map server
    $("#SpeciesID").val(speciesID);    


    var offset = $('#GUI').offset();
    var guiHeight = $('#GUI').height();
    var guiWidth = $('#GUI').width();
    
    var map_loading_div = '<div id="MLD">Loading ' 
                        + FullName 
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
                            $(this).val(SpeciesEntryBoxMessage);
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
        .css("width","93%")
        .blur(function() { $(this).val(SpeciesEntryBoxMessage); return false; })
        .focus(function() { $(this).val(''); return false; })
        .val(SpeciesEntryBoxMessage)
        ;


    $(".ToolBarItem")
        .css("width","19%")
        .css("float","left")
        .button()
        .css("height","40px")
        ;



});
