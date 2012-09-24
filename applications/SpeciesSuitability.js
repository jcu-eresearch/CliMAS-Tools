/* 
 * 
 * 
 */
var currentDataStyle = '';
var currentSpeciesName = '';
var currentSpeciesID = '';
var currentScenario = '';
var currentModel = '';
var currentTime = '';
var currentCombination = '';

function addSpecies(species_id,speciesName)
{
    currentDataStyle = 'CURRENT';
    
    currentScenario = 'CURRENT';
    currentModel = 'CURRENT';
    currentTime = '1975';

    currentCombination = currentScenario + '_' + currentModel + '_' + currentTime;    

    currentSpeciesName = speciesName;
    currentSpeciesID = species_id;
    
    $('#datastyle_selection').removeAttr('disabled');

    disableSelectorButtons();

    userSelectedLayer();


}



function selectDataStyle(src)
{
    var id = src.id.toString();
    currentDataStyle = $('#'+ id + ' option:selected').val();
    
    console.log('currentDataStyle = ' + currentDataStyle);
    
    if (currentDataStyle == "CURRENT")
    {
        currentScenario = 'CURRENT';
        currentModel = 'CURRENT';
        currentTime = '1975';
        
        disableSelectorButtons();
    }
    else
    {
        enableSelectorButtons();
        
    }
        

    setCurrentCombination();
    
    userSelectedLayer();


}


function selectScenario(src)
{
    var id = src.id.toString();
    currentScenario = $('#'+ id + ' option:selected').val();
    setCurrentCombination();
    userSelectedLayer();
}

function selectModel(src)
{
    var id = src.id.toString();
    currentModel = $('#'+ id + ' option:selected').val();
    setCurrentCombination();
    userSelectedLayer();
}

function selectTime(src)
{
    var id = src.id.toString();
    currentTime = $('#'+ id + ' option:selected').val();
    
    setCurrentCombination();
    userSelectedLayer();
}

function setCurrentCombination()
{
    
    if (currentDataStyle == 'CURRENT')
    {
        currentScenario = 'CURRENT';
        currentModel = 'CURRENT';
        currentTime = '1975';        
        currentCombination = currentScenario + '_' + currentModel + '_' + currentTime;    
        
        $('#information_content').attr('src','SpeciesSuitabilityInformation.php?combination=');  // call info with empty combo
    }
    else
    {
        currentScenario = $('#scenario_selection option:selected').val();
        currentModel    = $('#model_selection option:selected').val();
        currentTime     = $('#time_selection option:selected').val();
        
        currentCombination = currentScenario + '_' + currentModel + '_' + currentTime;    
        
        $('#information_content').attr('src','SpeciesSuitabilityInformation.php?combination='+currentCombination);    
    }
    
    
}


function userSelectedLayer()
{

    // data needs to be posted at the mapserver
    $("#UserLayer").val(currentCombination);    // file_id of grid file - sets the fileid to be posted at map server
    $("#SpeciesID").val(currentSpeciesID);


    console.log('userSelectedLayer = ' + currentCombination);
    console.log('currentSpeciesID = ' + currentSpeciesID);

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



function enableSelectorButtons()
{    
    $('#scenario_selection').removeAttr('disabled');
    $('#model_selection').removeAttr('disabled');
    $('#time_selection').removeAttr('disabled');
}

function disableSelectorButtons()
{
    
    $('#scenario_selection').attr('disabled', 'disabled');
    $('#model_selection').attr('disabled', 'disabled');
    $('#time_selection').attr('disabled', 'disabled');
    
}


function currentAsciiGrid()
{
    var result =    Maxent_Species_Data_folder_web 
                  + currentSpeciesID + '/'
                  + 'output/' 
                  + currentCombination + '.asc';
              
    return result;
}


function setMapOverview()
{
    
    var mapa = $("#GUI").contents().find("#mapa");
    
    if ($('#map_overview').html() == "") 
    {
        $('#map_overview').html('<img style="width: 100%; height="130%" src="'+ mapa.attr('src') +'">');    
        

        
    }
    
    
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

    

    // Type in the species of interest



    $( "#ToolFullExtent")
        .button({ text: false, icons: { primary: "ui-icon-image"  } })
        .height(40)
        .css('float','none')
        .css('clear','both')
        ;

    $( "#ToolZoomOut")
        .button({ text: false, icons: { primary: "ui-icon-zoomout"} })
        .click(function() { setTools(this); })
        .height(30)
        .css('float','none')
        .css('clear','both')
        ;

    $( "#ToolCentre" )
        .button({ text: false, icons: { primary: "ui-icon-plus"   } })
        .click(function() { setTools(this); })
        .height(30)
        .css('float','none')
        .css('clear','both')
        ;


    $( "#ToolZoomIn")
        .button({ text: false, icons: { primary: "ui-icon-zoomin" } })
        .click(function() { setTools(this); })
        .height(30)
        .css('float','none')
        .css('clear','both')
        ;

    $("#species")
        .css("padding","4px")
        .css("margin","3px")
        .css("width","97%")
        .css("height","24px")
        .css('font-size','1.2em')
        .blur(function() { $(this).val(currentSpeciesName); return false; })
        .focus(function() { $(this).val(''); return false; })
        ;

    disableSelectorButtons();

    $('#datastyle_selection').attr('disabled', 'disabled');


    $('#datastyle_selection')
        .css('float','none')
        .css('clear','both')
        .css('width','90%')
        .css('height','30px')
        .css('margin','2%')
        ;

    $('#scenario_selection')
        .css('float','none')
        .css('clear','both')
        .css('width','90%')
        .css('height','30px')
        .css('margin','2%')
        ;

    $('#model_selection')
        .css('float','none')
        .css('clear','both')
        .css('width','90%')
        .css('height','30px')
        .css('margin','2%')
        ;

    $('#time_selection')
        .css('float','none')
        .css('clear','both')
        .css('width','90%')
        .css('height','30px')
        .css('margin','2%')
        ;


});

