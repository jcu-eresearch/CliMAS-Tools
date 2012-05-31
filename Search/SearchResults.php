<?php
include_once 'includes.php';
$head = "";
$title = "Search Results";
$content = "";
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
        <?php echo $head; ?>
        <title><?php echo $title;?></title>
    </head>
    <body>
        <?php echo $content;?>
    </body>
</html>
