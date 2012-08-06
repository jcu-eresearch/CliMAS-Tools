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


function addInput(dataType,dataID,dataName)
{
    
    
    var addID = dataType + '_'+dataID;
    
    // check to see that ID doesn'y already exists
    
    if ( exists('#' + addID)) 
    {
        var properties = {
            color : '#FF0000',
            fontWeight : 'bold'
        };

        $('#' + addID).pulse(properties, {pulses : 2});        
        return ;
    }

    
    var removeID = 'remove_'+addID;
    $('#'+dataType+'Selection').append('<li id="'+addID+'" class="ui-widget-content ui-corner-all " ><button id="'+removeID+'" class="RemoveInput">remove</button><p>'+dataName+'</p> </li>');
    
    addSelectedTo('#' + addID);
    updateCurrentPackage();
    
    
    $( '#'+ removeID)
        .button({text: false, icons: {primary: "ui-icon-close"}})
        .css('height','20px')
        .css('width','20px')
        .css('float','left')
        .click(function () {
                
                var toRemove = this.id.toString().replace('remove_','');
                $('#' + toRemove).remove();
                updateCurrentPackage();
            }
     );
    
    
    
}

function addSelectedTo(selector)
{
    $(selector).hover(function () {hoverSelectElementsIn(this);},function () {hoverSelectElementsOut(this);});
    
}


function inputTypesSetClick(src)
{
    var changeTo = src.id.toString().replace("InputType","");  // chnage input to 
    
    switch(changeTo)
    {
        case 'Taxa':ChangeInputToTaxa();break;
        case 'Family':ChangeInputToFamily();break;
        case 'Genus':ChangeInputToGenus();break;
        case 'Species':ChangeInputToSpecies();break;
        case 'Location':ChangeInputToLocation();break;
    }    



    $( "#InputText" ).focus();

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
                            addInput('Taxa',ui.item.value,ui.item.label);
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
                            addInput('Family',ui.item.value,ui.item.label);
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
                            addInput('Genus',ui.item.value,ui.item.label);
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
                            addInput('Species',ui.item.value,ui.item.label);
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
                            addInput('Location',ui.item.value,ui.item.label);
                            $(this).val(blurMessage);
                            return false;
                        }
                     });
    
    $( "#InputText" ).unbind('blur').blur(function() {$( "#InputText" ).val(blurMessage);})
    
    
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
        updateCurrentPackage();
    }
    
    if(id.indexOf("SelectNone") != -1)
    {
        ofWhat = id.replace("SelectNone","");    
        $('#' + ofWhat + "Selection").find('li').removeClass('ui-selected') ;
        $('#' + ofWhat + "Selection").find('h4').removeClass('ui-selected') ;
        $('#' + ofWhat + "Selection").find('p').removeClass('ui-selected') ;
        updateCurrentPackage();
    }    
    
    if(id.indexOf("SelectDefault") != -1)
    {
        ofWhat = id.replace("SelectDefault","");    
        selectElementsDefault(ofWhat);
        updateCurrentPackage();
    }    


    if(id.indexOf("SelectSome") != -1)
    {
        ofWhat = id.replace("SelectSome","");    
        selectElementsSome(ofWhat.split("_")[0],ofWhat.split("_")[1]);
        updateCurrentPackage();
    }    



}


function selectWhereIDContains(rootSelector,listElementSelector,toFind,addClass)
{
    $(rootSelector).find(listElementSelector)
        .each(function(index) {
            if (toFind == null)
            {
                root = $("#"+this.id.toString());
                root.addClass(addClass) ;
                root.find('*').addClass(addClass) ;
            }
            else
            {
                if(this.id.toString().indexOf(toFind) != -1)
                {
                    root = $("#"+this.id.toString());
                    root.addClass(addClass) ;
                    root.find('*').addClass(addClass) ;
                }
            }

        })
}


function deselectWhereIDContains(rootSelector,listElementSelector,toFind,removeClass)
{
    $(rootSelector).find(listElementSelector)
        .each(function(index) {
            
            if (toFind == null)
            {
                root = $("#"+this.id.toString());
                root.removeClass(removeClass) ;
                root.find('*').removeClass(removeClass) ;
            }
            else
            {
                if(this.id.toString().indexOf(toFind) != -1)
                {
                    root = $("#"+this.id.toString());
                    root.removeClass(removeClass) ;
                    root.find('*').removeClass(removeClass) ;
                }
            }

        })
}



