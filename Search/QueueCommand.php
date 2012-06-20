<?php
include_once 'includes.php';
$refreshTime = "";
$head = "";
$title = "Queue Command";
$content = "";

$refreshSeconds = array_util::Value($_GET, "refresh", 30);

$queueID = array_util::Value($_GET, "queueID", null);

if (is_null($queueID))
{
    
    $actionName = array_util::Value($_GET, "a", null);
    
    echo "actionName = $actionName<br>";
    $A = FinderFactory::Action($actionName);  // first time in we don't have a queue id so execute the aqction and queue it
    
    
    
    $O = OutputFactory::Find($A);
    
    
    if ($A->Result() instanceof CommandAction)
    {
        $cmd = $A->Result();
        $cmd instanceof SpeciesMaxentCommand;

        if (!is_null($O))
        {
            $refreshTime = htmlutil::RefreshPageMetatag($O->Refresh(), $_SERVER['PHP_SELF']."?refresh={$O->Refresh()}&queueID={$cmd->ID()}");

            $head = $O->Head();
            $title = $O->Title();
            $content = $O->Content();
        }

    }
    else
        $content = "Can't queue anything other than a CommandAction, tried to queue ".get_class($A->Result());

}
else
{
    $refreshTime = htmlutil::RefreshPageMetatag($refreshSeconds, $_SERVER['PHP_SELF']."?refresh={$refreshSeconds}&queueID={$queueID}");

    $cmd = CommandUtil::GetCommandFromID($queueID);
    
    if (!is_null($cmd))
    {
        $toActionOutput = ($cmd->ExecutionFlag() == Command::$EXECUTION_FLAG_COMPLETE) ? $cmd->Result() : $cmd->Status();
        $content = OutputFactory::Find($toActionOutput);
    }
    
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
