<?php
/**
 * This get called from CRON to run every minute looking for commands in the QUEUE
 *
 */
include_once 'Command.includes.php';


// check the queue 6 times in this minute

for ($index = 1; $index <= 20; $index++) {

    /**
    * Loop thru command files and process
    */
    $files = file::find_files(CommandConfiguration::CommandQueueFolder(),CommandConfiguration::CommandExtension());

    foreach ($files as $filepath)
         processSingleQueueItem($filepath);
    
    sleep(3);
    echo "Process $index\n";
}

function processSingleQueueItem($filepath)
{

    echo "Process $filepath\n";

    $command = CommandUtil::GetCommandFromFile($filepath);

    if (!is_null($command))
    {
        $log  = datetimeutil::now().",checking $filepath\n";
        $log .= "Command was NULL\n\n\n";
        echo "{$log}";
    }
    else
    {

        print_r($command);

        exit();

        // just update with "has touched HPC " then wite dame file anme b ack with new object
        $command->Result($command->Result()."\n".datetimeutil::now()." Touch by HPC");

        $log  = datetimeutil::now().",checking $filepath\n";
        $log .= "Command Result now ".$command->Result()."\n\n\n";

        file_put_contents(CommandConfiguration::CommandQueueLog(),$log , FILE_APPEND);


        CommandUtil::PutCommandToFile($command);

    }

}


?>



