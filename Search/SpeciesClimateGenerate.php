<?php
/**
 * This get called from CRON to run every minute looking for commands in the QUEUE
 *
 */
include_once 'includes.php';


$argv_1 = array_util::Value($argv, 1);

SpeciesClimateGenerate::Execute($argv_1);

?>
