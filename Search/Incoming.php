<?php
/**
 * This get called from CRON to run every minute looking for commands in the QUEUE
 *
 */
include_once 'includes.php';
CommandProcessor::ProcessQueue();
?>



