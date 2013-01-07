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

// where to put all the species info
//    at path: $info_root / species / [Species_name]
//        and: $info_root / ByFamily / [FAMILY} / [Species_name]
//        etc etc etc
$data_root = "/home/TDH/data/Gilbert/source/";
// where that data is accessible by http / web
$http_data_root = "https://eresearch.jcu.edu.au/tdh/datasets/Gilbert/source/";

// where to find json info for species
//    at path: $json_root / [Species_name]
$json_root = "/home/TDH/data/Gilbert/ALA_JSON/";

// file to read bad common names from
$name_exclusion_file = $data_root . "exclude_names.txt";

// somewhere to log errors to
$error_logfile = "/home/TDH/data/Gilbert/setup_data_errors.log";

// ==================================================================
// READ FLAGS from command line
//
$execute = false;
$testing = false;

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

} else if ($action == 'TEST') {
    ErrorMessage::Marker("####### TEST EXECUTING... don't use this unless you're a developer #######");
    $execute = true;
    $testing = true;

    // short version for testing
    $clazz_list = array(
        'AVES' => 'birds'
    );

    // special testing data_root
    $data_root =  preg_replace('/\/$/', '_test/', $data_root);
}

// so now $execute is true if they want to actually do stuff.

// TODO: print a summary of the constants/paths being used so user can confirm them.


// ==================================================================
// FIND SPECIES that have been modelled
//

// here's the big list of all species modelled.
$species_list = array();

ErrorMessage::Marker("Reading modelled species..");

foreach ($clazz_list as $clazz_latin => $clazz_english) {

    ErrorMessage::Progress("({$clazz_english})");

    // get list of species-model-directories that exist for this class
    $spp_in_class = dir_list($model_root . $clazz_english . '/models/');

    // complain if there weren't any models there.
    if (count($spp_in_class) < 1) {
        ErrorMessage::EndProgress();
        ErrorMessage::Marker("### No {$clazz_english} found.  That seems odd.");
    }

    // go through the species we found
    foreach ($spp_in_class as $species_name) {

        $sp_data_dir = $model_root . $clazz_english . '/models/' . $species_name;

        // maybe there's no ASCII dir, because this species couldn't be modelled.
        // in that case, just don't add this species to the list.
        if (!is_dir($sp_data_dir . '/output/ascii')) continue;

        $species_info = array();
        $species_info['data_dir'] = $sp_data_dir;
        $species_info['name'] = $species_name;
        $species_list[$species_name] = $species_info;
        ErrorMessage::Progress();
    }
}

ErrorMessage::EndProgress();
ErrorMessage::Marker(" ..done reading species.");

// now, $species_list looks like this:
//     [Species_name1] => Array( [data_dir] => "../birds/models/Species_name1" ),
//     [Species_name2] => Array( [data_dir] => "../reptiles/models/Species_name2" ),

// ==================================================================
// FIND TAXA INFO for species, going to ALA when necessary
//

if ($testing) {
    // if we're testing, just do a few species
    $species_list = array_splice($species_list, 0, 5);
}

ErrorMessage::Marker("Filling in species taxonomic info..");
$last_clazz = '';
foreach ($species_list as $species_name => $species_data) {
    ErrorMessage::Progress();
    $new_data = injectSpeciesTaxaInfo($species_data, $json_root, $error_logfile);
    if ($new_data === false) {
        unset($species_list[$species_name]);
    } else {
        $species_list[$species_name] = $new_data;
    }

    if ($new_data['clazz'] != $last_clazz) {
        ErrorMessage::Progress('(' . $new_data['clazz'] . ')');
        $last_clazz = $new_data['clazz'];
    }
}
ErrorMessage::EndProgress();
ErrorMessage::Marker(" .. done filling in species info.");

