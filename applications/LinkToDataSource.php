<?php
include_once dirname(__FILE__).'/includes.php';
/**
 *
 * Link current data folder structures to new data sources 
 * 
 * NEW_SOURCE: /scratch/jc148322/AP02
 * 
 * CURRENT: configuration::Maxent_Species_Data_folder()
 * 
 * Loop through each species listed as a folder in NEW SOURCE FOLDER and 
 * create / update appropriate folder in current data sources.
 * 
 * 
 * Read Species Name
 * SPECIES_ID = species data lookup species id from scientific name
 * check current folder for species id (create if required)
 * 
 * CLAZZ = species data - lookup Clazz for Species ID
 * 
 * create link from old folder structure to new structure 
 * 
 * link -s  CURRENT/SPECIES_ID/occur.csv -->  NEW_SOURCE/CLAZZ/models/SPECIES_NAME/occur.csv
 * 
 * $PROJECTED_FILE = foreach  NEW_SOURCE/CLAZZ/models/SPECIES_NAME/output/ascii/*.asc.gz  
 * 
 *    link -s  NEW_SOURCE/CLAZZ/models/SPECIES_NAME/$PROJECTED_FILE CURRENT/SPECIES_ID/output/$PROJECTED_FILE
 * 
 * 
 * 
 */

$app_path = configuration::ApplicationFolder();

$execute = util::CommandScriptsFoldermandLineOptionValue($argv, 'execute', null);
$useALA = util::CommandScriptsFoldermandLineOptionValue($argv, 'useALA', false);


echo "\nLink current Data Folder with New Data Folder";
echo "\n";
echo "\nDue to updates and chnages to data the data folder structure has changed, \nthough to minmise changes to code this script will create the appropriate links from the current data folder to the new folder \n\n";


if (is_null($execute))
{
    echo "\n\nusage: {$argv[0]} --execute=true  \n";
    echo "\n\nusage: {$argv[0]} --execute=true  [--useALA=true]  .. if data NEW folde exists but no data in database get new data from ALA\n";
    echo "           ";
    echo " \n\n";
    
    exit(0);
}


$data_current = configuration::Maxent_Species_Data_folder();

$data_new = "/scratch/jc148322/AP02/";

echo "Current Data Folder ... [{$data_current}]\n";
echo "New     Data Folder ... [{$data_new}]\n";

echo "\n";


echo "Clazz (Taxa) Names\n";
echo "========================================================\n";
$taxa_names = file::folder_folders($data_new, configuration::osPathDelimiter(), true);


$error = array();

foreach ($taxa_names as $single_taxa_name => $single_taxa_folder) 
{
    
    $taxa_species_folders = file::folder_folders($single_taxa_folder."/models/", configuration::osPathDelimiter(), true);
    
    echo "Process link redirection for CLAZZ [{$single_taxa_name}] using parent folder [{$single_taxa_folder}]\n";
    
    
    foreach ($taxa_species_folders as $species_name => $species_folder) 
    {
    
        $search_species_name = str_replace("_", "%", $species_name);  // replace underscore
        
        echo "Get species ID for [{$species_name}] using [{$search_species_name}]\n";
        
        $result_array = SpeciesData::id4ScientificName($search_species_name, true);
        
        if ($result_array instanceof ErrorMessage)
        {
            $error[] =  ErrorMessage::Stacked(__METHOD__, __LINE__, "Failed to get ScientificName using $search_species_name", true, $result_array);
            continue;
        }
        
            
        if (count($result_array) > 1)
        {
            $error[] =  new ErrorMessage(__METHOD__, __LINE__, "Too Many Taxa rows that matched $search_species_name");
            continue;
        }

        
        if (count($result_array) == 0)
        {
            if ($useALA)
            {
             
                $ala_result = SpeciesData::FetchFromALA($species_name,true);

                if ($result_array instanceof ErrorMessage)
                {
                    $error[] =  ErrorMessage::Stacked(__METHOD__, __LINE__, "Failed to get ALA data for {$species_name}", true, $ala_result);
                    continue;
                }
                else
                {
                    // we just added it so look it up
                    $result_array = SpeciesData::id4ScientificName($search_species_name, true);
                    
                }
                
            }
            else
            {
                // no data and we said not to get data from ALA
                $error[] =  new ErrorMessage(__METHOD__, __LINE__, "Could not find any Taxa rows that matched $search_species_name  (try --useALA next next time to reteive data)");
                continue;                
            }

        }
        
        // if it's still zero then mmove on
        if (count($result_array) == 0) continue;
        
        
        echo "Found Species Row for [{$search_species_name}]\n";
        
        print_r($result_array);
        
        
        echo "SPECIES_ID = species data lookup species id from scientific name\n";
        
        
        //echo "Redirect occur.csv\n";
        
        
    }
    
    
    
    
}



if (count($error > 0))
{
    echo "#################### ERRORS OCCURED ####################\n";
    print_r($error);
    echo "#################### ERRORS OCCURED ####################\n";
    
}



exit();



echo "check current folder for species id (create if required)\n";

echo "CLAZZ = species data - lookup Clazz for Species ID\n";

echo "create link from old folder structure to new structure \n";

echo "link -s  CURRENT/SPECIES_ID/occur.csv -->  NEW_SOURCE/CLAZZ/models/SPECIES_NAME/occur.csv\n";

echo "$PROJECTED_FILE = foreach  NEW_SOURCE/CLAZZ/models/SPECIES_NAME/output/ascii/*.asc.gz  \n";

echo "link -s  NEW_SOURCE/CLAZZ/models/SPECIES_NAME/$PROJECTED_FILE CURRENT/SPECIES_ID/output/$PROJECTED_FILE\n";
 


?>
