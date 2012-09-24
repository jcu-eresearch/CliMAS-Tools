<?php
/**
 * Implementation of MAPSERVER mapping interface ffor use with Suitability Tool
 * 
 *  
 */
session_start();
include_once 'includes.php';

$bucket_count = 20;

$ascii_grid_filename = '';
$ascii_grid_webname = '';

$GUI = null; 

$species_id = array_util::Value($_POST, "SpeciesID",null);

$MaxentThreshold = DatabaseMaxent::GetMaxentThreshold($species_id);
if ($MaxentThreshold instanceof ErrorMessage || is_null($MaxentThreshold))  $MaxentThreshold = 0;


$UserLayer = array_util::Value($_POST, "UserLayer","");  // is a combination of Scenario_model_time we need the ascii grid version

$map_path = array_util::Value($_SESSION,'map_path');

if (!file_exists($map_path)) $map_path = null;

$body_background = "";

if (is_null($map_path) && $UserLayer == "")
{
    $M = new MapServerWrapper();

    // add background layers
    foreach (Session::MapableResults() as $MapableResult)
        $M->Layers()->AddLayer($MapableResult);
    
    $MF = Mapfile::create($M);
    $_SESSION['map_path'] = $MF->save($M);
    $map_path = $_SESSION['map_path'];
    
    
    $GUI = MapserverGUI::create($_SESSION['map_path']);
    if (is_null($GUI)) die ("Map Server GUI failed");

    if ($GUI->hasInteractive()) $GUI->ZoomAndPan();        
    
}

if (!is_null($map_path) && $UserLayer == "")
{
    
    $GUI = MapserverGUI::create($_SESSION['map_path']);
    if (is_null($GUI)) die ("Map Server GUI failed");

    $map_path = $_SESSION['map_path'];
    
    if ($GUI->hasInteractive()) $GUI->ZoomAndPan();        
    
}


if ($UserLayer != "")
{

    // get the ascvi grid filename
    
    
        
    $M = new MapServerWrapper();

    // add background layers
    foreach (Session::MapableResults() as $MapableResult)
        $M->Layers()->AddLayer($MapableResult);

    // add user layer

    $ascii_grid_filename = SpeciesFiles::species_data_folder($species_id)."{$UserLayer}.asc";
    if (file_exists($ascii_grid_filename))
    {
        $ascii_grid_webname  = SpeciesFiles::species_data_folder_web($species_id)."{$UserLayer}.asc";
    }
    else
    {
        $ascii_grid_filename = SpeciesFiles::species_data_folder($species_id)."{$UserLayer}_median.asc"; // old filename convention to add median to the end of the filename        
        $ascii_grid_webname  = SpeciesFiles::species_data_folder_web($species_id)."{$UserLayer}_median.asc";
    }
        
    
    
    if (file_exists($ascii_grid_filename))
    {
        $layer = $M->Layers()->AddLayer($ascii_grid_filename);
        $layer instanceof MapServerLayerRaster;
        $layer->HistogramBuckets($bucket_count);

        // this bit here needs to be moved - and only called if we want maxent 
        $ramp = RGB::Ramp($MaxentThreshold, 1, $bucket_count,RGB::ReverseGradient(RGB::GradientYellowOrangeRed()));

        $display_threshold = DatabaseMaxent::GetMaxentResult($species_id, DatabaseMaxent::$DisplayThresholdFieldName);
        if ( !($display_threshold instanceof ErrorMessage))
            foreach ($ramp as $key => $rgb)  
                if ($key < $display_threshold) $ramp[$key] = RGB::ColorWhite(); // set colour to black for below threshold


        $layer->ColorTable($ramp);             
        
    }
    


    $MF = Mapfile::create($M);
    $_SESSION['map_path'] = $MF->save($M);
    $map_path = $_SESSION['map_path'];

    
    $GUI = MapserverGUI::create($_SESSION['map_path']);
    if (is_null($GUI)) die ("Map Server GUI failed");

    if ($GUI->hasInteractive()) $GUI->ZoomAndPan();                

    
    if (!is_null($GUI)) 
    {
        
    }

} 


?>
<HTML>
<HEAD>
    <TITLE></TITLE>
    <link type="text/css" href="css/start/jquery-ui-1.8.21.custom.css" rel="stylesheet" />
    <script type="text/javascript" src="js/jquery-1.7.2.min.js"></script>
    <script type="text/javascript" src="js/jquery-ui-1.8.21.custom.min.js"></script>
    <script type="text/javascript" src="js/selectMenu.js"></script>
    <script type="text/javascript" src="js/Utilities.js"></script>
    
    
    <script type="text/javascript">
    function GetZoom() {
        document.getElementById('ZoomFactor').value = parent.document.getElementById('ZoomFactor').value;
        document.getElementById('UserLayer').value  = parent.document.getElementById('UserLayer').value;        
        document.getElementById('SpeciesID').value  = parent.document.getElementById('SpeciesID').value;
        document.getElementById('MaxentThreshold').value  = parent.document.getElementById('MaxentThreshold').value;
        
    }

    $(document).ready(function(){
            

    });
    </script>
    
    <style>
        <?php echo $body_background; ?>
        
    </style>
    
</HEAD>
<BODY>
    <?php // echo "map_path =  {$map_path}"; ?>
    <FORM style="margin: 0px; padding: 0px;"  id="MAP_FORM"  onsubmit="GetZoom()" METHOD=POST ACTION="<?php echo $_SERVER['PHP_SELF']?>">
        <INPUT id="mapa" TYPE=IMAGE NAME="mapa" SRC="<?php if (!is_null($GUI)) echo $GUI->MapImageLocation();?>" style="clear:both; ">
        <INPUT TYPE=HIDDEN ID="ZoomFactor" NAME="ZoomFactor" VALUE="">
        <INPUT TYPE=HIDDEN ID="UserLayer"  NAME="UserLayer"   VALUE="" >        
        <INPUT TYPE=HIDDEN ID="SpeciesID"  NAME="SpeciesID"   VALUE="<?php echo $species_id; ?>" >
        <INPUT TYPE=HIDDEN ID="MaxentThreshold"  NAME="MaxentThreshold"   VALUE="<?php echo $MaxentThreshold; ?>" >
        <INPUT TYPE=HIDDEN ID="extent"     NAME="extent"  VALUE="<?php if (!is_null($GUI)) echo $GUI->ExtentString(); ?>">
    </FORM>
    <table style="margin: 0px; padding: 0px;" width="97%" border="0" cellspacing="0">
        <tr>
            <td width="20%" style="text-align:left; "><?php if (!is_null($GUI)) echo round($GUI->Extent()->West(),3); ?>&deg;</td>
            <td width="55%" style="text-align:left; ">
                    <div id="ColorKeyContainer" >
                    <?php
                        $ramp = RGB::Ramp($MaxentThreshold, 1, $bucket_count,RGB::ReverseGradient(RGB::GradientYellowOrangeRed()));
                        echo RGB::RampDisplay($ramp,10,null,null,null,'OUTSIDE'); 
                    ?>
                    </div>
            
            </td>
            
            <td width="20%" style="text-align:right;"><?php if (!is_null($GUI)) echo round($GUI->Extent()->East(),3); ?>&deg;</td>
        </tr>
        <tr>
            <td colspan="3"><?php  if ($ascii_grid_webname != '') echo '<a target="_data_download"  href="'.$ascii_grid_webname.'">download ascii grid</a>'; ?></td>
        </tr>
    </table>
    
    <?php if (!is_null($GUI)) // echo $GUI->ExtentString(); ?>
</body>
</html>
