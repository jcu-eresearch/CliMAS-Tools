<?php
/**
 * Run on server to build at command oine to build speciesa data for a sipcies (by species ID) 
 * or for ALL
 */
include_once 'includes.php';
MaxentMainServerBuild::Run(array_util::Value($argv, "1"));

?>
