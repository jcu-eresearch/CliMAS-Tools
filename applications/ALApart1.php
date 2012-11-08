<?php
include_once dirname(__FILE__).'/includes.php';
if (php_sapi_name() != "cli") return;

$execute = false;

$execute_flag = array_util::Value($argv, 1);
if (is_null($execute_flag)) $execute_flag = 'NO_EXECUTE';


if ($execute_flag !== 'EXECUTE')
{
    ErrorMessage::Marker("####### DRY RUN ONLY .... no files will be changed #######");
    ErrorMessage::Marker("Please run as  'php {$argv[0]} EXECUTE'  to actually execute and do something ");
    $execute = false;
}
else
{
    ErrorMessage::Marker("####### EXECUTING DATA BUILD #######");
    $execute = true;
}

ErrorMessage::Marker("GET ALA DATA, and combine with managed Species Data to create Taxa a trees ada data lookup files");

$JSON_KEY = 'JSON';
$clazz_translation = array();
$clazz_translation['AMPHIBIA'] = 'amphibians';
$clazz_translation['MAMMALIA'] = 'mammals';
$clazz_translation['REPTILIA'] = 'reptiles';
$clazz_translation['AVES'] = 'birds';


$real_data_folder = "/home/TDH/data/SDM/";   // folder with real data
ErrorMessage::Marker("real_data_folder = [{$real_data_folder}]" );

$actionlist_filename = "actionlist.csv";
ErrorMessage::Marker("actionlist_filename = [{$actionlist_filename}]" );


ErrorMessage::Marker("Get modelled_list");
$modelled = modelled_list($real_data_folder,$actionlist_filename);

ErrorMessage::Marker("Current modelled_list");
ErrorMessage::Marker($modelled);




$AP02_data_folder  = configuration::Maxent_Species_Data_folder();
ErrorMessage::Marker("AP02_data_folder  = [{$AP02_data_folder}]" );

$sdf = configuration::SourceDataFolder();
ErrorMessage::Marker("(SourceDataFolder) sdf = [{$sdf}]" );

$taxa_data_folder = "{$sdf}Taxa/";
ErrorMessage::Marker("$taxa_data_folder = [{$taxa_data_folder}]" );

$error_list_filename = "{$sdf}errors.txt";
ErrorMessage::Marker("$error_list_filename = [{$error_list_filename}]" );




ErrorMessage::Marker("remove_data_lookup_files" );
if ($execute) remove_data_lookup_files($sdf);


ErrorMessage::Marker("create_taxa_folders" );
if ($execute) create_taxa_folders($AP02_data_folder,$sdf);



// arrays to store unique values - to be saved later for lookup data
$clazz        = array();
$family       = array();
$genus        = array();
$species      = array();
$common_names = array();

$species_to_id = array(); // - hold array where key = "Common Name (Species name)" - so we have somethinf to load into species lookup

$count = 1;
$species_list = species_list_from_folders($real_data_folder);

ErrorMessage::Marker("Current species list");
ErrorMessage::Marker($species_list);


