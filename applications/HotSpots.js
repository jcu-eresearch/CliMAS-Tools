// GLOBAL VARIABLES

/*

asa

*/


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
    
    // check to see that ID doesn't already exists
    
    if ( exists('#' + addID)) 
    {
        var properties = {
            color : '#FF0000',
            fontWeight : 'bold'
        };

        $('#' + addID).pulse(properties, {pulses : 2});        
        return null;
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
    
    
    
   return addID;
    
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
                            var addid = addInput('Genus',ui.item.value,ui.item.label);
                            
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
    
    $('#InputTypesSet').buttonset();

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



function selectAllModels()
{
    
    var ofWhat = "Models";    
    $('#' + ofWhat + "Selection").find('li').addClass('ui-selected') ;
    $('#' + ofWhat + "Selection").find('h4').addClass('ui-selected') ;
    $('#' + ofWhat + "Selection").find('p').addClass('ui-selected') ;
    updateCurrentPackage();    
    
}


function selectAllScenarios()
{
    
    var ofWhat = "Scenarios";    
    $('#' + ofWhat + "Selection").find('li').addClass('ui-selected') ;
    $('#' + ofWhat + "Selection").find('h4').addClass('ui-selected') ;
    $('#' + ofWhat + "Selection").find('p').addClass('ui-selected') ;
    updateCurrentPackage();    
    
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

    }    
    
    
    
}

function currentData()
{
    var jData = { 
              taxa: selected('#TaxaSelection'     ,"li", null," ",'Taxa_'     ,null)
           ,family: selected('#FamilySelection'   ,"li", null," ",'Family_'   ,null)
            ,genus: selected('#GenusSelection'    ,"li", null," ",'Genus_'    ,null)
          ,species: selected('#SpeciesSelection'  ,"li", null," ",'Species_'  ,null)
         ,location: selected('#LocationSelection' ,"li", null," ",'Location_' ,null)
        ,scenarios: selected('#ScenariosSelection',"li", "ui-selected"," ",'Scenarios_',null)
            ,times: selected('#TimesSelection'    ,"li", "ui-selected"," ",'Times_'    ,null)
  ,job_description: $('#job_description').val()
    ,ui_element_id: ''
    }
    
    //console.log(jData);
    
    return jData;
    
}




/**
 *  Current Display of selected items
 * 
 */
function updateCurrentPackage()
{
    
    var jData = currentData();
    
         $('#CountTaxa').html(jData.taxa.length);
       $('#CountFamily').html(jData.family.length);
        $('#CountGenus').html(jData.genus.length);
      $('#CountSpecies').html(jData.species.length);
     
       
    $('#CountScenarios').html(jData.scenarios.length);
        $('#CountTimes').html(jData.times.length);


     var inputsCount = jData.taxa.length +  jData.family.length + jData.genus.length + jData.species.length;
     
     // var futureCount = jData.models.length * jData.scenarios.length * jData.times.length;
     var futureCount = jData.times.length; // at the moment we are including all scenarios (all models are mdeians of all cliemnt models)
     

    $('#CountInputTotals').html(inputsCount);
    $('#CountFutureTotals').html(futureCount);
    $('#CountGrandTotal').html(inputsCount * futureCount);

    

}



var statusUpdateTimer = null;

function CreateProcess()
{
    // check currentDataPackage() to mak sure we have al;l the data we need to running
    var sData = currentData();

    addSingleProcess(sData);


    // if we have added one procvess thn setup a timer to up date display everyh so many seconds
    statusUpdateTimer = self.setInterval(function(){UpdateProcess()},5*1000);  // 5 seconds

}


