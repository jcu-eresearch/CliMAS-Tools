<?php
include_once dirname(__FILE__).'/includes.php';

// bail if not at comand line
if (php_sapi_name() != "cli") return;

// ==================================================================
// SETUP of constants etc
//

// places:

// where to find models:
//    at path: / $model_root / $clazz_list[class] / models / [Species_name]
$model_root = "/home/TDH/data/SDM/";

$clazz_list = array(
    'AVES' => 'birds',
    'MAMMALIA' => "mammals",
    'REPTILIA' => "reptiles",
    'AMPHIBIA' => "amphibians"
);

// where to find json info for species
//    at path: $json_root / [Species_name]
$json_root = "/home/TDH/Gilbert/source/ALA_JSON/";


print_r($clazz_list);

// ==================================================================
// READ FLAGS from command line
//
$execute = false;

$action = array_util::Value($argv, 1);
if (is_null($action)) {
    $action = 'HELP';
}

if ($action == 'HELP') {
    ErrorMessage::Marker("setupData.php Help");
    ErrorMessage::Marker("------------------");
    ErrorMessage::Marker("Run 'php {$argv[0]} HELP' to get this help message.");
    ErrorMessage::Marker("Run 'php {$argv[0]} DRYRUN' to do a dry run test without actually touching any files.");
    ErrorMessage::Marker("Run 'php {$argv[0]} EXECUTE' to actually do the job.");
    return;

} else if ($action == 'DRYRUN') {
    ErrorMessage::Marker("####### DRY RUN ONLY... no files will be changed #######");
    ErrorMessage::Marker("Please run as 'php {$argv[0]} EXECUTE' to actually do the job.");

} else if ($action == 'EXECUTE') {
    ErrorMessage::Marker("####### EXECUTING... we're through the looking glass here, people #######");
    $execute = true;
}

// so now $execute is true if they want to actually do stuff.

// TODO: print a summary of the constants/paths being used so user can confirm them.


// ==================================================================
// FIND SPECIES that have been modelled
//

// here's the big list of all species modelled.
$species_list = array();

foreach ($clazz_list as $clazz_latin => $clazz_english) {

    ErrorMessage::Marker("Reading {$clazz_english} modelled species..");

    // get list of species-model-directories that exist for this class
    $spp_in_class = dirList($model_root . $clazz_english . '/models/');

    // complain if there weren't any models there.
    if (count($spp_in_class) < 1) {
        ErrorMessage::Marker("### No {$clazz_english} models found.  That seems odd.");
    }

    // go through the species we found
    foreach ($spp_in_class as $species_name) {
        $sp_data_dir = $model_root . $clazz_english . '/models/' . $species_name;
        $species_info = array();
        $species_info['data_dir'] = $sp_data_dir;
        $species_list[$species_name] = $species_info;
        ErrorMessage::Progress();
    }
    ErrorMessage::EndProgress();

    ErrorMessage::Marker(" ..done reading {$clazz_english}.");
}

// now, $species_list looks like this:
//     [Species_name1] => Array( [data_dir] => "../birds/models/Species_name1" ),
//     [Species_name2] => Array( [data_dir] => "../reptiles/models/Species_name2" ),

// ==================================================================
// FIND TAXA INFO for species, going to ALA when necessary
//

foreach ($species_list as $species_name => $species_data) {
    ErrorMessage::Marker("Filling in species taxonomic info..");

    $species_data['name'] = $species_name;

    ErrorMessage::Marker(" .. done filling in species info.");
}



print_r($species_list);



// ------------------------------------------------------------------
// dirList returns a list (array of strings) of file/dir names at the path specified.
function dirList($path) {
    if (!file::reallyExists($path)) return array(); // bail if no data

    $dircontents = file::folder_folders($path, null, true);
    return array_keys($dircontents);
}
// ------------------------------------------------------------------
// ------------------------------------------------------------------
// ------------------------------------------------------------------
// ------------------------------------------------------------------