foreach ($species_list as $species_folder_name)
{

    ErrorMessage::Marker("Process ... $species_folder_name  {$count} / " . count($species_list) );

    if (!array_key_exists($species_folder_name, $modelled))
    {
        ErrorMessage::Marker("NOT MODELLED - $species_folder_name");
        file_put_contents($error_list_filename,"NOT MODELLED - $species_folder_name",FILE_APPEND);
        continue;
    }

    // if ($count > 2) continue;


    // check to see if we have '$sdf/ALA_JSON/$species_folder_name.json'
    $d = ALASpeciesTaxa($species_folder_name,$sdf,$error_list_filename,$execute);

    if (is_null($d))
    {
        ErrorMessage::Marker("### ERROR:: No ALA data found for - $species_folder_name");
        file_put_contents($error_list_filename,"No ALA data found for - $species_folder_name", 0, FILE_APPEND);
        continue;
    }


    $EXTRA_CHARS = '@##$%^&*()_+={}[]\|:";\'\\<>,.?/`~';

    ErrorMessage::Marker("Clean data from ALA  ... convert [{$EXTRA_CHARS}]   to EMPTY_STRING");

    $d['clazz']   = util::CleanStr($d['clazz']  ,NULL,$EXTRA_CHARS,"");
    $d['family']  = util::CleanStr($d['family'] ,NULL,$EXTRA_CHARS,"");
    $d['genus']   = util::CleanStr($d['genus']  ,NULL,$EXTRA_CHARS,"");
    $d['species'] = util::CleanStr($d['species'],NULL,$EXTRA_CHARS,"");

    $d['common_names'] = array_util::Trim(array_util::CleanStrings($d['common_names'], $EXTRA_CHARS, true, ''));

    $single_species_folder = "{$taxa_data_folder}{$d['clazz']}/{$d['family']}/{$d['genus']}/{$species_folder_name}/";

    ErrorMessage::Marker("Create folder {$single_species_folder}");
    if ($execute) file::mkdir_safe("{$single_species_folder}");

    ErrorMessage::Marker("Create file [{$taxa_data_folder}{$d['clazz']}/clazz_guid.txt]");
    if ($execute) file_put_contents("{$taxa_data_folder}{$d['clazz']}/clazz_guid.txt", $d['clazz_guid']."\n");

    ErrorMessage::Marker("Create file [{$taxa_data_folder}{$d['clazz']}/{$d['family']}/family_guid.txt");
    if ($execute) file_put_contents("{$taxa_data_folder}{$d['clazz']}/{$d['family']}/family_guid.txt", $d['family_guid']."\n");

    ErrorMessage::Marker("Create file {$taxa_data_folder}{$d['clazz']}/{$d['family']}/{$d['genus']}/genus_guid.txt");
    if ($execute) file_put_contents("{$taxa_data_folder}{$d['clazz']}/{$d['family']}/{$d['genus']}/genus_guid.txt", $d['genus_guid']."\n");

    ErrorMessage::Marker("Create file {$taxa_data_folder}{$d['clazz']}/{$d['family']}/{$d['genus']}/{$species_folder_name}/species_guid.txt");
    if ($execute) file_put_contents("{$taxa_data_folder}{$d['clazz']}/{$d['family']}/{$d['genus']}/{$species_folder_name}/species_guid.txt", $d['species_guid']."\n");

    ErrorMessage::Marker("Create file {$taxa_data_folder}{$d['clazz']}/{$d['family']}/{$d['genus']}/{$species_folder_name}/scientific_name.txt");
    if ($execute) file_put_contents("{$taxa_data_folder}{$d['clazz']}/{$d['family']}/{$d['genus']}/{$species_folder_name}/scientific_name.txt", $d['species']."\n");

    ErrorMessage::Marker("Create file {$taxa_data_folder}{$d['clazz']}/{$d['family']}/{$d['genus']}/{$species_folder_name}/data.json");
    if ($execute) file_put_contents("{$taxa_data_folder}{$d['clazz']}/{$d['family']}/{$d['genus']}/{$species_folder_name}/data.json", $d[$JSON_KEY]."\n");


    // set original occur.csv here  - we want to read the first line of the occur.csv and get the "species_id" that was used
    $original_occur = "{$real_data_folder}{$clazz_translation[$d['clazz']]}/models/{$species_folder_name}/occur.csv";

    // create link to original occur inside Taxa Heirachy
    $ln = "ln -s '{$original_occur}' '{$taxa_data_folder}{$d['clazz']}/{$d['family']}/{$d['genus']}/{$species_folder_name}/occur.csv'";

    ErrorMessage::Marker("$ln");
    if ($execute)  exec($ln);


    $original_ascii_data_folder = "{$real_data_folder}{$clazz_translation[$d['clazz']]}/models/{$species_folder_name}/output/ascii/";

    // create link to original ascii / gz  foldet to inside Taxa Heirachy
    $ln = "ln -s '{$original_ascii_data_folder}' '{$taxa_data_folder}{$d['clazz']}/{$d['family']}/{$d['genus']}/{$species_folder_name}/output'";
    ErrorMessage::Marker("$ln");
    if ($execute)  exec($ln);


    // get original_species_id from the occur file - so we can then use it to create the id's fo AP02 data
    $original_species_id = exec("head -n2 '$original_occur' | tail -n1 | cut -d, -s -f1");
    ErrorMessage::Marker("$species_folder_name  ... original_species_id = [{$original_species_id}]");


    // create Folder in $AP02_data_folder as $original_species_id
    ErrorMessage::Marker("mkdir {$AP02_data_folder}{$original_species_id}");
    if ($execute)  file::mkdir_safe("{$AP02_data_folder}{$original_species_id}");



    ErrorMessage::Marker("chmod u+rwxs,g+rwxs,o+rx'{$AP02_data_folder}{$original_species_id}'");
    if ($execute)  exec("chmod u+rwxs,g+rwxs,o+rx'{$AP02_data_folder}{$original_species_id}'");



    ErrorMessage::Marker("mkdir {$AP02_data_folder}{$original_species_id}/output");
    if ($execute)  file::mkdir_safe("{$AP02_data_folder}{$original_species_id}/output");


    ErrorMessage::Marker("chmod u+rwxs,g+rwxs,o+rx '{$AP02_data_folder}{$original_species_id}/output'");
    if ($execute)  exec("chmod u+rwxs,g+rwxs,o+rx '{$AP02_data_folder}{$original_species_id}/output'");


    ErrorMessage::Marker("link {$original_occur} to {$original_species_id}/occur.csv");

    $ln = "ln -s '{$original_occur}' '{$AP02_data_folder}{$original_species_id}/occur.csv'";
    ErrorMessage::Marker("$ln");
    if ($execute)  exec($ln);



    // set original maxentResults.csv here
    $original_maxentResults = "{$real_data_folder}{$clazz_translation[$d['clazz']]}/models/{$species_folder_name}/output/maxentResults.csv";

    ErrorMessage::Marker("link {$original_maxentResults} to {$original_species_id}/output/maxentResults.csv");

    $ln = "ln -s '{$original_maxentResults}' '{$AP02_data_folder}/{$original_species_id}/output/maxentResults.csv'";
    ErrorMessage::Marker("$ln");
    if ($execute)  exec($ln);


    // species commmon names
    if (is_array($d['common_names']))
    {

        ErrorMessage::Marker("WRITE {$taxa_data_folder}{$d['clazz']}/{$d['family']}/{$d['genus']}/{$species_folder_name}/common_names.txt");
        if ($execute)  file_put_contents("{$taxa_data_folder}{$d['clazz']}/{$d['family']}/{$d['genus']}/{$species_folder_name}/common_names.txt", implode("\n",$d['common_names'])."\n");

        foreach ($d['common_names'] as $common_name)
        {
            $single_common_name = "{$common_name} ({$d['species']})";
            $common_names[$single_common_name] = $single_common_name;
            $species_to_id[$single_common_name] = "{$single_common_name},{$original_species_id}";  // possible same $original_species_id for multiple species  as this list is using command names and scientific names
        }

    }


    // build unique list of each grouping - to be saved at end
      $clazz[$d['clazz']  ] = $d['clazz'];
     $family[$d['family'] ] = $d['family'];
      $genus[$d['genus']  ] = $d['genus'];
    $species[$d['species']] = $d['species'];


    // create links from the various folder structures to the original data



    ErrorMessage::Marker("link compressed ascii's {$original_species_id}/output to {$original_ascii_data_folder}");

    $gz_files = file::folder_gz($original_ascii_data_folder, '/', true);

    foreach ($gz_files as $gz_basename => $gz_pathname)
    {
        $ln = "ln -s '{$gz_pathname}' '{$AP02_data_folder}{$original_species_id}/output/{$gz_basename}'";
        ErrorMessage::Marker("$ln");
        if ($execute)  exec($ln);
    }


    ErrorMessage::Marker("Create Data Links - Clazz {$d['clazz']} ");


    ErrorMessage::Marker("mkdir {$sdf}ByClazz/{$d['clazz']}");
    if ($execute)  file::mkdir_safe("{$sdf}ByClazz/{$d['clazz']}");

    ErrorMessage::Marker("mkdir {$sdf}ByClazz/{$d['clazz']}/ByID");
    if ($execute)  file::mkdir_safe("{$sdf}ByClazz/{$d['clazz']}/ByID");

    ErrorMessage::Marker("mkdir {$sdf}ByClazz/{$d['clazz']}/ByName");
    if ($execute)  file::mkdir_safe("{$sdf}ByClazz/{$d['clazz']}/ByName");

    $ln = "ln -s '{$AP02_data_folder}{$original_species_id}/' '{$sdf}ByClazz/{$d['clazz']}/ByID/{$original_species_id}'";
    ErrorMessage::Marker("$ln");
    if ($execute)  exec($ln);

    $ln = "ln -s '{$AP02_data_folder}{$original_species_id}/' '{$sdf}ByClazz/{$d['clazz']}/ByName/{$species_folder_name}'";
    ErrorMessage::Marker("$ln");
    if ($execute)  exec($ln);


    ErrorMessage::Marker("mkdir {$sdf}ByFamily/{$d['family']}");
    if ($execute)  file::mkdir_safe("{$sdf}ByFamily/{$d['family']}");

    ErrorMessage::Marker("mkdir {$sdf}ByFamily/{$d['family']}/ByID");
    if ($execute)  file::mkdir_safe("{$sdf}ByFamily/{$d['family']}/ByID");

    ErrorMessage::Marker("mkdir {$sdf}ByFamily/{$d['family']}/ByName");
    if ($execute)  file::mkdir_safe("{$sdf}ByFamily/{$d['family']}/ByName");

    $ln = "ln -s '{$AP02_data_folder}{$original_species_id}/' '{$sdf}ByFamily/{$d['family']}/ByID/{$original_species_id}'";
    ErrorMessage::Marker("$ln");
    if ($execute)  exec($ln);

    $ln = "ln -s '{$AP02_data_folder}{$original_species_id}/' '{$sdf}ByFamily/{$d['family']}/ByName/{$species_folder_name}'";
    ErrorMessage::Marker("$ln");
    if ($execute)  exec($ln);


    ErrorMessage::Marker("mkdir {$sdf}ByGenus/{$d['genus']}");
    if ($execute)  file::mkdir_safe("{$sdf}ByGenus/{$d['genus']}");

    ErrorMessage::Marker("mkdir {$sdf}ByGenus/{$d['genus']}/ByID");
    if ($execute)  file::mkdir_safe("{$sdf}ByGenus/{$d['genus']}/ByID");

    ErrorMessage::Marker("mkdir {$sdf}ByGenus/{$d['genus']}/ByName");
    if ($execute)  file::mkdir_safe("{$sdf}ByGenus/{$d['genus']}/ByName");


    $ln = "ln -s '{$AP02_data_folder}{$original_species_id}/' '{$sdf}ByGenus/{$d['genus']}/ByID/{$original_species_id}'";
    ErrorMessage::Marker("$ln");
    if ($execute)  exec($ln);

    $ln = "ln -s '{$AP02_data_folder}{$original_species_id}/' '{$sdf}ByGenus/{$d['genus']}/ByName/{$species_folder_name}'";
    ErrorMessage::Marker("$ln");
    if ($execute)  exec($ln);


    $count++;

}


