<?php

// COMMENETD OUT SO it can't be run

//// to pre generate arachives
//include_once 'includes.php';
//
//
//$modelDesc = ToolsData::ClimateModels();
//$scenarioDesc = ToolsData::EmissionScenarios();
//$timeDesc = ToolsData::Times();
//
//
//foreach ($scenarioDesc->asSimpleArray() as $scenarioKey => $scenarioInfo) 
//{
//    $requestedData = array();
//    $requestedData["Scenarios"] = $scenarioKey;
//    $requestedData["Models"] = "all";
//    $requestedData["Times"] = "all";
//
//    $archive = zipFiles($requestedData);
//
//    echo "Creating   $archive\n";
//
//}
//
//
//
//foreach ($modelDesc->asSimpleArray() as $modelKey => $modelInfo)
//{
//    $requestedData = array();
//    $requestedData["Scenarios"] = "all";
//    $requestedData["Models"] = $modelKey;
//    $requestedData["Times"] = "all";
//
//    $archive = zipFiles($requestedData);
//
//    echo "Creating $archive\n";
//
//}
//
//
//
//foreach ($modelDesc->asSimpleArray() as $modelKey => $modelInfo)
//{
//    $r3[$modelKey] = array();
//    $r3[$modelKey][] = $modelKey;
//
//    foreach ($scenarioDesc->asSimpleArray() as $scenarioKey => $scenarioInfo)
//    {
//
//        $requestedData = array();
//        $requestedData["Scenarios"] = $scenarioKey;
//        $requestedData["Models"] = $modelKey;
//        $requestedData["Times"] = "all";
//
//        $archive = zipFiles($requestedData);
//
//        echo "Creating $archive\n";
//
//    }
//
//}
//
//
//    
//
//
//
//
//function zipFiles($requestedData)
//{
//
//
//    $modelsDesc = ToolsData::ClimateModels();
//    $scenarioDesc = ToolsData::EmissionScenarios();
//    $timeDesc = ToolsData::Times();
//
//
//    //
//    // ### SOURCE DATA FOLDER
//    //
//
//    if (util::contains(util::hostname(), "afakes"))
//    {
//        $DF = "/www/eresearch/TDH-Tools/source/data/";
//        $outputFolder = "/www/eresearch/TDH-Tools/output/";
//    }
//    else
//    {
//
//        if (util::contains(util::hostname(), "login"))
//        {
//            $DF = "/home/jc165798/Climate/CIAS/Australia/5km/bioclim_asc/";
//            $outputFolder = "/home/jc166922/TDH-Tools/outputs/";
//        }
//        else
//        {
//            $DF = "/local/climate_2012/data/";
//            $outputFolder = "/local/climate_2012/output/";
//
//        }
//
//    }
//    
//
//    //
//    // ### OUTPUT FOLDER - Full path name to WEB ACCESSIBLE FOLDER
//    //
//
//
//    $archiveFilename  = $outputFolder;
//    $archiveFilename .= "JCU-ClimateData";
//    $archiveFilename .= "-".str_replace(" ","_",$requestedData["Scenarios"])."";
//    $archiveFilename .= "-".str_replace(" ","_",$requestedData["Models"])."";
//    $archiveFilename .= "-".str_replace(" ","_",$requestedData["Times"])."";
//    $archiveFilename .= ".zip";
//
//
//
//    if (!file_exists($archiveFilename))
//    {
//
//        $requestedData["Scenarios"] = ($requestedData["Scenarios"] == "all") ? join(" ",array_keys($scenarioDesc->asSimpleArray())) : $requestedData["Scenarios"];
//        $requestedData["Models"]    = ($requestedData["Models"]    == "all") ? join(" ",array_keys($modelsDesc->asSimpleArray()))   : $requestedData["Models"];
//        $requestedData["Times"]     = ($requestedData["Times"]     == "all") ? join(" ",array_keys($timeDesc->asSimpleArray()))     : $requestedData["Times"];
//
//
//        $scr = "{$outputFolder}".uniqid().".sh";
//
//        $scenarios = explode(" ",$requestedData["Scenarios"]);
//        $models = explode(" ",$requestedData["Models"]);
//        $times = explode(" ",$requestedData["Times"]);
//
//        $total = count($scenarios) * count($models) * count($times);
//        $count = 1;
//        foreach ($scenarios as $scenario)
//            foreach ($models as $model)
//                foreach ($times as $time)
//                {
//                    $toStore = "{$scenario}_{$model}_{$time}".CommandConfiguration::osPathDelimiter()."*.gz";
//
//                    $prev_dir = trim(exec('pwd'));
//
//
//                    $cmd  = "cd '{$DF}'; ";
//                    $cmd .= "zip -0 $archiveFilename {$toStore}".";";
//                    $cmd .= "cd {$prev_dir}\n";
//                    
//                    file_put_contents($scr, $cmd,FILE_APPEND);
//
//
//                    $count++;
//                }
//
//
//
//        exec("qsub $scr"); // add files to archive
//
//
//    }
//
//    return str_replace($outputFolder, "", $archiveFilename);
//}
//
//

?>
