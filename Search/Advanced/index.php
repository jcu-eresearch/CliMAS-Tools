<?php 

include_once 'tdh.class.php';
$T = new TDH();
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title><?php echo $T;?></title>
    </head>
    <body>
        <h1><?php echo $T; ?></h1>
        <h2>Shapefiles</h2>
        
        <img src="http://localhost/cgi-bin/mapserv?map=/www/projects/tdh/gis/Australia_states/request_0001.map&layer=request_0001&mode=map" />
        
        
    </body>
</html>
