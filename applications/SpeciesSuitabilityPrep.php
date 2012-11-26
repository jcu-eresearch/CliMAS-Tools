<?php
/**
 * Implementation of MAPSERVER mapping interface for use with Suitability Tool
 *
 */
include_once 'includes.php';

$result = array();
$result['map_path'] = '';
$result['error'] = '';

//
// read in all the required config info
//

// species id

$species_id = array_util::Value($_POST, "SpeciesID");

if (is_null($species_id)) {
    $result['error'] = 'Species ID not specified';
    echo json_encode($result);    // if no user layer then return result now
    exit();
}

// user layer (combination of Scenario_model_time)

$UserLayer = array_util::Value($_POST, "UserLayer");

if ($UserLayer == "CURRENT_CURRENT_1990") $UserLayer = "1990";
$UserLayer = str_replace("_ALL_", "_all_", $UserLayer);

if (is_null($UserLayer)) {
    $result['error'] = 'Userlayer not specified';
    echo json_encode($result);    // if no user layer then return result now
    exit();
}

// number of buckets (colour bands)

$bucket_count = array_util::Value($_POST, "bucket_count", 5);

//
// create necessary files (map file and asciigrid) and send back path to map file.
//

$M = new MapServerWrapper();

// find the gzipped asciigrid
$grid_filename_gz  = SpeciesFiles::species_data_folder($species_id)."{$UserLayer}.asc.gz";
if (!file_exists($grid_filename_gz)) {
    $result = array();
    $result['grid_filename_gz'] = $grid_filename_gz;
    $result['error'] = "GZIPPED version of ascii grid does not exist species_id = [{$species_id}] file = [{$grid_filename_gz}]";
    echo json_encode($result);    // we can't find asc grid file so return empty map_path
    exit();
}
// $grid_filename_asc = SpeciesFiles::species_data_folder($species_id)."{$UserLayer}.asc";

// find the unzipped version of the asciigrid
$grid_filename_asc = "/tmp/{$UserLayer}_{$species_id}.asc";
// unzip a fresh new asciigrid if there isn't one there already
if (!file_exists($grid_filename_asc)) {
    // if the ascii is not there, create it by unzipping the gz into /tmp
    $cmd = "gunzip -c '{$grid_filename_gz}' > '{$grid_filename_asc}'";
    $result['cmd'] = $cmd;
    $result['cmd_result'] = exec($cmd);
}

// double check that the unzipping worked
if (!file_exists($grid_filename_asc)) {
    $result['error'] = "Still cant find ascii grid file [{$grid_filename_asc}] ";
    $result['grid_filename_asc'] = $grid_filename_asc;
    echo json_encode($result);    // we can't find asc grid file so return empty map_path
    exit();
}

// now locate the mapfile
$map_path =  "/tmp/{$UserLayer}_{$species_id}.map";
$result['map_path'] = $map_path;

// check if we already have a mapfile
if (file_exists($map_path)) {
    // if we do, just return result including the path to it
    echo json_encode($result);
    exit();
}

//
// if we get here, we need to make the mapfile.
//

// start the colour banding setup
$layer = $M->Layers()->AddLayer($grid_filename_asc);
// $layer instanceof MapServerLayerRaster;
$layer->HistogramBuckets($bucket_count);

// start colour ramp at zero
// TODO: actually, should start at the suitability threshold rather than 0
$ramp = RGB::Ramp(0, 1, $bucket_count,RGB::ReverseGradient(RGB::GradientYellowOrangeRed()));

print_r($ramp);

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

// add the colour bands to the layer
$layer->ColorTable($ramp);

// write out our completed mapfile
$MF = Mapfile::create($M);
$MF->save($M,$map_path,true);

// double check that the mapfile made it to disk
if (!file_exists($map_path)) {
    // no mapfile?  bail!
    $result['error'] = "Failed to create map file [{$map_path}]";
    $result['map_path'] = $map_path;
    echo json_encode($result);
    exit();
}

//
// everything worked!
// so make a new result hash with just the mapfile path in it and return that
//

$result = array();
$result['map_path'] = $map_path;
echo json_encode($result);
exit();

?>