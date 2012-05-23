<?php
include_once 'includes.php';
$result = FinderFactory::Result(array_util::Value($_GET, "a", null));

?>

<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title></title>
    </head>
    <body>
        <h1>popup</h1>
        <h2>Subset for <?php echo array_util::Value($_GET, "a", null);?></h2>
        <?php
        print_r($result);
        ?>
    </body>
</html>
