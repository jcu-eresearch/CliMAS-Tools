<?php

include_once 'includes.php';

echo "Test to see if we can write a COmmand Action obect to postgress database.\n";


echo "Table count = ".PG::CommandActionCount()."\n";
PG::CommandActionRemoveAll(true);

echo "After remove all Table count = ".PG::CommandActionCount()."\n";


echo "Add one Object to Database = \n";

$me = new SpeciesMaxent();

print_r($me);

$write = PG::WriteCommandAction($me);

$read = PG::ReadCommandAction($me->ID());

echo "This object should look the same as the one above \n";
print_r($read);


//
echo "Find original one id = {$me->ID()} \n";

$countme = PG::CommandActionCount($me->ID());

echo "count for  {$me->ID()} $countme\n";



 echo "add test Objects with different ID's";

echo "CReate 100\n";

echo "Count before create 100 before  = ".PG::CommandActionCount()."\n";

for ($index = 0; $index < 10; $index++) {
    $me = new SpeciesMaxent();
    $me->initialise();
    $write = PG::WriteCommandAction($me);    
}


echo "Count after Create 100 = ".PG::CommandActionCount()."\n";

echo "List all ID's\n";
print_r(PG::CommandActionListIDs());
echo "\n";



echo "List all ID's with Execution Flag\n";
print_r(PG::CommandActionExecutionFlag());
echo "\n";

echo "List ExecutionFlag  for {$me->ID()} \n";
print_r(PG::CommandActionExecutionFlag($me->ID()));
echo "\n";

echo "Get status of {$me->ID()}  \n";
print_r(PG::CommandActionStatus($me->ID()));
echo "\n";

echo "Update status of {$me->ID()} to 'I was here'  \n";
CommandUtil::QueueUpdateStatus($me, "I was here");
echo "\n";

echo "Get status of {$me->ID()}   - after update \n";
print_r(PG::CommandActionStatus($me->ID()));
echo "\n";

?>
