<?php
session_start();

    include_once 'util.class.php';
    include_once 'file.class.php';

    if (!array_key_exists("name", $_GET)) return;

    $info = $_SESSION[$_GET['name']];

    //** print_r($info);

    header("Content-type: application/**octet-stream");
    header('Content-Disposition: attachment; filename="'.$info['fn'].'.csv"');

    $link = database::connect($info['db']);
    $result = database::query($info['sql'], $link);

    if ($result == "" || $result == FALSE)
       echo "NO RESULT";
    else
        echo util::printableMatrix($result, $delim = ",");

    database::disconnect($link);

    unset($_SESSION[$_GET['name']]);

?>