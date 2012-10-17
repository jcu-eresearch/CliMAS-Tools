<?php

session_start();
include_once 'includes.php';
Session::clear();
function icon($name)
{
    echo '<img title="'.$name.'" style="height: 30px; width: 30px;" border="0" src="'.configuration::IconSource().$name.'" />';
}

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

    <div style="float: left; height: 700px; width: 200px;">
        <iframe ID="Selector" src="SearchSelection.php" width="190" height="600" frameBorder="0" border="0" style="overflow:hidden; float: none;" ></iframe><br>
        <iframe ID="Layers"   src="SearchLayers.php"    width="200" height="300" frameBorder="0" border="0" style="overflow:hidden; float: none;" ></iframe>
    </div>

    <div style="float: left; height: 500px; width: 700px;">
        <div id="ToolBar">
            <a href="#" class="Tool" id="ToolFullExtent"    onclick="SetFullExtent()"><?php icon("FullExtent.png")?></a>
            <a href="#" class="Tool" id="ToolZoomOutLow"    onclick="SetZoom(this,-2.0);" ><?php icon("Zoom-Out-icon.png")?></a>&nbsp;&nbsp
            <a href="#" class="Tool" id="ToolCentre"        onclick="SetZoom(this,1.0)"  ><?php icon("pan.png")?></a>&nbsp&nbsp
            <a href="#" class="Tool" id="ToolZoomInLow"     onclick="SetZoom(this,2.0)"  ><?php icon("Zoom-In-icon.png")?></a>&nbsp;&nbsp
            <!-- <a href="#" class="Tool" id="ToolZoomOutLow1"   onclick="zoomOut();" >Zoom Out1</a>&nbsp;&nbsp -->
        </div>
        <iframe ID="GUI" src="SearchMap.php" width="700" height="600" frameBorder="0" border="0" style="overflow:hidden; float:left;" ></iframe>
        <br style="clear:both;">

        <iframe ID="DataSummary" src="" width="900" height="600" frameBorder="0" border="0" style="overflow:scroll; float:left;" ></iframe>
    </div>

    <div ID="popup" >
        <a  ID="PopupCloseLink" onclick="closePopup();"  ><img ID="PopupCloseButton" border="0" src="/eresearch/TDH-Tools/Resources/icons/Close-icon.png"></a>
        <a  ID="PopupOKLink" onclick="okPopup();"  ><img ID="PopupOKButton" border="0" src="/eresearch/TDH-Tools/Resources/icons/Ok.png"></a>
        <iframe ID="popup_content"  frameBorder="0" border="0"  ></iframe>

    </div>


</body>
</html>