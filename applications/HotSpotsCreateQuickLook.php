<?php
/**
 * For a single species create the QUickLook images (pngs) for Richness
 *  
 */
include_once dirname(__FILE__).'/includes.php';
include_once dirname(__FILE__).'/extras/RichnessQuickLookCreate.class.php';

$prog = array_util::Value( $argv, 0);

$input  = util::CommandScriptsFoldermandLineOptionValue($argv, 'input');
$output = util::CommandScriptsFoldermandLineOptionValue($argv, 'output');
$cmd = util::CommandScriptsFoldermandLineOptionValue($argv, 'cmd');
$title = util::CommandScriptsFoldermandLineOptionValue($argv, 'title');
$recreate = util::CommandScriptsFoldermandLineOptionValue($argv, 'recreate');

if (is_null($output) ) 
{
    echo "\n" ;
    echo "ERROR: output folder not defined\n" ;
    echo "\n" ;
    usage($prog);
}

if (!is_dir($output)) 
{
    echo "\n" ;
    echo "ERROR: can't find output folder [{$output}] \n" ;
    echo "\n" ;
    usage($prog);
}

function usage($prog)
{
    echo "usage: {$prog} [--input=file_pattern] --output=folder [--cmd='some linux command line'] [--title='Title prefix'] [--recreate=true] \n" ;
    echo "\n" ;
    echo "notes:\n" ;
    echo "    treat all files as a set and find min and max across this set\n" ;
    echo "    note: you can use '-' or STDIN  to read from piped data \n" ;
    echo "\n" ;
    echo "e.g. {$prog} --input='data/*.gz' --output=fred/        treat all files as a set and write outputs to \n" ;
    echo "\n" ;
    echo " read from STDIN and write files to the folder 'fred'\n" ;
    echo " {$prog} --input=-     --output=fred/ \n" ;
    echo " {$prog} --input=STDIN --output=fred/ \n" ;
    echo "\n" ;
    echo "--cmd='cmd'  execute  command and use this as the list of files\n" ;
    echo "\n" ;
    exit(0);
}

$files = null;


$haveInput = false;

if ($input == "STDIN" || $input == "-")
{
    ErrorMessage::Marker("Get list from input from STDIN");
    $data = '';
    while ($input = fread(STDIN, 1024))  $data .= $input;
    $files_data = explode("\n",$data);
    
    $files = array();
    foreach ($files_data as $file) 
    {
        $file = trim($file);
        if ($file == '') continue;
        $files[basename($file)] = $file;
    }
    
    if (count($files) > 0) $haveInput = true;    
}

if (!$haveInput && !is_null($input))
{
    ErrorMessage::Marker("Get list from input folder [{$input}]");
    $files = file::LS($input, null, true);     
    $haveInput = true;
}

if (!$haveInput && !is_null($cmd))
{
    ErrorMessage::Marker("Get list from cmd  [{$cmd}]");
    
    $files_data = array();
    exec("{$cmd}",$files_data);
    
    foreach ($files_data as $file) 
    {
        $file = trim($file);
        if ($file == '') continue;
        $files[basename($file)] = $file;
    }
    
    $haveInput = true;
}


$R = new RichnessQuickLookCreate($files);
$R->ImageTitle($title);
$R->Recreate($recreate);
$R->execute();


?>