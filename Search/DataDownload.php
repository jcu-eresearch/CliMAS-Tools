<?php
include_once 'includes.php';

$haveRequest = null;
$requestID = null;

$newRequest = array_util::Value($_GET, "new", "false");

if ($newRequest == "true") 
{
    Session::add("requestID", null);
}
else
{

    $requestedScenario = array_util::Value($_GET, "scenario", null);
    $requestedModel = array_util::Value($_GET, "model", null);
    $requestedTime = array_util::Value($_GET, "time", null);

    $haveRequest = (!is_null($requestedScenario)) && (!is_null($requestedModel)) && (!is_null($requestedTime));

    // look at Session for Request ID otherwise look at URL for requestID
    $requestID = Session::get("requestID",array_util::Value($_GET, "requestID",null ));

}


function selectionTable()
{

    Session::add("requestID", null); // clear this requestID - so we are not rerequesting something


    $modelsDesc = ToolsData::ClimateModels();
    $scenarioDesc = ToolsData::EmissionScenarios();
    $timeDesc = ToolsData::Times();


    $self = $_SERVER['PHP_SELF'];

    $rowcount = 0;
    $colcount = 0;

    echo "<h1>Data Download - Step 1</h1>";
    echo "<h2>select the datset you would like</h2>";


    // loop thru the various data sets and create a display
    foreach ($modelsDesc->asSimpleArray() as $modelKey => $modelDescription)
    {
        echo "<tr>";
        echo "<td><a href=\"{$self}?scenario=all&model={$modelKey}&time=all\">$modelKey</a></td>";

        $colcount = 0;

        foreach ($scenarioDesc->asSimpleArray() as $scenarioKey => $scenarioDescription)
        {

            echo "<td>";
            echo "<table>";

            if ($rowcount == 0)
            {
                echo "<tr><td><a href=\"{$self}?scenario=$scenarioKey&model=all&time=all\">$scenarioKey</a></td></tr>";
            }

            echo "<tr><td><a href=\"{$self}?scenario={$scenarioKey}&model={$modelKey}&time=all\">$scenarioKey</a></td></tr>";

            foreach ($timeDesc->asSimpleArray() as $timeKey => $timeDescDescription)
            {

                echo "<tr>";
                echo "<td><a href=\"{$self}?scenario={$scenarioKey}&model={$modelKey}&time={$timeKey}\">$timeKey</a></td>";
                echo "</tr>";

                $colcount++;

            }

            echo "</table>";
            echo "</td>";

        }
        echo "</tr>";

        $rowcount++;


    }

}

function downloadRequestConfirmation($requestedScenario,$requestedModel,$requestedTime,$requestID)
{

    if (!is_null($requestID))
    {
        echo "<br><a href=\"$self\"> Request Already made  goto - Update page</a><br>";


    }
    else
    {

        echo "<h1>Data Download - Step 2</h1>";
        echo "<h2>requesting packaged files.</h2>";

        $scenarioDesc = ToolsData::EmissionScenarios();
        $modelsDesc = ToolsData::ClimateModels();
        $timeDesc = ToolsData::Times();

        echo "<br>Scenarios ".$requestedScenario;
        echo "<br>Models ".$requestedModel;
        echo "<br>Times ".$requestedTime;

        // get key list for each variable

        $scenarios = ($requestedScenario == "all") ? join(" ",array_keys($scenarioDesc->asSimpleArray())) : $requestedScenario;
        $models    = ($requestedModel    == "all") ? join(" ",array_keys($modelsDesc->asSimpleArray()))   : $requestedModel;
        $times     = ($requestedTime     == "all") ? join(" ",array_keys($timeDesc->asSimpleArray()))     : $requestedTime;

        echo "<br>Scenarios ".$scenarios;
        echo "<br>Models " .$models;
        echo "<br>Times ".$times;


        // check to see if zip already exists

        // setup a command and submit it.

        $pdc = new PackageDatafilesCommand();
        $pdc->EmissionScenarioIDs($scenarios);
        $pdc->ClimateModelIDs($models);
        $pdc->TimeIDs($times);

        CommandFactory::Queue($pdc);

        $self = $_SERVER['PHP_SELF'];

        // store ID in session as well - so if this page is realoaded we don't do it again.
        Session::add("requestID", $pdc->ID());

        // even just push to new URL

        echo "<br><a href=\"$self?requestID={$pdc->ID()}\">Update page</a><br>Use this link to come back later and check progress<br>";

    }


}



function requestStatus($requestID)
{

    $cmd = CommandFactory::CommandFromQueue($requestID);

    if (is_null($cmd))
    {
        $self = $_SERVER['PHP_SELF'];
        echo "<br>Process with ID  $requestID does not exists anymore";
        echo "<br><a href=\"$self?new=true\">New Request</a><br>"; // link to page again for download
    }
    else
    {

        if ($cmd->ExecutionFlag() == Command::$EXECUTION_FLAG_COMPLETE)
        {

            if ($cmd instanceof PackageDatafilesCommand)
            {
                $cmd instanceof PackageDatafilesCommand;
                
                $toDownload = configuration::WebDownloadFolder().$cmd->PackageFilename();

                echo "<br><a href=\"{$toDownload}\">Download</a><br>"; //
            }

            $O = OutputFactory::Find($cmd->Result());

            if ($O instanceof Output)
                echo $O->Content();
            else
                echo $O;

            $self = $_SERVER['PHP_SELF'];
            echo "<br><a href=\"$self?new=true\">New Request</a><br>"; // link to page again for download

        }
        else
        {
            echo "<br>Running ";
            echo "<br>Execution Phase ".$cmd->ExecutionFlag();
            echo "<br>Status:: ".$cmd->Status();
            echo "<br>Last Server Update:: ".$cmd->LastUpdated();


        }

        
    }



}
?>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <script type="text/javascript">
        </script>
        <title>Data Download</title>
    </head>
    <body>
        <table>
        <?php

            if (!is_null($requestID)) 
                requestStatus($requestID);
            else
            {
                if ($haveRequest)
                    downloadRequestConfirmation($requestedScenario,$requestedModel,$requestedTime,$requestID);
                else
                    selectionTable();
            }
                
        ?>
        </table>
    </body>
</html>
