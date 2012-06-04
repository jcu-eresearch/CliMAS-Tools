<?php
/**
 * This get called from CRON to run every minute looking for commands in the QUEUE
 *
 */
include_once 'Command.includes.php';

$files = file::folder_with_extension(CommandConfiguration::CommandQueueFolder(), RemoteCommandConfiguration::CommandExtension(), RemoteCommandConfiguration::osExtensionDelimiter(),true);

/**
 * Loop thru command files and process 
 */
foreach ($files as $filename => $filepath)
{

    // look at files and unserialase and check there status - may have incoming queue and outgoing queue
    
    // log time and what files

    file_put_contents(CommandConfiguration::CommandQueueLog(),"HPC checking $filepath\n" , FILE_APPEND);

}




?>



