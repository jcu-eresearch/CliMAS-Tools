<?php
/**
 * Hotspots / Species Richness Tool
 * 
 *  
 */
session_start();
include_once dirname(__FILE__).'/includes.php';
if (php_sapi_name() == "cli") return;

$cmd = htmlutil::ValueFromGet('cmd',''); // if we have a command_id on the url then they have returned.

?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Richness</title>
    <script type="text/javascript" src="js/jquery-1.7.2.min.js"></script>
    <script type="text/javascript" src="js/jquery-ui-1.8.21.custom.min.js"></script>
    <script type="text/javascript" src="js/jquery.pulse.min.js"></script>
    <script type="text/javascript" src="js/selectMenu.js"></script>
    <script type="text/javascript" src="js/Utilities.js"></script>

    <link type="text/css" href="css/start/jquery-ui-1.8.21.custom.css" rel="stylesheet" />
    <link type="text/css" href="css/selectMenu.css" rel="stylesheet" />
    <link type="text/css" href="styles.css"         rel="stylesheet" />
    <link type="text/css" href="HotSpots.css"       rel="stylesheet" />
    
    <script type="text/javascript" >
    <?php     
    
        $exclude_word_list = file(configuration::SourceDataFolder()."exclude_word_list.txt");
    
        echo htmlutil::AsJavaScriptSimpleVariable(configuration::ApplicationFolderWeb(),'ApplicationFolderWeb');
        
        echo htmlutil::AsJavaScriptSimpleVariable(configuration::Maxent_Species_Data_folder_web().'richness/' ,'richness_folder');
    
        echo htmlutil::AsJavaScriptArrayFromFile(configuration::SourceDataFolder()."clazz_list.txt",'availableTaxa',true);
        
        echo htmlutil::AsJavaScriptArrayFromFile(configuration::SourceDataFolder()."family_list.txt",'availableFamily',true);

        echo htmlutil::AsJavaScriptArrayFromFile(configuration::SourceDataFolder()."genus_list.txt",'availableGenus',true,$exclude_word_list);
        
        $species_taxa_data = matrix::Load(configuration::SourceDataFolder()."species_to_id.txt", ",");
        echo htmlutil::AsJavaScriptObjectArray($species_taxa_data,"name","id","availableSpecies");
        
        echo htmlutil::AsJavaScriptSimpleVariable(configuration::IconSource(),'IconSource');
        
        echo htmlutil::AsJavaScriptSimpleVariable($cmd,'cmd');
        
        echo htmlutil::AsJavaScriptSimpleVariable(configuration::Maxent_Species_Data_folder_web(),'Maxent_Species_Data_folder_web');
        
        
     ?>
         

    </script>
    
    <script type="text/javascript" src="HotSpots.js"></script>

</head>
<body>
<h1 class="pagehead">
    <a href="index.php"><img src="<?php echo configuration::IconSource()."Hotspots.png" ?>" border="0" /></a>
</h1>
    
<?php 
    $liFormat = '<li class="ui-widget-content ui-corner-all " ><h4>{DataName}</h4><p>{Description}</p> </li>'; 
    
