<!DOCTYPE html>
<?php
include_once 'includes.php';

// updated 

$requestedScenario = array_util::Value($_GET, "scenario", null);
$requestedModel = array_util::Value($_GET, "model", null);
$requestedTime = array_util::Value($_GET, "time", null);

$haveRequest = (!is_null($requestedScenario)) && (!is_null($requestedModel)) && (!is_null($requestedTime));

function selectionTable()
{
    $self = "http://".$_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF'];

    $modelDesc = ToolsData::ClimateModels();
    $scenarioDesc = ToolsData::EmissionScenarios();
    $timeDesc = ToolsData::Times();

    $f = array();
    $f[] = "Description";
    $f[] = "URI";

    $result = "";

/*

    // files for EACH scenario, ALL models
    $r1 = array();
    foreach ($scenarioDesc->asSimpleArray($f) as $scenarioKey => $scenarioInfo)
    {
        $r1[$scenarioKey] = "{$self}?scenario=$scenarioKey&model=all&time=all";
    }
    $result .= "\n"."<h2>Modelling data for one Emission Scenario & all models (~4GB)</h2>";
    $result .= "\n".htmlutil::TableByCSS($r1, "<a target=\"_dl\" href=\"{#value#}\">{#key#}</a>","scenTable", "scenRow","scenCell");


    // files for ALL scenarios, EACH model
    $r2 = array();
    foreach ($modelDesc->asSimpleArray($f) as $modelKey => $modelInfo)
    {
        $r2[$modelKey] = "{$self}?scenario=all&model={$modelKey}&time=all";
    }
    $result .= "\n"."<h2>Modelling data for one Climate model and all scenarios (~2GB)</h2>";
    $result .= "\n".htmlutil::TableByCSS($r2,"<a target=\"_dl\" href=\"{#value#}\">{#key#}</a>","modelTable", "modelRow","modelCell");
    $result .= "<br>";

    // files for EACH scenario, EACH model
    $r3 = array();
    foreach ($modelDesc->asSimpleArray($f) as $modelKey => $modelInfo)
    {
        $r3[$modelKey] = array();
        $r3[$modelKey][] = $modelKey;

        foreach ($scenarioDesc->asSimpleArray($f) as $scenarioKey => $scenarioInfo)
        {
            $link = $self."?scenario={$scenarioKey}&model={$modelKey}&time=all";

            $value = '<div class="scenModelCell" ><a target="_dl" href="'.$link.'">'.$scenarioKey.'</a></div>';

            $r3[$modelKey][$scenarioKey] = $value;
        }

    }

    $result .= "\n"."<h2>Modelling data Individual Models and Emission Scenarios (~300MB)</h2>";
    $result .= "\n".htmlutil::table($r3,false);
    $result .= "<br>";
*/

    // --------------------------------------------------------------------
    // files for EACH scenario, EACH model
    // new version, table with integrated header links

    // modes, for a simpler page
    global $modes;
    $modes = array();

    $modes['rcp'] = array();
    $modes['rcp']['buttonname'] = 'show RCP scenarios';
    $modes['rcp']['scenarios'] = array('RCP3PD','RCP45','RCP6','RCP85');
    
    $modes['sres'] = array();
    $modes['sres']['buttonname'] = 'show SRES scenarios';
    $modes['sres']['scenarios'] = array('SRESA1B','SRESA1FI','SRESA2','SRESB1','SRESB2');

    $modes['all'] = array();
    $modes['all']['buttonname'] = 'show all scenarios';
    $modes['all']['scenarios'] = array(); // special mode that always includes everything

    function modesForScenario($scenario) { // - - - - - - - - - -
        global $modes;
        $scenmodes = array();
        foreach ($modes as $modeName => $modeData) {
            if (in_array($scenario, $modeData['scenarios'])) {
                $scenmodes[] = $modeName;
            }
        }
        $scenmodes[] = 'all';
        return $scenmodes;
    } // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

    //
    // write mode selection buttons
    //
    $result .= "<div class='modeselector linktabs'>\n";
    foreach ($modes as $modeName => $modeData) {
        $result .= "<a id='" . $modeName . "'>" . $modeData['buttonname'] . "</a>\n";
        
    }
    $result .= "</div>\n";

    //
    // start table
    //
    $result .= "<table class='scenariodownloads linktable linkbuttons' cellspacing='0' cellpadding='0'>\n";

    //
    // write table scenario headers
    //
    
    // make a big cell in the top left intersection of the heading rows and
    // columns, and label the horizontal and vertical heading rows with it.
    $result .= "<tr class='tophalf'><td class='rowcollabels always' colspan='2' rowspan='2'>\n";
    $result .= "<div class='rightlabel'>Emission Scenarios</div>";
    $result .= "<div class='downlabel'>Climate Models</div>";
    $result .= "</td>\n";
    foreach ($scenarioDesc->asSimpleArray($f) as $scenarioKey => $scenarioInfo) {
        $modeclasses = join(modesForScenario($scenarioKey), " ");
        $result .= "<td class='{$modeclasses}'><b>{$scenarioKey}</b><br>all models, this scenario</td>";
    }
    $result .= "</tr>\n";

    $result .= "<tr class='bottomhalf'>";
    foreach ($scenarioDesc->asSimpleArray($f) as $scenarioKey => $scenarioInfo) {
        $scenarioURL = "{$self}?scenario=$scenarioKey&model=all&time=all";
        $modeclasses = join(modesForScenario($scenarioKey), " ");
        $result .= "<td class='" . $modeclasses . "'><a href='" . $scenarioURL . "'>~4Gb zip</a></td>";
    }
    $result .= "</tr>\n";

    //
    // write body of table
    //
    foreach ($modelDesc->asSimpleArray($f) as $modelKey => $modelInfo) {

        // start of row is model description etc
        $modelURL = "{$self}?scenario=all&model={$modelKey}&time=all";
        $result .= "<tr class='tophalf'><th rowspan='2' class='always'>{$modelKey}</th>";
        // two actual html table rows for each model
        // top row is descriptions, starting with model description
        $result .= "<td class='always'>";
        $result .= $modelInfo['Description'];
        $result .= " <a class='morelink' href='{$modelInfo['URI']}'>model ref &raquo;</a> ";
        $result .= "<br>this model, all scenarios";
        $result .= "</td>";
        // rest of the row is model/scenario descriptions
        foreach ($scenarioDesc->asSimpleArray($f) as $scenarioKey => $scenarioInfo) {
            $modeclasses = join(modesForScenario($scenarioKey), " ");
            $result .= "<td class='{$modeclasses}'>{$modelKey} for {$scenarioKey}</td>";
        }
        $result .= "</tr>\n";
        // bottom row is download links, starting with model download
        $result .= "<tr class='bottomhalf'><td class='always'><a href='{$modelURL}'>~2Gb zip</a></td>";

        // rest of the row is model/scenario download links
        foreach ($scenarioDesc->asSimpleArray($f) as $scenarioKey => $scenarioInfo) {
            $modeclasses = join(modesForScenario($scenarioKey), " ");
            $result .= "<td class='" . $modeclasses . "'>";
            $result .= "<a href='" . $self . "?scenario={$scenarioKey}&model={$modelKey}&time=all'>~300Mb zip</a></td>";
        }
        $result .= "</tr>\n";
    }

    //
    // write foot of table
    //
    $result .= "</table>\n";

    //
    // all done!
    //
    $result .= "<br>";
    // --------------------------------------------------------------------

/*
    $result .= OutputFactory::Find($modelDesc->asSimpleArray($f));

    $result = "<div class=\"FileSelection\">".$result."</div>";
*/
    return $result;

}

