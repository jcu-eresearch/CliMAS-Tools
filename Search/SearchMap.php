<?php
include_once 'includes.php';

$M = new MapServerWrapper();

$caption = VisualText::create("Species suitability", 10, "Red");
$M->Caption($caption);

$finder = FinderFactory::Execute("ContextLayer","AustraliaStates");


$context = $M->Layers()->AddLayer($finder->Result());

$LayerList = array_util::Value($_SESSION, 'LayerList', null);

if (!is_null($LayerList))
{
    
    foreach (explode(",", $LayerList) as $layer_filename) 
    {        
        if ($layer_filename == "") continue;

        $layer_path = "/www/eresearch/source/species/".str_replace("~", "/output/", $layer_filename);
        
        $current = $M->Layers()->AddLayer($layer_path);
        
        $current instanceof MapServerLayerRaster;
        $current->HistogramBuckets(20);
        $current->ColorTableByStats(RGB::ReverseGradient(RGB::GradientGreenBeige()));  // RGB::ReverseGradient()
        $current->ColorTableResetFirstElement();   
    }
    
}


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
