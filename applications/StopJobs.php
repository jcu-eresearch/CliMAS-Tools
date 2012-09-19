<?php

$DEFAULT = "jc166922";
include_once dirname(__FILE__).'/includes.php';
/**
 * Try to Stop / remove jobs from PBS queue that contain string from command line  
 * or default 'jc166922'
 *  
 */


$result = array();

$key  = array_util::Value($argv, 1);
if (is_null($key)) $key = $DEFAULT;

exec("qstat | grep '{$key}'| grep 'R normal' ",$result);

print_r($result);

$job_ids = array();

foreach ($result as $key => $value) 
{
    $job_ids[] = util::leftStr($value, '.');
}

echo "Stopping ".count($job_ids)." jobs\n";

$cmd = "qdel ".implode("; qdel ",$job_ids).";";

exec($cmd);


?>
