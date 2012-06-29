<?php

include_once 'includes.php';

echo "Test to see if we can write a COmmand Action obect to postgress database.\n";

$me = new SpeciesMaxent();
$me->initialise();

print_r($me);


$write = PG::WriteCommandAction($me);


//$read = PG::ReadCommandAction($me->ID());


?>
