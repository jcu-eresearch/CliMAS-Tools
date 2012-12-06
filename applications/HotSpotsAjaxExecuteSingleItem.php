<?php 
// used to process hot spoit / richness of a single "pre-canned" item.

    // delimied string of  (scenario)_(time)=(asc grid location)~(scenario)_(time)=(asc grid location)~(scenario)_(time)=(asc grid location)
    //  
    // asc grid location is from the richness folder down. 
    // so here it will be ByGenus/GenusName/(Scenario)_(Time).asc
    // expects to be PNG quick look image here as well available as    ByGenus/GenusName/(Scenario)_(Time).png
    //
session_start();
include_once dirname(__FILE__).'/includes.php';
if (php_sapi_name() == "cli") return;

$result = array();
$result['dataType'] = htmlutil::ValueFromPost('dataType');
$result['dataName'] = htmlutil::ValueFromPost('dataName');
$result['result'] = '';
$result['error'] = '';

$richness_sdf = configuration::Maxent_Species_Data_folder()."richness/By{$result['dataType']}/{$result['dataName']}/";

if (!is_dir($richness_sdf))
{
    $result['error'] = "Now precomputed richness found for [{$result['dataType']}:: {$result['dataName']}]";
    echo json_encode($result);    
    exit();    
}

// richness found - create delimited result
// we need to make sure that all GZ files have be extr5acted 


$files = file::folder_gz($richness_sdf, configuration::osPathDelimiter(), true);

$parts = array();
foreach ($files as $basename => $path) 
{
    list($scenario,$time) = explode("_",  str_replace(".asc.gz", "", $basename) );
    $parts[] = "{$scenario}_{$time}=By{$result['dataType']}/{$result['dataName']}/{$scenario}_{$time}.asc";
    
}



$result['result'] = implode("~",$parts);
echo json_encode($result);    

exit();
?>
