<?php
/**
 * For Quicklook for any file patterbn
 *  
 */
include_once dirname(__FILE__).'/includes.php';

$prog = array_util::Value( $argv, 0);
$species_id = array_util::Value($argv, 1);
$pattern = array_util::Value($argv, 2);

if (is_null($species_id)) usage($prog);
if (is_null($pattern)) $pattern = "*";

function usage($prog)
{
   echo "usage: {$prog} species_id 'pattern'\n" ;
   echo "hint: enclose pattern in single quotes to stop command line expansion\n" ;
   exit(1);
}

$result = SpeciesData::CreateQuickLook($species_id,$pattern,true);

if ($result instanceof ErrorMessage)
{
    echo $result;
    exit(1);
}



?>