function downloadRequestConfirmation($requestedScenario,$requestedModel,$requestedTime)
{

    $requestedData = array();
    $requestedData["Scenarios"] = $requestedScenario;
    $requestedData["Models"] = $requestedModel;
    $requestedData["Times"] = $requestedTime;

    $archive = zipFiles($requestedData);

    //
    // ### WEB ACCESSIBLE FOLDER
    //
    if (util::contains(util::hostname(), "afakes"))
        $WEB_FOLDER = "/eresearch/TDH-Tools/output/";
    else
        $WEB_FOLDER = "/climate_2012/output/";


    $result =  "";
    $result .= OutputFactory::Find($requestedData);

    $result .= '<br>'.'<a class="downloadbtn" href="http://'.$_SERVER['SERVER_NAME'].$WEB_FOLDER.$archive.'">DOWNLOAD</a>';

    return $result ;

}
function zipFiles($requestedData)
{

    if (util::contains(util::hostname(), "afakes"))
    {
        $DF = "/www/eresearch/TDH-Tools/source/data/";
        $outputFolder = "/www/eresearch/TDH-Tools/output/";
    }
    else
    {
        //
        // ### SOURCE DATA FOLDER
        //
        // $DF = "/www/eresearch/TDH-Tools/source/data/";

        $DF = "/homes/jc165798/Climate/CIAS/Australia/5km/bioclim_asc";

        //
        // ### OUTPUT FOLDER - Full path name to WEB ACCESSIBLE FOLDER
        //
        $outputFolder = "/local/climate_2012/output/";
    }


    $archiveFilename  = $outputFolder;
    $archiveFilename .= "JCU-ClimateData";
    $archiveFilename .= "-".str_replace(" ","_",$requestedData["Scenarios"])."";
    $archiveFilename .= "-".str_replace(" ","_",$requestedData["Models"])."";
    $archiveFilename .= "-".str_replace(" ","_",$requestedData["Times"])."";
    $archiveFilename .= ".zip";


    if (!file_exists($archiveFilename))
    {

        $scenarioDesc = ToolsData::EmissionScenarios();
        $modelsDesc = ToolsData::ClimateModels();
        $timeDesc = ToolsData::Times();

        $requestedData["Scenarios"] = ($requestedData["Scenarios"] == "all") ? join(" ",array_keys($scenarioDesc->asSimpleArray())) : $requestedData["Scenarios"];
        $requestedData["Models"]    = ($requestedData["Models"]    == "all") ? join(" ",array_keys($modelsDesc->asSimpleArray()))   : $requestedData["Models"];
        $requestedData["Times"]     = ($requestedData["Times"]     == "all") ? join(" ",array_keys($timeDesc->asSimpleArray()))     : $requestedData["Times"];



        $scenarios = explode(" ",$requestedData["Scenarios"]);
        $models = explode(" ",$requestedData["Models"]);
        $times = explode(" ",$requestedData["Times"]);

        $total = count($scenarios) * count($models) * count($times);
        $count = 1;
        foreach ($scenarios as $scenario)
            foreach ($models as $model)
                foreach ($times as $time)
                {
                    $toStore = "{$scenario}_{$model}_{$time}".CommandConfiguration::osPathDelimiter()."*.gz";

                    $cmd  = "cd '{$DF}'; ";
                    $cmd .= "zip -0 $archiveFilename {$toStore}".";";

                    exec("{$cmd}"); // add files to archive

                    $count++;
                }

    }

    return str_replace($outputFolder, "", $archiveFilename);
}



