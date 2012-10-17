<?php
/**
 * This get called from CRON to run every minute looking for commands in the QUEUE
 *
 */
include_once 'includes.php';
$result = CommandProcessor::ProcessQueue();



if ($result instanceof ErrorMessage) 
    ErrorMessage::Stacked (__METHOD__, __LINE__, "", true, $result);
    


?>



