<?php
/**
 * Main page for Species Suitability tool
 *
 *
 *
 */
session_start();
include_once dirname(__FILE__).'/includes.php';

$species_id = array_util::Value($_GET, "species_id",null);
$name = SpeciesData::SpeciesCommonNameSimple($species_id);

$filename = configuration::SourceDataFolder() . 'species/{$species_id}/species_data_{$name}.zip';

if (is_file($filename)) {

    http_send_content_disposition(basename($filename), true);
    http_send_content_type();
    // http_throttle(0.1, 2048);
    http_send_file($filename);

    /*
    header("X-Sendfile: {$filename}");
    header("Content-type: application/octet-stream");
    header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
    header("Content-Length: ". filesize($filename));
    readfile($filename);
    */
} else {
    echo "Well, this is embarassing.  I looked for the file you wanted, {$filename}, but it turns out that file is not available for download.  I'm really sorry.";
}
?>