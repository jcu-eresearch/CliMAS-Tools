<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

include_once 'includes.php';

$prog = array_util::Value( $argv, 0);
$ascii_filename = array_util::Value($argv, 1);
$output_filename = array_util::Value($argv, 2);

if (is_null($ascii_filename)) usage($prog);

if (!file_exists($ascii_filename))
{
    echo "{$prog}:: File not found: $ascii_filename\n";
    exit(1);
}

function usage($prog)
{
   echo "usage: {$prog} ascii_grid_filename [output_filename] \n" ;
   exit(1);
}

$generated_filename = spatial_util::CreateImage($ascii_filename, $output_filename);

if ($generated_filename instanceof Exception)
{
    echo "{$prog}:: FAILED {$generated_filename->getMessage()}\n";
    exit(1);
}

echo "CREATED: {$generated_filename}\n";
?>
