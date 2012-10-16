<?php
include_once dirname(__FILE__).'/includes.php';

$JSON_KEY = 'JSON';
$clazz_translation = array();
$clazz_translation['AMPHIBIA'] = 'amphibians';
$clazz_translation['MAMMALIA'] = 'mammals';
$clazz_translation['REPTILIA'] = 'reptiles';

$real_data_folder = "/scratch/jc148322/AP02/";   // folder with real data 

$AP02_data_folder  = configuration::Maxent_Species_Data_folder();


ErrorMessage::Marker("RICHNESS: Link Clazz ");


foreach ($clazz_translation as $clazz_name =>$clazz_common_name) 
{
    //create_clazz_richness_links($clazz_name,$clazz_common_name);  
    //create_genus_richness_links($clazz_name,$clazz_common_name);
    
    create_richness_links_for_hotspot_tool($clazz_name,$clazz_common_name);
    
}

exit();


function create_richness_links_for_hotspot_tool($clazz_name,$clazz_common_name)
{
    $msdf = configuration::Maxent_Species_Data_folder();
    $msdf_richness = "{$msdf}richness/ByGenus/";

    $richness_files = array();
    exec("find /scratch/jc148322/AP02/{$clazz_common_name}/richness/*.asc.gz",$richness_files);

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
        
        file::mkdir_safe("$f");
        exec("rm -f -r {$f}/*");
        
        $single_genus_richness_files = array();
        exec("find /scratch/jc148322/AP02/{$clazz_common_name}/richness/*{$genus_name}.asc.gz",$single_genus_richness_files);
        
        
        ErrorMessage::Marker("Genus Link for Hotspot Tool - [{$genus_name}]");
        
        foreach ($single_genus_richness_files as $single_genus_richness_file) 
        {
            
            list($scenario,$time,$rest) = explode("_",basename($single_genus_richness_file));

            $link_name = "{$msdf_richness}{$genus_name}/{$scenario}_{$time}.asc.gz";
            
            $ln = "ln -s '$single_genus_richness_file' '{$link_name}'";
            //ErrorMessage::Marker("$ln");
            exec($ln);    
            
        }
        
    }    
    
    
    
}


function create_genus_richness_links($clazz_name,$clazz_common_name)
{
    
    $sdf = configuration::SourceDataFolder();
    $msdf = configuration::Maxent_Species_Data_folder();
    
    file::mkdir_safe("{$msdf}ByGenus");
    exec("rm -f -r {$msdf}ByGenus/*");
    
    
    
    ErrorMessage::Marker("Link Genus Richness for [{$clazz_name}]");
    
    // get list of genus for this clazz
    
    $richness_files = array();
    exec("find /scratch/jc148322/AP02/{$clazz_common_name}/richness/*.asc.gz",$richness_files);

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
        file::mkdir_safe("{$sdf}ByGenus/{$genus_name}/richness");
        exec("rm -f -r {$sdf}ByGenus/{$genus_name}/richness/*");
        
        $single_genus_richness_files = array();
        exec("find /scratch/jc148322/AP02/{$clazz_common_name}/richness/*{$genus_name}.asc.gz",$single_genus_richness_files);

        
        file::mkdir_safe("{$msdf}ByGenus/{$genus_name}");
        
        foreach ($single_genus_richness_files as $single_genus_richness_file) 
        {
            
            $ln = "ln -s '$single_genus_richness_file' '{$sdf}ByGenus/{$genus_name}/richness/".str_replace("_{$genus_name}","", basename($single_genus_richness_file)."'");
            ErrorMessage::Marker("$ln");
            exec($ln);    
            
            $ln = "ln -s '$single_genus_richness_file' '{$msdf}ByGenus/{$genus_name}'";
            ErrorMessage::Marker("$ln");
            exec($ln);    
            
            
            
        }
        
    }
    
}


function create_clazz_richness_links($clazz_name,$clazz_common_name)
{
 
    $sdf = configuration::SourceDataFolder();

    ErrorMessage::Marker("Link Clazz Richness [{$clazz_name}]");

    file::mkdir_safe("{$sdf}ByClazz/{$clazz_name}/richness");
    exec("rm -f -r {$sdf}ByClazz/{$clazz_name}/richness/*");

    $clazz_richness_files = array();
    exec("find /scratch/jc148322/AP02/{$clazz_common_name}/richness/*_{$clazz_common_name}.asc.gz",$clazz_richness_files);

    foreach ($clazz_richness_files as $clazz_richness_file) 
    {
        list($scenario,$time,$rest) = explode("_",util::fromLastSlash($clazz_richness_file));

        ErrorMessage::Marker("{$clazz_name} .. {$scenario}_{$time}");

        $ln = "ln -s {$clazz_richness_file} {$sdf}ByClazz/{$clazz_name}/richness/{$scenario}_{$time}.asc.gz";

        exec($ln);

    }

    ErrorMessage::Marker("updated richness links for {$sdf}ByClazz/$clazz_name/richness/");
    
    
}



?>
