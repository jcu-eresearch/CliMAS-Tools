<?php
include_once dirname(__FILE__).'/includes.php';

$n = FinderFactory::Find("SpeciesHotSpots");

$oReflectionClass = new ReflectionClass($n); 



$methods = $oReflectionClass->getMethods();

print_r($oReflectionClass);

print_r($methods);


foreach ($methods as $index => $data) 
{
    
    $rm = new ReflectionMethod("SpeciesHotSpots", $data->name);    
    
    $mods = $rm->getModifiers();
    
    $modNames = Reflection::getModifierNames($mods);

    $modNames_str = implode(",",$modNames);
    
    $ispub = isPublicMethod("SpeciesHotSpots",$rm->name);
    
    echo " {$oReflectionClass->name}  {$data->name} = {$rm->name} {$modNames_str}   [{$ispub}] \n";
    
}


function isPublicMethod($obj,$methodName)
{
    $rm = new ReflectionMethod($obj, $methodName);
    
    return array_util::Contains(Reflection::getModifierNames($rm->getModifiers()), 'public');
    
}


?>


