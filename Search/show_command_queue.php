<?php
include_once 'includes.php';


$idList = pgdb::CommandActionListIDs();

if (is_array($idList))
{
    foreach ($idList as $commandID) 
    {
        $ca = pgdb::CommandActionRead($commandID);
        $ca instanceof CommandAction;
        $ca->Debug();
    }
    
}


?>
