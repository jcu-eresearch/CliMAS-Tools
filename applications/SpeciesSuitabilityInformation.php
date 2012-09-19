<?php
/**
 * Read Query String variable "combination" and find Scenario and Model Descriptions
 *  
 * combination must be in the form   SCENARIO_MODEL_TIME
 * 
 */
session_start();
include_once 'includes.php';

$combination = array_util::Value($_GET, "combination",null);

// get Scenario and MOdel and then get their descriptions

$scenario_info = "";
$model_info = "";

if (!is_null($combination))
{
    list($scenario,$model,$time) = explode("_",$combination);

    $scenario_desc = DatabaseClimate::GetScenarioDescription($scenario);
    $model_desc    = DatabaseClimate::GetModelDescription($model);
    
    $format = '<a target="scenario" href="{URI}"><b>{DataName}</b></a><i>{Description}</i>{MoreInformation}';
    $scenario_info = $scenario_desc->asFormattedString($format);
    
    $format = '<a target="model" href="{URI}"><b>{DataName}</b></a><i>{Description}</i>{MoreInformation}';
    $model_info = $model_desc->asFormattedString($format);
    
}




?>
<HTML>
<HEAD>
    <title>Information</title>
    <script type="text/javascript" src="js/jquery-1.7.2.min.js"></script>
    <script type="text/javascript" src="js/jquery-ui-1.8.21.custom.min.js"></script>
    <script type="text/javascript" src="js/jquery.pulse.min.js"></script>
    <script type="text/javascript" src="js/Utilities.js"></script>

    <link type="text/css" href="css/start/jquery-ui-1.8.21.custom.css" rel="stylesheet" />
    <link type="text/css" href="css/selectMenu.css" rel="stylesheet" />
    
    <script type="text/javascript" >
    <?php     
     ?>
         
    $(document).ready(function(){
        
        
    });


    </script>
    
    <script type="text/javascript" src="SpeciesScenarioTimeline.js"></script>
   
</HEAD>
    <BODY>        
        <div id="ScenarioInformation"><?php echo $scenario_info; ?></div>
        <div id="ModelInformation"><?php echo $model_info; ?></div>
        
    </body>
</html>