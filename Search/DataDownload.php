<!DOCTYPE html>
<?php
include_once 'includes.php';

// coverage types
$coverages = array();

$coverages['australia-5km'] = array();
$coverages['australia-5km']['tag'] = 'Australia, 5km resolution';
$coverages['australia-5km']['size_current'] = '~21Mb zip';  // current data
$coverages['australia-5km']['size_single'] = '~300Mb zip';  // a single model and single scenario
$coverages['australia-5km']['size_RCP'] = '~1.2Gb zip';     // one model, all RCP scenarios
$coverages['australia-5km']['size_SRES'] = '~1.5Gb zip';    // one model, all SRES scenarios
$coverages['australia-5km']['size_models'] = '~5.2Gb zip';  // all models, a single scenario

$coverages['world-20km'] = array();
$coverages['world-20km']['tag'] = 'World, 20km resolution';
$coverages['world-20km']['size_current'] = '~200Mb zip';  // current data
$coverages['world-20km']['size_single'] = '~650Mb zip';  // a single model and single scenario
$coverages['world-20km']['size_RCP'] = '~2.6Gb zip';     // one model, all RCP scenarios
$coverages['world-20km']['size_SRES'] = '~3.2Gb zip';    // one model, all SRES scenarios
$coverages['world-20km']['size_models'] = '~12Gb zip';  // all models, a single scenario


$requestedScenario = array_util::Value($_GET, "scenario", null);
$requestedModel = array_util::Value($_GET, "model", null);
$requestedTime = array_util::Value($_GET, "time", null);
$requestedCoverage = array_util::Value($_GET, "coverage", null);

$haveCoverage = !is_null($requestedCoverage);
$haveRequest = $haveCoverage && (!is_null($requestedScenario)) && (!is_null($requestedModel)) && (!is_null($requestedTime));

// made this file-global
$self = "http://" . $_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF'];
if ($_SERVER['SERVER_PORT'] != '80') {
    $self = "http://" . $_SERVER['SERVER_NAME'] . ":" . $_SERVER['SERVER_PORT'] . $_SERVER['PHP_SELF'];
}

function coverageSelector($promptTerm = "select")
{
    global $self, $coverages, $requestedCoverage;

    $modelDesc = ToolsData::ClimateModels();

    $result = "";
    $result .= "<div class='coverageselector linkbuttons'>\n";
    $result .= "<h2>Modelling Coverage</h2>\n";
    foreach ($coverages as $coverId => $coverData) {
        $coverageURL = "{$self}?coverage={$coverId}";
        if ($coverId === $requestedCoverage) {
            $coverageTip = "currently showing " . $coverData['tag'];
            $result .= "\t<a href='{$self}' class='selected' title='{$coverageTip}'>{$coverData['tag']}</a>\n";
        } else {
            $coverageTip = $promptTerm . " " . $coverData['tag'];
            $result .= "\t<a href='{$coverageURL}' title='{$coverageTip}'>{$coverData['tag']}</a>\n";
        }
    }
    $result .= "</div>\n";
    return $result;
}

