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


ErrorMessage::Marker("Create links to Richness data for use with AP02 Tools");

$JSON_KEY = 'JSON';
$clazz_translation = array();
$clazz_translation['AMPHIBIA'] = 'amphibians';
$clazz_translation['MAMMALIA'] = 'mammals';
$clazz_translation['REPTILIA'] = 'reptiles';
$clazz_translation['AVES'] = 'birds';

$real_data_folder = "/home/TDH/data/SDM/";   // folder with real data
ErrorMessage::Marker("real_data_folder = [{$real_data_folder}]" );


$AP02_data_folder  = configuration::Maxent_Species_Data_folder();
ErrorMessage::Marker("AP02_data_folder  = [{$AP02_data_folder}]" );



ErrorMessage::Marker("RICHNESS: Using Clazz Lookup Species Sets");


$msdf = configuration::Maxent_Species_Data_folder();

ErrorMessage::Marker("mkdir {$msdf}richness");
if ($execute) file::mkdir_safe("{$msdf}richness");

ErrorMessage::Marker("remove contents of  {$msdf}richness");
if ($execute) exec("rm -f -r {$msdf}richness/*");



foreach ($clazz_translation as $clazz_name => $clazz_common_name)
{
    ErrorMessage::Marker("RICHNESS: CLAZZ $clazz_name => $clazz_common_name");
    create_clazz_richness_links($clazz_name,$clazz_common_name,$real_data_folder,$execute);

    ErrorMessage::Marker("RICHNESS: GENUS $clazz_name => $clazz_common_name");
    create_genus_richness_links($clazz_name,$clazz_common_name,$real_data_folder,$execute);


    ErrorMessage::Marker("RICHNESS: Hotspot tool  $clazz_name => $clazz_common_name");
    create_richness_links_for_hotspot_tool($clazz_name,$clazz_common_name,$real_data_folder,$execute);

}

exit();


function create_richness_links_for_hotspot_tool($clazz_name,$clazz_common_name,$real_data_folder,$execute)
{
    $msdf = configuration::Maxent_Species_Data_folder();


    $msdf_richness = "{$msdf}richness/ByGenus/";


    $richness_files = array();
    exec("find {$real_data_folder}{$clazz_common_name}/richness/*.asc.gz",$richness_files);

    // get unique Genus List
    $genus_names = array();
    foreach ($richness_files as $richness_file)
    {
        $name = util::fromLastChar(str_replace(".asc.gz","", basename($richness_file)),'_');
        if (util::contains($name, $clazz_common_name))  continue;
        $genus_names[$name] = $name;
    }


    print_r($genus_names);

    // process per Genus
    foreach ($genus_names as $genus_name)
    {

        $f = "{$msdf_richness}{$genus_name}";


        ErrorMessage::Marker("mkdir $f");
        if ($execute) file::mkdir_safe("$f");

        ErrorMessage::Marker("remove contents of  $f");
        if ($execute) exec("rm -f -r {$f}/*");

        $single_genus_richness_files = array();
        exec("find {$real_data_folder}{$clazz_common_name}/richness/*{$genus_name}.asc.gz",$single_genus_richness_files);


        ErrorMessage::Marker("Genus Link for Hotspot Tool - [{$genus_name}]");

        foreach ($single_genus_richness_files as $single_genus_richness_file)
        {

            list($scenario,$time,$rest) = explode("_",basename($single_genus_richness_file));

            $link_name = "{$msdf_richness}{$genus_name}/{$scenario}_{$time}.asc.gz";

            $ln = "ln -s '$single_genus_richness_file' '{$link_name}'";
            ErrorMessage::Marker("$ln");
            if ($execute)  exec($ln);

        }

    }



}


function create_genus_richness_links($clazz_name,$clazz_common_name,$real_data_folder,$execute)
{

    $sdf = configuration::SourceDataFolder();
    $msdf = configuration::Maxent_Species_Data_folder();

    ErrorMessage::Marker("Link Genus Richness for [{$clazz_name}]");

    ErrorMessage::Marker("mkdir {$msdf}richness/ByGenus");
    if ($execute) file::mkdir_safe("{$msdf}richness/ByGenus");


    // get list of genus for this clazz

    $richness_files = array();
    exec("find {$real_data_folder}{$clazz_common_name}/richness/*.asc.gz",$richness_files);

    $genus_names = array();
    foreach ($richness_files as $richness_file)
    {
        $name = util::fromLastChar(str_replace(".asc.gz","", basename($richness_file)),'_');
        if (util::contains($name, $clazz_common_name))  continue;
        $genus_names[$name] = $name;
    }


    print_r($genus_names);

    foreach ($genus_names as $genus_name)
    {

        ErrorMessage::Marker("mkdir {$sdf}ByGenus/{$genus_name}/richness");
        if ($execute)  file::mkdir_safe("{$sdf}ByGenus/{$genus_name}/richness");


        ErrorMessage::Marker("remove contents of {$sdf}ByGenus/{$genus_name}/richness");
        if ($execute)  exec("rm -f -r {$sdf}ByGenus/{$genus_name}/richness/*");


        $single_genus_richness_files = array();
        exec("find {$real_data_folder}{$clazz_common_name}/richness/*{$genus_name}.asc.gz",$single_genus_richness_files);


        if ($execute)  file::mkdir_safe("{$msdf}richness/ByGenus/{$genus_name}");

        foreach ($single_genus_richness_files as $single_genus_richness_file)
        {

            $ln = "ln -s '$single_genus_richness_file' '{$sdf}ByGenus/{$genus_name}/richness/".str_replace("_{$genus_name}","", basename($single_genus_richness_file)."'");
            ErrorMessage::Marker("$ln");
            if ($execute)  exec($ln);

            $ln = "ln -s '$single_genus_richness_file' '{$msdf}richness/ByGenus/{$genus_name}'";
            ErrorMessage::Marker("$ln");
            if ($execute)  exec($ln);

        }

    }

}


function create_clazz_richness_links($clazz_name,$clazz_common_name,$real_data_folder,$execute)
{

    $sdf = configuration::SourceDataFolder();

    ErrorMessage::Marker("Link Clazz Richness [{$clazz_name}]");


    ErrorMessage::Marker("mkdir {$sdf}ByClazz/{$clazz_name}/richness");
    if ($execute)  file::mkdir_safe("{$sdf}ByClazz/{$clazz_name}/richness");

    ErrorMessage::Marker("remove contents of {$sdf}ByClazz/{$clazz_name}/richness");
    if ($execute)  exec("rm -f -r {$sdf}ByClazz/{$clazz_name}/richness/*");

    $clazz_richness_files = array();
    exec("find {$real_data_folder}{$clazz_common_name}/richness/*_{$clazz_common_name}.asc.gz",$clazz_richness_files);

    foreach ($clazz_richness_files as $clazz_richness_file)
    {
        list($scenario,$time,$rest) = explode("_",util::fromLastSlash($clazz_richness_file));

        ErrorMessage::Marker("{$clazz_name} .. {$scenario}_{$time}");

        $ln = "ln -s {$clazz_richness_file} {$sdf}ByClazz/{$clazz_name}/richness/{$scenario}_{$time}.asc.gz";
        ErrorMessage::Marker("$ln");
        if ($execute)  exec($ln);


    }

    ErrorMessage::Marker("updated richness links for {$sdf}ByClazz/$clazz_name/richness/");


}



?>
