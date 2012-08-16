<?php
session_start();
include_once 'includes.php';

$reset = array_util::Value($_GET, "reset","false");

$species_id = array_util::Value($_GET, "id",null);


function display($species_id)
{
    
    if (is_null($species_id)) return "";
    
    $data = SpeciesData::GetModelledDataForModel($species_id,'QUICK_LOOK');

    $result = "";

    $first = array_keys(util::first_element($data));

    $width = round(100/count($first),0);
    
    $result .= '<td width="'.$width.'%" >'.implode("</td><td>",$first)."</td>";

    foreach ($data as $time => $scenario_data)
    {

        $result .= "<tr>";

        foreach ($scenario_data as $scenario => $file_unique_id) 
        {

            $result .= "<td>";
            $result .= '<img src="file.php?id='.$file_unique_id.'" style="width: 220px; height:180px;">';
            $result .= "</td>";
        }

        $result .= "</tr>";

    }
 
    return $result;
    
}



?>
<HTML>
<HEAD>
    <script type="text/javascript">

    </script>
</HEAD>
    <BODY>        
        <table width="100%" >
            <?php echo display($species_id); ?>
        </table>
    </body>
</html>