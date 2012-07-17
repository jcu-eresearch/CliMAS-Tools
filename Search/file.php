<?php
include_once 'includes.php';
$id = array_util::Value($_GET, 'id');
$fn = array_util::Value($_GET, 'fn');

if (is_null($id)) return;
$db = new PGDB();

if (!is_null($fn))
    header('Content-disposition: attachment; filename='.$fn.' '); 

header('Content-Type: '.$db->ReadFileMimeType($id));
$db->ReadFile2Stream($id);
unset($db);
?>
