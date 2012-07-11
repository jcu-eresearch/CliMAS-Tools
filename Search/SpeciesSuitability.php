<?php
session_start();
include_once dirname(__FILE__).'/includes.php';


$scenarios = FinderFactory::Result("EmissionScenarioAllValues");
$scenarios instanceof Descriptions;

$models = FinderFactory::Result("ClimateModelAllValues");
$models instanceof Descriptions;

$times = FinderFactory::Result("TimeAllValues");
$times instanceof Descriptions;


?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Species Suitability</title>

<link type="text/css" href="css/start/jquery-ui-1.8.21.custom.css" rel="stylesheet" />
<script type="text/javascript" src="js/jquery-1.7.2.min.js"></script>
<script type="text/javascript" src="js/jquery-ui-1.8.21.custom.min.js"></script>

<link href="styles.css" rel="stylesheet" type="text/css">

<style>
#feedback { font-size: 1.4em; }
#selectable_species .ui-selecting { background: #FECA40; }
#selectable_species .ui-selected { background: #F39814; color: white; }
#selectable_species { list-style-type: none; margin: 0; padding: 0; width: 60%; }
#selectable_species li { margin: 3px; padding: 0.4em; font-size: 1.1em; height: 18px; }

#selectable_scenario .ui-selecting { background: #FECA40; }
#selectable_scenario .ui-selected { background: #F39814; color: white; }
#selectable_scenario { list-style-type: none; margin: 0; padding: 0; width: 60%; }
#selectable_scenario li { margin: 3px; padding: 0.4em; font-size: 1.0em; height: 18px; }

#selectable_model .ui-selecting { background: #FECA40; }
#selectable_model .ui-selected { background: #F39814; color: white; }
#selectable_model { list-style-type: none; margin: 0; padding: 0; width: 60%; height: 300px; }
#selectable_model li { margin: 3px; padding: 0.4em; font-size: 1.0em;  width: 25%; height: 30px; float: left;}

#selectable_time .ui-selecting { background: #FECA40; }
#selectable_time .ui-selected { background: #F39814; color: white; }
#selectable_time { list-style-type: none; margin: 0; padding: 0; width: 60%; }
#selectable_time li { margin: 3px; padding: 0.4em; font-size: 1.0em; height: 18px; }

#selectable_display_1 .ui-selecting { background: #FECA40; }
#selectable_display_1 .ui-selected { background: #F39814; color: white; }
#selectable_display_1 { list-style-type: none; margin: 0; padding: 0; width: 60%; }
#selectable_display_1 li { margin: 3px; padding: 0.4em; font-size: 1.0em; height: 100px; }



#test_process {float: left; }
#run_process {float: left; }


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


#demo-frame > div.slide_display { padding: 10px !important; }


.species-remove-button
{
    height: 20px;
    width: 20px;
}


</style>
<script>

<?php 

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

    if (jData.species == "")
    {   
        flash_red('#tab_handle_species a');
        $('#tabs').tabs( "select" , 0 );
        return false;
    }

    if (jData.model == "")
    {
        flash_red('#tab_handle_model a');        
        $('#tabs').tabs( "select" , 1 );
        return false;
    }

    if (jData.scenario == "")
    {
        flash_red('#tab_handle_scenario a');
        $('#tabs').tabs( "select" , 2 );
        return false;
    }
    
    if (jData.time == "")
    {
        flash_red('#tab_handle_time a');
        $('#tabs').tabs( "select" , 3 );
        return false;
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
                                //$( "#species-result" ).append(ui.item.value + " ");

                                //$( "#species" ).val(ui.item.label);

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

    $( "#tabs" ).tabs({
        show: function(event, ui) {toProcessDisplay();}
    });


    $( "#run_process" )
            .button()
            .click ( function() { startProcess(); return false;} );


    $( "#selectable_species" ).selectable();    
    $( "#selectable_model" ).selectable();
    $( "#selectable_scenario" ).selectable();
    $( "#selectable_time" ).selectable();
    
    
    $( "#selectable_model li" ).button();
    
    $( "#process-display" ).button();
    

    //$('#tabs').height(500);


})

$(document).ready(function(){
});


    
</script>
    
</head>
<body>
<h1 class="pagehead"><a href="SpeciesSuitability.php">Species Suitability</a></h1>

<div class="maincontent">

<div class="rhs">

<div id="tabs">
    <ul>
        <li id="tab_handle_species"><a href="#tabs-1">Species</a></li>
        <li id="tab_handle_model"><a href="#tabs-2">Climate Model</a></li>
        <li id="tab_handle_scenario"><a href="#tabs-3">Emission Scenario</a></li>
        <li id="tab_handle_time"><a href="#tabs-4">Time</a></li>
        <li id="tab_handle_process"><a href="#tabs-5">process</a></li>
    </ul>

    <div id="tabs-1">
        
        
        <div class="ui-widget">
            <label for="species">Species lookup: </label>
            <input id="species">
        </div>
        
        <ol id="selectable_species">

        </ol>	        
        
        
    </div>
	<div id="tabs-2">
        
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
	<div id="tabs-3">
        
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
	<div id="tabs-4">
        
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
	<div id="tabs-5">
        
            <div id="run_process">START GENERATION</div>

            <div id="process-display"></div>
            
            <div id="remote-queue-id"></div>
            <div id="remote-status"></div>
            <div id="remote-content"></div>
            <div id="remote-result"></div>


        </div>
    
</div>

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
