<?php
include_once 'includes.php';

Session::clear();

?>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Search</title>
    <link rel="stylesheet" type="text/css" href="Search.css" />
    <script src="Search.js" type="text/javascript"></script>
</head>
<body>    
    <FORM METHOD=POST ACTION="<?php echo $_SERVER['PHP_SELF']?>">
        <INPUT TYPE=HIDDEN ID="ZoomFactor" NAME="ZoomFactor"  VALUE="2">
    </FORM> 
    <div id="ToolBar">
        <a href="#" class="Tool" id="ToolFullExtent"    onclick="SetFullExtent()"    >Full Extent</a>
        <a href="#" class="Tool" id="ToolZoomOutLow1"   onclick="zoomOut();" >Zoom Out1</a>&nbsp;&nbsp
        <a href="#" class="Tool" id="ToolZoomOutLow"    onclick="SetZoom(this,-2.0);" >Zoom Out</a>&nbsp;&nbsp
        <a href="#" class="Tool" id="ToolCentre"        onclick="SetZoom(this,1.0)"  >Center </a>&nbsp&nbsp
        <a href="#" class="Tool" id="ToolZoomInLow"     onclick="SetZoom(this,2.0)"  >Zoom In</a>&nbsp;&nbsp
    </div>
    <iframe ID="GUI" src="SearchMap.php" width="900" height="600" frameBorder="0" border="0" style="overflow:hidden; float:left;" >
    </iframe>

    <iframe ID="Selector" src="SearchSelection.php" width="600" height="400" frameBorder="0" border="0" style="overflow:auto; float:left;" >
    </iframe>


    <iframe ID="Layers" src="SearchLayers.php" width="600" height="600" frameBorder="0" border="0" style="overflow:auto; float:left;" >
    </iframe>

    <iframe ID="DataSummary" src="SearchResults.php" width="900" height="600" frameBorder="0" border="0" style="overflow:hidden; float:left;" >
    </iframe>
    
    
    
</body>
</html>
