<?php 
include_once 'utilities/includes.php';
include_once 'configuration.class.php';
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title></title>
    <link rel="stylesheet" type="text/css" href="map.css" />
    <script type="text/javascript">
    function ReloadGUI() 
    {
        document.getElementById('GUI').src = document.getElementById('GUI').src
    }



    function ResetToolDisplay()
    {
        
        var toolNames = new Object();
        
        toolNames["ToolFullExtent"   ] ="ToolFullExtent"    ;
        toolNames["ToolZoomOutHigh"  ] ="ToolZoomOutHigh"   ;
        toolNames["ToolZoomOutMedium"] ="ToolZoomOutMedium" ;
        toolNames["ToolZoomOutLow"   ] ="ToolZoomOutLow"    ;
        toolNames["ToolCentre"       ] ="ToolCentre"        ;
        toolNames["ToolZoomInLow"    ] ="ToolZoomInLow"     ;
        toolNames["ToolZoomInMedium" ] ="ToolZoomInMedium"  ;
        toolNames["ToolZoomInHigh"   ] ="ToolZoomInHigh"    ;
        
        for (var toolName in toolNames)
	{
            document.getElementById(toolName).style.backgroundColor = "black";	
            document.getElementById(toolName).style.Color = "white";	
	}        
        
    }

    function SetZoom(caller,zoom_value) {
        
        ResetToolDisplay();
        document.getElementById(caller.id).style.backgroundColor = "green";
        
        document.getElementById('ZoomFactor').value = zoom_value;   
    }

    function SetFullExtent() {
        ReloadGUI();
    }
    </script>
</head>
<body>    
    <FORM METHOD=POST ACTION="<?php echo $_SERVER['PHP_SELF']?>">
        <INPUT TYPE=HIDDEN ID="ZoomFactor" NAME="ZoomFactor"  VALUE="1"><br>
    </FORM>
    <div id="ToolBar">
        <a href="#" class="Tool" id="ToolFullExtent"    onclick="SetFullExtent()">Full</a>
        <a href="#" class="Tool" id="ToolZoomOutHigh"   onclick="SetZoom(this,-4.0)" >high</a>&nbsp;&nbsp
        <a href="#" class="Tool" id="ToolZoomOutMedium" onclick="SetZoom(this,-3.0)" >med</a>&nbsp;&nbsp
        <a href="#" class="Tool" id="ToolZoomOutLow"    onclick="SetZoom(this,-2.0)" >low</a>&nbsp;&nbsp
        <a href="#" class="Tool" id="ToolCentre"        onclick="SetZoom(this,1.0)"  >center</a>&nbsp&nbsp
        <a href="#" class="Tool" id="ToolZoomInLow"     onclick="SetZoom(this,2.0)"  >low</a>&nbsp;&nbsp
        <a href="#" class="Tool" id="ToolZoomInMedium"  onclick="SetZoom(this,3.0)"  >med</a>
        <a href="#" class="Tool" id="ToolZoomInHigh"    onclick="SetZoom(this,4.0)"  >high</a>
    </div>
    
    <iframe ID="GUI" src="map.php" width="1000" height="800" frameBorder="0" border="0" style="overflow:hidden;" />

</body>
</html>
