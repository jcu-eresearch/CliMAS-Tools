<?php
include_once 'includes.php';

$head = "";
$title = "POPUP";
$content = "NO CONTENT";

$a = array_util::Value($_GET, "a", null);
$F = FinderFactory::Action($a);

if (!is_null($F))
{
    $O = OutputFactory::Find($F);
    $head = $O->Head();
    $title = $O->Title();
    $content = $O->Content();
}

?>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link rel="stylesheet" type="text/css" href="popup.css" />
        <?php echo $head; ?>
        <script type="text/javascript">
            function action(action)
            {
                location.href = "popup.php?a=" + action;
            }
            function value(action)
            {
                location.href = "popup.php?a=" + action;
            }


        </script>
        <title><?php echo $title;?></title>
    </head>
    <body>
        <h1><?php echo $title;?></h1>

        <?php echo $content;?>
    </body>
</html>