// Save Lookup Data

ksort($clazz);
ErrorMessage::Marker("WRITE {$sdf}clazz_list.txt");
if ($execute)  file_put_contents("{$sdf}clazz_list.txt",    implode("\n",$clazz)."\n");

ksort($family);
ErrorMessage::Marker("WRITE {$sdf}family_list.txt");
if ($execute)  file_put_contents("{$sdf}family_list.txt",   implode("\n",$family)."\n");

ksort($genus);
ErrorMessage::Marker("WRITE {$sdf}genus_list.txt");
if ($execute)  file_put_contents("{$sdf}genus_list.txt",    implode("\n",$genus)."\n");

ksort($species);
ErrorMessage::Marker("WRITE {$sdf}species_list.txt");
if ($execute)  file_put_contents("{$sdf}species_list.txt",  implode("\n",$species)."\n");

ksort($common_names);
ErrorMessage::Marker("WRITE {$sdf}common_names.txt");
if ($execute)  file_put_contents("{$sdf}common_names.txt",  implode("\n",$common_names)."\n");

ksort($species_to_id);
ErrorMessage::Marker("WRITE {$sdf}species_to_id.txt");
if ($execute)  file_put_contents("{$sdf}species_to_id.txt", "name,id\n".implode("\n",$species_to_id)."\n");

