<?php
include_once 'includes.php';
$head = "";
$title = "POPUP";
$content = "NO CONTENT";
$refresh = "";

$a = array_util::Value($_GET, "a", null);
if (is_null($a)) $a = array_util::Value($_POST, "actionName", null);

Session::addActionIds($a,array_util::Value($_POST, "selectedIDs", null) );  // if we have posted any ids we need to get them and store them to the session
$ids = Session::getActionIds($a); // get ids that have been saved ffor this action

$F = FinderFactory::Action($a);
if (!is_null($F))
{
    $O = OutputFactory::Find($F);
    $refresh = htmlutil::RefreshPageMetatag($O->Refresh(), $_SERVER['PHP_SELF']);

    $head = $O->Head();
    $title = $O->Title();
    $content = $O->Content();

}
?>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link rel="stylesheet" type="text/css" href="popup.css" />
        <script src="popup.js" type="text/javascript"></script>
        <?php echo $head."\n".$refresh."\n"; ?>
        <title><?php echo $title;?></title>
    </head>
    <body >
        <h1><?php echo $title;?></h1>
        <?php echo $content;?>
        <FORM method="POST" ID="popupSelectedForm" NAME="popupSelectedForm" action="<?php echo $_SERVER['PHP_SELF'];?>" >
            <INPUT TYPE="HIDDEN" ID="actionName" NAME="actionName"  VALUE="<?php echo $a; ?>">
            <INPUT TYPE="HIDDEN" ID="selectedIDs" NAME="selectedIDs" VALUE="">
        </FORM>
        <script>selectIDs('<?php echo $ids; ?>')</script>
    </body>
</html>
