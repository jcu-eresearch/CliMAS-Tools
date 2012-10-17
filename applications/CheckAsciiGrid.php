<?php
include_once dirname(__FILE__).'/includes.php';

$options = $argv;

$prog = array_util::Value( $argv, 0);
$species_id = array_util::Value($argv, 1);

if ($argc == 1) usage($prog);

$list = optionValue($options,'list',null);
if (!is_null($list)) 
{
    doList($list,$options);
    exit();
}

$info = optionValue($options,'info',null);
if (!is_null($info)) 
{
    doInfo($info,$options);
    exit();
}

$folder = optionValue($options,'folder',null);
if (!is_null($folder)) doFolder($folder,$options);


$species_id = optionValue($options,'species',null);
if (!is_null($species_id)) doSpecies($species_id,$options);

$genus      = optionValue($options,'genus',null);
if (!is_null($genus)) doGenus($genus,$options);

$clazz      = optionValue($options,'clazz',null);
if (!is_null($clazz)) doClazz($clazz,$options);


exit(0);


function doSpecies($species_id,$options)
{
    
    $info = SpeciesData::SpeciesQuickInformation($species_id);
    if ($info instanceof ErrorMessage) 
    {
        print_r($info) ;
        exit(1);
    }
    
    echo $species_id ." .. ".$info."\n";    
    
    check_ascii($species_id,$options);
    
}


function doFolder($folder,$options)
{
    $folders = file::folder_folders(configuration::Maxent_Species_Data_folder(),'/',true);
    
    
    foreach ($folders as $species_id => $path) 
    {
        
        check_ascii($species_id,$options);
    }
    
    
}


function doClazz($clazz,$options)
{
    
    
}


function doGenus($genus,$options)
{
    
    
}


function doInfo($species_id,$options)
{
    
    $result = SpeciesData::CurrentInfo2File($species_id);
    if ($result instanceof ErrorMessage) 
    {
        echo $result;
        exit(1);
    }
    
    echo "file created:: [{$result}]\n";
    

}

function doList($listType,$options)
{

    $allowed = "kingdom\nphylum\nclazz\norderz\nfamily\ngenus\nspecies\n";
    
    if ($listType == "?")
    {
        echo $allowed;
        return;
    }

    if (!util::contains($allowed, $listType))
    {
        echo "List type [{$listType}] is unknown\n";
        return;
    }
    
    
    $result = matrix::Column(DBO::Unique('species_taxa_tree', $listType),$listType);
    
    print_r($result);
    
    echo "\n";
    
}


function check_ascii($species_id,$options)
{
    
    $file_pattern = optionValue($options,'filepattern','*');
    
    echo "Checking status of Ascii grid using gdalinfo for species [{$species_id}] using file pattern [{$file_pattern}] \n";
    
    $files = file::LS(SpeciesData::species_data_folder($species_id)."*".$file_pattern."*", null, true);
    $files = file::arrayFilter($files, "asc");
    $files = file::arrayFilterOut($files, "aux");
    $files = file::arrayFilterOut($files, "xml");
    
    
    $tooShort = "File short";
    
    foreach ($files as $key => $path) 
    {
        $result = array();
        $exitValue = null;
        echo "{$path}\n";
        
        
        $lineCount = file::lineCount($path);
        
        if($lineCount < 697)
        {
            echo "\nlineCount = {$lineCount} deleting  \n";
            file::Delete($path);
            continue;
        }
        
        exec("gdalinfo -stats '{$path}'",$result,$exitValue);
        
        $count = array_util::ElementsThatContain($result,$tooShort);
        if (count($count) > 0 )
        {
            echo "\nexitValue = {$exitValue}  too Short Count = {$count}\n";    
            print_r($result);
        }
        
        
    }
    


}

function optionValue($array,$optionName,$default = null)
{
    
    if (!array_util::Contains($array, $optionName)) return $default;
    
    $fp = array_util::FirstElementsThatContain($array, $optionName);
    if (is_null($fp)) return $default;
    
    $value = str_replace("--{$optionName}=", '', $fp);
    $value = str_replace("'", '', $value);
    
    return $value;
    
}


function usage($prog)
{
   echo "usage: {$prog} --species=species_id [--filepattern=* ] \n" ;
   echo "   or  {$prog} --genus=name         [--filepattern=* ] \n" ;
   echo "   or  {$prog} --clazz=name         [--filepattern=* ] \n" ;
   echo "   or  {$prog} --info=species_id    - information on data in Species folder\n" ;
   echo "               --filepattern=       - file pattern to use when checking data\n";
   echo "               --list=DataType      - Get a list of data of this type and exit\n ";
   echo "               --folder=true        - Check data for all data already downloaded\n ";
   echo "\n";
   echo "          e.g  --list=genus         - will display list of all Genus\n";
   echo "          e.g  --list=species       - will display list of all Species\n";
   echo "          e.g  --list=?             - will display list of all recognised dataTypes \n";
   
   exit(1);
}


?>
