<?php
include_once 'includes.php';


$idList = PG::CommandActionListIDs();

if (is_array($idList))
{
    foreach (PG::CommandActionListIDs() as $commandID) 
    {
        $ca = PG::ReadCommandAction($commandID);
        $ca instanceof CommandAction;
        $ca->Debug();
    }
    
}


?>
