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

// somewhere to log errors to
$error_logfile = "/home/TDH/Gilbert/setup_data_errors.log";

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
        $species_info['name'] = $species_name;
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

ErrorMessage::Marker("Filling in species taxonomic info..");
foreach ($species_list as $species_name => $species_data) {
    $species_list[$species_name] = injectSpeciesTaxaInfo($species_data, $json_root, $error_logfile);
}
ErrorMessage::Marker(" .. done filling in species info.");



print_r($species_list);

// ------------------------------------------------------------------
// dirList returns a list (array of strings) of file/dir names at the path specified.
function dirList($path) {
    if (!file::reallyExists($path)) return array(); // bail if no data

    $dircontents = file::folder_folders($path, null, true);
    return array_keys($dircontents);
}
// ------------------------------------------------------------------
// make a dir, if we are in execute mode
function safemkdir($dir) {
    global $execute;

    if ($execute) {
        file::mkdir_safe($dir);
    } else {
        ErrorMessage::Marker("(DRYRUN) not making directory " . $dir);
    }
}
// ------------------------------------------------------------------
// populate a file from a url, if we don't already have the file.
// returns false if the file doesn't exist and can't be fetched, otherwise returns true.
function fetchIfRequired($filename, $url) {
    global $execute;
    global $error_logfile;

    if (!file_exists($filename)) {
        ErrorMessage::Marker("Fetching data from {$url} ..");
        file_put_contents( $filename, file_get_contents($url) );
    }

    if (!file_exists($filename)) {
        ErrorMessage::Marker("### Error getting data from ALA at URL " . $url);
        file_put_contents($error_logfile,"ERROR GETTING ALA DATA FROM URL " . $url, 0, FILE_APPEND);
        return false;

    } else {
        return true;
    }
}
// ------------------------------------------------------------------
// get taxonomic info about a species and leave it in a dir in JSON form.
// Fetches new JSON info from ALA if necessary.
// Takes an array $species_info that must include: [name] => 'Species_name'.
// Returns the array with additonal fields added.
function injectSpeciesTaxaInfo($species_info, $json_dir, $errlog) {

    global $execute;

    $species_name = str_replace("_", " ", $species_info['name']);
    $sp_json_dir = $json_dir . '/' . $species_name;

    safemkdir($sp_json_dir);

    try {
        // fill out search_result.json
        $file = $sp_json_dir . "/search_result.json";
        $url = 'http://bie.ala.org.au/ws/search.json?q=' . urlencode($species_name);
        if (fetchIfRequired($file, $url)) {
            ErrorMessage::Progress();
        } else {
            ErrorMessage::EndProgress();
            ErrorMessage::Marker("Couldn't get identifying data for {$species_name}.");
        }

        $data = json_decode(file_get_contents($file));
        $guid = $data->searchResults->results[0]->guid;

        $result0 = get_object_vars($data->searchResults->results[0]);

        // now get the guid out and re-query using that, to get more info about the species

        if (!array_key_exists('parentGuid', $result0)) return $species_info;

        $file = $sp_json_dir . "/species_data_search_results.json";
        $url = "http://bie.ala.org.au/ws/species/{$guid}.json";
        if (fetchIfRequired($file, $url)) {
            ErrorMessage::Progress();
        } else {
            ErrorMessage::EndProgress();
            ErrorMessage::Marker("Couldn't get taxonomic data for {$species_name}.");
        }

        $species_data = json_decode(file_get_contents($file));

        $f = $species_data->classification;

        $species_info['parent_guid']  = $result0['parentGuid'];
        $species_info['guid']         = $f->guid;
        $species_info['kingdom']      = $f->kingdom;
        $species_info['kingdom_guid'] = $f->kingdomGuid;
        $species_info['phylum']       = $f->phylum;
        $species_info['phylum_guid']  = $f->phylumGuid;
        $species_info['clazz']        = $f->clazz;
        $species_info['clazz_guid']   = $f->clazzGuid;
        $species_info['orderz']       = $f->order;
        $species_info['orderz_guid']  = $f->orderGuid;
        $species_info['family']       = $f->family;
        $species_info['family_guid']  = $f->familyGuid;
        $species_info['genus']        = $f->genus;
        $species_info['genus_guid']   = $f->genusGuid;
        $species_info['species']      = $f->species;
        $species_info['species_guid'] = $f->speciesGuid;
        $species_info['url_search']         = 'http://bie.ala.org.au/ws/search.json?q='.urlencode($species_name);
        $species_info['url_classification'] = "http://bie.ala.org.au/ws/species/{$guid}.json";
        $species_info['url_species_data']   = "http://bie.ala.org.au/ws/species/{$guid}.json";

        $commonNames = $species_data->commonNames;

        $names = array();
        foreach ($commonNames as $commonNameRow)
        {
            $single_common_name = trim($commonNameRow->nameString);
            $names[$single_common_name] = $single_common_name;
        }

        $species_info['common_names'] = $names;

        file_put_contents($sp_json_dir . "/data_array.txt", print_r($species_info,true));

        return $species_info;

    } catch (Exception $exc) {
        ErrorMessage::Marker("Can't get data for {$species_name} " .$exc->getMessage());
    }

    return null;
}
// ------------------------------------------------------------------


