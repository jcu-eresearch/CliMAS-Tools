<?php
include_once 'includes.php';
echo "pre count = ".PG::CommandActionCount()."\n";
PG::CommandActionRemoveAll(true);
echo "post count = ".PG::CommandActionCount()."\n";

?>