function selectElementsDefault(selectFor)
{
    
    switch(selectFor)
    {
        case 'Models':
            deselectWhereIDContains('#' + selectFor + 'Selection','li',null  ,'ui-selected')
              selectWhereIDContains('#' + selectFor + 'Selection','li',"ccsr",'ui-selected');
            break;
        
        case 'Scenarios':
            deselectWhereIDContains('#' + selectFor + 'Selection','li',null  ,'ui-selected')
              selectWhereIDContains('#' + selectFor + 'Selection','li',"RCP",'ui-selected');
            
            break;
        
        case 'Times':
            deselectWhereIDContains('#' + selectFor + 'Selection','li',null,'ui-selected')
              selectWhereIDContains('#' + selectFor + 'Selection','li',null,'ui-selected');
            
            break;
        
        case 'Bioclims':
            deselectWhereIDContains('#' + selectFor + 'Selection','li',null,'ui-selected')
              selectWhereIDContains('#' + selectFor + 'Selection','li',null,'ui-selected');
            
            break;
    }    
    
    
}

function selectElementsSome(selectFor,selectSomeFilterString)
{
    
    switch(selectFor)
    {
        case 'Models':
            break;
        
        case 'Scenarios':
              deselectWhereIDContains('#' + selectFor + 'Selection','li',null  ,'ui-selected')
                selectWhereIDContains('#' + selectFor + 'Selection','li',selectSomeFilterString,'ui-selected');
            break;
        
        case 'Times':
            break;
        
        case 'Bioclims':
            break;
    }    
    
    
    
}

function currentData()
{
    var jData = { 
         cmdaction:'SpeciesMaxent'
             ,taxa: selected('#TaxaSelection'     ,"li", null," ",'Taxa_'     ,null)
           ,family: selected('#FamilySelection'   ,"li", null," ",'Family_'   ,null)
            ,genus: selected('#GenusSelection'    ,"li", null," ",'Genus_'    ,null)
          ,species: selected('#SpeciesSelection'  ,"li", null," ",'Species_'  ,null)
         ,location: selected('#LocationSelection' ,"li", null," ",'Location_' ,null)
           ,models: selected('#ModelsSelection'   ,"li", "ui-selected"," ",'Models_'   ,null)
        ,scenarios: selected('#ScenariosSelection',"li", "ui-selected"," ",'Scenarios_',null)
            ,times: selected('#TimesSelection'    ,"li", "ui-selected"," ",'Times_'    ,null)
         ,bioclims: selected('#BioclimsSelection' ,"li", "ui-selected"," ",'Bioclims_' ,null)
    }
    
    console.log(jData);
    
    return jData;
    
}




/**
 *  Current Display of selected items
 * 
 */
function updateCurrentPackage()
{
    console.log("Update updateCurrentPackage");
    
    
    var jData = currentData();
    
         $('#CountTaxa').html(jData.taxa.length);
       $('#CountFamily').html(jData.family.length);
        $('#CountGenus').html(jData.genus.length);
      $('#CountSpecies').html(jData.species.length);
     
     
       $('#CountModels').html(jData.models.length);
    $('#CountScenarios').html(jData.scenarios.length);
        $('#CountTimes').html(jData.times.length);
     $('#CountBioclims').html(jData.bioclims.length);


     var inputsCount = jData.taxa.length +  jData.family.length + jData.genus.length + jData.species.length;
     var futureCount = jData.models.length * jData.scenarios.length * jData.times.length;
     

    $('#CountInputTotals').html(inputsCount);
    $('#CountFutureTotals').html(futureCount);
    $('#CountGrandTotal').html(inputsCount * futureCount);

}



function CreateProcess()
{
    // check currentDataPackage() to mak sure we have al;l the data we need to running
    var jData = currentData();


    var sData = null;

    // loop over each of the values of taxa, family, genus, and species
    // species first
    
    var inputType   = 'species';
    var inputPrefix = 'Species_';
    var cmdaction   = 'SpeciesHotSpots';
    var inputID = null
    $.each(jData[inputType], function(index, value) 
    { 
        
         inputID = value.replace(inputPrefix,"");
        
         sData = { 
                 cmdaction: cmdaction
                ,inputType: inputType
                  ,inputID: inputID
                ,inputName: $('#' + value).find('p').html()
                   ,models: array_replace(jData.models,    "Models_","")  
                ,scenarios: array_replace(jData.scenarios, "Scenarios_","")  
                    ,times: array_replace(jData.times,     "Times_","")  
                 ,bioclims: array_replace(jData.bioclims,  "Bioclims_BIO","")  
                ,elementID: inputType + "_" + inputID
         }
        
        
        addSingleProcess(sData);
    });

}

