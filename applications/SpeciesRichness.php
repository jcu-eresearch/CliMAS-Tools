<?php
include_once 'includes.php';

$genus  = util::CommandScriptsFoldermandLineOptionValue($argv, 'genus', null);
$family = util::CommandScriptsFoldermandLineOptionValue($argv, 'family', null);
$clazz  = util::CommandScriptsFoldermandLineOptionValue($argv, 'clazz', null);


if (is_null($genus) && 
    is_null($family) && 
    is_null($clazz))
{
    echo "usage {$argv[0]} --clazz=name   (taxa)\n";
    echo "usage {$argv[0]} --clazz=LIST   Get List of clazz and exit\n";
    echo "\n";
    echo "usage {$argv[0]} --family=name\n";
    echo "usage {$argv[0]} --family=LIST   Get list of Family and exit\n";
    echo "\n";
    echo "usage {$argv[0]} --genus=name\n";
    echo "usage {$argv[0]} --genus=LIST    Get list of Genus and Exit\n";
    echo "\n";
    echo "      --model=a,b,c                  default: ALL    Limit model lookup to this model (ALL = median)\n";
    echo "      --scenario=a,b,c               default: ALL    all scenarios will be processed\n";
    echo "      --time=yyyy,yyyy,yyyy          default: ALL    all times will be processed\n";
    echo "      --minimum_occurance=nnn        default: 10     Minimum number of occurences\n";
    echo "\n";
    echo "      --ValidateExistenceOnly=[true|false]\n ";
    echo "                                     default: false  When validating file only check for existsence \n";
    echo "                                              true   otherwise check statistics asw ell\n";
    echo "\n";
    exit(0);
}

$minimum_occurance  = util::CommandScriptsFoldermandLineOptionValue($argv, 'minimum_occurance', 10);



if (!is_null($clazz) && $clazz == "LIST")  
{
    ErrorMessage::Marker("Class List limited by genus[{$genus}] family[$family]  clazz[{$clazz}]");
    
    $data = SpeciesData::TaxaWithOccurancesFilteredFor(null, $family, $genus,$minimum_occurance);
    matrix::display($data, " ",null,20);
    exit(0);
}

if (!is_null($family) && $family == "LIST") 
{
    ErrorMessage::Marker("Family List limited by genus[{$genus}] family[$family]  clazz[{$clazz}]");
    
    $data = SpeciesData::TaxaWithOccurancesFilteredFor($clazz, null, $genus,$minimum_occurance);
    matrix::display($data, " ",null,20);
    print_r($data);
    exit(0);

}

if (!is_null($genus)  && $genus  == "LIST")
{
    ErrorMessage::Marker("Genus List limited by genus[{$genus}] family[$family]  clazz[{$clazz}]");
    
    $data = SpeciesData::TaxaWithOccurancesFilteredFor($clazz, $family, null,$minimum_occurance);
    matrix::display($data, " ",null,20);
    exit(0);

}



if (!is_null($clazz)) 
{
    $list = SpeciesData::Clazz();
    if (!array_key_exists($clazz, $list))
    {
        ErrorMessage::Marker("Clazz requested with value [{$clazz}], this does not exists in that list");
        ErrorMessage::Marker("Valid Clazz");
        print_r($list);
        echo "\n";
        exit(0);
    }
    
}    

if (!is_null($family)) 
{
    $list = SpeciesData::Family();
    if (!array_key_exists($family, $list))
    {
        ErrorMessage::Marker("family requested with value [{$family}], this does not exists in that list");
        ErrorMessage::Marker("Valid families");
        print_r($list);
        echo "\n";
        exit(0);
    }
    
}


if (!is_null($genus)) 
{
    $list = SpeciesData::Genus();
    if (!array_key_exists($genus, $list))
    {
        ErrorMessage::Marker("genus requested with value [{$genus}], this does not exists in that list");
        ErrorMessage::Marker("Valid genus");
        print_r($list);
        echo "\n";
        exit(0);
    }
}


$model    = util::CommandScriptsFoldermandLineOptionValue($argv, 'model', null);
$scenario = util::CommandScriptsFoldermandLineOptionValue($argv, 'scenario', null);
$time     = util::CommandScriptsFoldermandLineOptionValue($argv, 'time', null);


$ValidateExistenceOnly = util::CommandScriptsFoldermandLineOptionValue($argv, 'ValidateExistenceOnly', false);

$SR = FinderFactory::Find("SpeciesRichness");
if ($SR instanceof ErrorMessage)
{
    echo $SR;
    exit(0);
    
}

$SR instanceof SpeciesRichness;


$SR->initialise();
$SR->clazz($clazz);
$SR->family($family);
$SR->genus($genus);
$SR->scenario($scenario);
$SR->model($model);
$SR->time($time);
$SR->MinimumOccurance($minimum_occurance);
$SR->ValidateExistenceOnly($ValidateExistenceOnly);
$result = $SR->Execute();

if ($result instanceof ErrorMessage)
{
    ErrorMessage::Marker("{$clazz} Incomplete and cannot be processed at this time \n".$result);

    print_r($SR->CombinationsMissing());

    exit(0);
}


?>
