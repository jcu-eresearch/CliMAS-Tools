<?php
/**
 * Main page for Species Suitability tool
 * 
 * 
 *  
 */
session_start();
include_once dirname(__FILE__).'/includes.php';

$species_id = array_util::Value($_GET, "species_id",null);

$name = SpeciesData::SpeciesCommonNameSimple($species_id);


$scenarios = DatabaseClimate::GetScenarios();
$models    = DatabaseClimate::GetModels();
$times     = DatabaseClimate::GetTimes();

$scenarios = array_flip($scenarios);
$models    = array_flip($models);
$times     = array_flip($times);

unset($scenarios['CURRENT']);
foreach (explode(",","SRESA1B,SRESA1FI,SRESA2,SRESB1,SRESB2") as $remove)  unset($scenarios[$remove]);


unset($models['CURRENT']);
unset($models['ALL']);

$models['all'] = "Median";

unset($times['1990']);
unset($times['1975']);

$scenarios = array_flip($scenarios);
$models    = array_flip($models);
$times     = array_flip($times);

sort($scenarios);
sort($models);
sort($times);




?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Species Suitability:: Data Download for <?php echo "{$name}"; ?></title>

<script>
// GLOBAL VARIABLES
<?php
$Maxent_Species_Data_folder_web = configuration::Maxent_Species_Data_folder_web().$species_id.configuration::osPathDelimiter().configuration::Maxent_Species_Data_Output_Subfolder().configuration::osPathDelimiter();
echo htmlutil::AsJavaScriptSimpleVariable($Maxent_Species_Data_folder_web,'Maxent_Species_Data_folder_web');
?>
    
    var data_folder = Maxent_Species_Data_folder_web + ""
    
</script>

<script type="text/javascript" src="js/jquery-1.7.2.min.js"></script>
<script type="text/javascript" src="js/jquery-ui-1.8.21.custom.min.js"></script>
<script type="text/javascript" src="js/Utilities.js"></script>
<link type="text/css" href="css/start/jquery-ui-1.8.21.custom.css" rel="stylesheet" />
<link href="styles.css" rel="stylesheet" type="text/css">

<style>

    #download_links td
    {
        margin: 0px;
        padding: 0px;
    }
    
    #download_links .tc 
    {
        
        width: 70px;
    }
    
</style>

</head>
<body>
    <h1 class="pagehead"><a href="index.php"><img src="<?php echo configuration::IconSource()."Suitability.png" ?>" border="0" /></a></h1>

<div class="maincontent">

    <div id="ToolBar" class="ui-widget-header ui-corner-all" style="padding: 20px; width: 50%; font-size: 1.4em;">
            DATA DOWNLOAD <?php echo "{$name}"; ?>
    </div>
    <br>
    <div id="download_links" class="ui-widget-header ui-corner-all" style="padding:5px; font-size: 1.2em;" >
        Future Predictions  (ASCII grid format)
    </div>
    
    <div id="download_links" class="ui-widget-content ui-corner-all" style="float: none; clear: both; height: 3000px;" >

        <?php 
            $current = "{$Maxent_Species_Data_folder_web}1990.asc.gz";
        ?>

        <div id="current_condition" class="ui-widget-header ui-corner-all" style="margin: 10px; text-align: center; padding: 8px; width: 300px; height:30px;  font-size: 1.2em;float: left;  height:" >
            <a href="<?php echo $current; ?>">CURRENT CONDITIONS</a>
        </div>
        
        
        <div id="download_all" class="ui-widget-header ui-corner-all" style="margin: 10px; text-align: center; padding: 8px; width: 300px; height:30px;  font-size: 1.2em;float: left;  height:" >

            <?php 
            
                $true_complete_filename = SpeciesData::species_data_folder($species_id)."complete.zip";
                $complete = "{$Maxent_Species_Data_folder_web}complete.zip";
                if (file_exists($true_complete_filename))
                {
                    echo '<a href="'.$complete.'">DOWNLOAD ALL (large zip file)</a>';
                }
                else
                {
                    echo 'full download unavailable';
                }
            
            ?>
            
        </div>

        <br style="clear: both; float: none;">
        
        
        <?php 
            
            foreach ($scenarios as $scenario) 
            {

                echo "\n".'<table width="650" border="1" style="margin: 10px;float:left; height: 500px;"><tr>';
                
                echo "\n".'<td width="100" >'.$scenario.'</td>';
                
                echo "\n".'<td width="500" >'; // inside here is model time table
                
                echo "\n".'<table  border="0" cellspaceing="0" cellpadding="0">';
                
                foreach ($models as $model) 
                {
                    
                    echo "\n".'<tr>';
                    
                    $model_name = $model;
                    
                    if ($model_name == "ALL") $model_name = "Median";
                    
                    echo "\n".'<td width="150" >'.$model_name.'</td>';
                    
                    foreach ($times as $time) 
                    {
                        // creating link  - if model is ALL check that we get the right filename
                        // look for standard name otherwise look with _median 

                        $true_filename = SpeciesData::species_data_folder($species_id)."{$scenario}_{$model}_{$time}.asc.gz";
                        
                        $link = (file_exists($true_filename)) ?  basename($true_filename) : null;
                        
                        echo "\n".'<td width="50" ><a href="'.((is_null($link) ? '#' : $Maxent_Species_Data_folder_web.$link)).'">'.((is_null($link) ? '' : $time)).'</a></td>';

                    }
                    echo "\n".'</tr>';

                }
                
                echo "\n".'</table>';
                
                echo "\n".'</td>';
                
                echo "\n".'</tr></table>';
                
            }
        
        
        ?>
        
        
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

<div id="messages_container" style="height:0px; width:0px;"></div>

</body>
</html>
