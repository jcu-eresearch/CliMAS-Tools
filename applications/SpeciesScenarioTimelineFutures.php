<?php
session_start();
include_once 'includes.php';

$reset = array_util::Value($_GET, "reset","false");

$species_id = array_util::Value($_GET, "id",null);

$times = DatabaseClimate::GetTimes();


function display($species_id)
{
    

    if (is_null($species_id)) return "";
    
    $species_id = trim($species_id);    
    
    if ($species_id == "") return "";
    
    $current_quick_look_id = SpeciesData::SpeciesCurrentQuickLook($species_id);
    
    $data = SpeciesData::GetModelledDataForModelStandardised($species_id,'QUICK_LOOK','ALL');
    
    if ($data instanceof ErrorMessage)
    {
        print_r($data);
        return "";
        
    }
    
    $result = "";
    
    foreach ($data as $scenario => $scenario_data)
    {
        if ($scenario == "CURRENT") continue;
        
        $result .= "\n".'<div class="row">';

            $result .= "\n".'<div id="scenario_'.$scenario.'" class="rowHeader">'.$scenario.'</div>';

            if (is_null($current_quick_look_id))
                $result .= "\n".'<div id="current_'.$scenario.'" class="cell current">CURRENT</div>';
            else    
                $result .= "\n".'<div id="current_'.$scenario.'"class="cell current"><img src="file.php?id='.$current_quick_look_id.'" style="width: 180px; height:260px;"></div>';
        
        
            foreach ($scenario_data as $time => $file_unique_id) 
            {

                $id = "{$scenario}_{$time}";
                $result .= "\n".'<div id="'.$id.'" class="cell future  time_'.$time.'">';
                if (is_null($file_unique_id))
                    $result .= "\n".'NOT COMPUTED';
                else    
                    $result .= "\n".'<a target="_new" href="file.php?id='.$file_unique_id.'" border="0" ><img id="image_'.$id.'" src="file.php?id='.$file_unique_id.'" style="width: 180px; height:260px;"></a>';
                $result .= "\n"."</div>";

            }

            
        $result .= "\n"."</div>";

    }
 
    return $result;
    
}



?>
<HTML>
<HEAD>
    <title>Projections for <?php echo $species_id ?></title>
    <script type="text/javascript" src="js/jquery-1.7.2.min.js"></script>
    <script type="text/javascript" src="js/jquery-ui-1.8.21.custom.min.js"></script>
    <script type="text/javascript" src="js/jquery.pulse.min.js"></script>
    <script type="text/javascript" src="js/Utilities.js"></script>

    <link type="text/css" href="css/start/jquery-ui-1.8.21.custom.css" rel="stylesheet" />
    <link type="text/css" href="css/selectMenu.css" rel="stylesheet" />
    
    <link type="text/css" href="SpeciesScenarioTimelineFutures.css" rel="stylesheet" />
    
    <script type="text/javascript" >
    <?php     
     ?>
         
         
    function ShowImage(src)
    {
        
        var id = src.id.toString();        
        $( "#" + id).dialog();
        
    }
         
    function SetCurrent()
    {
        $('.future_tool_button').removeClass('ui-state-active');
        $('.future_tool_button').attr('aria-pressed',false);
        $('.cell').hide();
        $('#ToolCurrent').addClass('ui-state-active');
        $('.current').show();
    }
         
    function setTime(time)
    {
        $('#ToolCurrent').removeClass('ui-state-active')
        $('.cell').hide();
        $('.time_' + time).show();
    }
         
         
    $(document).ready(function(){
        $('.future').hide();
        
        $( "#projections_toolbar_tools" )
            .buttonset()
            .css('font-size','0.7em')
            ;
        
        
    });


    </script>
    
    <script type="text/javascript" src="SpeciesScenarioTimeline.js"></script>
   
</HEAD>
    <BODY>        
        
        <div id="projections_toolbar" class="ui-widget-header ui-corner-all" >
            <div id="projections_toolbar_tools">
                <button id="ToolCurrent" onclick="SetCurrent();" >CURRENT</button>
                <?php 
                foreach ($times as $time) 
                {
                    if ($time  < 2000) continue;
                    echo '<input class="future_tool_button" name="TimesTools" type="radio" id="times_tool_'.$time.'"  onclick="setTime(\''.$time.'\')" /><label for="times_tool_'.$time.'">'.$time.'</label>';    
                }
                
                ?>
                
            </div>

        </div>

        <?php echo display($species_id); ?>
    </body>
</html>