<?php
include_once dirname(__FILE__).'/includes.php';

$result = array();

$key  = array_util::Value($argv, 1);
if (is_null($key)) $key = 'tdh_';

exec("qstat | grep '{$key}'| grep 'R normal' ",$result);

print_r($result);



?>
