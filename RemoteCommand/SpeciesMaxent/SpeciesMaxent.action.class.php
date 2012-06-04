<?php
include_once 'SpeciesMaxent.configuration.class.php';

class SpeciesMaxentAction extends CommandAction {


    public function __construct() {
        parent::__construct();
        $this->ActionName(__CLASS__);
        $this->FinderName("");
    }


    public function __destruct() {
        parent::__destruct();
    }


    public function Execute()
    {

        $result = "Try to run as QSUB job SPecies Maxent ";

        $this->Result($result);

        return $result;

    }


    /**
     *
     * Take inarray contain lists of Species, EmissionScenario, ClimateModel & Time
     *
     *  $toCompute = array();
     *   $toCompute['Species'] = $this->getSpeciesIDs();
     *   $toCompute['EmissionScenario'] = $this->getIDs("EmissionScenarioSearch");
     *   $toCompute['ClimateModel'] = $this->getIDs("ClimateModelSearch");
     *   $toCompute['Time'] = $this->getIDs("TimeSearch");
     *
     *
     * @param type $toCompute
     */
    public static function ComputeAnySpecies($toCompute)
    {

        self::writeMaxentSpeciesProjectionScriptFile($toCompute);

    }


    private static function writeMaxentSpeciesProjectionScriptFile($toCompute)
    {

        // $toCompute['Species'] - file per species.

        // species file contains a row for each permutation of
        //     $toCompute['EmissionScenario']
        //     $toCompute['ClimateModel']
        //     $toCompute['Time']

        foreach ($toCompute['Species'] as $speciesID => $speciesName )
        {
            $file = array();
            $file[$speciesName] = array();

            foreach ($toCompute['EmissionScenario'] as $emissionScenarioID => $emissionScenarioName)
            {

                foreach ($toCompute['ClimateModel'] as $climateModelID => $climateModelName)
                {

                    foreach ($toCompute['Time'] as $timeID => $TimeName)
                    {
                        $combo = "{$emissionScenarioName}_{$climateModelName}_{$TimeName}";
                        $file[$speciesName][$combo] = $combo;
                    }

                }

            }

        }


        foreach ($file as $speciesName => $combinations)
        {
            $script = self::writeMaxentSingleSpeciesProjectionScriptFile($speciesName,$combinations);

            file_put_contents("/data/dmf/TDH/maxent_model/{$speciesName}.sh", $script);

        }


    }



    private static function writeMaxentSingleSpeciesProjectionScriptFile($speciesName,$combinations)
    {


        $maxent = RemoteCommandConfiguration::$MAXENT;
        $train = RemoteCommandConfiguration::$TRAINCLIMATE;
        $project = RemoteCommandConfiguration::$PROJECTCLIMATE;
        $data_folder = RemoteCommandConfiguration::$SPECIES_DATA_FOLDER;

        $output_subfolder = RemoteCommandConfiguration::$SPECIES_DATA_OUTPUT_SUBFOLDER;
        $script_subfolder = RemoteCommandConfiguration::$SPECIES_DATA_SCRIPT_SUBFOLDER;

        $occur = RemoteCommandConfiguration::$SPECIES_DATA_OCCURANCE_FILE_NAME;


        $os_path = configuration::osPathDelimiter();

        $species_folder = "{$data_folder}{$os_path}{$speciesName}";
        $output_folder  = "{$data_folder}{$os_path}{$speciesName}{$os_path}{$output_subfolder}";
        $script_folder =  "{$data_folder}{$os_path}{$speciesName}{$os_path}{$script_subfolder}";

        $occur =  "{$data_folder}{$os_path}{$speciesName}{$os_path}{$occur}";


$script = <<<AAA
#!/bin/tcsh
if (! -e "{$species_folder}" ) then
  echo "Folder for {$speciesName} does not exist  ({$species_folder})"
  exit
endif

#define file locations
set MAXENT=/home/jc166922/TDH/maxent_model/maxent.jar
set TRAINCLIMATE=/home/jc165798/Climate/PCMDI/01.Oz.5km.61.90/mxe/1975
set PROJECTCLIMATE=/home/jc165798/Climate/PCMDI/01.Oz.5km.61.90/mxe/
set OCCUR=/home/jc166922/TDH/maxent_model/{$speciesName}/occur.csv

#load the java module for the HPC
module load java

#make an output directory
echo Make output folder {$output_folder}
if (! -e "{$output_folder}" ) then
  mkdir {$output_folder}
endif

echo Make script folder {$script_folder}
if (! -e "{$script_folder}" ) then
  mkdir {$script_folder}
endif

echo execute model for {$speciesName}

#model the species distribution

java -mx2048m -jar {$maxent} environmentallayers={$train} samplesfile={$occur} outputdirectory={$output_folder} -J -P -x -z redoifexists autorun

AAA;


        foreach ($combinations as $combination)
        {
            $script .= "\necho qsub this $combination\n";
        }

        return $script;

    }



//echo "cycle through the projections and project the maps"
//
//foreach PROJ (`find $PROJECTCLIMATE -type d` )
//
//  set PROJ_OUTPUT="$OUTPUT_FOLDER/`basename $PROJ`.asc"
//  set SCRIPT_NAME="$SCRIPT_FOLDER/`basename $PROJ`.sh"
//  set LAMBDAS="$OUTPUT_FOLDER/${SPP}.lambdas"
//  echo "create and execute scripts for future projectstions $SPP  $PROJ"
//
//  echo "#\!/bin/tcsh" > $SCRIPT_NAME
//  echo "module load java" >> $SCRIPT_NAME
//  echo "java -mx2048m -cp {$maxent} density.Project /home/jc166922/TDH/maxent_model/{$speciesName}/output/{$speciesName}.lambdas $PROJ $PROJ_OUTPUT fadebyclamping nowriteclampgrid nowritemess -x" >> $SCRIPT_NAME
//  echo "rm $SCRIPT_NAME*" >> $SCRIPT_NAME
//
//  qsub -e $SCRIPT_FOLDER -o $SCRIPT_FOLDER  $SCRIPT_NAME
//
//end
//AAA;
//
//

    

}


?>