// ==================================================================
// symlink ALL the places!
//
ErrorMessage::Marker("Linking..");
$last_clazz = '';
foreach ($species_list as $species_name => $species_data) {
    ErrorMessage::Progress();

    if ($species_data['clazz'] != $last_clazz) {
        ErrorMessage::Progress('(' . $species_data['clazz'] . ')');
        $last_clazz = $species_data['clazz'];
    }

    // first make a home base dir at .../species/{Species_name}/

    // homebase path relative to $data_root
    $rel_homebase = 'species/' . $species_data['name'];
    // full homebase path
    $homebase = $data_root . $rel_homebase;
    safemkdir($homebase);

    // symlink data into the home base dir

    // the occurrence file
    ln($homebase . '/occur.csv', $species_data['data_dir'] . '/occur.csv');

    // the maxent output (this has the threshold value in it)
    ln($homebase . '/maxentResults.csv', $species_data['data_dir'] . '/output/maxentResults.csv');

    // the entire original ascii dir of gz's into our new outputs dir
    ln($homebase . '/output', $species_data['data_dir'] . '/output/ascii');
    // ErrorMessage::Progress(':');

    // now there's a home base.

    // discover species id from the occur.csv in the homebase.
    $species_id = exec("head -n2 '{$homebase}/occur.csv' | tail -n1 | cut -d, -s -f1");
    $species_id = trim($species_id, '"');
    $species_id = preg_replace('/\([^\)]+\)/', '', $species_id);
    $species_data['id'] = $species_id;
    $species_list[$species_name] = $species_data;

    // link /species/{speciesid} to homebase
    // ln($data_root . 'species/' . $species_id, $data_root . 'species/' . $species_data['name']);
    ln($data_root . 'species/' . $species_id, '../' . $species_data['name']);

    // link /ByClazz/{classname}/ByID/{id} and .../ByName/{sp} back to homebase
    $clazzpath = $data_root . 'ByClazz/' . $species_data['clazz'];
    safemkdir($clazzpath . '/ByID');
    safemkdir($clazzpath . '/ByName');
    ln("{$clazzpath}/ByID/{$species_data['id']}",     '../../../' . $rel_homebase);
    ln("{$clazzpath}/ByName/{$species_data['name']}", '../../../' . $rel_homebase);
    // ErrorMessage::Progress();

    // link /ByFamily/{classname}/ByID/{id} and .../ByName/{sp} back to homebase
    $familypath = $data_root . 'ByFamily/' . $species_data['family'];
    safemkdir($familypath . '/ByID');
    safemkdir($familypath . '/ByName');
    ln("{$familypath}/ByID/{$species_data['id']}",     '../../../' . $rel_homebase);
    ln("{$familypath}/ByName/{$species_data['name']}", '../../../' . $rel_homebase);
    // ErrorMessage::Progress();

    // link /ByGenus/{classname}/ByID/{id} and .../ByName/{sp} back to homebase
    $genuspath = $data_root . 'ByGenus/' . $species_data['genus'];
    safemkdir($genuspath . '/ByID');
    safemkdir($genuspath . '/ByName');
    ln("{$genuspath}/ByID/{$species_data['id']}",     '../../../' . $rel_homebase);
    ln("{$genuspath}/ByName/{$species_data['name']}", '../../../' . $rel_homebase);
    // ErrorMessage::Progress();

    // link /Taxa/{classname}/{familyname}/{genusname}/{sp} back to homebase
    $taxapath = $data_root . 'Taxa/' . $species_data['clazz'] . '/' . $species_data['family'] . '/' . $species_data['genus'];
    safemkdir($taxapath);
    ln("{$taxapath}/{$species_data['name']}", '../../../../' . $rel_homebase);
}

ErrorMessage::EndProgress();
ErrorMessage::Marker(" .. done linking.");


// ==================================================================
// write the biodiversity list files
//
ErrorMessage::Marker("Creating taxa species lists..");

// need to make all, class, family, and genus lists.

// Do the 'all' list first - - - - - - - - - - - - - - - - - -

ErrorMessage::Marker("All vertebrates");

