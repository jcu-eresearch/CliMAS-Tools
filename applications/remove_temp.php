<?php
include_once dirname(__FILE__).'/includes.php';
if (php_sapi_name() != "cli") return;
DatabaseFile::RemoveUsedFiles();
?>
