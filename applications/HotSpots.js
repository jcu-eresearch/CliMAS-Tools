// GLOBAL VARIABLES

function GetZoom() {
    document.getElementById('ZoomFactor').value = parent.document.getElementById('ZoomFactor').value;
}
    

function map_gui_loaded()
{
    if (exists('#MLD')) $('#MLD').fadeOut(200).remove();
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
function ClearAll() {
    var currentSrc = $('#GUI').attr('src');
    $('#GUI').attr('src',currentSrc.toString().replace('&clear=true','') + "&clear=true");
}


function screenSetup()
{

    if ($(window).width() > 1200)
        $('#thecontent').css("width",1200);
    else
        $('#thecontent').css("width",$(window).width());
        
}


function mapToolsInit()
{
    
    $( "#ToolClearAll")
        .button({text: false, icons: {primary: "ui-icon-image"}})
        .height(40)
        ;

    $( "#ToolFullExtent")
        .button({text: false, icons: {primary: "ui-icon-image"}})
        .height(40)
        ;

    $( "#ToolZoomOut")
        .button({text: false, icons: {primary: "ui-icon-zoomout"}})
        .click(function() {setTools(this);})
        .height(30)
        ;

    $( "#ToolCentre" )
        .button({text: false, icons: {primary: "ui-icon-plus"}})
        .click(function() {setTools(this);})
        .height(30)
        ;


    $( "#ToolZoomIn")
        .button({text: false, icons: {primary: "ui-icon-zoomin"}})
        .click(function() {setTools(this);})
        .height(30)
        ;
    
}

function InputTaxa(dataID,dataName)
{
    alert("InputTaxa " + dataID + " ... " + dataName);
}


function InputFamily(dataID,dataName)
{
    alert("InputFamily " + dataID + " ... " + dataName);
}

function InputGenus(dataID,dataName)
{
    alert("InputGenus " + dataID + " ... " + dataName);
}

function InputSpecies(dataID,dataName)
{
    alert("InputSpecies " + dataID + " ... " + dataName);
}

function InputLocation(dataID,dataName)
{
    alert("InputLocation " + dataID + " ... " + dataName);
}


function ChangeInputToTaxa()
{
    
    var blurMessage = "Enter taxanomic name";
    
    $( "#InputText" ).val(blurMessage);
    
    $( "#InputText" ).autocomplete('destroy'); 
    $( "#InputText" ).autocomplete({ 
                        source: availableTaxa,
                        select: function(event, ui) 
                        {
                            InputTaxa(ui.item.value,ui.item.label);
                            $(this).val(blurMessage);
                            return false;
                        }
                     });
    
    $( "#InputText" ).unbind('blur').blur(function() {$( "#InputText" ).val(blurMessage);})
    
}


function ChangeInputToFamily()
{
    
    var blurMessage = "Enter family name";
    
    $( "#InputText" ).val(blurMessage);
    
    $( "#InputText" ).autocomplete('destroy'); 
    $( "#InputText" ).autocomplete({ 
                        source: availableFamily,
                        select: function(event, ui) 
                        {
                            InputFamily(ui.item.value,ui.item.label);
                            $(this).val(blurMessage);
                            return false;
                        }
                     });
    
    $( "#InputText" ).unbind('blur').blur(function() {$( "#InputText" ).val(blurMessage);})
    
}

function ChangeInputToGenus()
{
    
    var blurMessage = "Enter genus name";
    
    $( "#InputText" ).val(blurMessage);
    
    $( "#InputText" ).autocomplete('destroy'); 
    $( "#InputText" ).autocomplete({ 
                        source: availableGenus,
                        select: function(event, ui) 
                        {
                            InputGenus(ui.item.value,ui.item.label);
                            $(this).val(blurMessage);
                            return false;
                        }
                     });
    
    $( "#InputText" ).unbind('blur').blur(function() {$( "#InputText" ).val(blurMessage);})
    
}



function ChangeInputToSpecies()
{
    
    var blurMessage = "Enter species name";
    
    $( "#InputText" ).val(blurMessage);
    
    $( "#InputText" ).autocomplete('destroy'); 
    $( "#InputText" ).autocomplete({ 
                        source: availableSpecies,
                        select: function(event, ui) 
                        {
                            InputSpecies(ui.item.value,ui.item.label);
                            $(this).val(blurMessage);
                            return false;
                        }
                     });
    
    $( "#InputText" ).unbind('blur').blur(function() {$( "#InputText" ).val(blurMessage);})
    
}

function ChangeInputToLocation()
{
    
    var blurMessage = "Enter location name or select from map";
    
    $( "#InputText" ).val(blurMessage);
    
    $( "#InputText" ).autocomplete('destroy'); 
    $( "#InputText" ).autocomplete({ 
                        source: availableLocation,
                        select: function(event, ui) 
                        {
                            InputLocation(ui.item.value,ui.item.label);
                            $(this).val(blurMessage);
                            return false;
                        }
                     });
    
    $( "#InputText" ).unbind('blur').blur(function() {$( "#InputText" ).val(blurMessage);})
    
    
}

function inputTypesSetClick(src)
{

    // chnage input to 
    var changeTo = src.id.toString().replace("InputType","");
    
    switch(changeTo)
    {
        case 'Taxa':
            ChangeInputToTaxa();
        break;
        case 'Family':
            ChangeInputToFamily();
        break;
        case 'Genus':
            ChangeInputToGenus();
        break;
        case 'Species':
            ChangeInputToSpecies();
        break;
        case 'Location':
            ChangeInputToLocation();
        break;
    }    

    $( "#InputText" ).focus();

}

function setupInputs()
{
    $('#InputsSearchBar').css("padding-left","5px");
    
    $('#InputTypesSet') .buttonset();

    $('#InputTypesSet label')
        .css("height",20)
        .css("font-size","0.6em")
    ;

    $('#InputTypesSet h4')
        .css("float","left")
        .css("margin-right","10px")
    ;

    $('#InputTypesSet input')
        .click(function () {inputTypesSetClick(this);})
    ;
    
}

function hoverSelectElementsIn(src)
{
    var id = src.id.toString();
    
    var state = 'ui-state-active';
    
    $('#' + id).addClass(state) ;
    $('#' + id).find('h4').addClass(state) ;
    $('#' + id).find('p').addClass(state) ; 
}

function hoverSelectElementsOut(src)
{
    var id = src.id.toString();
    var state = 'ui-state-active';
 
    $('#' + id).removeClass(state) ;
    $('#' + id).find('h4').removeClass(state) ;
    $('#' + id).find('p').removeClass(state) ; 
 
}


function selectElements(src)
{
    
    var id = src.id.toString();
    
    var ofWhat = "";
    
    if(id.indexOf("SelectAll") != -1)
    {
        ofWhat = id.replace("SelectAll","");    
        $('#' + ofWhat + "Selection").find('li').addClass('ui-selected') ;
        $('#' + ofWhat + "Selection").find('h4').addClass('ui-selected') ;
        $('#' + ofWhat + "Selection").find('p').addClass('ui-selected') ;

    }
    
    if(id.indexOf("SelectNone") != -1)
    {
        ofWhat = id.replace("SelectNone","");    
        $('#' + ofWhat + "Selection").find('li').removeClass('ui-selected') ;
        $('#' + ofWhat + "Selection").find('h4').removeClass('ui-selected') ;
        $('#' + ofWhat + "Selection").find('p').removeClass('ui-selected') ;

    }    
    
    
}


function getSelected()
{
    
    var species  = new Array();
    species[0] = 50;
    
    var models    = selected('#ModelsSelection'   ,"li", "ui-selected");
    var scenarios = selected('#ScenariosSelection',"li", "ui-selected");
    var times     = selected('#TimesSelection'    ,"li", "ui-selected");
    var bioclims  = selected('#BioclimsSelection' ,"li", "ui-selected");
    
    var jData = { 
         cmdaction:'SpeciesMaxent'
        ,species: species.join(" ")
        ,models: models.join(" ")
        ,scenarios: scenarios.join(" ")
        ,times: times.join(" ")
        ,bioclims: bioclims.join(" ")
    }

    
    return jData;
    
}

function CreateProcess()
{
    // get selected 
    
    jData = getSelected();

    var html  = '<div class="ProcessData">';
        html += '<li><h4>Species</h4>'   + '<p>' + jData.species    + '</p></li>' 
        html += '<li><h4>Models</h4>'    + '<p>' + jData.models     + '</p></li>' 
        html += '<li><h4>Scenarios</h4>' + '<p>' + jData.scenarios  + '</p></li>' 
        html += '<li><h4>Times</h4>'     + '<p>' + jData.times      + '</p></li>' 
        html += '<li><h4>Bioclims</h4>'  + '<p>' + jData.bioclims   + '</p></li>' 
        html += '</div>';


    $('#CreateProcessData').html(html);

}

$(document).ready(function(){

    screenSetup();

    $('#tabs').height(600).tabs();
    $('.selectable').selectable();

    mapToolsInit();

    $('#InputText')
        .css("width","98%")
        .css("height","40px")
        .css("font-size","1.1em")
        .focus(function() {$('#InputText').val('');})
        .focus()
        ;

    setupInputs();
    
    $('.SelectionToolBar button')
        .button()
        .css("font-size","0.8em")
        .css("height","90%")
        .css("margin","2px")
        .click(function() {selectElements(this);})
        ;    
    

    $('.selectable li').hover(function () {hoverSelectElementsIn(this);},function () {hoverSelectElementsOut(this);});


    $('#CreateProcess').click(function() {CreateProcess();});

    $('#InputTypeSpecies').click();





});