$species_list_dir = $data_root . 'species/';
$species_web_dir = $http_data_root . 'species/';
$spp_list = array();
$spp_list[] = "Species Name,Species Data URL";
foreach (glob($species_list_dir . '*_*') as $spdir) {
    if (is_dir($spdir)) {
        ErrorMessage::Progress();
        $spname = basename($spdir);
        $nicename = str_replace('_', ' ', $spname);
        $spp_list[] = "{$nicename},{$species_web_dir}{$spname}/species_data_{$spname}.zip";
    }
}
write_file($data_root . 'all_vertebrates.csv', implode("\n", $spp_list));

// Do the other three groups next - - - - - - - - - - - - - - 

$grouplist = array();
$grouplist[] = 'Clazz';
$grouplist[] = 'Family';
$grouplist[] = 'Genus';

foreach ($grouplist as $grouptype) {

    ErrorMessage::EndProgress();
    ErrorMessage::Marker($grouptype);

    $meta_list_dir = $data_root . 'By' . $grouptype;
    $web_dir = $http_data_root . 'By' . $grouptype;

    foreach (glob($meta_list_dir . '/*') as $list_dir) {
        if (is_dir($list_dir)) {

            $groupname = basename($list_dir);

            $spp_list = array();
            $spp_list[] = "Species Name,Species Data URL";

            foreach (glob($list_dir . '/ByName/*_*') as $spdir) {
                if (is_dir($spdir)) {
                    ErrorMessage::Progress();
                    $spname = basename($spdir);
                    $nicename = str_replace('_', ' ', $spname);
                    $spp_list[] = "{$nicename},{$web_dir}/{$groupname}/ByName/{$spname}/species_data_{$spname}.zip";
                }
            }
            write_file($list_dir . "/all_{$groupname}.csv", implode("\n", $spp_list));
        }
    }

}

ErrorMessage::EndProgress();
ErrorMessage::Marker(" .. done listing taxa.");












// ==================================================================
// make the biodiversity downloadable files
//
ErrorMessage::Marker("Creating downloadable biodiversity zip files - be patient, this bit takes AGES..");

// make a list of arrays of all the ascii files available, indexed by its taxa name
// do this by finding via glob all the "*_(Taxa).asc.gz" files.
// this a terrible glob technique but I can't think of a quicker way to do it.
$taxalist = array();

foreach(glob('{'.$model_root.'*/richness/*_*.asc.gz,'.$model_root.'vertebrate_richness/*_*.asc.gz}',GLOB_BRACE) as $biodiv) {

    // now $biodiv is one of our many biodiversity maps.
    // identify the taxa this maps belongs to
    $filebits = explode('_', $biodiv);
    $taxaname = str_replace('.asc.gz', '', end($filebits));
    // use lower case for indexing into the file lists
    $taxaname_lc = strtolower($taxaname);

    // make sure that taxa exists in the list
    if (!array_key_exists($taxaname_lc, $taxalist)) {
        $taxalist[$taxaname_lc] = array();
    }
    // add this biodiv map to that taxa
    $taxalist[$taxaname_lc][] = $biodiv;
}
// now, $taxalist is an array with taxa name keys and values that are arrays of biodiv ascii grid files.

echo array_keys($taxalist);

foreach ($grouplist as $grouptype) {

    ErrorMessage::Progress($grouptype);

    $meta_list_dir = $data_root . 'By' . $grouptype;

    foreach (glob($meta_list_dir . '/*') as $taxa_dir) {
        if (is_dir($taxa_dir)) {

            $taxaname = basename($taxa_dir);
            // use lower case for indexing into the file lists
            $taxaname_lc = strtolower($taxaname);

            echo ("\ndoing taxa " . $taxaname);

            if (array_key_exists($taxaname_lc, $taxalist)) {
                $zip_dir = $taxa_dir . '/biodiversity/';
                safemkdir($zip_dir);

                foreach($taxalist[$taxaname_lc] as $biodiv) {
                    $zip_file = explode('.', basename($biodiv), 2);
                    $zip_file = $zip_dir . $zip_file[0] . ".zip";

                    echo "\nabout to zip [{$biodiv}] into {$zip_file}\n";

                    if (zip(array($biodiv), $zip_file)) {
                        echo " worked!\n"
                    } else {
                        echo " DIDN'T WORK!\n"
                    }
                }
            } else {
                echo "\n" . $taxaname . " not in list.";
            }

            ErrorMessage::Progress();
        }
    }
}

