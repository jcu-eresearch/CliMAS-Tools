<?php
/**
 * Used to stream a file from the database primarily to a web page.
 * 
 * file.php?id=xxxxx.ccccc  search database for this "unique_file_id" and stream back.
 *  
 * file.php?id=xxxxx.ccccc&fn=somefile.ext  same as above though filename will be place in HTTP header and user can download. as attachement
 * 
 */
include_once 'includes.php';
$id = array_util::Value($_GET, 'id');
$fn = array_util::Value($_GET, 'fn');

$mimetype = DatabaseFile::ReadFileMimeType($id);

if (is_null($id)) return;
if (!is_null($fn)) 
{
    $fn .= ".".util::fromLastChar($mimetype,"/");
    header('Content-disposition: attachment; filename='.urldecode ($fn)); 
}
    
header('Content-Type: '.$mimetype);
header('Content-Transfer-Encoding: binary'); 
ob_clean(); 
flush(); 
DatabaseFile::ReadFile2Stream($id);
?>