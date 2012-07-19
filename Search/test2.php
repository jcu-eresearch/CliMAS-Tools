<?php
// 5006684c7bddc 

include_once 'includes.php';

$db = new PGDB();

DatabaseFile::ReadFile2Filesystem('5006684c7bddc', '/home/jc166922/test/fred1.txt');

unset($db);

?>
