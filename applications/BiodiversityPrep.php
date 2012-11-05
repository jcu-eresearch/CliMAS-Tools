<?php
/**
 * Implementation of MAPSERVER mapping interface for use with Suitability Tool
 *
 */
include_once 'includes.php';


$clazz = array_util::Value($_GET, "class");
$taxon = array_util::Value($_GET, "taxon");
$settings = array_util::Value($_GET, "settings");

$bucket_count = array_util::Value($_GET, "bucket_count",20);

$result = array();
$result['map_path'] = '';
$result['error'] = '';

if (is_null($taxon)) {
    $result['error'] = 'taxon not specified';
    echo json_encode($result);    // if no user layer then return result now
    exit();
}

if (is_null($clazz)) {
    $result['error'] = 'class not specified';
    echo json_encode($result);    // if no user layer then return result now
    exit();
}

if (is_null($settings))
{
    $result['error'] = 'settings (year and emission scenario) not specified';
    echo json_encode($result);    // if no user layer then return result now
    exit();
}

// translate clazz to the version used in folder names - the plural
// of the common name, e.g. MAMMALIA = mammals

$clazz = ClazzData::clazzCommonName($clazz, true);

// if ($UserLayer == "CURRENT_CURRENT_1990") $UserLayer = "1990";
// $UserLayer = str_replace("_ALL_", "_all_", $UserLayer);

// create map file and send back path to map file.

$M = new MapServerWrapper();

$grid_filename_gz  = configuration::SDMFolder() . "{$clazz}/richness/{$settings}_{$taxon}.asc.gz";
if (!file_exists($grid_filename_gz)) {
    $result = array();
    $result['grid_filename_gz'] = $grid_filename_gz;
    $result['error'] = "GZIPPED version of ascii grid does not exist ({$clazz}, {$settings} for taxa {$taxon})";
    echo json_encode($result);    // we can't find asc grid file so return empty map_path
    exit();
}


// $grid_filename_asc = SpeciesFiles::species_data_folder($species_id)."{$UserLayer}.asc";

$grid_filename_prefix = "/tmp/{$clazz}_richness_{$settings}_{$taxon}";

$grid_filename_asc = $grid_filename_prefix . ".asc";

// get the ascii grid filename
if (!file_exists($grid_filename_asc))
{
    // gzip is there but asc is not then create asc - leave gz in place
    // as gz is linked from somewhere else.
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

$map_path =  $grid_filename_prefix . ".map";
$result['map_path'] = $map_path;

// Already have a MAP file so just hand it back for this Map
if (file_exists($map_path)) {
    echo json_encode($result);
    exit();
}

$layer = $M->Layers()->AddLayer($grid_filename_asc);
$layer instanceof MapServerLayerRaster;
$layer->HistogramBuckets($bucket_count);

$layer->set('name', 'maplayer');

// start ramp at Zero -
$ramp = RGB::Ramp(0, 1, $bucket_count,RGB::ReverseGradient(RGB::GradientYellowOrangeRed()));

/*
$MaxentThreshold = DatabaseMaxent::GetMaxentThresholdForSpeciesFromFile($species_id);

if ($MaxentThreshold instanceof ErrorMessage)
{
    $result['error'] = print_r($MaxentThreshold,true);
    $result['map_path'] = "";
    echo json_encode($result);
    exit();
}
*/
$MaxentThreshold = 0;

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