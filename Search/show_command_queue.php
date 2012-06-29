<?php
include_once 'includes.php';

foreach (PG::CommandActionListIDs() as $commandID) 
{
    $ca = PG::ReadCommandAction($commandID);
    $ca instanceof CommandAction;
    $ca->Debug();
}


?>
