<?php
session_start();
include_once dirname(__FILE__).'/includes.php';

$EXTRA_CHARS = '@#$%^&*+-={}[]\|:";\'\\<>,?/`~';

// storew the Species List on the webser if we can' find it then get a new one

$reset = array_util::Value($_GET, 'reset');

$speciesListFilename = "/tmp/tdh_tools-species_list.csv";

if (!is_null($reset)) file::Delete ($speciesListFilename);

$species_list = null;
if (!file_exists($speciesListFilename))
{
    
    $species_list = SpeciesData::ComputedNameList();
    // make it - ie cache on server 
    $file = "name,species_id\n";
    foreach ($species_list as $name => $species_id)  
    {   
        $name = util::CleanStr($name,null,$EXTRA_CHARS,"");        
        $file .= "{$name},{$species_id}\n";
    }
        
    file_put_contents($speciesListFilename,$file);
}

$species_list = matrix::Load($speciesListFilename, ',', 'name');   


?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Species Scenario Timeline</title>
    <script type="text/javascript" src="js/jquery-1.7.2.min.js"></script>
    <script type="text/javascript" src="js/jquery-ui-1.8.21.custom.min.js"></script>
    <script type="text/javascript" src="js/jquery.pulse.min.js"></script>
    <script type="text/javascript" src="js/selectMenu.js"></script>
    <script type="text/javascript" src="js/Utilities.js"></script>

    <link type="text/css" href="css/start/jquery-ui-1.8.21.custom.css" rel="stylesheet" />
    <link type="text/css" href="css/selectMenu.css" rel="stylesheet" />
    <link type="text/css" href="styles.css"         rel="stylesheet" />
    <link type="text/css" href="SpeciesScenarioTimeline.css" rel="stylesheet" />
    
    <script type="text/javascript" >
    <?php     
     ?>
         

    </script>
    
    <script type="text/javascript" src="SpeciesScenarioTimeline.js"></script>

</head>
<body>
<a href="index.php"><img src="<?php echo configuration::IconSource()."SpeciesScenarioTimeline.png" ?>" border="0" /></a>
    
<?php 
    $liFormat = '<li class="ui-widget-content ui-corner-all " ><h4>{DataName}</h4><p>{Description}</p> </li>'; 
?>
    
<div id="thecontent" class="ui-widget-content ui-corner-all">

    <div id="lhs" class="ui-widget-header ui-corner-all">
        <div id="lhs_header" class="ui-widget-header ui-corner-all">
            Species
        </div>
        
        <div id="lhs_list" class="ui-widget-content ui-corner-all">
            <ul>
            <?php 

            foreach ($species_list as $name => $row) 
            {
                $species_id = $row['species_id'];
                
                if (substr($name,0,2) == "..")
                {
                    $name = substr($name,2,500);
                    echo '<li><button tag="'.$species_id.'" class="SpeciesButton" style="text-align: right; " >'.$name.'</button></li>';    
                }
                else
                {
                    echo '<li><button tag="'.$species_id.'" class="SpeciesButton" style="margin-top: 8px; font-weight: bold; " >'.$name.'</button></li>';
                }
                
            }
                
            ?>
                
            </ul>
            
        </div>
        
        
    </div>
    
    <div id="rhs" class="ui-widget-content ui-corner-all">
        <div id="rhs_header" class="ui-widget-header ui-corner-all">
            Future Suitability 
        </div>
        <div id="rhs_list" class="ui-widget-content ui-corner-all">
            
            <iframe class="ui-widget-content ui-corner-all"  
                    ID="futures" 
                    src="SpeciesScenarioTimelineFutures.php" 
                    width="99%" 
                height="99%" 
            frameBorder="0" 
                border="0" 
                    style="margin: 2px; overflow:hidden; float:none; clear:both;" 
                    >
            </iframe>
            
            
        </div>
        
        
    </div>
    

</div>

    
<?php    include_once 'ToolsFooter.php'; ?>    

</body>
</html>
