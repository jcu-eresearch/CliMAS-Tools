<?php
include_once 'utilities/includes.php';
include_once 'configuration.class.php';

    // Default values and configuration
    $map_path="/www/projects/tdh/picard/code/map.map";
    $web_root = "/www";
    
    
    $map = ms_newMapObj($map_path);


    if ( isset($_POST["mapa_x"]) && isset($_POST["mapa_y"]) && !isset($_POST["full"]) ) 
    {

          $extent_to_set = explode(" ",$_POST["extent"]);

          $map->setextent($extent_to_set[0],$extent_to_set[1],$extent_to_set[2],$extent_to_set[3]);

          $my_point = ms_newpointObj();
          $my_point->setXY($_POST["mapa_x"],$_POST["mapa_y"]);

          $my_extent = ms_newrectObj();
          $my_extent->setextent($extent_to_set[0],$extent_to_set[1],$extent_to_set[2],$extent_to_set[3]);

          $ZoomFactor = $_POST["ZoomFactor"];
          
          $map->zoompoint($ZoomFactor,$my_point,$map->width,$map->height,$my_extent);

     }

     $extent_to_html = $map->extent->minx." ".$map->extent->miny." ".$map->extent->maxx." ".$map->extent->maxy;


     //$scale_obj = $map->drawScaleBar();
     //$scale_img = $scale_obj->saveWebImage();
     
     
     $legend_obj = $map->drawLegend();
     $legend_img = $legend_obj->saveWebImage();
     
     $image = $map->draw();
     $image_url = $image->saveWebImage();

     $size = getimagesize("{$web_root}".$image_url);
     print_r($size);

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
    <FORM onsubmit="GetZoom()" METHOD=POST ACTION="<?php echo $_SERVER['PHP_SELF']?>">
    <INPUT TYPE=IMAGE NAME="mapa" SRC="<?php echo $image_url;?>">      
    <INPUT TYPE=HIDDEN ID="ZoomFactor" NAME="ZoomFactor"   VALUE=""><br>           
    <INPUT TYPE=HIDDEN NAME="extent" VALUE="<?php echo $extent_to_html?>"><br>
    <?php echo $extent_to_html?><br>
    <?php echo $_POST["mapa_x"]; ?><br>
    <?php echo $_POST["mapa_y"]; ?><br>
    </FORM>
    <img src="<?php echo $legend_img; ?>" />
</body>
</html>
