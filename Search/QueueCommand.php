<?php
include_once 'includes.php';
$refreshTime = "";
$head = "";
$title = "Queue Command";
$content = "";

$refreshSeconds = array_util::Value($_GET, "refresh", 5);

$queueID = array_util::Value($_GET, "queueID", null);
if (is_null($queueID))
{
    // first time in we don't have a queue id so execute the aqction and queue it
    $A = FinderFactory::Action(array_util::Value($_GET, "a", null));
    $O = OutputFactory::Find($A);


    if ($A->Result() instanceof Command)
    {
        $cmd = $A->Result();
        $cmd instanceof Command;

        if (!is_null($O))
        {
            $refreshTime = htmlutil::RefreshPageMetatag($O->Refresh(), $_SERVER['PHP_SELF']."?refresh={$O->Refresh()}&queueID={$cmd->ID()}");

            $head = $O->Head();
            $title = $O->Title();
            $content = $O->Content();
        }

    }
    else
    {
        $content = "Can't queue anything other than a command, tried to queue ".get_class($A->Result());
    }

}
else
{
    $content = datetimeutil::now()." ... Watch queue for changes in ID {$queueID}<br>";
    $refreshTime = htmlutil::RefreshPageMetatag($refreshSeconds, $_SERVER['PHP_SELF']."?refresh={$refreshSeconds}&queueID={$queueID}");

    $status = CommandFactory::QueueStatus($queueID);

    $content .= "Status: ".OutputFactory::Find($status)."<br>";


}



?>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <?php echo $head."\n".$refreshTime."\n"; ?>
        <title><?php echo $title;?></title>
    </head>
    <body>
        <?php echo $content;?>
    </body>
</html>
