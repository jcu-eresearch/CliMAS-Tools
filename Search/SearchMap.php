<?php
session_start();
include_once 'includes.php';

$M = new MapServerWrapper();

foreach (Session::MapableResults() as $MapableResult)
    $M->Layers()->AddLayer($MapableResult);

$UserLayer = array_util::Value($_POST, "UserLayer","");


if ($UserLayer != "")
{
    $layer = $M->Layers()->AddLayer(DatabaseFile::ReadFile2Filesystem($UserLayer,configuration::TempFolder().$UserLayer.".asc",false,true) );
    $layer instanceof MapServerLayerRaster;
    $layer->HistogramBuckets(10);
    
    $layer->ColorTable(RGB::Ramp(0, 1, 10,RGB::ReverseGradient(RGB::GradientYellowOrangeRed())));
    
}




$MF = Mapfile::create($M);


$_SESSION['map_path'] = $MF->save($M);

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
    }
    </script>
</HEAD>
<BODY>
    
    
    
    <FORM id="MAP_FORM"  onsubmit="GetZoom()" METHOD=POST ACTION="<?php echo $_SERVER['PHP_SELF']?>">
        <INPUT id="mapa" TYPE=IMAGE NAME="mapa" SRC="<?php echo $GUI->MapImageLocation();?>" style="clear:both; ">
        <INPUT TYPE=HIDDEN ID="ZoomFactor" NAME="ZoomFactor" VALUE="">
        <INPUT TYPE=HIDDEN ID="UserLayer"  NAME="UserLayer"   VALUE="" >        
        <INPUT TYPE=HIDDEN ID="extent"     NAME="extent"  VALUE="<?php echo $GUI->ExtentString(); ?>">
    </FORM>
    
    <?php echo str_replace("\n", "<br>\n", $MF->Text());?>
    
    
</body>
</html>