ErrorMessage::EndProgress();
ErrorMessage::Marker(" .. done zipping biodiversity downloads.");











// ==================================================================
// make the species_to_id.txt file
//
ErrorMessage::Marker("Creating species_to_id file with common names..");

// read in the exclude_names file
$excludes = array();
if (file_exists($name_exclusion_file)) {
    $excludes = explode( "\n", strtolower(file_get_contents($name_exclusion_file)) );
    ErrorMessage::Progress();
}
// now build the species_to_id file
$names = array("name,id");
foreach ($species_list as $species_name => $species_data) {
    ErrorMessage::Progress();

    $done_one = false;
    // make an entry for each acceptable common name
    foreach ($species_data['common_names'] as $candidate_name => $dummy) {
        $long_name = "{$candidate_name} ({$species_data['species']})";
        if (!in_array(strtolower($candidate_name), $excludes) && !in_array(strtolower($long_name), $excludes)) {
            $names[] = "\"{$long_name}\",\"{$species_data['id']}\"";
            $done_one = true;
        }
    }
    // did we end up with no names?
    if (!$done_one) {
        // no acceptable common names, so juse use the scientific name
        $names[] = "\"{$species_data['species']}\",\"{$species_data['id']}\"";
    }
}
// now we've got a big list of names.  write it out to the file.
write_file($data_root . 'species_to_id.txt', implode("\n", $names));
// and wrap up
ErrorMessage::EndProgress();
ErrorMessage::Marker(" .. written file.");

// ==================================================================
// make the suitability downloadable files
//
ErrorMessage::Marker("Creating downloadable species zip files - be patient, this bit takes AGES..");

foreach ($species_list as $species_name => $species_data) {

    ErrorMessage::Progress();

    // discover the species home base dir
    $homebase = $data_root . 'species/' . $species_data['name'];

    $zip_file_name = $homebase . '/species_data_' . $species_data['name'] . '.zip';

    // DON'T rebuild zipfile if it's already there. Comment this bit out if you want to rebuild all the zips.
    if (is_file($zip_file_name)) continue;

    // okay so if the line above is commented out, the zip file might exist.  so delete it.
    if (is_file($zip_file_name)) delete_file($file);

    // get a file list of everything in the homebase dir, plus the asciigrids in {homebase}/output
    // the file list is an associative array of realpath => path_for_zip, for example:
    // '/user/TDH/CliMAS/species/Ukrainian_Ironbelly/ascii/current.asc.gz' => 'Ukranian_Ironbelly/grids/current.asc.gz'
    // ..would get the file in the asci subdir, and add it to the zip into a Urkanian_Ironbelly/grids subdir.
    $files = array();

    foreach (glob($homebase .'/*') as $candidate_file) {
        // just add files, not directories
        if (is_file($candidate_file)) {
            $in_zip_name = $species_data['name'] . '/' . pathinfo($candidate_file, PATHINFO_BASENAME);
            $files[$candidate_file] = $in_zip_name;
        }
    }

    foreach (glob($homebase .'/output/*') as $candidate_file) {
        // just add files, not directories
        if (is_file($candidate_file)) {
            $in_zip_name = $species_data['name'] . '/asciigrids/' . pathinfo($candidate_file, PATHINFO_BASENAME);
            $files[$candidate_file] = $in_zip_name;
        }
    }

    zip($files, $zip_file_name);

}
ErrorMessage::EndProgress();
ErrorMessage::Marker(" .. created downloadable files.");

// ==================================================================
// all done
//
if ($testing) {
    print_r(reset($species_list)); // reset returns the first array element
}