//

ErrorMessage::Marker("will allow apache to add files to these folders, but data is symbolicly linked and wont be changed");

ErrorMessage::Marker("chmod -R u+rwxs,g+rwxs,+o+rwsx {$sdf}*");
if ($execute)  exec("chmod -R u+rwxs,g+rwxs,+o+rwsx {$sdf}*");


ErrorMessage::Marker("######## COMPLETED ######## ");


function remove_data_lookup_files($sdf)
{
    ErrorMessage::Marker("remove data lookup files");

    ErrorMessage::Marker("remove data lookup files [clazz_list.txt]");
    $clazz_list_filename = "{$sdf}clazz_list.txt";
    file::Delete($clazz_list_filename);

    ErrorMessage::Marker("remove data lookup files [family_list.txt]");
    $family_list_filename = "{$sdf}family_list.txt";
    file::Delete($family_list_filename);

    ErrorMessage::Marker("remove data lookup files [genus_list.txt]");
    $genus_list_filename = "{$sdf}genus_list.txt";
    file::Delete($genus_list_filename);

    ErrorMessage::Marker("remove data lookup files [species_list.txt]");
    $species_list_filename = "{$sdf}species_list.txt";
    file::Delete($species_list_filename);

    ErrorMessage::Marker("remove data lookup files [common_names.txt]");
    $common_list_filename = "{$sdf}common_names.txt";
    file::Delete($common_list_filename);

    ErrorMessage::Marker("remove data lookup files [species_to_id.txt]");
    $species_to_id_filename = "{$sdf}species_to_id.txt";
    file::Delete($species_to_id_filename);


}


