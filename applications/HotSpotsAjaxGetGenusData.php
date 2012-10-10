<?php 
session_start();
include_once dirname(__FILE__).'/includes.php';

$result = array();

foreach ($_POST as $key => $value) 
    Session::add($key, $value);

$genus = array_util::Value($_POST, "genus", null);
if (is_null($genus))
{
    $result['msg'] = "ERROR:: genus passed as NULL ";
    echo json_encode($result);
    return;  
}


$addid = array_util::Value($_POST, "addid", null);
if (is_null($addid))
{
    $result['msg'] = "ERROR:: addid passed as NULL ";
    echo json_encode($result);
    return;  
}


$data = GenusData::GetProjectedFiles($genus, 'QUICK_LOOK');
if ($data instanceof ErrorMessage)
{
    $result['msg'] = print_r($data,true);
    echo json_encode($result);
    return;  
}    

if (count($data) == 0)
{
    $result['modelled'] = '';
}
else
{
    $modelled = array();
    foreach ($data as $file_unique_id => $row) 
    {
        $combination = "{$row['scenario']}_{$row['model']}_{$row['time']}";
        $modelled[] = "{$combination}={$file_unique_id}"  ;
    }
    $result['modelled'] = implode("~", $modelled);
    
}



$result['addid'] = $addid;  // so we know ehere to place a link / info when it returns
$result['genus'] = $genus;  // so we know ehere to place a link / info when it returns


echo json_encode($result);
return;   // will stop here and return if we have all results requested.


?>
