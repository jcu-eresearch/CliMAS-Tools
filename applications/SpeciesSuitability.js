/*
 *
 *
 */
var currentDataStyle = 'CURRENT';
var currentSpeciesName = '';
var currentSpeciesID = '';
var currentScenario = 'CURRENT';
var currentModel = 'CURRENT';
var currentTime = '1990';
var currentCombination = '';

function addSpecies(species_id,speciesName)
{
    currentCombination = currentScenario + '_' + currentModel + '_' + currentTime;

    currentSpeciesName = speciesName;
    currentSpeciesID = species_id;

    $('#datastyle_selection').removeAttr('disabled');

    disableFuturePropertySelectors();

    userSelectedLayer();

    $('#download_all').attr('href', "SpeciesSuitabilityDownload.php?species_id="+species_id);
    $('#download_all').removeAttr('disabled');
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
        currentTime = '1990';

        disableFuturePropertySelectors();
    }
    else
    {
        enableFuturePropertySelectors();

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
        currentTime = '1990';
        currentCombination = currentTime;

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
    if (currentCombination == "CURRENT_CURRENT_1990") {
        currentCombination = "1990";
    }

    $.ajax('SpeciesSuitabilityPrep.php', {
        cache: false,
        dataType: 'json',
        type: 'POST',
        data: {
            UserLayer: currentCombination,
            SpeciesID: currentSpeciesID
        },
        success: function(data, testStatus, jqx) {
            console.log(['got data back:', data]);

            data = new L.TileLayer.WMS("http://tdh-tools-2.hpc.jcu.edu.au/cgi-bin/mapserv", {
                layers: currentCombination + '_' + currentSpeciesID + '&map=' + data.map_path,
                format: 'image/png',
                opacity: 0.75,
                transparent: true
            }).addTo(window.map);
        }
    });

}



function enableFuturePropertySelectors()
{
    $('#scenario_selection').removeAttr('disabled');
    $('#model_selection').removeAttr('disabled');
    $('#time_selection').removeAttr('disabled');
}

function disableFuturePropertySelectors()
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
        select: function(event, ui) {
            addSpecies(ui.item.value,ui.item.label);
            $(this).val(ui.item.label);
            return false;
        }
    });

    // add the map
    window.map = L.map('leafletmap').setView([-27, 135], 3);

    L.tileLayer('http://{s}.tile.cloudmade.com/831e24daed21488e8205aa95e2a14787/997/256/{z}/{x}/{y}.png', {
      maxZoom: 18
    }).addTo(map);

    disableFuturePropertySelectors();

    $('#datastyle_selection').attr('disabled', 'disabled');


});

