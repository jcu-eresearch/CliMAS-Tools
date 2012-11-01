<?php
/**
 * Implementation of MAPSERVER mapping interface for use with Suitability Tool
 *  
 */
include_once 'includes.php';


$species_id = array_util::Value($_POST, "SpeciesID");
$UserLayer = array_util::Value($_POST, "UserLayer");  // is a combination of Scenario_model_time we need the ascii grid version

$bucket_count = array_util::Value($_POST, "bucket_count",20);

$result = array();
$result['map_path'] = '';
$result['error'] = '';


if (is_null($UserLayer)) 
{    
    
    $result['error'] = 'Userlayer not specified';
    echo json_encode($result);    // if no user layer then return result now
    exit();
}

if (is_null($species_id)) 
{
    $result['error'] = 'Species ID not specified';
    echo json_encode($result);    // if no user layer then return result now
    exit();
}



// create map file and send back path to map file.


$M = new MapServerWrapper();

$grid_filename_gz  = SpeciesFiles::species_data_folder($species_id)."{$UserLayer}.asc.gz";
if (!file_exists($grid_filename_gz))
{
    $result = array();
    $result['grid_filename_gz'] = $grid_filename_gz;
    $result['error'] = "GZIPPED version of ascii grid does not exist species_id = [{$species_id}] file = [{$grid_filename_gz}]";
    echo json_encode($result);    // we can't find asc grid file so return empty map_path
    exit();        
}




// $grid_filename_asc = SpeciesFiles::species_data_folder($species_id)."{$UserLayer}.asc";

$grid_filename_asc = "/tmp/{$UserLayer}_{$species_id}.asc";


// get the ascii grid filename
if (!file_exists($grid_filename_asc))
{
    // gzip is there bu asc is not then create asc - but leave gz inplace
    // as gz is loinked from somewhere else.
    $cmd = "gunzip -c '{$grid_filename_gz}' > '{$grid_filename_asc}'";;
    
    $result['cmd'] = $cmd;
    
    $result['cmd_result'] = exec($cmd);
    
}

if (!file_exists($grid_filename_asc))
{
    $result['error'] = "Still cant find ascii grid file [{$grid_filename_asc}] ";
    $result['grid_filename_asc'] = $grid_filename_asc;

    echo json_encode($result);    // we can't find asc grid file so return empty map_path
    exit();    
}

$map_path =  "/tmp/{$UserLayer}_{$species_id}.map";
$result['map_path'] = $map_path;

// Already have a MAP file so just hand it back for this Map
if (file_exists($map_path))
{
    echo json_encode($result);    
    exit();        
    
}



$layer = $M->Layers()->AddLayer($grid_filename_asc);
$layer instanceof MapServerLayerRaster;
$layer->HistogramBuckets($bucket_count);



// start ramp at Zero - 
$ramp = RGB::Ramp(0, 1, $bucket_count,RGB::ReverseGradient(RGB::GradientYellowOrangeRed()));

$MaxentThreshold = DatabaseMaxent::GetMaxentThresholdForSpeciesFromFile($species_id);

if ($MaxentThreshold instanceof ErrorMessage)
{
    $result['error'] = print_r($MaxentThreshold,true);
    $result['map_path'] = "";
    echo json_encode($result);    
    exit();            
}
    
    
foreach (array_keys($ramp) as $key )  
    if ($key < $MaxentThreshold) $ramp[$key] = null;    // chnage all values below threshold to trasparent

$layer->ColorTable($ramp);             

$MF = Mapfile::create($M);
$MF->save($M,$map_path,true);

// $result['marker1'] = "marker"; echo json_encode($result);  exit();        

if (!file_exists($map_path))
{
    $result['error'] = "Failed to create map file [{$map_path}]";
    $result['map_path'] = $map_path;
    echo json_encode($result);    
    exit();        
    
}


$result = array();
$result['map_path'] = $map_path;
echo json_encode($result);    
exit();        


?>