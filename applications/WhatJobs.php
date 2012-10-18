<?php
include_once dirname(__FILE__).'/includes.php';
if (php_sapi_name() != "cli") return;
/**
 * PBS jobs running for a specific string (tdh_) default string for TDH Tools jobs
 *  
 */

$result = array();

$key  = array_util::Value($argv, 1);
if (is_null($key)) $key = 'tdh_';

exec("qstat | grep '{$key}'| grep 'R normal' ",$result);

print_r($result);


?>