function addSingleProcess(sData)
{
    
    console.log("Create for  " );
    console.log(sData);

    var rowElementID = $('#RunningProcessesTable li').children().length;

    sData.ui_element_id = rowElementID;

    //alert("rowElementID = " + rowElementID  + "\n" + sData.toString());

    var displayNameStr = "";

    displayNameStr +=                     "Taxa: "    + sData.taxa.length;
    displayNameStr += "&nbsp&nbsp&nbsp" + "Family: "  + sData.family.length
    displayNameStr += "<br>"            + "Genus: "   + sData.genus.length
    displayNameStr += "&nbsp&nbsp&nbsp" + "Species: " + sData.species.length


    var cancelButton = '<button id="cancel_'+ rowElementID +'">CANCEL</button>';


    
    //var calcCount   = '<h1 class="ui-widget-content ui-corner-all">' + (sData.models.length * sData.scenarios.length * sData.times.length) +'<p>datasets</p> </h1>';

    // atre only want to calc the nimber inputs * the number of Times
    var calcCount   = '<h1 class="ui-widget-content ui-corner-all">' + (sData.times.length) +'<p>datasets</p> </h1>';
    
    var displayName = '<h2>' + displayNameStr + '</h2>';
    
    var button = '<button id="info_'+ rowElementID +'">'+ Value(sData.job_description,'')+'</button>';
    
    var progress = '<div id="progress_'+ rowElementID +'"><img style="width: 100%; height: 100%" src="'+IconSource+'Loading.gif"></div>';
    
    var html = '<li class="ui-widget-content">' + cancelButton + button + calcCount + displayName +  progress + '<p id="status_'+rowElementID+'">........</p></li>'+"\n";

    $('#RunningProcessesTable').append(html);

    $('#info_' + rowElementID)
        .button()
        .css('float','left')
        .css('width','200px')
        .css('height','97%')
        .css('margin','2px')
        .css('text-align','center')
        .click(function() {infoDialog(this);return false;})
        .button( "option", "disabled", true )
        ;

    $('#cancel_' + rowElementID)
        .button()
        .button({text: false, icons: {primary: "ui-icon-closethick"}})
        .css('float','left')
        .css('width','40px')
        .css('height','97%')
        .css('margin','2px')
        .css('text-align','center')
        ;


    $('#progress_' + rowElementID)
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
    
    var progressStr = "";
    
    progressStr = Value(data.ProgressPercent,0);
    if (progressStr == 'null') progressStr = 0;

    // give the php Object id to the Info button
    $('#info_' + data.ui_element_id)
        .data('action_id',Value(data.NiceID))
        ;  


    $('#info_' + data.ui_element_id).button( "option", "disabled", false );

    $('#progress_' + data.ui_element_id).html(progressStr + "%");    
    $('#status_' + data.ui_element_id).html(Value(data.Status,""));
    
    
    $.each(data, function(index, value) 
    { 
        console.log('postAddSingleProcess  ' + index + " = " +value);

    });
    
}




function UpdateProcess()
{
    // get selected 
    
    //console.log("Update Process - get status of all running jobs and report");

    // get all #info_*

    var sData = null;

    var id = null;
    var action_id = null;
    var ui_element_id = null;
    
    $('[id*="info_"]').each( function() {
    
        id = this.id.toString();
    
        action_id = $('#'+id).data('action_id');
    
        ui_element_id = id.replace('info_','');
    

        // when we scan for updates  - check the progress percent 
        // should only log jobs for update where   progress percent <  100   $('#info_' + data.ui_element_id).data('ProgressPercent',Value(data.ProgressPercent));  
    
    
        //console.log("Update Process - with action_id  " + action_id);
    
        sData = { 
              action_id:  action_id,
          ui_element_id:  ui_element_id
              
        }
    
        $.post("HotSpotsAjaxUpdate.php", sData , function(data) {postUpdateProcess(data);},"json");
    
    });


     /// Clear Timer Interval here really
     // scan thru all the jobs and make sure all are complete


}

/**
 *  Update screen for one job (row)
 *  
 */
function postUpdateProcess(data)
{
    
    //console.log("POST Update Process - get status of all running jobs and report");
    
    console.log("Timed Update ?");
    console.log(data);
    
    
    var ui_element_id = Value(data.ui_element_id);
    
    $('#status_' + ui_element_id).html(Value(data.Status,""));
    
    $('#info_' + data.ui_element_id).data('result',Value(data.Result));  
    $('#info_' + data.ui_element_id).data('job_description',Value(data.job_description));  

    $('#info_' + data.ui_element_id).data('ProgressPercent',Value(data.ProgressPercent));  

    $('#progress_' + ui_element_id).html(Value(data.ProgressPercent) + "%");
    
    
                              
    if (data.ExecutionFlag == 'EXECUTION_FLAG_COMPLETE')
    {
        clearInterval(statusUpdateTimer);  // this needs to happen when all have been complete    
        
        // setup button to allow user to show results for this job.
        buildRichnessOutputTab(Value(data.job_description,''),Value(data.Result),data)
        
        $('#tabs').tabs('select', $('#tabs').tabs('length') -1 ); // once complete and tab is there chnage to it
        
    }
    
    
    
}


