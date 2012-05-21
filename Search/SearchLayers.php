<?php 
$LayerListField = "LayerList";
include_once 'includes.php';

$O = OutputFactory::Content(FinderFactory::Result(configuration::MapableBackgroundLayers())); // we want to get a list of SCreen Mappable Context Layers

$contextLayersTemplate = <<<CT
<INPUT class="AvailableLayerButton" 
 ID="{Classname}"
 onclick="AddToLayerList('{Classname}');"
 TYPE=BUTTON
 NAME="AvailableLayers[]"
 VALUE="{Name}"
>
CT;

Session::UpdateFromPostedFinderActionNames($LayerListField);

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
            var layers_raw = document.getElementById('<?php echo $LayerListField; ?>').value
            var layers_raw_list =  layers_raw.split(",");
            
            for ( layer_key in layers_raw_list )
                toggleLayer(layers_raw_list[layer_key]);
            
            updateLayerButtons();
            
            parent.ReloadGUI();
            
        }


        function AddToLayerList(toAdd)
        {
            
            UpdateLayerList(toAdd);
        }

        function updateLayerButtons()
        {
            for ( layer in layers )
                if (layers[layer])
                    document.getElementById(layer).className = 'layer_button_selected';

        }


        function UpdateLayerList(FinderAction) {

            toggleLayer(FinderAction);

            var tmp = "";
            for ( layer in layers )
                if (layers[layer]) tmp += layer + ",";

            if (tmp == "")
            {
                toggleLayer(FinderAction); // re toggle to layer to back on
                alert("Must have at least one Active layer");
                return;
            }

            document.getElementById('<?php echo $LayerListField; ?>').value = tmp;
            document.getElementById('LAYERS_FORM').submit();
            
        }
        


        function toggleLayer(FinderAction)
        {

            if (FinderAction != "")
            {                
                tmp = layers[FinderAction];
                if (tmp == undefined)
                    layers[FinderAction] = true;
                else
                    layers[FinderAction] = !layers[FinderAction];
            }
            
        }

        
        </script>
        
    </head>
    <body onload="init()">
        <h1>layer manager</h1>
        <FORM id="LAYERS_FORM"  METHOD=POST ACTION="<?php echo $_SERVER['PHP_SELF']?>">
            <?php echo htmlutil::TableRowTemplate($O,$contextLayersTemplate); ?>
            <INPUT TYPE="hidden" SIZE="100" ID="<?php echo $LayerListField; ?>" NAME="<?php echo $LayerListField; ?>" VALUE="<?php echo Session::PostableFinderActionNames(); ?>" ><br>
        </FORM>
        
    </body>
</html>

