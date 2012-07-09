<?php
include_once 'includes.php';

$idList = pgdb::CommandActionListIDs();

if (is_array($idList))
{
    foreach ($idList as $commandID) 
    {
        $ca = pgdb::CommandActionRead($commandID);
        
        if (is_null($ca)) 
        {
            echo "Error reading ca back from id $commandID\n";
            continue;
        }
        
        $ca instanceof CommandAction;
        $ca->Debug();
    }
    
}


?>
