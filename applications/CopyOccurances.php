<?php
include_once dirname(__FILE__).'/includes.php';

echo "Copy Data TDH2 -> TDH1 \n";

//get_occurrences(1);

//create_occurrences();

copy_all_species();


function copy_all_species()
{
    
    $to = new PGDB("tdh-tools-1.hpc.jcu.edu.au");
    $from = new PGDB("tdh-tools-2.hpc.jcu.edu.au");
    
    $ids = $from->Unique('species_occur', 'species_id');
    
    echo "Copy Species Occurances Species Count = ".count($ids)."\n";
    
    foreach ($ids as $row) 
    {
        $id = $row['species_id'];
        
        echo "Copy Species Occurances Species  for {$id} .. ".SpeciesData::SpeciesQuickInformation($id)."\n";
        
        get_occurrences($id);
        
        
    }
    
    
    unset($from);
    unset($to);
    
    
}

function get_occurrences($species_id)
{
 
    $table_name = 'species_occurrences';
    
    $from = new PGDB("tdh-tools-2.hpc.jcu.edu.au");
    $to = new PGDB("tdh-tools-1.hpc.jcu.edu.au");
    
    // does $to server have all the data already
    $fromCount = $from->count('occurrences',        "species_id = {$species_id}");
    $toCount   =   $to->count('species_occurrences',"species_id = {$species_id}");
    
    if ($fromCount != $toCount)
    {
        echo "REDO::   $fromCount != $toCount  for [$species_id}]\n";
        $to->delete('species_occurrences', "species_id = {$species_id}");
        
    }
    else
    {
        echo " $fromCount == $toCount  for [{$species_id}] - JOb Done\n";
        return;
    }
    
    
    $from_sql = "select 
                   id as occurrences_id
                  ,species_id
                  ,ST_X(location) AS longitude
                  ,ST_Y(location) AS latitude 
                from 
                   occurrences
                 where species_id = {$species_id}
                ";
    
    echo "from_sql = $from_sql\n";
    
    $fromResult = $from->query($from_sql);
    
    
    
    
    
    
    $keys = "occurrences_id,species_id,longitude,latitude";
    $count = 0;
    
    $p1 = "insert into {$table_name} ({$keys}) VALUES ";
    
    $values = array();
    foreach ($fromResult as $index => $row) 
    {
        
        $values[] = "(" 
        .$row['occurrences_id'].','
        .$row['species_id'].','
        .$row['longitude'].','
        .$row['latitude']
        .")";
        
        if ($count % 100 == 0)  
        {
            if ($count % 1000 == 0)   echo $count."/".count($fromResult)." ";
            $sql = "insert into {$table_name} ({$keys}) VALUES ".  implode(",", $values);
            $to->insert($sql,false,false);
            $values = array();
        }
        
        
        $count++;
        
    }
    
    
    echo "Last Bit insert \n";
    
    $sql = "insert into {$table_name} ({$keys}) VALUES ".  implode(",", $values);
    
    //echo "$sql\n";
    
    $to->insert($sql,false,false);
    
    
    echo "\n";
    
    
    unset($from);
    unset($to);
    
    
}


function create_occurrences()
{
 
    $table_name = 'species_occurrences';
    
    $from = new PGDB("tdh-tools-2.hpc.jcu.edu.au");
    $to = new PGDB("tdh-tools-1.hpc.jcu.edu.au");
    
    
$table_sql = <<<SQL
DROP TABLE IF EXISTS {$table_name};
CREATE TABLE {$table_name}
(
      id SERIAL NOT NULL PRIMARY KEY
     ,occurrences_id    integer
     ,species_id        integer
     ,longitude         float
     ,latitude          float
     
);
GRANT ALL PRIVILEGES ON {$table_name} TO ap02;
GRANT USAGE, SELECT ON SEQUENCE {$table_name}_id_seq TO ap02;
SQL;

    $table_result =  DBO::CreateAndGrant($table_sql);
    
       
    unset($from);
    unset($to);

    
    
}

function species()
{
    
}


?>
