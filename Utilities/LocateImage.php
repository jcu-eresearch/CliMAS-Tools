<?php
include_once 'includes.php';


$csv = util::CommandLineOptionValue($argv,'csv',null);
if (!is_null($csv)) 
{
    
    echo image_location::LatLong2CSV($csv)."\n";  
    exit(1);
}

$kml = util::CommandLineOptionValue($argv,'kml',null);
if (!is_null($kml)) 
{
    
    echo image_location::folder2KML($kml)."\n";  
    exit(1);
}

echo "usage {$argv[0]}  --csv=path_to_images \n";
echo "usage {$argv[0]}  --kml=path_to_images \n";



?>
