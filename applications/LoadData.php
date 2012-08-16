<?php
include_once dirname(__FILE__).'/includes.php';

$prog = array_util::Value( $argv, 0);
$species_id = array_util::Value($argv, 1);
$todo = array_util::Value($argv, 2);

if (is_null($species_id)) usage($prog);

function usage($prog)
{
   echo "usage: {$prog} species_id [load/remove/[check]] [--filepattern=* ] \n" ;
   echo "        load            - load this species data from database  \n";
   echo "        remove          - remove this species data from database\n";
   echo "        check           - list this species data in database  \n";
   echo "        --filepattern=  - file pattern to use when loading data ";
   exit(1);
}

$info = SpeciesData::SpeciesQuickInformation($species_id);
if ($info instanceof ErrorMessage) 
{
    print_r($info) ;
    exit(1);
}
    
echo $species_id ." .. ".$info."\n";

if (is_null($todo)) $todo = "check";

switch ($todo) {
    case 'load':
        load($species_id,$argv);
        break;

    case 'check':
        check($species_id,$argv);
        break;

    case 'remove':
        remove($species_id,$argv);
        break;
    
    default:
        echo "Option [$todo] is unknown action \n";
        usage($prog);
        break;
}

exit(0);

function load($species_id,$options)
{
    
    $file_pattern = '*';
    
    $opt = 'filepattern';
    if (array_util::Contains($options, $opt))
    {
        $fp = array_util::FirstElementsThatContain($options, $opt);
        if (!is_null($fp))
        {
             $file_pattern = str_replace("--{$opt}=", '', $fp);
             $file_pattern = str_replace("'", '', $file_pattern);
        }
        
    }
    
    echo "Loading data for species [{$species_id}] using file pattern [{$file_pattern}] \n";
    
    $result  = SpeciesData::LoadData($species_id,$file_pattern);

    if ($result instanceof ErrorMessage) 
    {
        echo "####LOAD FAILED \n";
        print_r($result) ;
        exit(1);
    }
    
    echo "Data loaded for species [{$species_id}]\n";        

}

function remove($species_id)
{
    $result = SpeciesData::RemoveDataforSpecies($species_id);

    if ($result instanceof ErrorMessage) 
    {
        echo "####REMOVE FAILED \n";
        print_r($result) ;
        exit(1);
    }
    
    echo "Data removed for species [{$species_id}]\n";        
    
}

function check($species_id)
{
    $result  = SpeciesData::CurrentInfo($species_id);

    if ($result instanceof ErrorMessage) 
    {
        echo "####CHECK FAILED \n";
        print_r($result) ;
        exit(1);
    }
    
    if (!is_array($result)) exit(1);

    
    foreach ($result as $named => $array) 
    {
        echo "{$named}\n";
        echo "=======================================\n";

        if (is_array($array))
        {
            foreach ($array as $key => $value) 
            {
                if (is_array($value))
                {
                    //matrix::display($value, " ", null, 15);
                    echo "$key => ".  implode(", ", $value)."\n";
                }
                else 
                {
                    echo "$key => $value\n";    
                }
                
                
            }            
        }
        else
        {
            echo "$array\n";
        }
        
        
        echo "\n\n";
        
    }
    
    
    
        
    
    echo "Data checked for species [{$species_id}]\n";        
    
    
}



?>
