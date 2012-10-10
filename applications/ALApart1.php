<?php
include_once dirname(__FILE__).'/includes.php';

$JSON_KEY = 'JSON';
$clazz_translation = array();
$clazz_translation['AMPHIBIA'] = 'amphibians';
$clazz_translation['MAMMALIA'] = 'mammals';
$clazz_translation['REPTILIA'] = 'reptiles';

$real_data_folder = "/scratch/jc148322/AP02/";   // folder with real data 


ErrorMessage::Marker("ALA Species and Taxa data");

$AP02_data_folder  = configuration::Maxent_Species_Data_folder();

$sdf = configuration::SourceDataFolder();

$error_list_filename = "{$sdf}errors.txt";

remove_data_lookup_files();

create_taxa_folders();

$modelled = modelled_list();

$taxa_data_folder = "{$sdf}Taxa/";


// arrays to store unique values - to be saved later for lookup data
$clazz        = array();
$family       = array();
$genus        = array();
$species      = array();
$common_names = array();

$species_to_id = array(); // - hold array where key = "Common Name (Species name)" - so we have somethinf to load into species lookup 


$count = 1;
$species_list = species_list_from_folders();
foreach ($species_list as $species_folder_name) 
{
    
    
    if (!array_key_exists($species_folder_name, $modelled))
    {
        ErrorMessage::Marker("NOT MODELLED - $species_folder_name");
        continue;
    }

    // if ($count > 2) continue;
    
    ErrorMessage::Marker("Process ... $species_folder_name  {$count} / " . count($species_list) );    
    
    // check to see if we have '$sdf/ALA_JSON/$species_folder_name.json'
    $d = ALASpeciesTaxa($species_folder_name);
    
    
    if (is_null($d)) 
    {
        ErrorMessage::Marker("\n### ERROR ALA data for $species_folder_name NOT FOUND\n");
        file_put_contents($error_list_filename,$species_folder_name, 0, FILE_APPEND);
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
    file::mkdir_safe("{$single_species_folder}");
    
    file_put_contents("{$taxa_data_folder}{$d['clazz']}/clazz_guid.txt", $d['clazz_guid']."\n");

    file_put_contents("{$taxa_data_folder}{$d['clazz']}/{$d['family']}/family_guid.txt", $d['family_guid']."\n");

    file_put_contents("{$taxa_data_folder}{$d['clazz']}/{$d['family']}/{$d['genus']}/genus_guid.txt", $d['genus_guid']."\n");
     
    file_put_contents("{$taxa_data_folder}{$d['clazz']}/{$d['family']}/{$d['genus']}/{$species_folder_name}/species_guid.txt", $d['species_guid']."\n");

    file_put_contents("{$taxa_data_folder}{$d['clazz']}/{$d['family']}/{$d['genus']}/{$species_folder_name}/scientific_name.txt", $d['species']."\n");
    
    file_put_contents("{$taxa_data_folder}{$d['clazz']}/{$d['family']}/{$d['genus']}/{$species_folder_name}/data.json", $d[$JSON_KEY]."\n");
    
    
    // set original occur.csv here  - we want to read the first line of the occur.csv and get the "species_id" that was used
    $original_occur = "{$real_data_folder}{$clazz_translation[$d['clazz']]}/models/{$species_folder_name}/occur.csv";
    
    // create link to original occur inside Taxa Heirachy
    $ln = "ln -s '{$original_occur}' '{$taxa_data_folder}{$d['clazz']}/{$d['family']}/{$d['genus']}/{$species_folder_name}/occur.csv'";     
    exec($ln);
    

    
    $original_ascii_data_folder = "{$real_data_folder}{$clazz_translation[$d['clazz']]}/models/{$species_folder_name}/output/ascii/";
    
    // create link to original ascii / gz  foldet to inside Taxa Heirachy
    $ln = "ln -s '{$original_ascii_data_folder}' '{$taxa_data_folder}{$d['clazz']}/{$d['family']}/{$d['genus']}/{$species_folder_name}/output'";     
    exec($ln);
    
    
    // get original_species_id from the occur file - so we can then use it to create the id's fo AP02 data
    $original_species_id = exec("head -n2 '$original_occur' | tail -n1 | cut -d, -s -f1");

    // create Folder in $AP02_data_folder as $original_species_id
    file::mkdir_safe("{$AP02_data_folder}{$original_species_id}");
    
    exec("chmod u+rwxs,g+rwxs,o+rwxs '{$AP02_data_folder}{$original_species_id}'");
    
    file::mkdir_safe("{$AP02_data_folder}{$original_species_id}/output");
    exec("chmod u+rwxs,g+rwxs,o+rwxs '{$AP02_data_folder}{$original_species_id}/output'");
    
    
    ErrorMessage::Marker("link {$original_occur} to {$original_species_id}/occur.csv");
    
    $ln = "ln -s '{$original_occur}' '{$AP02_data_folder}{$original_species_id}/occur.csv'";
    //ErrorMessage::Marker($ln);
    exec($ln);
    
    
    // set original maxentResults.csv here  
    $original_maxentResults = "{$real_data_folder}{$clazz_translation[$d['clazz']]}/models/{$species_folder_name}/output/maxentResults.csv";
    
    
    ErrorMessage::Marker("link {$original_maxentResults} to {$original_species_id}/output/maxentResults.csv");
    
    $ln = "ln -s '{$original_maxentResults}' '{$AP02_data_folder}/{$original_species_id}/output/maxentResults.csv'";
    //ErrorMessage::Marker($ln);
    exec($ln);
    

    // species commmon names
    if (is_array($d['common_names']))
    {
        file_put_contents("{$taxa_data_folder}{$d['clazz']}/{$d['family']}/{$d['genus']}/{$species_folder_name}/common_names.txt", implode("\n",$d['common_names'])."\n");
        
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
        exec($ln);
    }
    
    ErrorMessage::Marker("Create Data Links - Clazz {$d['clazz']} ");

    file::mkdir_safe("{$sdf}ByClazz/{$d['clazz']}");
    file::mkdir_safe("{$sdf}ByClazz/{$d['clazz']}/ByID");
    file::mkdir_safe("{$sdf}ByClazz/{$d['clazz']}/ByName");
    
    $ln = "ln -s '{$AP02_data_folder}{$original_species_id}/' '{$sdf}ByClazz/{$d['clazz']}/ByID/{$original_species_id}'"; 
    exec($ln);
    
    $ln = "ln -s '{$AP02_data_folder}{$original_species_id}/' '{$sdf}ByClazz/{$d['clazz']}/ByName/{$species_folder_name}'"; 
    exec($ln);
    
    
    file::mkdir_safe("{$sdf}ByFamily/{$d['family']}");
    file::mkdir_safe("{$sdf}ByFamily/{$d['family']}/ByID");
    file::mkdir_safe("{$sdf}ByFamily/{$d['family']}/ByName");

    $ln = "ln -s '{$AP02_data_folder}{$original_species_id}/' '{$sdf}ByFamily/{$d['family']}/ByID/{$original_species_id}'"; 
    exec($ln);
    
    $ln = "ln -s '{$AP02_data_folder}{$original_species_id}/' '{$sdf}ByFamily/{$d['family']}/ByName/{$species_folder_name}'"; 
    exec($ln);
    
    
    
    file::mkdir_safe("{$sdf}ByGenus/{$d['genus']}");
    file::mkdir_safe("{$sdf}ByGenus/{$d['genus']}/ByID");
    file::mkdir_safe("{$sdf}ByGenus/{$d['genus']}/ByName");
    
    
    $ln = "ln -s '{$AP02_data_folder}{$original_species_id}/' '{$sdf}ByGenus/{$d['genus']}/ByID/{$original_species_id}'"; 
    exec($ln);
    
    $ln = "ln -s '{$AP02_data_folder}{$original_species_id}/' '{$sdf}ByGenus/{$d['genus']}/ByName/{$species_folder_name}'"; 
    exec($ln);
    
    
    $count++;
    
}


// Save Lookup Data

ksort($clazz);          file_put_contents("{$sdf}clazz_list.txt",    implode("\n",$clazz)."\n");
ksort($family);         file_put_contents("{$sdf}family_list.txt",   implode("\n",$family)."\n");
ksort($genus);          file_put_contents("{$sdf}genus_list.txt",    implode("\n",$genus)."\n");
ksort($species);        file_put_contents("{$sdf}species_list.txt",  implode("\n",$species)."\n");
ksort($common_names);   file_put_contents("{$sdf}common_names.txt",  implode("\n",$common_names)."\n");
ksort($species_to_id);  file_put_contents("{$sdf}species_to_id.txt", "name,id\n".implode("\n",$species_to_id)."\n");

// will allow apache to add files to these folders, but data is symbolicly linked and wont be changed

exec("chmod -R u+rwxs,g+rwxs,+o+rwsx {$sdf}*");

function remove_data_lookup_files()
{
    ErrorMessage::Marker("remove_data_lookup_files");
    
    $sdf = configuration::SourceDataFolder();
    
    $clazz_list_filename = "{$sdf}clazz_list.txt";
    file::Delete($clazz_list_filename);

    $family_list_filename = "{$sdf}family_list.txt";
    file::Delete($family_list_filename);

    $genus_list_filename = "{$sdf}genus_list.txt";
    file::Delete($genus_list_filename);

    $species_list_filename = "{$sdf}species_list.txt";
    file::Delete($species_list_filename);

    $common_list_filename = "{$sdf}common_names.txt";
    file::Delete($common_list_filename);

    $species_to_id_filename = "{$sdf}species_to_id.txt";
    file::Delete($species_to_id_filename);
    
    
}



function create_taxa_folders()
{
    
    ErrorMessage::Marker("create_taxa_folders");
    
    $maxent_data = configuration::Maxent_Species_Data_folder();
    ErrorMessage::Marker("Remove all AP02 Species ID data / links  from [{$maxent_data}]");
    exec("rm -r -f {$maxent_data}/*");

    

    $sdf = configuration::SourceDataFolder();
    
    ErrorMessage::Marker("create Taxa folder - Heirachy of Species groupings");
    exec("rm -r -f '{$sdf}Taxa/'");
    file::mkdir_safe("{$sdf}Taxa/");  // hold heirarchy of Clazz/Family/Genus/Species

    ErrorMessage::Marker("create Taxa folder - Clazz - All species belonging to this Clazz  [{{$sdf}ByClazz}]");
    exec("rm -f -r '{$sdf}ByClazz/'");
    file::mkdir_safe("{$sdf}/ByClazz");    

    ErrorMessage::Marker("create Taxa folder - Family - All species belonging to this Family [{{$sdf}ByFamily}]");
    exec("rm -r -f '{$sdf}ByFamily/'");
    file::mkdir_safe("{$sdf}/ByFamily/");

    ErrorMessage::Marker("create Taxa folder - Genus - All species belonging to this Genus [{{$sdf}ByGenus}]");
    exec("rm -r -f '{$sdf}ByGenus/'");
    file::mkdir_safe("{$sdf}/ByGenus");
    
    
}


function species_list_from_folders()
{
    
    $species_list = array();
    
    exec("ls -1 /scratch/jc148322/AP02/amphibians/models/",$species_list );
    exec("ls -1 /scratch/jc148322/AP02/mammals/models/"   ,$species_list );
    exec("ls -1 /scratch/jc148322/AP02/reptiles/models/",  $species_list );

    return $species_list;
    
}


function modelled_list()
{
    ErrorMessage::Marker("Read MODELLED list");
    $modelled = array();
    exec(" cat /scratch/jc148322/AP02/actionlist.csv | grep -v 'not_modelled' | grep -v 'class' | less",$modelled);
    $modelled = array_flip(util::leftStrArray(array_util::Replace($modelled, '"', ''), ',', false));
    
    return $modelled;
    
}


function ALASpeciesTaxa($species_folder_name)
{

    $sdf = configuration::SourceDataFolder();
    
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
