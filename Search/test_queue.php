<?php

include_once 'includes.php';

echo "Test to see if we can write a COmmand Action obect to postgress database.\n";


echo "Table count = ".DatabaseCommands::CommandActionCount()."\n";
DatabaseCommands::CommandActionRemoveAll(true);

echo "After remove all Table count = ".DatabaseCommands::CommandActionCount()."\n";


echo "Add one Object to Database = \n";

$me = new SpeciesMaxent();

print_r($me);

$write = DatabaseCommands::CommandActionQueue($me);

$read = DatabaseCommands::CommandActionRead($me->ID());

echo "This object should look the same as the one above \n";
print_r($read);


echo "add test Objects with different ID's";

echo "CReate 100\n";

echo "Count before create 100 before  = ".PG::CommandActionCount()."\n";

for ($index = 0; $index < 10; $index++) {
    $me = new SpeciesMaxent();
    $me->initialise();
    $write = DatabaseCommands::CommandActionQueue($me);    
}


echo "Count after Create 100 = ".DatabaseCommands::CommandActionCount()."\n";

echo "List all ID's\n";
print_r( DatabaseCommands::CommandActionListIDs());
echo "\n";



echo "List all ID's with Execution Flag\n";
print_r(DatabaseCommands::CommandActionExecutionFlag());
echo "\n";

echo "List ExecutionFlag  for {$me->ID()} \n";
print_r(DatabaseCommands::CommandActionExecutionFlag($me->ID()));
echo "\n";

echo "Get status of {$me->ID()}  \n";
print_r(DatabaseCommands::CommandActionStatus($me->ID()));
echo "\n";

echo "Update status of {$me->ID()} to 'I was here'  \n";
$me->Status('I was here');
DatabaseCommands::CommandActionQueue($me);
echo "\n";

echo "Get status of {$me->ID()}   - after update \n";
print_r(DatabaseCommands::CommandActionStatus($me->ID()));
echo "\n";

?>
