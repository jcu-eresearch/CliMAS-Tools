<?php
// to pre generate arachives
include_once 'includes.php';


$modelDesc = ToolsData::ClimateModels();
$scenarioDesc = ToolsData::EmissionScenarios();
$timeDesc = ToolsData::Times();


foreach ($scenarioDesc->asSimpleArray() as $scenarioKey => $scenarioInfo) 
{
    $requestedData = array();
    $requestedData["Scenarios"] = $scenarioKey;
    $requestedData["Models"] = "all";
    $requestedData["Times"] = "all";

    $archive = zipFiles($requestedData);

    echo "Created $archive\n";

}



foreach ($modelDesc->asSimpleArray() as $modelKey => $modelInfo)
{
    $requestedData = array();
    $requestedData["Scenarios"] = "all";
    $requestedData["Models"] = $modelKey;
    $requestedData["Times"] = "all";

    $archive = zipFiles($requestedData);

    echo "Created $archive\n";

}



foreach ($modelDesc->asSimpleArray() as $modelKey => $modelInfo)
{
    $r3[$modelKey] = array();
    $r3[$modelKey][] = $modelKey;

    foreach ($scenarioDesc->asSimpleArray() as $scenarioKey => $scenarioInfo)
    {

        $requestedData = array();
        $requestedData["Scenarios"] = $scenarioKey;
        $requestedData["Models"] = $modelKey;
        $requestedData["Times"] = "all";

        $archive = zipFiles($requestedData);

    }

}


    




function zipFiles($requestedData)
{


    $modelsDesc = ToolsData::ClimateModels();
    $scenarioDesc = ToolsData::EmissionScenarios();
    $timeDesc = ToolsData::Times();


    //
    // ### SOURCE DATA FOLDER
    //

    if (util::contains(util::hostname(), "afakes"))
    {
        $DF = "/www/eresearch/TDH-Tools/source/data/";
        $outputFolder = "/www/eresearch/TDH-Tools/output/";
    }
    else
    {
        $DF = "/homes/jc165798/Climate/CIAS/Australia/5km/bioclim_asc";
        $outputFolder = "/local/climate_2012/output/";
    }
    

    //
    // ### OUTPUT FOLDER - Full path name to WEB ACCESSIBLE FOLDER
    //


    $archiveFilename  = $outputFolder;
    $archiveFilename .= "JCU-ClimateData";
    $archiveFilename .= "-".str_replace(" ","_",$requestedData["Scenarios"])."";
    $archiveFilename .= "-".str_replace(" ","_",$requestedData["Models"])."";
    $archiveFilename .= "-".str_replace(" ","_",$requestedData["Times"])."";
    $archiveFilename .= ".zip";

    $requestedData["Scenarios"] = ($requestedData["Scenarios"] == "all") ? join(" ",array_keys($scenarioDesc->asSimpleArray())) : $requestedData["Scenarios"];
    $requestedData["Models"]    = ($requestedData["Models"]    == "all") ? join(" ",array_keys($modelsDesc->asSimpleArray()))   : $requestedData["Models"];
    $requestedData["Times"]     = ($requestedData["Times"]     == "all") ? join(" ",array_keys($timeDesc->asSimpleArray()))     : $requestedData["Times"];


    if (!file_exists($archiveFilename))
    {

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

                    $prev_dir = trim(exec('pwd'));

                    $cmd  = "cd '{$DF}'; ";
                    $cmd .= "zip -0 $archiveFilename {$toStore}".";";
                    $cmd .= "cd {$prev_dir}";

                    exec("{$cmd}"); // add files to archive

                    $count++;
                }

    }

    return str_replace($outputFolder, "", $archiveFilename);
}



?>
