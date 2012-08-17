<?php
include_once 'includes.php';

$lhs    = util::CommandScriptsFoldermandLineOptionValue($argv, 'lhs', null);
$rhs    = util::CommandScriptsFoldermandLineOptionValue($argv, 'rhs', null);
$output = util::CommandScriptsFoldermandLineOptionValue($argv, 'output', null);

$species_id = util::CommandScriptsFoldermandLineOptionValue($argv, 'species', null);


if (is_null($lhs) || is_null($rhs) || is_null($output))
{
    echo "usage {$argv[0]} --lhs=filename --rhs=filename --output=filename [--species=n  (prefix files with species data folder) ]\n";
    exit(1);
}

if (!is_null($species_id))
{
    $folder = SpeciesData::species_data_folder($species_id);
    $lhs    = "{$folder}{$lhs}";
    $rhs    = "{$folder}{$rhs}";
    $output = "{$folder}{$output}";
}

$result = spatial_util::subtract($lhs, $rhs, $output);
if ($result instanceof ErrorMessage)
{
    echo $result;
    exit(1);
}

echo "{$argv[0]}::difference file created [{$result}]\n";

?>
