<?php
include_once dirname(__FILE__).'/includes.php';
if (php_sapi_name() != "cli") return;

$prog = array_util::Value( $argv, 0);
$clazz = array_util::Value($argv, 1);
$pattern = array_util::Value($argv, 2);

if (is_null($clazz)) usage($prog);

if ($clazz == "LIST") 
{
    print_r(SpeciesData::Clazz());
    exit();
}

if (is_null($pattern)) usage($prog);

function usage($prog)
{
   echo "Find all Species in this Clazz (Taxa) and create QuickLook images\n";
   echo "usage: {$prog} clazz 'pattern'\n\n" ;
   echo "pattern = filename pattern for files  ie. 'median'  as a filename = '*median*' \n" ;
   echo "hint: enclose pattern in single quotes to stop command line expansion\n" ;
   echo "\n" ;
   echo " for List of Clazz  and exit  {$prog} LIST \n\n" ;
      
   exit(1);
}


$species_rows = SpeciesData::TaxaForClazzWithOccurances($clazz);

if ($species_rows instanceof ErrorMessage)
{
    echo $species_rows;
    exit(1);
}

$count = 1;
foreach ($species_rows as $species_id => $row) 
{
    $info = SpeciesData::SpeciesQuickInformation($species_id);
    if ($info instanceof ErrorMessage)
    {
        echo $info;
        exit(1);
    }
    
    echo "{$count} of ".count($species_rows)." Create Quick Look for {$info} using pattern [{$pattern}]  \n";
    
    $result = SpeciesData::CreateQuickLook($species_id,$pattern,true);    
    
    if ($result instanceof ErrorMessage)
    {
        echo $result;
        exit(1);
    }
    
    $count++;
    
}




?>
