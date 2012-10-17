<?php
include_once dirname(__FILE__).'/includes.php';

echo "Move Data TDH2 -> TDH1 \n";

echo "Copy Table  species_taxa_tree \n";

//species_taxa_tree();

echo "Copy Table  occurrences \n";

//create_occurrences();

get_occurrences(50);

echo "Copy Table  species \n";

function species_taxa_tree()
{

    $table_name = 'species_taxa_tree';
    
    $from = new PGDB("tdh-tools-2.hpc.jcu.edu.au");
    $to = new PGDB("tdh-tools-1.hpc.jcu.edu.au");
    
    $from_count = $from->CountUnique($table_name, 'id');
    $to_count   =   $to->CountUnique($table_name, 'id');
    
    echo "from_count  = $from_count \n";
    echo "to_count    = $to_count\n";

    
    $desc = $from->describe_table($table_name);
    
    print_r($desc);
    
    $keys = ""
        .'id,' 
        .'species_id,' 
        .'parent_guid,' 
        .'guid,' 
        .'kingdom,' 
        .'kingdom_guid,' 
        .'phylum,' 
        .'phylum_guid,' 
        .'clazz,' 
        .'clazz_guid,' 
        .'orderz,' 
        .'orderz_guid,' 
        .'family,' 
        .'family_guid,' 
        .'genus,' 
        .'genus_guid,' 
        .'species,' 
        .'species_guid' 
        ."";
    
    
    $from_sql = "select $keys from {$table_name}";
    
    //echo "from_sql = $from_sql\n";
    
    $fromResult = $from->query($from_sql);
    
    foreach ($fromResult as $index => $row) 
    {
        
        echo "{$index} {$row['species']}\n";
        
        $values = "(" 
        .$row['id'].','
        .$row['species_id'].','
        .util::dbq($row['parent_guid']).','
        .util::dbq($row['guid']).','
        .util::dbq($row['kingdom']).','
        .util::dbq($row['kingdom_guid']).','
        .util::dbq($row['phylum']).','
        .util::dbq($row['phylum_guid']).','
        .util::dbq($row['clazz']).','
        .util::dbq($row['clazz_guid']).','
        .util::dbq($row['orderz']).','
        .util::dbq($row['orderz_guid']).','
        .util::dbq($row['family']).','
        .util::dbq($row['family_guid']).','
        .util::dbq($row['genus']).','
        .util::dbq($row['genus_guid']).','
        .util::dbq($row['species']).','
        .util::dbq($row['species_guid'])
        .")";
        
        
        $sql = "insert into {$table_name} ({$keys}) VALUES {$values}";
        
        //echo "$sql\n";
        
        $to->insert($sql,false,false);
        
    }
    
    
    
    
    unset($from);
    unset($to);
    
}
    

?>