// ------------------------------------------------------------------
// ------------------------------------------------------------------
// helper functions
// ------------------------------------------------------------------
// ------------------------------------------------------------------
// cleans a string down to a-z, A-Z, 0-9, space and underscore.
// throws away anything that's in brackets.
function clean($string) {
    return preg_replace(
        '/[^a-zA-Z0-9 _]+/',
        '_',
        preg_replace('/\([^\)]+\) /', '', $string)
    );
}
// ------------------------------------------------------------------
// writes content to a file.
function write_file($file, $content) {
    global $execute;

    if ($execute) {
        return file_put_contents($file, $content);
    } else {
        ErrorMessage::Marker("(DRYRUN) not writing out file " . $file);
        return true;
    }
}
// ------------------------------------------------------------------
// make an archive at $archive containing the filenames in the $files array.
function zip($files, $archive) {

    global $execute;
    global $error_logfile;

    if (!$execute) {
        ErrorMessage::Marker("DRYRUN: Not creating archive '{$archive}' with " . count($files) . " files." );
        return true;
    }

    if (is_array($files)) {

        //create the archive
        $zip = new ZipArchive();
        if($zip->open($archive, ZIPARCHIVE::OVERWRITE) !== true) {
            echo "couln't open zip file at {$archive}";
            return false;
        }

        //add the files
        foreach($files as $realpath => $zippath) {
            $zip->addFile($realpath,$zippath);
        }
        //debug
        //echo 'The zip archive contains ',$zip->numFiles,' files with a status of ',$zip->status;

        //close the zip -- done!
        $zip->close();

        //check to make sure the file exists
        return file_exists($archive);

    } else {
        // $files isn't an array.. bail
        ErrorMessage::Marker("### couldn't create archive '{$archive}', file list provided wasn't a list.");
        save_to_file($error_logfile,"couldn't create archive '{$archive}', file list provided wasn't a list.", 0, FILE_APPEND);
    }
}
// ------------------------------------------------------------------
// make a symlink called $link that points to $real.
function ln($link, $real) {
    global $execute;
    global $error_logfile;

    if (!$execute) return true;
    if ( file_exists($link) ) return true;

    // try linking.
    // identify dirs so we can make relative links
    $olddir = getcwd();
    $newdir = dirname($link);
    // change to the destionation dir to make the new link
    chdir($newdir);
    $success = symlink($real, $link);
    chdir($olddir);

    if ($success) {
        return true;
    } else {
        ErrorMessage::EndProgress();
        ErrorMessage::Marker("### symlinking {$link} -> {$real} failed.");
        save_to_file($error_logfile,"symlinking {$link} -> {$real} failed", 0, FILE_APPEND);
        return false;
    }
}
// ------------------------------------------------------------------
// delete a file
function delete_file($file) {
    global $execute;

    if ($execute) {
        file::Delete($file);
    } else {
        ErrorMessage::Marker("(DRYRUN) not delete file " . $file);
    }
}
// ------------------------------------------------------------------
// dirList returns a list (array of strings) of file/dir names at the path specified.
function dir_list($path) {
    if (!file::reallyExists($path)) return array(); // bail if no data

    $dircontents = file::folder_folders($path, null, true);
    return array_keys($dircontents);
}
// ------------------------------------------------------------------
// make a dir, if we are in execute mode
function save_to_file($file, $content) {
    global $execute;

    if ($execute) {
        safemkdir( pathinfo($file, PATHINFO_DIRNAME) );
        file_put_contents( $file, $content );
    } else {
        ErrorMessage::Marker("(DRYRUN) not saving file " . $file);
    }
}
// ------------------------------------------------------------------
// make a dir, if we are in execute mode
function safemkdir($dir) {
    global $execute;

    if (is_dir($dir)) return;

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
        // try getting the url a few times...
        $attempts = 0;
        $content = false;
        while ($attempts < 5 && $content === false) {
            $delay = $attempts * $attempts * $attempts;
            if ($delay > 1) {
                ErrorMessage::Progress("({$delay}s wait)");
            }
            sleep($delay);
            $content = @file_get_contents($url);
            $attempts++;
        }

        if ($content === false) {
            ErrorMessage::EndProgress();            
            ErrorMessage::Marker("Having trouble getting data from ALA at URL " . $url);
            ErrorMessage::Marker("Here's what happens when I try:");
            $content = file_get_contents($url);
        }

        if ($content) {
            save_to_file( $filename, $content );
        } else {
            ErrorMessage::Marker("### Error getting data from ALA at URL " . $url);
            save_to_file($error_logfile,"ERROR GETTING ALA DATA FROM URL " . $url, 0, FILE_APPEND);
            return false;
        }
    }

    if (!file_exists($filename) && $execute) {
        ErrorMessage::EndProgress();
        ErrorMessage::Marker("### File {$filename} not updated with data from URL " . $url);
        save_to_file($error_logfile,"ERROR SAVING ALA DATA INTO FILE " . $filename, 0, FILE_APPEND);
        return false;
    } else if (!file_exists($filename) && !$execute) {
        ErrorMessage::EndProgress();
        ErrorMessage::Marker("DRYRUN: No previously saved data for this species -- skipping.");
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
    $sp_json_dir = $json_dir . $species_info['name'] . '/';

    safemkdir($sp_json_dir);
    $backone = "\033[1D";
    ErrorMessage::Progress('|');

    try {
        // fill out search_result.json
        $file = $sp_json_dir . "search_result.json";
        $url = 'http://bie.ala.org.au/ws/search.json?q=' . urlencode($species_name);
        if (fetchIfRequired($file, $url)) {
            ErrorMessage::Progress($backone . "!");
        } else {
            ErrorMessage::EndProgress();
            ErrorMessage::Marker("Couldn't get identifying data for {$species_name}.");
            return false;
        }

        $data = json_decode(file_get_contents($file));
        $guid = $data->searchResults->results[0]->guid;

        $result0 = get_object_vars($data->searchResults->results[0]);

        // now get the guid out and re-query using that, to get more info about the species

        if (!array_key_exists('parentGuid', $result0)) return $species_info;

        $file = $sp_json_dir . "species_data_search_results.json";
        $url = "http://bie.ala.org.au/ws/species/{$guid}.json";
        if (fetchIfRequired($file, $url)) {
            ErrorMessage::Progress($backone . ":");
        } else {
            ErrorMessage::EndProgress();
            ErrorMessage::Marker("Couldn't get taxonomic data for {$species_name}.");
        }

        $species_data = json_decode(file_get_contents($file));

        $f = $species_data->classification;

        $species_info['parent_guid']  =        $result0['parentGuid'];
        $species_info['guid']         =        $f->guid;
        $species_info['kingdom']      = clean( $f->kingdom );
        $species_info['kingdom_guid'] =        $f->kingdomGuid;
        $species_info['phylum']       = clean( $f->phylum );
        $species_info['phylum_guid']  =        $f->phylumGuid;
        $species_info['clazz']        = clean( $f->clazz );
        $species_info['clazz_guid']   =        $f->clazzGuid;
        $species_info['orderz']       = clean( $f->order );
        $species_info['orderz_guid']  =        $f->orderGuid;
        $species_info['family']       = clean( $f->family );
        $species_info['family_guid']  =        $f->familyGuid;
        $species_info['genus']        = clean( $f->genus );
        $species_info['genus_guid']   =        $f->genusGuid;
        $species_info['species']      = clean( $f->species );
        $species_info['species_guid'] =        $f->speciesGuid;
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

        file_put_contents($sp_json_dir . "data_array.txt", print_r($species_info,true));
        ErrorMessage::Progress($backone);

        return $species_info;

    } catch (Exception $exc) {
        ErrorMessage::Marker("Can't get data for {$species_name} " .$exc->getMessage());
    }

    return null;
}
// ------------------------------------------------------------------


