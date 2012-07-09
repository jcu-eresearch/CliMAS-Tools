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
<link rel="stylesheet" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/black-tie/jquery-ui.css" type="text/css" />
<script src='http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js'></script>
<script src='http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/jquery-ui.min.js'></script>
<link href="styles.css" rel="stylesheet" type="text/css">        

<style>
#feedback { font-size: 1.4em; }
#selectable_species .ui-selecting { background: #FECA40; }
#selectable_species .ui-selected { background: #F39814; color: white; }
#selectable_species { list-style-type: none; margin: 0; padding: 0; width: 60%; }
#selectable_species li { margin: 3px; padding: 0.4em; font-size: 1.4em; height: 18px; }

#selectable_scenario .ui-selecting { background: #FECA40; }
#selectable_scenario .ui-selected { background: #F39814; color: white; }
#selectable_scenario { list-style-type: none; margin: 0; padding: 0; width: 60%; }
#selectable_scenario li { margin: 3px; padding: 0.4em; font-size: 1.1em; height: 18px; }

#selectable_model .ui-selecting { background: #FECA40; }
#selectable_model .ui-selected { background: #F39814; color: white; }
#selectable_model { list-style-type: none; margin: 0; padding: 0; width: 60%; }
#selectable_model li { margin: 3px; padding: 0.4em; font-size: 1.1em; height: 18px; }

#selectable_time .ui-selecting { background: #FECA40; }
#selectable_time .ui-selected { background: #F39814; color: white; }
#selectable_time { list-style-type: none; margin: 0; padding: 0; width: 60%; }
#selectable_time li { margin: 3px; padding: 0.4em; font-size: 1.1em; height: 18px; }

#test_process {float: left; }
#run_process {float: left; }

#species-result {
/*    display: none;*/
}
#model-result  {
/*    display: none;*/
}
#scenario-result  {
/*    display: none;*/
}
#time-result {
/*    display: none;*/
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


</style>
<script>

<?php 

echo htmlutil::AsJavaScriptObjectArray(SpeciesData::speciesList(),"full_name","scientific_name","availableSpecies");    

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

function setTestData()
{
    
    selectSelectableElement(jQuery("#selectable_scenario"), $("#RCP3PD"),true);
    selectSelectableElement(jQuery("#selectable_model"), $("#gfdl-cm20"),true);
    selectSelectableElement(jQuery("#selectable_time"), $("#2015"),true);
    selectSelectableElement(jQuery("#selectable_time"), $("#2025"),false);
    selectSelectableElement(jQuery("#selectable_time"), $("#2035"),false);
    selectSelectableElement(jQuery("#selectable_time"), $("#2045"),false);
    selectSelectableElement(jQuery("#selectable_time"), $("#2055"),false);
    selectSelectableElement(jQuery("#selectable_time"), $("#2065"),false);
    selectSelectableElement(jQuery("#selectable_time"), $("#2075"),false);
    selectSelectableElement(jQuery("#selectable_time"), $("#2085"),false);
    

    var value = "Lethrinus lentjan";
    var valueID = value.replace(" ","_");
    var li = $("<li id=\""+valueID+"\"  class=\"ui-widget-content\" >"+value+"</li>");
    $('#selectable_species').append(li);
    $( "#species-result" ).append(valueID + " ");             
    
    
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
    var jData = { 
        cmdaction: 'SpeciesMaxent',
        species:   $('#species-result').html(),
        model:     $('#model-result').html(),
        scenario:  $('#scenario-result').html(),
        time:      $('#time-result').html()
    }
            
            
   $.post("QueueCommandAjax.php", jData , function(data) { postRun(data); },"json");
}

function updateProcess()
{

   var jData = { queueID: $('#remote-queue-id').html()}
   $.post("UpdateCommandAjax.php", jData , function(data) { postRun(data); },"json");
}

function clearQueue()
{
    
    $('#remote-queue-id').html(''); 
    $('#remote-status').html(''); 
    $('#remote-content').html(''); 
    
    $('#run_process').html('<span class="ui-button-text">RUN</span>');
    $('#run_process').button();
    
    $('#run_process').unbind('click');
    
    $('#run_process').click( function() { startProcess(); return false;} );
    
    $('#remote-result').html(''); 
    
}



$(document).ready(function(){

	$( "#species" ).autocomplete({ source: availableSpecies });

    $( "#tabs" ).tabs();    
    
    $( ".test_button" ).button();

    $( "#run_process" ).button();
    $( "#run_process" ).click ( function() { startProcess(); return false;} );


    $( "#test_process" ).click ( function() { setTestData(); return false;} );

    $( "#test_clear_queueid" ).button();
    $( "#test_clear_queueid" ).click ( function() { clearQueue();return false;} );


    $( "#selectable_model" ).selectable({
			stop: function() {
				var result = $( "#model-result" ).empty();
                $("#selectable_model .ui-selected").each(function(index) {result.append($(this).attr('id') + " ");});
			}
		});    



    $( "#selectable_scenario" ).selectable({
			stop: function() {
				var result = $( "#scenario-result" ).empty();
                $("#selectable_scenario .ui-selected").each
                (
                    function(index) {
                        result.append($(this).attr('id') + " ");
                    }
                );
			}
		});    

    
    $( "#selectable_time" ).selectable({
			stop: function() {
				var result = $( "#time-result" ).empty();
                $("#selectable_time .ui-selected").each(function(index) {result.append($(this).attr('id') + " ");});
			}
		});    
    
    
    $( "#selectable_species" ).selectable();    
    
    $("#species").keypress(function(event) {
            if(event.keyCode == 13) 
            { 
                var value = $("#species").val();
                var valueID = value.replace(" ","_");

                if ($('#'+valueID).html() == null )
                {
                    var li = $("<li id=\""+valueID+"\"  class=\"ui-widget-content\" >"+value+"</li>");
                    $('#selectable_species').append(li);
                    $( "#species-result" ).append(valueID + " ");             
                }

            }

        });


})

    
</script>
    
</head>
<body>
<h1 class="pagehead"><a href="SpeciesSuitability.php">Species Suitability</a></h1>

<div class="maincontent">

<div class="rhs">

<div id="tabs">
	<ul>
		<li><a href="#tabs-1">Species</a></li>
		<li><a href="#tabs-2">Climate Model</a></li>
		<li><a href="#tabs-3">Emission Scenario</a></li>
		<li><a href="#tabs-4">Time</a></li>
		<li><a href="#tabs-5">process</a></li>
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
                <li id="<?php  echo $key; ?>" class="ui-widget-content"><?php  echo $key; ?><span style="font-size: 80%;"></span></li>
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
                <li id="<?php  echo $key; ?>" class="ui-widget-content"> <?php  echo $key; ?>&nbsp;&nbsp;&nbsp;<span style="font-size: 80%;">(<?php  echo $value; ?>)</span></li>
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
                <li id="<?php  echo $key; ?>" class="ui-widget-content"> <?php  echo $key; ?></li>
            <?php                     
            }
            ?>
        </ol>	
	</div>
	<div id="tabs-5">
        
        <div id="test_process" class="test_button">test data</div> 
        <div id="test_clear_queueid">CLEAR ID</div>
        <div id="run_process">RUN</div>
        
        <div id="species-result"></div>
        <div id="model-result"></div>
        <div id="scenario-result"></div>
        <div id="time-result"></div>
        
        <div id="remote-queue-id"></div>
        <div id="remote-status"></div>
        <div id="remote-content"></div>
        <div id="remote-result"></div>
        
        
	</div>
    
</div>

</div><!-- End demo -->    
    
    
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
