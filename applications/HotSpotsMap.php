<?php
session_start();
include_once 'includes.php';

$SpeciesID = array_util::Value($_POST, "SpeciesID","");

$UserLayer = array_util::Value($_POST, "UserLayer","");

$map_path = array_util::Value($_SESSION,'map_path');

if (is_null($map_path) || $UserLayer != "")
{
    
    $M = new MapServerWrapper();

    foreach (Session::MapableResults() as $MapableResult)
        $M->Layers()->AddLayer($MapableResult);
    
    if ($UserLayer != "")
    {
        $layer = $M->Layers()->AddLayer(DatabaseFile::ReadFile2Filesystem($UserLayer,configuration::TempFolder().$UserLayer.".asc",false,true) );
        $layer instanceof MapServerLayerRaster;
        $layer->HistogramBuckets(100);

        
        // this bit here needs to be moved - and only called if we want maxent 
        $ramp = RGB::Ramp(0, 1, 100,RGB::ReverseGradient(RGB::GradientYellowOrangeRed()));

        $display_threshold = DatabaseMaxent::GetMaxentResult($SpeciesID, DatabaseMaxent::$DisplayThresholdFieldName);

        // remove under threshold
        foreach ($ramp as $key => $rgb) 
            if ($key < $display_threshold) $ramp[$key] = RGB::ColorBlack();        


        $layer->ColorTable($ramp);
        
    }
    
 
    
    // only recreate mapfile if we have added / chnaged a user layer
    $MF = Mapfile::create($M);
    $_SESSION['map_path'] = $MF->save($M);
    
}



$GUI = MapserverGUI::create($_SESSION['map_path']);
if (is_null($GUI)) die ("Map Server GUI failed");

if ($GUI->hasInteractive()) $GUI->ZoomAndPan();

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
</HEAD>
<BODY>
    
    <FORM id="MAP_FORM"  onsubmit="GetZoom()" METHOD=POST ACTION="<?php echo $_SERVER['PHP_SELF']?>">
        <INPUT id="mapa" TYPE=IMAGE NAME="mapa" SRC="<?php echo $GUI->MapImageLocation();?>" style="clear:both; ">
        <INPUT TYPE=HIDDEN ID="ZoomFactor" NAME="ZoomFactor" VALUE="">
        <INPUT TYPE=HIDDEN ID="UserLayer"  NAME="UserLayer"   VALUE="" >        
        <INPUT TYPE=HIDDEN ID="SpeciesID"  NAME="SpeciesID"   VALUE="<?php echo $SpeciesID; ?>" >
        <INPUT TYPE=HIDDEN ID="extent"     NAME="extent"  VALUE="<?php echo $GUI->ExtentString(); ?>">
    </FORM>
    
    
</body>
</html>
