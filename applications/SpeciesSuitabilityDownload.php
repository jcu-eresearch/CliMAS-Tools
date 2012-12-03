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
$homebase = configuration::SourceDataFolder() . "species/{$species_id}/";

$zipfiles = glob($homebase . "species_data_*.zip");
$filename = $zipfiles[0];

if (is_file($filename)) {

    /*
    http_send_content_disposition(basename($filename), true);
    http_send_content_type();
    // http_throttle(0.1, 2048);
    http_send_file($filename);
    */

    header("X-Sendfile: {$filename}");
    header("Content-type: application/octet-stream");
    header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
    header("Content-Length: ". filesize($filename));
    readfile($filename);

} else {
    echo <<<OOPS
    <html><head></head><body>
    <p>
        Well, this is embarassing.
    </p><p>
        I looked for the file that matches the species you wanted.  It looks like you're after this:
    </p><ul>
        <li><b>Species ID</b>: {$species_id}</li>
        <li><b>Data file</b>: {$filename}</li>
    </ul><p>
        ...but it turns out that data file is not available for download.  I'm really sorry.
    </p>

OOPS;
}
?>