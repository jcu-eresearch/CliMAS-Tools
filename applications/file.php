<?php
include_once 'includes.php';
$id = array_util::Value($_GET, 'id');
$fn = urldecode(array_util::Value($_GET, 'fn'));
if (is_null($id)) return;
if (!is_null($fn)) 
    header('Content-disposition: attachment; filename='.$fn); 
header('Content-Type: '.DatabaseFile::ReadFileMimeType($id));
header('Content-Transfer-Encoding: binary'); 
ob_clean(); 
flush(); 
DatabaseFile::ReadFile2Stream($id);

?>