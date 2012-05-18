<?php
include_once 'includes.php';

$M = new MapServerWrapper();

$caption = new VisualText("Species suitability", 10, "Red");
$M->Caption($caption);


foreach (Session::MapableResults() as $MapableResult) $M->Layers()->AddLayer($MapableResult);
    


$MF = Mapfile::create($M);

$_SESSION['map_path'] = $MF->save($M);

$GUI = MapserverGUI::create($_SESSION['map_path']);
if (is_null($GUI)) die ("Map Server GUI failed");

if ($GUI->hasInteractive()) $GUI->ZoomAndPan();



?>
<HTML>
<HEAD>
    <TITLE>Map 2</TITLE>
    <script type="text/javascript">
    function GetZoom() {
        document.getElementById('ZoomFactor').value = parent.document.getElementById('ZoomFactor').value;
    }
    
    
    </script>
</HEAD>
<BODY>
    <FORM id="MAP_FORM"  onsubmit="GetZoom()" METHOD=POST ACTION="<?php echo $_SERVER['PHP_SELF']?>">
        <INPUT id="mapa" TYPE=IMAGE NAME="mapa" SRC="<?php echo $GUI->MapImageLocation();?>">      
        <INPUT TYPE=HIDDEN ID="ZoomFactor" NAME="ZoomFactor" VALUE="">
        <INPUT TYPE=HIDDEN ID="extent"     NAME="extent"     VALUE="<?php echo $GUI->ExtentString(); ?>">
    </FORM>
    <!-- <img src="<?php echo $GUI->MapLegendLocation(); ?>"  style="float:left;" /> -->

    
</body>
</html>
