<?php
include_once 'includes.php';

$idList = DatabaseCommands::CommandActionListIDs();

if (is_array($idList))
{
    foreach ($idList as $commandID) 
    {
        $ca = DatabaseCommands::CommandActionRead($commandID);
        
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
