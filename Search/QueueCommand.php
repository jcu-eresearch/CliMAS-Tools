<?php
session_start();
include_once 'includes.php';
$head = "";
$title = "Queue Command";
$content = "";

$pageRefresh = 3;


$refreshSeconds = null;

$queueID = array_util::Value($_GET, "queueID", null);

if (is_null($queueID))
{
    $action = array_util::Value($_GET, "a", null);
    
    
    // queue the action
    $cmd = FinderFactory::Action($action);  // first time in we don't have a queue id so execute the aqction and queue it
    
    if ($cmd instanceof CommandAction)
    {
        $cmd->initialise();
        
        if ($cmd->initialised())  // here is where you can check to see if command init ok
        {
            
            //print_r($cmd);
            
            $queueID = CommandUtil::Queue($cmd);

            if (is_null($queueID))
            {
                $content = "Could npot queue command for some reason ".$cmd->CommandName();
            }
            else
            {
                $content = "Queued ".$cmd->CommandName();    
                $refreshSeconds = $pageRefresh;
            }
            
        }
        else
        {
            $content = "Could initialise command ".$cmd->CommandName();
        }
        
    }
    else
    {
            $content = "Can't queue anything other than a CommandAction, tried to queue ".get_class($cmd);
    }

}
else
{
    
    $cmd = CommandUtil::GetCommandFromID($queueID);
    
    if (is_null($cmd))
    {
        $content = "tried to read queue and it's null???";
    }
    else
    {
        if ($cmd instanceof CommandAction)
        {
            if ($cmd->ExecutionFlag() == CommandAction::$EXECUTION_FLAG_COMPLETE) 
                $refreshSeconds = null; // stop refreshing page    
            else
            {
                $content .="<h3>Partial Results ".datetimeutil::NowDateTime()." </h3>";
                $refreshSeconds = $pageRefresh;
            }
            
            
            $O = OutputFactory::Find($cmd);
            
            if (!is_null($O))
            {
                $head = $O->Head();
                $title = $O->Title();
                $content .= $O->Content();
            }
            else
            {
                $content .= OutputFactory::Find($cmd->Result());   
            }

        }
        else
        {
            $content = "Waiting for Server Response";
        }

        
    }
}

?>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <?php 
            
            $refreshTime = htmlutil::RefreshPageMetatag($refreshSeconds, $_SERVER['PHP_SELF']."?refresh={$refreshSeconds}&queueID={$queueID}");
        
            echo $head."\n".$refreshTime."\n"; 
        
        ?>
        <title><?php echo $title;?></title>
    </head>
    <body>
        <?php 
        
            $content = trim($content);
            if ($content == "") $content = "Waiting on update from Cluster<br>" ;
        echo $content;
        ?>
    </body>
</html>
