<?php
/**
 * Implementation of MAPSERVER mapping interface ffor use with Suitability Tool
 * 
 *  
 */
session_start();
include_once 'includes.php';

$bucket_count = 20;

$grid_filename_asc = '';
$grid_filename_web = '';

$GUI = null; 

$species_id = array_util::Value($_POST, "SpeciesID",null);

$MaxentThreshold = DatabaseMaxent::GetMaxentThresholdForSpeciesFromFile($species_id);
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
    {
        $M->Layers()->AddLayer($MapableResult);
    }
        
    
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
    // name replaces to match new data sources
    if ($UserLayer == "CURRENT_CURRENT_1990") $UserLayer = "1990";
    $UserLayer = str_replace("_ALL_", "_all_", $UserLayer);
    
    $grid_filename_asc = SpeciesFiles::species_data_folder($species_id)."{$UserLayer}.asc";
    $grid_filename_gz  = SpeciesFiles::species_data_folder($species_id)."{$UserLayer}.asc.gz";
    
    if (!file_exists($grid_filename_asc))
    {
        // check for GZ file is here 
        if ( file_exists($grid_filename_gz)  )
        {
            // gzip is there bu asc is not then create asc - but leave gz inplace
            // as gz is loinked from somewhere else.
            $cmd = "gunzip -c '{$grid_filename_gz}' > '{$grid_filename_asc}'";;
            exec($cmd);
        }
        
    }
    
    
    $grid_filename_web  = SpeciesFiles::species_data_folder_web($species_id)."{$UserLayer}.asc.gz";
    
    
    if (file_exists($grid_filename_asc))
    {
        $layer = $M->Layers()->AddLayer($grid_filename_asc);
        $layer instanceof MapServerLayerRaster;
        $layer->HistogramBuckets($bucket_count);

        // start ramp at Zero - 
        $ramp = RGB::Ramp(0, 1, $bucket_count,RGB::ReverseGradient(RGB::GradientYellowOrangeRed()));
        
            // chnage all values below thrteshgold to trasparent
        foreach ($ramp as $key => $rgb)  
            if ($key < $MaxentThreshold) $ramp[$key] = null; // set colour to black for below threshold


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
                        echo RGB::RampDisplay($ramp,7,null,null,null,null,"Least Suitable","Most Suitable"); 
                    ?>
                    </div>
            
            </td>
            
            <td width="20%" style="text-align:right;"><?php if (!is_null($GUI)) echo round($GUI->Extent()->East(),3); ?>&deg;</td>
        </tr>
        <tr>
            <td colspan="3"><?php  if ($grid_filename_asc != '') echo '<a target="_data_download"  href="'.$grid_filename_web.'">download ascii grid</a>&nbsp;&nbsp; <i>(or right click `save as`)</i>'; ?> </td>
        </tr>
    </table>
    
    <?php if (!is_null($GUI)) // echo $GUI->ExtentString(); ?>
</body>
</html>
