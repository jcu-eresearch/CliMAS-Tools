<?php
include_once 'image_location.class.php';

$loc = image_location::LatLong($argv[1]);
if (!$loc) return;

if ($argc == 2)
{
    echo "filename,lat,long,alt\n";
    echo $loc;
    return TRUE;
}

if (($argc == 3) && ($argv[2] == "-kml") && is_dir($argv[1]))
{
    echo image_location::folder2KML($argv[1],$argv[3]);
    return TRUE;
}


if (($argc == 3) && ($argv[2] == "-kml") && file_exists($argv[1]))
{
    echo image_location::image2KML($argv[1]);
    return TRUE;
}



?>
