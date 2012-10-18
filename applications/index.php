<?php
include_once dirname(__FILE__).'/includes.php';
/**
 * Basic Menu to select between AP02- Tools
 * 
 *  
 */

if (array_util::Value($_GET, "clean") == 'xyz123') DatabaseFile::RemoveUsedFiles(); 


?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Tropical Data Hub Tools</title>
<link type="text/css" href="css/start/jquery-ui-1.8.21.custom.css" rel="stylesheet" />
 <script type="text/javascript" src="js/jquery-1.7.2.min.js"></script>
 <script type="text/javascript" src="js/jquery-ui-1.8.21.custom.min.js"></script>
 <script type="text/javascript" src="js/jquery.pulse.min.js"></script>
 <script type="text/javascript" src="js/Utilities.js"></script>

<link href="styles.css" rel="stylesheet" type="text/css">
    
    <script type="text/javascript" >
    <?php     
     ?>
         
    </script>
    
    <script type="text/javascript" src="SpeciesScenarioTimeline.js"></script>

<style>

.maincontent
{
        height: 200px;
}
    
.maincontent div{

    border-top: 10px solid transparent;
    float:left;
    height: 120px;
    margin-left: 100px; 
}

.maincontent div:hover
{

    border-top: 10px solid maroon;
    
    margin-left: 100px; 
}


</style>
<script>

$(document).ready(function(){

});
    
</script>
    
</head>
<body>
<h1 class="pagehead"><a href="index.php"><img src="<?php echo configuration::IconSource()."CIT.png" ?>" border="0" /></a></h1>

<div class="maincontent">
    <div id="Suitability"><a href="SpeciesSuitability.php">
        <img src="<?php echo configuration::IconSource()."Suitability.png" ?>" border="0" /></a>
    </div>
    
    <div id="Hotspots">
        <a href="HotSpots.php">
            <img src="<?php echo configuration::IconSource()."Hotspots.png" ?>" border="0" />
        </a>
    </div>
                    
    <div id="EIS">
        <a href="#">
            <img src="<?php echo configuration::IconSource()."Impact.png" ?>" border="0" />
        </a>
    </div>
    
</div>

<div class="credits">
    <a href="http://www.jcu.edu.au/ctbcc/"><img src="../images/ctbcc_sm.png" alt="Centre for Tropical Biodiversity and Climate Change"></a>
    <a href="http://www.tyndall.ac.uk/"><img src="../images/themenews_logo.jpg" alt="Tyndall Centre for Climate Change Research"></a>
    <a href="http://www.jcu.edu.au"><img src="../images/jcu_logo_sm.png" alt="JCU Logo"></a>
    <a href="http://eresearch.jcu.edu.au/"><img src="../images/eresearch.png" alt="eResearch Centre, JCU"></a>
</div>


<div class="footer">
<p class="contact"> please contact Jeremy VanDerWal (<a href="mailto:jeremy.vanderwal@jcu.edu.au">jeremy.vanderwal@jcu.edu.au</a>) with any queries.</p>
</div>

</body>
</html>
