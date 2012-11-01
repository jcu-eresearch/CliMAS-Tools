<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
include_once 'includes.php';


?>
<head>
    <script src="http://cdn.leafletjs.com/leaflet-0.4/leaflet.js"></script>    
    <link rel="stylesheet" href="http://cdn.leafletjs.com/leaflet-0.4/leaflet.css" />    
    <script type="text/javascript" src="js/jquery-1.7.2.min.js"></script>
    <script type="text/javascript" src="js/jquery-ui-1.8.21.custom.min.js"></script>
    <script type="text/javascript" src="js/Utilities.js"></script>

    <link type="text/css" href="css/start/jquery-ui-1.8.21.custom.css" rel="stylesheet" />
    
<script type="text/javascript" >
    
    var map = null;
    var map_path = '';
    
    function post_prep(data)
    {
        
        // see whats in the data retuned in browser log    
        $.each(data, function(index, value) 
        { 
            console.log('post_prep  ' + index + " = " +value);
        });
        
        map_path = Value(data, 'map_path', '');

        var layer_name = 'RCP85_all_2015_5970';
        
        console.log('map_path =   ' + map_path);
        
        
        
    var  data1 = new L.TileLayer.WMS("http://localhost/cgi-bin/mapserv", {
                                      layers: layer_name + '&map=' + map_path,
                                      format: 'image/png',
                                      opacity: 0.75,
                                      transparent: true
                                    }).addTo(map);    
        
        
    }
    
    
    $(document).ready(function(){


       map = L.map('map', { minZoom: 3 }).setView([-27, 135], 4);
        
        L.tileLayer('http://{s}.tile.cloudmade.com/d20cea5b7fc84870819a1c4f8501965b/997/256/{z}/{x}/{y}.png', {
          attribution: 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery © <a href="http://cloudmade.com">CloudMade</a>',
          maxZoom: 18
        }).addTo(map);


    var sData = {  SpeciesID: 5970
                  ,UserLayer: 'RCP85_all_2015'
                  ,bucket_count: 25
                }
    
    $.post("SpeciesSuitabilityPrep.php", sData , function(data) {post_prep(data);},"json"); 

    });
</script>
</head>
<body>
    Test 
    <div id="map" style="height: 500px; width: 700px;">
    </div>
    
    <script>
    
    //Request URL:http://localhost/cgi-bin/mapserv?service=WMS&request=GetMap&version=1.1.1&layers=tdh&map=/home/jc166922/projects/tdh_mapfiles/bert.map&styles=&format=image/png&transparent=true&height=256&width=256&srs=EPSG:3857&bbox=15028131.257091932,-7514065.628545966,17532819.799940586,-5009377.085697311    
    
    
    var mapfileUrl = '/home/jc166922/projects/tmp/bert.map';
    
//    var  data1 = new L.TileLayer.WMS("http://localhost/jc166922/projects/tools/applications/leaflet-richness-mapserv.php", {
//                                      layers: 'RCP3PD_2015&map=&genus=Rattus&scenario=RCP3PD&time=2015',
//                                      format: 'image/png',
//                                      opacity: 0.75
//                                    }).addTo(map);    


    



    </script>
    
</body>
