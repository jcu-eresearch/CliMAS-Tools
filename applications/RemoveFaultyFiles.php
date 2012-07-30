<?php
/**
 * Run from commandline to remove files that have less parts than their total
 *  
 */
include_once 'includes.php';

$id = array_util::Value($argv, 1);
if (is_null($id)) return;

DBO::Delete('files_data',             'file_unique_id = '.util::dbq($id));
DBO::Delete('files',                  'file_unique_id = '.util::dbq($id));
DBO::Delete('modelled_climates',      'file_unique_id = '.util::dbq($id));
DBO::Delete('modelled_species_files', 'file_unique_id = '.util::dbq($id));

?>
