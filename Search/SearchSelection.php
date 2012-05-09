<?php
include_once 'includes.php';

$variableNames = FinderFactory::Result("Variables");


?>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>Search Selector</title>
    </head>
    <body>
        Search Selector<br>

        <div id="MapForEach" class="Selector">

            A single map for a subset of values in this variable  (a Subset could be ALL)<br>

            <br>Marker 1<br>
            <?php print_r($variableNames); ?>
            <br>Marker 2<br>

            <br>
            Time <br>
            Species<br>
            Climate Model<br>
            Emission Scenario<br>
            

        </div>

        <div id="ShowOnEachMap" class="Selector">
            On each map show this variable (Mutually Exclusive to the "MapForEach")<br>
            Time <br>
            Species<br>
            Climate Model<br>
            Emission Scenario<br>



        </div>

        <div id="Restrictions" class="Selector">
            Restrict Data on each map to this subset ()
            Time <br>
            Species<br>
            Climate Model<br>
            Emission Scenario<br>

        </div>


        <div id="Process" >
            RUN BUtton<br>
            Message here for wait / email.

        </div>

        <?php


        ?>
    </body>
</html>
