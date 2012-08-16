<?php
/* 
 */
?>
<?php
include_once dirname(__FILE__).'/includes.php';

$sql = "select species_id,common_name,count(*) from modelled_species_files group by species_id,common_name order by species_id;";

$result = DBO::Query($sql,'species_id');

if ($result instanceof ErrorMessage) exit(1);

matrix:: display($result, " ",null, 20);

?>

