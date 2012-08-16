<?php
session_start();
include_once 'includes.php';

$GUI = null; 

$SpeciesID = array_util::Value($_POST, "SpeciesID","");

$UserLayer = array_util::Value($_POST, "UserLayer","");

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
    
    $GUI = MapserverGUI::create($_SESSION['map_path']);
    if (is_null($GUI)) die ("Map Server GUI failed");

    if ($GUI->hasInteractive()) $GUI->ZoomAndPan();        
    
}

if (!is_null($map_path) && $UserLayer == "")
{
    
    $GUI = MapserverGUI::create($_SESSION['map_path']);
    if (is_null($GUI)) die ("Map Server GUI failed");

    if ($GUI->hasInteractive()) $GUI->ZoomAndPan();        
    
}


if ($UserLayer != "")
{

    $mimeType = DatabaseFile::ReadFileMimeType($UserLayer);
    if (util::contains(strtolower($mimeType), 'png'))
    {
        $body_background = "
            body 
            {
                background-image:url('file.php?id={$UserLayer}');
            }
            ";
        
    }
    else
    {
        
        $M = new MapServerWrapper();

        // add background layers
        foreach (Session::MapableResults() as $MapableResult)
            $M->Layers()->AddLayer($MapableResult);

        // add user layer
        
        $layer = $M->Layers()->AddLayer(DatabaseFile::ReadFile2Filesystem($UserLayer,configuration::TempFolder().$UserLayer.".asc",false,true) );
        $layer instanceof MapServerLayerRaster;
        $layer->HistogramBuckets(100);

        // this bit here needs to be moved - and only called if we want maxent 
        $ramp = RGB::Ramp(0, 1, 100,RGB::ReverseGradient(RGB::GradientYellowOrangeRed()));

        $display_threshold = DatabaseMaxent::GetMaxentResult($SpeciesID, DatabaseMaxent::$DisplayThresholdFieldName);
        if ( !($display_threshold instanceof ErrorMessage))
            foreach ($ramp as $key => $rgb)  if ($key < $display_threshold) $ramp[$key] = RGB::ColorBlack(); // set colour to black for below threshold


        $layer->ColorTable($ramp);             
        
        
        $MF = Mapfile::create($M);
        $_SESSION['map_path'] = $MF->save($M);

        $GUI = MapserverGUI::create($_SESSION['map_path']);
        if (is_null($GUI)) die ("Map Server GUI failed");

        if ($GUI->hasInteractive()) $GUI->ZoomAndPan();                
        
    }
    
}


?>
<HTML>
<HEAD>
    <TITLE></TITLE>
    <script type="text/javascript">
    function GetZoom() {
        document.getElementById('ZoomFactor').value = parent.document.getElementById('ZoomFactor').value;
        document.getElementById('UserLayer').value  = parent.document.getElementById('UserLayer').value;        
        document.getElementById('SpeciesID').value  = parent.document.getElementById('SpeciesID').value;        
    }
    </script>
    <style>
        <?php echo $body_background; ?>
        
    </style>
    
</HEAD>
<BODY>
    
    <FORM id="MAP_FORM"  onsubmit="GetZoom()" METHOD=POST ACTION="<?php echo $_SERVER['PHP_SELF']?>">
        <INPUT id="mapa" TYPE=IMAGE NAME="mapa" SRC="<?php if (!is_null($GUI)) echo $GUI->MapImageLocation();?>" style="clear:both; ">
        <INPUT TYPE=HIDDEN ID="ZoomFactor" NAME="ZoomFactor" VALUE="">
        <INPUT TYPE=HIDDEN ID="UserLayer"  NAME="UserLayer"   VALUE="" >        
        <INPUT TYPE=HIDDEN ID="SpeciesID"  NAME="SpeciesID"   VALUE="<?php echo $SpeciesID; ?>" >
        <INPUT TYPE=HIDDEN ID="extent"     NAME="extent"  VALUE="<?php if (!is_null($GUI)) echo $GUI->ExtentString(); ?>">
    </FORM>
    
    <?php // echo str_replace("\n", "<br>\n", $MF->Text());?>
    
    
</body>
</html>
