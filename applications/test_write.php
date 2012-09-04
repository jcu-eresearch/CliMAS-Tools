<?php
session_start();
include_once dirname(__FILE__).'/includes.php';

$name = "fred.txt";
$sf = configuration::CommandScriptsFolder();
$fn = "{$sf}{$name}";


$result = file_put_contents($fn, "so data goes here");

$contents = "xxxxxx";
if ($result)
{
    $contents = file_get_contents($fn);
}


?>
<html>
    <head>
        
    </head>
    <body>
        SF <?php echo $sf;?><br>
        FN <?php echo $fn;?><br>
        result <?php echo $result;?><br>
        contents <?php echo $contents;?><br>
        
        
    </body>
</html>