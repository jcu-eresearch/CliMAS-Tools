<?php
include_once 'includes.php';
$variableNames = FinderFactory::Result("Searcher");

$snapshotTemplate = <<<CT
\n<INPUT class="MapVariableButton" ID="Map{#key#}" onclick="SetSnapshotVariable('{#key#}');" TYPE=BUTTON NAME="MapVariables[]" VALUE="{#value#}">
CT;
$showTemplate = <<<CT
\n<INPUT class="MapVariableButton" ID="Map{#key#}" onclick="SetShowVariable('{#key#}');" TYPE=BUTTON NAME="MapVariables[]" VALUE="{#value#}">
CT;
$restrictionsTemplate = <<<CT
\n<INPUT class="MapVariableButton" ID="Map{#key#}" onclick="SetRestriction('{#key#}');" TYPE=BUTTON NAME="MapVariables[]" VALUE="{#value#}">
CT;

?>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <style>
            body {
                font-family: sans-serif;
                font-size: 12pt;
            }

            .selected {
                background-color: pink;
            }

            .selector{
                height: 270px;
                width: 32%;
                float: left;
                margin: 2px;
                background-color: aliceblue;
                overflow: hidden;
                font-weight: lighter;
            }

            .selector h2
            {
                margin: 0px;
                display: block;
                border-bottom: 1px solid black;
            }


            .selector table {
                border: 0px transparent none;
                width: 96%;
                text-align: center;
                margin: 2%;
            }

            .selector input {
                width: 100%;
                text-align: left;
            }

            .selector input:hover {
                background-color: pink;
            }


            #snapshot{


            }

            #Show{

            }

            #Restrictions{

            }

            #Process{
                height: 30px;
                width: 100%;
                clear: both;
                float: none;
                margin: 2px;
                background-color: aliceblue;

            }
            #Messages{
                height: 50px;
                width: 100%;
                clear: both;
                float: none;
                margin: 2px;
                background-color: lightyellow;
            }

        </style>
        <script>
            function SetSnapshotVariable(src)
            {
                alert(" SetSnapshotVariable = " + src);
            }

            function SetShowVariable(src)
            {
                alert(" SetShowVariable = " + src);
            }

            function SetRestriction(src)
            {
                alert(" SetRestriction = " + src);
            }

        </script>
        <title>Search Selector</title>
    </head>
    <body>
        Search Selector<br>

        <div id="snapshot" class="selector">
            <h2>Snapshot</h2>
            <?php
                echo htmlutil::TableRowTemplate($variableNames,$snapshotTemplate);
            ?>
            <dfn>A single map for a subset of values in this variable  (a Subset could be ALL)</dfn>
            
        </div>

        <div id="Show" class="selector">
            <h2>Show</h2>
            <?php
                echo htmlutil::TableRowTemplate($variableNames,$showTemplate);
            ?>
            <dfn>On each map show this variable (Mutually Exclusive to the "Snapshot")</dfn>

        </div>

        <div id="Restrictions" class="selector">
            <h2>subset</h2>
            <?php
                echo htmlutil::TableRowTemplate($variableNames,$restrictionsTemplate);
            ?>
            <dfn>Restrict Data on each map to this subset</dfn>
            
        </div>


        <div id="Process">
            RUN BUtton<br>

        </div>

        <div id="Messages" >
            Message here for wait / email.

        </div>


        <?php


        ?>
    </body>
</html>