function addSingleProcess(sData)
{
    
    console.log("Create for  " );
    console.log(sData);


    var cancelButton = '<button id="cancel_'+ sData.elementID +'">CANCEL</button>';

    var calcCount   = '<h1 class="ui-widget-content ui-corner-all">' + (sData.models.length * sData.scenarios.length * sData.times.length) +'<p>datasets</p> </h1>';
    
    var displayName = '<h2>' + sData.inputName + '</h2>';
    
    var button = '<button id="info_'+ sData.elementID +'">'+datetime_now()+'</button>';
    
    var progress = '<div id="progress_'+ sData.elementID +'"><img style="width: 100%; height: 100%" src="'+IconSource+'Loading.gif"></div>';
    
    var html = '<li class="ui-widget-content">' + cancelButton + button + calcCount + displayName +  progress + '<p id="status_'+sData.elementID+'">........</p></li>'+"\n";

    $('#RunningProcessesTable').append(html);

    $('#info_' + sData.elementID)
        .button()
        .css('float','left')
        .css('width','200px')
        .css('height','97%')
        .css('margin','2px')
        .css('text-align','center')
        .click(function() {infoDialog(this);return false;})
        .button( "option", "disabled", true );
        ;

    $('#cancel_' + sData.elementID)
        .button()
        .button({text: false, icons: {primary: "ui-icon-closethick"}})
        .css('float','left')
        .css('width','40px')
        .css('height','97%')
        .css('margin','2px')
        .css('text-align','center')
        ;


    $('#progress_' + sData.elementID)
        .css('float','left')
        .css('width','100px')
        .css('height','40%')
        .css('margin-top','20px')
        ;


    $.post("HotSpotsAjaxExecute.php", sData , function(data) {postAddSingleProcess(data);},"json");

    // json / ajax calls here to execute this process
    
    // clear selected and - gray out the run button again
    
}

function postAddSingleProcess(data)
{
    
    // get error message and put in dialog 
    // data.error
    
    var progressStr = "";
    
    if (isNull(data.ResultsFullCountString) && isNull(data.ResultsDoneCountString) )
        progressStr = "Can't read progress";
    else
        progressStr = Value(data.ResultsDoneCountString,null)  + " of " +  Value(data.ResultsFullCountString,null);

    // give the php Object id to the Info button
    $('#info_' + data.elementID)
        .data('action_id',Value(data.ID))
        .data('inputName',Value(data.inputName))
        ;  

    $('#info_' + data.elementID).button( "option", "disabled", false );


    $('#progress_' + data.elementID).html(progressStr);    
    $('#status_' + data.elementID).html(Value(data.Status,""));
    
    $.each(data, function(index, value) 
    { 
        console.log(index + " = " +value);

    });
    
}


function UpdateProcess()
{
    // get selected 
    
    console.log("Update Process - get status of all running jobs and report");

}

var dialog = null;


function infoDialog(src)
{
    
    var dialogContent = 'Reteiving Job Information<br>' + 
                        '<img src="'+IconSource+'wait.gif">';
    
    var infoButtonID = src.id.toString();
    

    dialog = $('<div></div>')
            .html(dialogContent)
            .dialog({
                    autoOpen: false,
                    title: $('#' + infoButtonID).data('inputName'),
                    modal: true
            });

    dialog.dialog('open');
    
    
    // send to server command_id and the ID of the element that sent it.
    
        jData = { 
                 cmdID: $('#' + infoButtonID).data('action_id')
            ,elementID: src.id.toString()
        }
    
    
    $.post("HotSpotsCommandInfoAjax.php", jData , function(data) {postInfoDialog(data);},"json");    
    
    
    
}




function postInfoDialog(data)
{
    
    var msg = '';
    
    console.log('postInfoDialog ');
    
    $.each(data, function(index, value) 
    { 
        console.log('postInfoDialog .. ' + index + " = " +value);

    });    
    
    $(dialog).html('' + Value(data.msg.toString().replace('\n','<br>\n')));
    
    dialog = null;
    
}



function setDefaults()
{
    selectElementsDefault('Models');
    selectElementsDefault('Scenarios');
    selectElementsDefault('Times'  );
    selectElementsDefault('Bioclims');
    
    
    addInput('Species',50,"Pacific Black Duck (Anas (Anas) superciliosa)");
    addInput('Species',1999,"Red Wattlebird (Anthochaera (Anthochaera) carunculata)");
    
    updateCurrentPackage();
}


$(document).ready(function(){

    screenSetup();

    $('#tabs').height(699).tabs();
    $('.selectable')
        .selectable()
        .selectable(
              {stop: function(event, ui) {updateCurrentPackage();} 
            })
    ;


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


    $('#CreateProcess')
        .button()
        .click(function() {CreateProcess();})
        .css('margin',"10%")
        ;

    $('#UpdateProcess')
        .button()
        .click(function() {UpdateProcess();})
        ;

    $('#Defaults')
        .button()
        .click(function() {setDefaults();})
        ;




    $('#InputTypeSpecies').click();





});
