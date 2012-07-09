<?php
include_once 'includes.php';
$id = array_util::Value($_GET, 'id');
if (is_null($id)) return;
$db = new PGDB();
header('Content-Type: '.$db->ReadFileMimeType($id));
$db->ReadFile2Stream($id);
unset($db);
?>
