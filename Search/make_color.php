<?php
include_once 'includes.php';
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

$filename = '/home/jc166922/test/RCP3PD_gfdl-cm20_2015.asc';

$stats = spatial_util::RasterStatisticsPrecision($filename);

$ramp = RGB::Ramp($stats['Minimum'], $stats['Maximum'], 20, RGB::GradientYellowOrangeRed());
  
file::Delete("/home/jc166922/test/color.txt");
file::Delete("/home/jc166922/test/colour.png");

$indexes = array_keys($ramp);

$low_threshold = $indexes[1];

$color_table = "nv 0 0 0 0\n";  // no value
$count = 0;
foreach ($ramp as $index => $rgb) 
{
    $rgb instanceof RGB;
    
    if ($index < $low_threshold)
    {
        $color_table .= $count."% 0 0 0 0\n";
    }
    else
    {
        $color_table .= $count."% ".$rgb->Red()." ".$rgb->Green()." ".$rgb->Blue()." 255\n";    
    }
  
    
    $count++;
}

echo "{$color_table}";

file_put_contents("/home/jc166922/test/color.txt", $color_table);

$cmd = "gdaldem  color-relief /home/jc166922/test/RCP3PD_gfdl-cm20_2015.asc /home/jc166922/test/color.txt -nearest_color_entry -alpha -of PNG /home/jc166922/test/colour.png";
$cmd_result = exec($cmd);

echo "cmd_result = $cmd_result\n";

exec('display /home/jc166922/test/colour.png &');

?>