?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Data Download</title>
<link rel="stylesheet" type="text/css" href="Output/Descriptions.css" />

<link href="styles.css" rel="stylesheet" type="text/css">

<script type="text/javascript">
  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-16452055-10']);
  _gaq.push(['_trackPageview']);
  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();
</script>

</head>
<body>
    <h1 class="pagehead"><a href="DataDownload.php">Climate Data Downloads</a></h1>
    <div class="maincontent">
<?php

    // this is used later in the javascript
    $modeListString = '';

    if ($haveRequest) {
        echo downloadRequestConfirmation($requestedScenario,$requestedModel,$requestedTime);
        echo "<p><a href='DataDownload.php'>&laquo; climate data downloads page</a></p>";
    } else {
        echo "<div class='intro'>";
        include 'DataDownloadDesc.html';
        echo "</div>";
        echo selectionTable();
        
        // here's where we put actual modes into this string
        $modeListString = '"' . join(array_keys($modes), '", "') . '"';
    }
?>
    </div>

    <div class="credits">
        <a href="http://www.jcu.edu.au/ctbcc/">
            <img src="../images/ctbcc_sm.png" alt="Centre for Tropical Biodiversity and Climate Change">
        </a>
        <a href="http://www.tyndall.ac.uk/">
            <img src="../images/themenews_logo.jpg" alt="Tyndall Centre for Climate Change Research">
        </a>
        <a href="http://www.jcu.edu.au">
            <img src="../images/jcu_logo_sm.png" alt="JCU Logo">
        </a>
        <a href="http://eresearch.jcu.edu.au/">
            <img src="../images/eresearch.png" alt="eResearch Centre, JCU">
        </a>
    </div>

    <div class="footer">
        <p class="contact">
            please contact Jeremy VanDerWal
            (<a href="mailto:jeremy.vanderwal@jcu.edu.au">jeremy.vanderwal@jcu.edu.au</a>)
            with any queries.
        </p>
    </div>

    <!-- javascript is here at the bottom of the page, where it should be -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
    <script>

        // javascript to set up the mode display buttons to hide/show the columns
        // $modeListString was set up earlier in a php portion of the page
        var modes = [<?php echo $modeListString; ?>];

        $( function() {
            //
            // stuff to run when the page is loaded
            //

            // prepare additionalcontent sections
            additionals = $('.additionalcontent');
            additionals.each( function(index, add) {
                showhide = $(add);

                var content = showhide.children('.add');
                content.hide();

                var opener = showhide.children('.opener');
                opener.click( function(event) {
                    if (content.filter(':visible').length > 0) {
                        // ..then we're already open, so close
                        showhide.removeClass('open');
                        content.hide('blind');
                    } else {
                        // ..we're closed, so open up
                        showhide.addClass('open');
                        content.show('blind');
                    }
                });
            });
            

            // hide/show some of the table columns so it fits on the page            
            if ($.browser.msie && $.browser.version <= 7) {
                // can't hide and show table cells in old versions of IE. sorry.
            } else {
            
                // show the mode selector
                $('.modeselector').show();
            
                // add the 'hidden' class to every mode cell (all TDs and THs but not if they're 'always')
                var allmodalcells = $('.scenariodownloads td').add('.scenariodownloads th').not('.always');
                // hide the remaining modal cells.
                allmodalcells.addClass('hidden');

                // set up the click 
                for (var modeIndex in modes) {
                    var mode = modes[modeIndex];
                    var btn = $('#' + mode);
                    btn.click( function(clickevent) {
                        thisbtn = $(clickevent.srcElement);
                        
                        // make that mode selector the 'active' one
                        $('.modeselector a').removeClass('selected');
                        thisbtn.addClass('selected');

                        // handle the table cells
                        allmodalcells.removeClass('reallynothidden');
                        var thismode = thisbtn.attr('id');
                        // add the 'reallynothidden' class to cells that have the class
                        var thismodecells = $('.scenariodownloads td.' + thismode).add('.scenariodownloads th.' + thismode);
                        thismodecells.addClass('reallynothidden');
                    });
                }
                
                // finally, click the sres button
                $('#rcp').trigger(jQuery.Event('click', {srcElement: $('#rcp')}) );
            }
        });
    </script>
    
</body>
</html>


