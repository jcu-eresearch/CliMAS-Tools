<?php

class PackageDatafilesCommand extends CommandAction {
    //put your code here


    public function __construct() {
        parent::__construct();
        $this->ActionName("PackageDatafilesCOmmand");
        $this->Description("Package Scenario and Model Bioclim Datafiles");

    }

    public function __destruct() {
        parent::__destruct();
    }


    /**
     * will be called by HPC to start
     *
     * @return string
     */
    public function Execute() 
    {

        // here we will take the the paraemeters and package up the files.
        $this->Status("Started something here ");
        CommandUtil::PutCommandToFile($this);

        $result = $this->zipFiles();  // add zip files from bioclim folders
        
        $this->Result($result);

        $this->Status("Archiving completed");
        $this->ExecutionFlag(Command::$EXECUTION_FLAG_FINALISE);
        CommandUtil::PutCommandToFile($this);

        return $result;


    }
    

    /**
     * Names of files to be zipped
     *
     * @param type $zipfilename
     * @param type $scenarios
     * @param type $models
     * @param type $times
     * @return type
     */
    private function zipFiles()
    {
        $result = array();

        $result["EmissionScenario"] = $this->EmissionScenarioIDs();
        $result["ClimateModel"]     = $this->ClimateModelIDs();
        $result["TimeIDs"] = $this->TimeIDs();

        $archiveFilename  = CommandConfiguration::CommandOutputsFolder();
        $archiveFilename .= CommandConfiguration::ApplicationName()."-ClimateData";
        $archiveFilename .= "-".str_replace(" ","_",$result["EmissionScenario"])."";
        $archiveFilename .= "-".str_replace(" ","_",$result["ClimateModel"])."";
        $archiveFilename .= "-".str_replace(" ","_",$result["TimeIDs"])."";
        $archiveFilename .= ".zip";

        if (!file_exists($archiveFilename))
        {
            foreach (explode(" ",$this->EmissionScenarioIDs()) as $scenario)
                foreach (explode(" ",$this->ClimateModelIDs()) as $model)
                    foreach (explode(" ",$this->TimeIDs()) as $time)
                    {
                        $folder = "{$scenario}_{$model}_{$time}".CommandConfiguration::osPathDelimiter()."*.gz";

                        $toStore = $folder;

                        $cmd  = "pushd ".PackageDatafilesConfiguration::$PROJECTCLIMATE_ASC."; ";
                        $cmd .= "zip -0 $archiveFilename {$toStore}".";";
                        $cmd .= "popd";

                        exec("{$cmd}"); // add files to archive

                        $this->Status("Added files from {$folder}");
                        CommandUtil::PutCommandToFile($this);
                    }

        }

        $this->PackageFilename(str_replace(CommandConfiguration::CommandOutputsFolder(), "", $archiveFilename));

        return $result;
    }


    public function EmissionScenarioIDs() {
        if (func_num_args() == 0) return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }

    public function ClimateModelIDs() {
        if (func_num_args() == 0) return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }

    public function TimeIDs() {
        if (func_num_args() == 0) return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }

    /**
     * Filename of package output - Filename only no folder as this will be different on the webserver
     *
     * @return string filename of package file
     */
    public function PackageFilename() {
        if (func_num_args() == 0) return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }




}


?>