function selectionTable()
{
    global $self, $coverages, $requestedCoverage;
    
    // self URL will now always include the selected coverage
    $baseURL = $self . "?coverage=" . $requestedCoverage;

    $modelDesc = ToolsData::ClimateModels();
    $scenarioDesc = ToolsData::EmissionScenarios();
    $timeDesc = ToolsData::Times();

    $f = array();
    $f[] = "Description";
    $f[] = "URI";

    $result = "";

    // --------------------------------------------------------------------
    // files for EACH scenario, EACH model
    // new version, table with integrated header links

    // modes, for a simpler page
    global $modes;
    $modes = array();

    $modes['rcp'] = array();
    $modes['rcp']['buttonname'] = 'show RCP scenarios';
    $modes['rcp']['scenarios'] = array('RCP', 'RCP3PD','RCP45','RCP6','RCP85');
    
    $modes['sres'] = array();
    $modes['sres']['buttonname'] = 'show SRES scenarios';
    $modes['sres']['scenarios'] = array('SRES', 'SRESA1B','SRESA1FI','SRESA2','SRESB1','SRESB2');
/*
    $modes['all'] = array();
    $modes['all']['buttonname'] = 'show all scenarios';
    $modes['all']['scenarios'] = array(); // special mode that always includes everything
*/
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
    } // - - - - - - - - - - - - - - - - - - - - - - - - - - - -

    function downloadUrl($coverage, $scenario, $model = 'all') { // - - -
        // calc the server url
        $url = "http://" . $_SERVER['SERVER_NAME'];
        // add port if required
        if ($_SERVER['SERVER_PORT'] != '80') {
            $url .= ":" . $_SERVER['SERVER_PORT'];
        }
        // add the download path
        $url .= '/climate_2012/output/';
        // add the coverage
        $url .= $coverage . '/';

        // bail out if they asked for current
        if ($scenario === 'current') {
            $url .= 'current.zip';
            return $url;
        }

        // make the filename
        if ($scenario !== 'all') {
            $url .= $scenario;
        }
        if ($scenario !== 'all' && $model !== 'all') {
            $url .= "_";
        }
        if ($model !== 'all') {
            $url .= $model;
        }

        // add .zip and return
        $url = $url . '.zip';
        return $url;
    } // - - - - - - - - - - - - - - - - - - - - - - - - - - - -

    //
    // link to the current data
    //
    $result .= "<p class='current linkbuttons'>";
    $url = downloadUrl($requestedCoverage,'current');
    $covername = $coverages[$requestedCoverage]['tag'];
    $text = $coverages[$requestedCoverage]['size_current'];
    $result .= "Current climate layers for {$covername}: <a href='{$url}'>{$text}</a>";
    $result .= "</p>";

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
    $result .= "<tr class='tophalf'><td class='rowcollabels always' rowspan='2'>\n";
    $result .= "<div class='rightlabel'>Emission Scenarios</div>";
    $result .= "<div class='downlabel'>Climate Models</div>";
    $result .= "</td>\n";

    // top row: scenario titles
    // first the two all-scenario-group cells
    $modeclasses = join(modesForScenario('SRES'), " ");
    $result .= "<td class='{$modeclasses}'><b>All SRES</b><br>all SRES scenarios</td>\n";

    $modeclasses = join(modesForScenario('RCP'), " ");
    $result .= "<td class='{$modeclasses}'><b>All RCP</b><br>all RCP scenarios</td>\n";
    // now loop through the individial scenarios
    foreach ($scenarioDesc->asSimpleArray($f) as $scenarioKey => $scenarioInfo) {
        $modeclasses = join(modesForScenario($scenarioKey), " ");
        $result .= "<td class='{$modeclasses}'><b>{$scenarioKey}</b><br>all models, this scenario</td>\n";
    }
    $result .= "</tr>\n";

    // bottom row: scenario download buttons
    $result .= "<tr class='bottomhalf'>";

    // first the two all-scenario-group cells
    $modeclasses = join(modesForScenario('SRES'), " ");
    $result .= "<td class='" . $modeclasses . "'></td>";

    $modeclasses = join(modesForScenario('RCP'), " ");
    $result .= "<td class='" . $modeclasses . "'></td>";

    // now loop through the individial scenarios
    foreach ($scenarioDesc->asSimpleArray($f) as $scenarioKey => $scenarioInfo) {
        $scenarioURL = downloadUrl($requestedCoverage, $scenarioKey, 'all');
        $modeclasses = join(modesForScenario($scenarioKey), " ");
        $text = $coverages[$requestedCoverage]['size_models'];
        $result .= "<td class='" . $modeclasses . "'><a href='" . $scenarioURL . "'>{$text}</a></td>";
    }
    $result .= "</tr>\n";

    //
    // write body of table
    //
    foreach ($modelDesc->asSimpleArray($f) as $modelKey => $modelInfo) {

        // first cell is double high, and has model name & description
        $result .= "<tr class='tophalf'><td class='always' rowspan='2'><b>{$modelKey}</b><br>";
        $result .= $modelInfo['Description'];
        $result .= "<br><a class='morelink' href='{$modelInfo['URI']}'>model ref &raquo;</a>";
        $result .= "</td>";

        // then the subtotal columns for all SRES & all RCPs
        $modeclasses = join(modesForScenario('SRES'), " ");
        $result .= "<td class='{$modeclasses}'>{$modelKey} for all SRES</td>";
        $modeclasses = join(modesForScenario('RCP'), " ");
        $result .= "<td class='{$modeclasses}'>{$modelKey} for all RCP</td>";

        // rest of the top row is model/scenario descriptions
        foreach ($scenarioDesc->asSimpleArray($f) as $scenarioKey => $scenarioInfo) {
            $modeclasses = join(modesForScenario($scenarioKey), " ");
            $result .= "<td class='{$modeclasses}'>{$modelKey} for {$scenarioKey}</td>";
        }
        $result .= "</tr>\n";


        // the bottom row is model/scenario download links

        // first couple are for all SRES & all RCPs
        $modeclasses = join(modesForScenario('SRES'), " ");
        $url = downloadUrl($requestedCoverage, 'SRES', $modelKey);
        $text = $coverages[$requestedCoverage]['size_SRES'];
        $result .= "<td class='{$modeclasses}'><a href='{$url}''>{$text}</a></td>";

        $modeclasses = join(modesForScenario('RCP'), " ");
        $url = downloadUrl($requestedCoverage, 'RCP', $modelKey);
        $text = $coverages[$requestedCoverage]['size_RCP'];
        $result .= "<td class='{$modeclasses}'><a href='{$url}''>{$text}</a></td>";

        // remainder are for single intersections
        foreach ($scenarioDesc->asSimpleArray($f) as $scenarioKey => $scenarioInfo) {
            $modeclasses = join(modesForScenario($scenarioKey), " ");
            $url = downloadUrl($requestedCoverage, $scenarioKey, $modelKey);
            $text = $coverages[$requestedCoverage]['size_single'];
            $result .= "<td class='{$modeclasses}'><a href='{$url}''>{$text}</a></td>";
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

function downloadRequestConfirmation($requestedCoverage,$requestedScenario,$requestedModel,$requestedTime)
{

    $requestedData = array();
    $requestedData["Coverage"] = $requestedCoverage;
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
    $archiveFilename .= "-".str_replace(" ","_",$requestedData["Coverage"])."";
    $archiveFilename .= "-".str_replace(" ","_",$requestedData["Scenarios"])."";
    $archiveFilename .= "-".str_replace(" ","_",$requestedData["Models"])."";
    $archiveFilename .= "-".str_replace(" ","_",$requestedData["Times"])."";
    $archiveFilename .= ".zip";


    if (!file_exists($archiveFilename))
//    if (false)
    {
        $scenarioDesc = ToolsData::EmissionScenarios();
        $modelsDesc = ToolsData::ClimateModels();
        $timeDesc = ToolsData::Times();

        // assume here that only one Coverage can be selected at a time, so we don't need
        // to have a coverageDesc or replace any "all" coverage selections.

        $requestedData["Scenarios"] = ($requestedData["Scenarios"] == "all") ? join(" ",array_keys($scenarioDesc->asSimpleArray())) : $requestedData["Scenarios"];
        $requestedData["Models"]    = ($requestedData["Models"]    == "all") ? join(" ",array_keys($modelsDesc->asSimpleArray()))   : $requestedData["Models"];
        $requestedData["Times"]     = ($requestedData["Times"]     == "all") ? join(" ",array_keys($timeDesc->asSimpleArray()))     : $requestedData["Times"];


        $coverages = array($requestedData['Coverage']);
        $scenarios = explode(" ",$requestedData["Scenarios"]);
        $models = explode(" ",$requestedData["Models"]);
        $times = explode(" ",$requestedData["Times"]);

        $total = count($coverages) * count($scenarios) * count($models) * count($times);
        $count = 1;
        foreach ($coverages as $coverage)
        {
            foreach ($scenarios as $scenario)
            {
                foreach ($models as $model)
                {
                    foreach ($times as $time)
                    {
//                        $toStore = "{$coverage}_{$scenario}_{$model}_{$time}".CommandConfiguration::osPathDelimiter()."*.gz";
                        $toStore = "{$coverage}_{$scenario}_{$model}_{$time}/*.gz";

                        $cmd  = "cd '{$DF}'; ";
                        $cmd .= "zip -0 $archiveFilename {$toStore}".";";

                        exec("{$cmd}"); // add files to archive

                        $count++;
                    }
                }
            }
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
        echo downloadRequestConfirmation($requestedCoverage,$requestedScenario,$requestedModel,$requestedTime);
        echo "<p><a href='DataDownload.php'>&laquo; climate data downloads page</a></p>";
    } else if ($haveCoverage) {
        echo "<div class='intro'>";
        include 'DataDownloadDesc.html';
        echo "</div>";
        echo coverageSelector("change");
        echo selectionTable();
        
        // here's where we put actual modes into this string
        $modeListString = '"' . join(array_keys($modes), '", "') . '"';
    } else {
        echo "<div class='intro'>";
        include 'DataDownloadDesc.html';
        echo "</div>";
        echo coverageSelector("choose");
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