?>
        

    
<div id="thecontent">

    <div id="tabs">
        <ul>
            <li id="tab_label_1"><a href="#tabs-1">1). Species List</a></li>
            <li id="tab_label_3"><a href="#tabs-3">2). Select Emission Scenarios</a></li>
            <li id="tab_label_4"><a href="#tabs-4">3). Select Time points</a></li>
            <li id="tab_label_6"><a href="#tabs-6">4). Start Calculation</a></li>
        </ul>
        <div id="tabs-1">
        
            <i>Use this area to build a list of species to be modelled. Use Taxa, Family and genus to add all species from that grouping</i>
            <h3 id="InputsSearchBar" class="ui-widget-header ui-corner-all">
                <form>
                    <div id="InputTypesSet">
                        <h4>SEARCH BY</h4>
                        <input type="radio" id="InputTypeTaxa"     name="InputTypes" /><label for="InputTypeTaxa">Taxa</label>
                        <input type="radio" id="InputTypeFamily"   name="InputTypes" /><label for="InputTypeFamily">Family</label>
                        <input type="radio" id="InputTypeGenus"    name="InputTypes" /><label for="InputTypeGenus">Genus</label>
                        <input type="radio" id="InputTypeSpecies"  name="InputTypes" /><label for="InputTypeSpecies">Species</label>
                        <!--  <input type="radio" id="InputTypeLocation" name="InputTypes" /><label for="InputTypeLocation">Location</label> -->
                        <input type="input" id="InputText"         name="InputText" class="ui-corner-all">
                    </div>
                </form>                
            </h3>
            
            <ul id="TaxaSelection"     class="UserInputs"></ul>
            <ul id="FamilySelection"   class="UserInputs"></ul>
            <ul id="GenusSelection"    class="UserInputs"></ul>
            <ul id="SpeciesSelection"  class="UserInputs"></ul>
            <ul id="LocationSelection" class="UserInputs"></ul>
            
            <div id="MapContainer" class="ui-widget-content ui-corner-all" >

                <div id="MapTools" class="ui-widget-header ui-corner-all">
                    <button id="ToolClearAll"   onclick="ClearAll();"      >Clear All</button>
                    <button id="ToolFullExtent" onclick="SetFullExtent();" >Reset Map</button>
                    <input name="MapsTools" type="radio" id="ToolZoomOut"  onclick="SetZoom(this,-2.0);"                   /><label for="ToolZoomOut">Zoom Out</label>
                    <input name="MapsTools" type="radio" id="ToolCentre"   onclick="SetZoom(this,1.0)"                     /><label for="ToolCentre" >Centre</label>
                    <input name="MapsTools" type="radio" id="ToolZoomIn"   onclick="SetZoom(this,2.0)"   checked="checked" /><label for="ToolZoomIn" >Zoom In</label>
                </div>

                <iframe class="ui-widget-content ui-corner-all" ID="GUI" src="HotSpotsMap.php?w=800&h=600" width="820" height="626" frameBorder="0" border="0" style="margin: 0px; overflow:hidden; float:none; clear:both;" onload="map_gui_loaded()"></iframe>
                <FORM id="MapInteractionData" METHOD=POST ACTION="<?php echo $_SERVER['PHP_SELF']?>"><INPUT TYPE="HIDDEN" ID="ZoomFactor" NAME="ZoomFactor" VALUE="2"><INPUT TYPE="HIDDEN" ID="UserLayer"  NAME="UserLayer"  VALUE=""><INPUT TYPE="HIDDEN" ID="SpeciesID"  NAME="SpeciesID"  VALUE=""></FORM>        

            </div>
            
            
        </div>
        
        <div id="tabs-3">
            <div class="SelectionToolBar ui-widget-header ui-corner-all">
                <button id="SelectAllScenarios"  >select all</button>
                <button id="SelectNoneScenarios" >deselect all</button>
                <button id="SelectDefaultScenarios" >Defaults</button>
                <!-- <button id="SelectSomeScenarios_SRES" >select SRES*</button> -->
                <button id="SelectSomeScenarios_RCP" >select RCP*</button>
            </div>       
            
            <ul id="ScenariosSelection" class="selectable" >
            <?php  
               // aaa 
                $liFormat = '<li    id="Scenarios_{DataName}" class="ui-widget-content ui-corner-all " ><h4>{DataName}</h4><p>{Description}</p> </li>'; 
                echo DatabaseClimate::GetScenarioDescriptions('RCP')->asFormattedString($liFormat); 
            ?>
            </ul>
            <ul class="references">
                <li>references <i>(links to exernal site)</i>&nbsp;&nbsp;</li>
                <?php echo DatabaseClimate::GetScenarioDescriptions('RCP')->asFormattedString('<li><a target="_ref" href="{URI}">{DataName}</a></li>'); ?>
            </ul>
            
            
        </div>
        

        <div id="tabs-4">
            <div class="SelectionToolBar ui-widget-header ui-corner-all">
                <button id="SelectAllTimes"  >select all</button>
                <button id="SelectNoneTimes" >deselect all</button>
                <button id="SelectDefaultTimes" >Defaults</button>
            </div>       
            <ul id="TimesSelection" class="selectable" >
            <?php 
                $liFormat = '<li id="Times_{DataName}" class="ui-widget-content ui-corner-all " ><h4>{DataName}</h4><p>{Description}</p> </li>'; 
                echo DatabaseClimate::GetFutureTimesDescriptions()->asFormattedString($liFormat); 
            ?>                
            </ul>    
            
        </div>


        <div id="tabs-6">
            
            <div id="CountPanels ui-corner-all">
                
                <div id="CountPanel1" class="CountPanel ui-widget-content ui-corner-all">
                    <div class="Header ui-widget-header ui-corner-all">
                        <h1 id="CountInputTotals" >0</h1><h2>User<br>selections</h2>    
                    </div>
                    <div class="Value ui-widget-content ui-corner-all">
                        <h3 id="CountTaxa" class="Count">0</h3><h4>Taxa</h4>    
                    </div>
                    <div class="Value ui-widget-content ui-corner-all">
                        <h3 id="CountFamily" class="Count">0</h3><h4>Family</h4>    
                    </div>
                    <div class="Value ui-widget-content ui-corner-all">
                        <h3 id="CountGenus" class="Count">0</h3><h4>Genus</h4>    
                    </div>
                    <div class="Value ui-widget-content ui-corner-all">
                        <h3 id="CountSpecies" class="Count" >0</h3><h4>Species</h4>                    
                    </div>
                    
                </div>       

                <div id="CountPanel2" class="CountPanel ui-widget-content ui-corner-all">
                    <div class="Header ui-widget-header ui-corner-all">
                        <h1 id="CountFutureTotals" >0</h1><h2>Future<br>Datasets</h2>    
                    </div>

                    <div class="Value ui-widget-content ui-corner-all">
                        <h3 id="CountTimes" >0</h3><h4>Times</h4>    
                    </div>

                </div>       

                <div id="CountPanel4" class="CountPanel ui-widget-content ui-corner-all">

                    <div class="GrandTotal ui-widget-header ui-corner-all">
                        <h1 id="CountGrandTotal" >0</h1><h2 >Datasets to<br>Examine</h2>    
                    </div>
                    
                    <br>
                    <i>enter a name for your job here</i>
                    <input class="ui-widget-content ui-corner-all" name="job_description" id="job_description" size="20" value="job description" style="width: 100%;">
                    <br>
                    
                    <button id="CreateProcess">Submit for Calculation</button>
                    <div> </div>
                </div>    
                
            </div>

            <br style="clear: both; float: none;">
            <div id="RunningProcessesToolBar" class="ui-widget-header ui-corner-all">
                <button id="UpdateProcess">Update Status</button>
            </div>
            <ul id="RunningProcessesTable" class="ui-widget-content ui-corner-all">
                
            </ul>
            
        </div>


    </div>    


</div>
<div id="working" style="display:none;"></div>

    
    
<?php    include_once 'ToolsFooter.php'; ?>    
    

</body>
</html>