/**
 * Create new table for this job
 */
function buildRichnessOutputTab(job_description,result,data)
{
    
    var ID = Value(data.ID,-1);
    
    if (ID == -1) return;
    
    ID = ID.replace('.',"_");
    ID = ID.replace(' ',"_");
    
    
    var parameters = modelledParametersFromRichnessResult(result);

    var newTabContentId = 'completed_'+ID;

    if (exists('#' + newTabContentId)) return;
    
    $('working').append('<div id="'+newTabContentId+'"></div>');

    var cmd_id = Value(data.ID,-1);

    var returnLinkHREF = '';
    if (cmd_id != -1)
    {
        var url = ApplicationFolderWeb + "applications/HotSpots.php?cmd=" + Value(data.ID);
        returnLinkHREF = '<a target="_results' + ID + '" href="'+url+'"><img name="Bookmark result" alt="Bookmark result" style="width: 20px; height: 20px" border="0" src="'+IconSource+'bookmark.png"></a>';
    }


    $('#tabs').tabs("add",'#' + newTabContentId,"Richness:: " + job_description + '&nbsp;' + returnLinkHREF);
    
    $("#" + newTabContentId).css("display","block");

    //var combinations = result.split("~");

    var combination = null;

    var pair = null;

        
    var scenario_id = null
    
    var comboStart = null;
    var comboEnd = null;
    var comboLength = null;
    var comboStr = "";
    var comboFilename_asc = '';
    var comboFilename_png = '';
    
    var firstTime = '';
    
    var msg  = ""; 
    
    
    $.each(parameters.scenarios, function(index, scenario) 
    {
        
        scenario_id = 'row_'+newTabContentId+'_'+scenario;
        
        //console.log('scenario_id = ' + scenario_id + "\n");

        msg += '<div class="richnessScenario ui-widget-content ui-corner-all" id="'+scenario_id+'">';
        msg += '<h1 class="ui-widget-header ui-corner-all" >' + scenario + '</h1>';
        msg += '<ul class=" ui-widget-content ui-corner-all"  >'; 
        $.each(parameters.times, function(index, time) 
        {
            
            comboFilename_asc = "";
            comboFilename_png = "";
            
            time = $.trim(time);
            if (time == '') return;
                        
            if (firstTime == '') firstTime = time;
            
            combination = scenario + '_' + time;
            
            comboStart = result.indexOf(combination);
            
            comboEnd = result.indexOf('~',comboStart);
            
            comboLength = comboEnd - comboStart;
                        
            comboStr = result.substr(comboStart,comboLength);
            
            comboFilename_asc = $.trim(comboStr.replace(combination + "=",""));

            
            if (comboFilename_asc == '') return;
            
            comboFilename_asc = Maxent_Species_Data_folder_web + 'richness/' + comboFilename_asc;
            
            comboFilename_png = comboFilename_asc.replace('.asc','.png');
            
            msg += '<li class="time_cell time_'+time+'" id="'+newTabContentId +  '_' + combination+'">';
            msg += '<img src="' + comboFilename_png + '" />' + '';
            msg += '</li>';

        });
        msg += "</ul>"; 
        msg += "</div>"; 
        
    });



    var time_menu = ''

        time_menu += '<div class="richnessScenario_timeselector ui-widget-content ui-corner-all" id="'+scenario_id+'">';
        time_menu += '<h1 class="ui-widget-header ui-corner-all" >TIME</h1>';
        time_menu += '<ul class="ui-widget-content ui-corner-all"  >'; 
        
        $.each(parameters.times, function(index, time) 
        {
            
            time_menu += '<li >';
            time_menu += '<button class="job_time_select" id="timeSelect_' + newTabContentId + '_' + time + '">' + time + '</button>';
            time_menu += '</li>';
        });
        
        time_menu += "</ul>"; 
        time_menu += "</div>"; 

    $("#" + newTabContentId).html(time_menu + msg);

    $('.job_time_select')
        .button()
        .css('float','left')
        .css('width','100%')
        .click(function() {richness_time_select(this)})
        ;


    $('.time_cell').hide();
    
    $('.time_' + firstTime).show();

    if ($('.job_time_select').length == 1)
    {
        // disable ability to hide as there is nothing else to show
        
         $('.job_time_select').button( "option", "disabled", true );
    }


}

