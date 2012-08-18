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
    echo "      --model=a,b,c                default: ALL    Limit model lookup to this model (ALL = median)\n";
    echo "      --scenario=a,b,c             default: ALL    all scenarios will be processed\n";
    echo "      --time=yyyy,yyyy,yyyy        default: ALL    all times will be processed\n";
    echo "      --minimum_occurance=nnn      default: 10     Minimum value is 10\n";
    echo "\n";
    echo "      --LoadAscii=[true|false]     default: false  Load ASCII grid into database\n";
    echo "      --LoadQuickLook=[true|false] default: true   Load QuickLook data into database\n";
    echo "      --Recalculate=[true|false]   default: false  Remove current files and recalculate Species Richness\n";
    echo "                                                   (this may be usefull when more species have been added and moddelled)\n";
    echo "\n";
    exit(1);
}

$minimum_occurance  = util::CommandScriptsFoldermandLineOptionValue($argv, 'minimum_occurance', 10);


$SR = FinderFactory::Find("SpeciesRichness");
if ($SR instanceof ErrorMessage)
{
    echo $SR;
    exit(1);
    
}

$SR instanceof SpeciesRichness;

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


$model    = util::CommandScriptsFoldermandLineOptionValue($argv, 'model', null);
$scenario = util::CommandScriptsFoldermandLineOptionValue($argv, 'scenario', null);
$time     = util::CommandScriptsFoldermandLineOptionValue($argv, 'time', null);


$LoadAscii     = util::CommandScriptsFoldermandLineOptionValue($argv, 'LoadAscii', null);
$LoadQuickLook = util::CommandScriptsFoldermandLineOptionValue($argv, 'LoadQuickLook', null);
$Recalculate   = util::CommandScriptsFoldermandLineOptionValue($argv, 'Recalculate', null);


if (!is_null($clazz)) 
{
    $list = SpeciesData::Clazz();
    if (!array_key_exists($clazz, $list))
    {
        ErrorMessage::Marker("Clazz requested with value [{$clazz}], this does not exists in that list");
        ErrorMessage::Marker("Valid Clazz");
        print_r($list);
        echo "\n";
        exit(1);
    }
    
    
    $SR->initialise();
    $SR->clazz($clazz);
    $SR->scenario($scenario);
    $SR->model($model);
    $SR->time($time);
    $SR->LoadAscii($LoadAscii);
    $SR->LoadQuickLook($LoadQuickLook);
    $SR->Recalculate($Recalculate);
    $result = $SR->Execute();
    
    if ($result instanceof ErrorMessage)
    {
        ErrorMessage::Marker("{$clazz} Incomplete and cannot be processed at this time \n".$result);
        exit(1);
    }
    
    
    exit(0);
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
        exit(1);
    }
    
    $SR->initialise();
    $SR->family($family);
    $SR->scenario($scenario);
    $SR->model($model);
    $SR->time($time);
    $SR->LoadAscii($LoadAscii);
    $SR->LoadQuickLook($LoadQuickLook);
    $SR->Recalculate($Recalculate);
    $result = $SR->Execute();
    
    if ($result instanceof ErrorMessage)
    {
        ErrorMessage::Marker("{$family} Incomplete and cannot be processed at this time \n".$result);
        exit(1);
    }

    
    exit(0);
    
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
        exit(1);
    }
    
    $SR->initialise();
    $SR->genus($genus);
    $SR->scenario($scenario);
    $SR->model($model);
    $SR->time($time);    
    $SR->LoadAscii($LoadAscii);
    $SR->LoadQuickLook($LoadQuickLook);
    $SR->Recalculate($Recalculate);
    $result = $SR->Execute();
    
    if ($result instanceof ErrorMessage)
    {
        ErrorMessage::Marker("{$genus} Incomplete and cannot be processed at this time \n".$result);
        
        // this holds MaxentServerBuld parametesr to restore /build missing files 
        $SR->CombinationsMissing();
        
        exit(1);
    }

    
    exit(0);
}



?>
