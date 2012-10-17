<?php
/**
 * USed on server to pre generate species data - from commandline
 *
 */
include_once 'includes.php';

$argv_1 = array_util::Value($argv, 1);
if (is_null($argv_1)) return;
DatabaseSetup::Execute($argv_1);

?>