function richness_time_select(src)
{
 
   var id = src.id.toString();
 
   $('.job_time_select').removeClass('ui-state-active');
   $('#'+id).addClass('ui-state-active');
 
   var bits = id.split('_');
 
   var time = bits[2];
 
    $('.time_cell').hide();
    
    $('.time_' + time).show();
 
}

/**
s * From result passed back via AJX - return arrays of Scenario names, MOdel names, and times   
 *    
 * returns object
 *    scenarios: array of scenario names
 *    models:    array of model names
 *    times:     array of times 
 * 
 */
function modelledParametersFromRichnessResult(result)
{
    
    var combinations = result.split("~");


    var pair = null;
    var combination = null;
    
    var scenario = null;
    var time     = null;

    var scenario_str  = '';
    var time_str     = '';

    $.each(combinations, function(index, combination_quicklook) 
    { 
        
        
        pair = combination_quicklook.split('=');
        combination = pair[0].split('_');

        scenario = combination[0];
        time     = combination[1];

        if (!contains(scenario_str,scenario))
        {
            if (scenario_str == "") 
                scenario_str  = scenario
            else
                scenario_str += ","+ scenario
        }

        if (!contains(time_str,time))
        {
            if (time_str == "") 
                time_str  = time
            else
                time_str += ","+ time
        }


    });


        result = {scenarios: scenario_str.split(",")
                     ,times: time_str.split(",")
                 }


    // returns object
    // scenarios: array of scenario names
    // times:     array of times 

    return result;

}



/**
 * Happens if they passed in cmd=12345.122345 on url 
 */
function previousCommand(cmd)
{
    
        if (cmd == '') return;
    
        sData = { 
              action_id:cmd
        }
    
    
    
        $.post("HotSpotsAjaxGetCommandValues.php", sData , function(data) {postPreviousCommand(data);},"json");
    
}


function postPreviousCommand(data)
{
    
    
    buildRichnessOutputTab(Value(data.job_description), Value(data.Result),data)
    
    $('#tabs').tabs('select', $('#tabs').tabs('length') -1 );
    
    
}



var dialog = null;

function infoDialog(src)
{
    
    var dialogContent = 'Reteiving Job Information<br>' + 
                        '<img src="'+IconSource+'wait.gif">';
    
    var infoButtonID = src.id.toString();

    var result = $('#' + infoButtonID).data('result');  


    var action_id = $('#' + infoButtonID).data('action_id');

    console.log("from info button result =    " + result);

    console.log("get info for  " + action_id);
    

    dialog = $('<div></div>')
            .html(dialogContent)
            .dialog({
                    autoOpen: false,
                    title: "Info",
                    modal: true
            });

    dialog.dialog('open');
    
    
    // send to server command_id and the ID of the element that sent it.
    
    var 
        jData = { 
             action_id: action_id
        }
    
    
    $.post("HotSpotsCommandInfoAjax.php", jData , function(data) {postInfoDialog(data);},"json");    
    
    
    
}




function postInfoDialog(data)
{


    // for debug
    $.each(data, function(index, value) 
    { 
        console.log('postInfoDialog .. ' + index + " = " +value);
    });    

    var msg = '';

    var job_description = Value(data.job_description);
    
    var status = Value(data.Status);

    var result = Value(data.Result);

    msg = "<h1>" + job_description + "</h1><br><i>" + status + "</i>";
    

    $(dialog).html(msg);

    dialog = null;
    
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


    $('#InputTypeSpecies').click();


    selectAllModels();
    selectAllScenarios();

    $('#tabs-2').hide();
    $('#tabs-3').hide();

    $('#tab_label_2').hide();
    $('#tab_label_3').hide();
   
    if (cmd != '') previousCommand(cmd);
   
   
   
   

});