function create_taxa_folders($AP02_data_folder,$sdf)
{

    ErrorMessage::Marker("create taxa folders in [{$AP02_data_folder}]");

    ErrorMessage::Marker("Remove all AP02 Species ID data / links  from [{$AP02_data_folder}]");
    exec("rm -r -f {$AP02_data_folder}/*");


    ErrorMessage::Marker("create Taxa folder - Heirachy  groupings");

    if (is_dir("{$sdf}Taxa/"))
        exec("rm -r -f '{$sdf}Taxa/'");     // clear it if it exist
    else
        file::mkdir_safe("{$sdf}Taxa/");    // create it if it does NOT exist




    ErrorMessage::Marker("create Taxa folder - Clazz - All species belonging to this Clazz  [{{$sdf}ByClazz}]");
    if (is_dir("{$sdf}ByClazz/"))
        exec("rm -f -r '{$sdf}ByClazz/'");
    else
        file::mkdir_safe("{$sdf}/ByClazz");



    ErrorMessage::Marker("create Taxa folder - Family - All species belonging to this Family [{{$sdf}ByFamily}]");
    if (is_dir("{$sdf}ByFamily/"))
        exec("rm -r -f '{$sdf}ByFamily/'");
    else
        file::mkdir_safe("{$sdf}/ByFamily/");



    ErrorMessage::Marker("create Taxa folder - Genus - All species belonging to this Genus [{{$sdf}ByGenus}]");
    if (is_dir("{$sdf}ByGenus/"))
        exec("rm -r -f '{$sdf}ByGenus/'");
    else
        file::mkdir_safe("{$sdf}/ByGenus");



}


function species_list_from_folders($real_data_folder)
{

    ErrorMessage::Marker("Getting species list from [{$real_data_folder}]");


    $species_list = array();

    ErrorMessage::Marker("Getting species amphibians list from [{$real_data_folder}amphibians/models/]");
    exec("ls -1 {$real_data_folder}amphibians/models/",$species_list );


    ErrorMessage::Marker("Getting species mammals list from [{$real_data_folder}mammals/models/]");
    exec("ls -1 {$real_data_folder}mammals/models/"   ,$species_list );


    ErrorMessage::Marker("Getting species reptiles list from [{$real_data_folder}reptiles/models/]");
    exec("ls -1 {$real_data_folder}reptiles/models/",  $species_list );


    ErrorMessage::Marker("Getting species birds list from [{$real_data_folder}birds/models/]");
    exec("ls -1 {$real_data_folder}birds/models/",  $species_list );

    return $species_list;

}


function modelled_list($real_data_folder,$actionlist_filename = "actionlist.csv")
{
    ErrorMessage::Marker("Read MODELLED list from [{$real_data_folder}{$actionlist_filename}]" );
    $modelled = array();
    exec(" cat {$real_data_folder}{$actionlist_filename} | grep -v 'not_modelled' | grep -v 'class' | less",$modelled);
    $modelled = array_flip(util::leftStrArray(array_util::Replace($modelled, '"', ''), ',', false));

    return $modelled;

}


