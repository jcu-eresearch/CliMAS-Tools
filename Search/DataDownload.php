<?php
include_once 'includes.php';

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

    $r1 = array();
    foreach ($scenarioDesc->asSimpleArray($f) as $scenarioKey => $scenarioInfo) $r1[$scenarioKey] = "{$self}?scenario=$scenarioKey&model=all&time=all";
    $result .= "\n"."<h2>Modelling data for one Emission Scenario & all models (~4GB)</h2>";
    $result .= "\n".htmlutil::TableByCSS($r1, "<a target=\"_dl\" href=\"{#value#}\">{#key#}</a>","scenTable", "scenRow","scenCell");


    $r2 = array();
    foreach ($modelDesc->asSimpleArray($f) as $modelKey => $modelInfo) $r2[$modelKey] = "{$self}?scenario=all&model={$modelKey}&time=all";
    $result .= "\n"."<h2>Modelling data for one Climate model and all scenarios (~2GB)</h2>";
    $result .= "\n".htmlutil::TableByCSS($r2,"<a target=\"_dl\" href=\"{#value#}\">{#key#}</a>","modelTable", "modelRow","modelCell");
    $result .= "<br>";

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

    $result .= OutputFactory::Find($modelDesc->asSimpleArray($f));

    $result = "<div class=\"FileSelection\">".$result."</div>";

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

    $result .= '<br>'.'<a href="http://'.$_SERVER['SERVER_NAME'].$WEB_FOLDER.$archive.'">DOWNLOAD</a>';

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
<script type="text/javascript">
</script>
<title>Data Download</title>
<link rel="stylesheet" type="text/css" href="Output/Descriptions.css" />
<style type="text/css">

    body
    {
        font-family: sans-serif;
    }

    .scenCell a, .scenModelCell a, .modelCell a {
        display: block; width: 100%; height: 100%;
    }

    .scenTable
    {
        width: 100%;
        
        float: none;
        clear: both;
        overflow: hidden;
        margin-bottom: 20px;
    }

    .scenRow
    {
        width: 100px;
        height: 30px;
        float: left;        
        margin: 2px;
        background-color: #DDDDDD;
        border-radius: 15px;
        border-right: 2px solid black;
        border-bottom: 2px solid black;
        
    }

    .scenRow:hover
    {
        background-color: #AAAAAA;
        color: white;
    }


    .scenCell
    {
        width: 100%;
        height: 100%;
        margin:5px;
        padding-left:5px;
    }

    .scenCell a
    {
        color: black;
        font-weight: bold;
        text-decoration: none;
    }




    .modelTable
    {
        width: 100%;
        
        float: none;
        clear: both;
            overflow: hidden;
        margin-bottom: 20px;

    }

    .modelRow
    {
        width: 150px;
        height: 30px;
        float: left;
        margin: 2px;
        background-color: #DDDDDD;
        border-radius: 15px;
        border-right: 2px solid black;
        border-bottom: 2px solid black;

    }

    .modelRow:hover
    {
        background-color: #AAAAAA;
        color: white;
    }


    .modelCell
    {
        width: 100%;
        height: 100%;
        margin:5px;
        padding-left:5px;
    }

    .modelCell a
    {
        color: black;
        font-weight: bold;
        text-decoration: none;
    }




    .scenModelCell
    {
        display: block;
        padding: 5px;
        width: 100px;
        background-color: #DDDDDD;
        border-radius: 15px;
        border-right: 2px solid black;
        border-bottom: 2px solid black;
        text-align: center;
        margin-bottom: 5px;
    }

    .scenModelCell a
    {
        color: black;
        font-weight: bold;
        text-decoration: none;
    }



    .scenModelCell:hover
    {
        background-color: #AAAAAA;
        color: white;
    }



</style>
</head>
<body>
    <h1>Spatial Ecology Climate Change Data</h1>

<?php

    if ($haveRequest)
        echo downloadRequestConfirmation($requestedScenario,$requestedModel,$requestedTime);
    else
        echo selectionTable();
?>

</body>
</html>
