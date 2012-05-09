<?php 
include_once 'includes.php';


$_SESSION['LayerList'] = array_util::Value($_POST, 'LayerList', "");

// Probably better here to use Factory to get the appropriate finder
$finder = new FinderSpeciesSuitability();
$finder->ParentFolder('/www/eresearch/source/species'); // TODO:: need to set as Singleton Object
$finder->Filter("SPECIES", 'GOULFINC');
$finder->Species('GOULFINC');
$finder->Find();

// make selector
$params = array();
$params['page'] = 'SpeciesSuitability.page.php';
$params['species'] = 'GOULFINC';


// finder result displayed here
$layersSelector = matrix::FromTemplate(
                                        $finder->Result(),
                                        '<input type=BUTTON onClick="selectedScenarioModel(this,\'{page}?species={species}&model={RowName}&scenario={ColumnName}\');">',
                                        $params
                                      );

?>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>Layers</title>
        
        <style>
            .layer_button_selected 
            {
                background-color: pink;
            }
            
            .layer_button_unselected 
            {
                
            }

            
        </style>
        <script type="text/javascript">
            
         var layers = [];
            
        function init() 
        {
            var layers_raw = document.getElementById('LayerList').value
            var layers_raw_list =  layers_raw.split(",");
            
            for ( layer_key in layers_raw_list )
                toggleLayer(layers_raw_list[layer_key]);
            
            updateLayerButtons();
            
            parent.ReloadGUI();
            
        }
            
        function UpdateLayerList(caller) {
            
            toggleLayer(caller.value);
            
            var t = "";
            for ( layer_key in layers )
                if (layers[layer_key]) t += layer_key + ",";
            
            document.getElementById('LayerList').value = t;
            document.getElementById('LAYERS_FORM').submit();
            
        }
        
        
        function updateLayerButtons()
        {
            for ( layer_key in layers )
            {
                if (layers[layer_key]) 
                {
                    var buttonID = "LB_" + layer_key;
                    document.getElementById(buttonID).className = 'layer_button_selected';
                }
            }
            
        }
        
        function toggleLayer(layerName)
        {
            if (layerName != "")
            {
                tmp = layers[layerName];
                if (tmp == undefined)
                    layers[layerName] = true;
                else
                    layers[layerName] = !layers[layerName];
            }
            
        }
        
        function selectedScenarioModel(caller,url)
        {
            var extent = parent.GetExtentText();
            url += "&extent=" + encodeURIComponent(extent);
            parent.SetDataSummary(url);
        }
        
        </script>
        
    </head>
    <body onload="init()">
        <h1>layer manager</h1>
        <FORM id="LAYERS_FORM"  onsubmit="GetLayerList()" METHOD=POST ACTION="<?php echo $_SERVER['PHP_SELF']?>">
            
            <INPUT class="layer_button_unselected" ID="LB_GOULFINC~1975.asc"                               onclick="UpdateLayerList(this);" TYPE=BUTTON NAME="UserLayers[]" VALUE="GOULFINC~1975.asc" ><br>
            <INPUT class="layer_button_unselected" ID="LB_GOULFINC~sresa1b.bccr_bcm2_0.run1.run1.1990.asc" onclick="UpdateLayerList(this);" TYPE=BUTTON NAME="UserLayers[]" VALUE="GOULFINC~sresa1b.bccr_bcm2_0.run1.run1.1990.asc" ><br>
            <INPUT class="layer_button_unselected" ID="LB_GOULFINC~sresa1b.csiro_mk3_0.run1.run1.1990.asc" onclick="UpdateLayerList(this);" TYPE=BUTTON NAME="UserLayers[]" VALUE="GOULFINC~sresa1b.csiro_mk3_0.run1.run1.1990.asc" ><br>
            <INPUT class="layer_button_unselected" ID="LB_GOULFINC~sresa1b.inmcm3_0.run1.run1.2080.asc"    onclick="UpdateLayerList(this);" TYPE=BUTTON NAME="UserLayers[]" VALUE="GOULFINC~sresa1b.inmcm3_0.run1.run1.2080.asc"    ><br>
            
            <INPUT TYPE=HIDDEN ID="LayerList" NAME="LayerList" VALUE="<?php echo $_SESSION['LayerList']; ?>" ><br>
        </FORM>
        <?php
        
        
        echo matrix::toHTML($layersSelector);
        
        // print_r($map_object);
        echo array_util::Value($_POST, 'LayerList', "");
        ?>
    </body>
</html>