function ALASpeciesTaxa($species_folder_name,$sdf,$error_list_filename,$execute)
{

    $species_name = str_replace("_", " ", $species_folder_name);

    file::mkdir_safe("{$sdf}ALA_JSON/$species_folder_name");

    try {

        ErrorMessage::Marker("Get data from ALA for [{$species_name}]");

        // check to see if we already have "{$sdf}$species_folder_name/search_result.json"

        if (!file_exists("{$sdf}ALA_JSON/$species_folder_name/search_result.json"))
        {

            ErrorMessage::Marker("No Clasification Search data stored - get from ALA  [{$species_name}]");

            file_put_contents(  "{$sdf}ALA_JSON/$species_folder_name/search_result.json",
                                file_get_contents('http://bie.ala.org.au/ws/search.json?q='.urlencode($species_name))
                             );
        }

        if (!file_exists("{$sdf}ALA_JSON/$species_folder_name/search_result.json"))
        {

            ErrorMessage::Marker("ERROR GETTING ALA DATA - $species_folder_name");
            file_put_contents($error_list_filename,"ERROR GETTING ALA DATA - $species_folder_name", 0, FILE_APPEND);
            continue;


        }


        $data = json_decode(file_get_contents("{$sdf}ALA_JSON/$species_folder_name/search_result.json"));

        $guid = $data->searchResults->results[0]->guid;

        $result0 = get_object_vars($data->searchResults->results[0]);

        if (!array_key_exists('parentGuid', $result0)) return null;


        if (!file_exists("{$sdf}ALA_JSON/$species_folder_name/species_data_search_results.json"))
        {
            ErrorMessage::Marker("No Species Data stored - get from ALA  [{$species_name}]");

            file_put_contents(  "{$sdf}ALA_JSON/$species_folder_name/species_data_search_results.json",
                                file_get_contents("http://bie.ala.org.au/ws/species/{$guid}.json")
                             );
        }

        $species_data = json_decode(file_get_contents("{$sdf}ALA_JSON/$species_folder_name/species_data_search_results.json"));

        $f = $species_data->classification;

        $d = array();
        $d['parent_guid']  = $result0['parentGuid'];;
        $d['guid']         = $f->guid;
        $d['kingdom']      = $f->kingdom;
        $d['kingdom_guid'] = $f->kingdomGuid;
        $d['phylum']       = $f->phylum;
        $d['phylum_guid']  = $f->phylumGuid;
        $d['clazz']        = $f->clazz;
        $d['clazz_guid']   = $f->clazzGuid;
        $d['orderz']       = $f->order;
        $d['orderz_guid']  = $f->orderGuid;
        $d['family']       = $f->family;
        $d['family_guid']  = $f->familyGuid;
        $d['genus']        = $f->genus;
        $d['genus_guid']   = $f->genusGuid;
        $d['species']      = $f->species;
        $d['species_guid'] = $f->speciesGuid;
        $d['url_search']         = 'http://bie.ala.org.au/ws/search.json?q='.urlencode($species_name);
        $d['url_classification'] = "http://bie.ala.org.au/ws/species/{$guid}.json";
        $d['url_species_data']   = "http://bie.ala.org.au/ws/species/{$guid}.json";


        if (!file_exists("{$sdf}ALA_JSON/$species_folder_name/species_data.json"))
        {
            ErrorMessage::Marker("No Species data stored - get from ALA  [{$species_name}]");

            file_put_contents(  "{$sdf}ALA_JSON/$species_folder_name/species_data.json",
                                file_get_contents($d['url_species_data'])
                             );
        }


        $data = json_decode(file_get_contents("{$sdf}ALA_JSON/$species_folder_name/species_data.json"));

        $commonNames = $data->commonNames;

        $names = array();
        foreach ($commonNames as $commonNameRow)
        {
            $single_common_name = trim($commonNameRow->nameString);
            $names[$single_common_name] = $single_common_name;
        }

        $d['common_names'] = $names;

        file_put_contents(  "{$sdf}ALA_JSON/$species_folder_name/data_array.txt", print_r($d,true));

        return $d;

    } catch (Exception $exc) {
        ErrorMessage::Marker("Can't get data for {$species_name} " .$exc->getMessage());
    }

    return null;

}







?>
