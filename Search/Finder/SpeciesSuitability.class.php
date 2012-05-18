<?php
/**
 *
 * @todo  Look at the Species Range output folders and pull out Models, Scenarios and Years
 * Threshold value of wich we don't want to see  i e set to transparent
 *
 * Filename            Column Name
 * maxentResults.csv - "Equate entropy of thresholded and original distributions logistic threshold"
 *   
 */
class SpeciesSuitabilityFinder extends aFinder {
    
    private $file_tree = null;
    
    public function __construct() { 
        parent::__construct();
        $this->Name(__CLASS__);
        
    }
    
    public function __destruct() {
        parent::__destruct();
    }
    

    /**
     *
     */
    public function Find() 
    {
        $path = $this->ParentFolder()."/".$this->Species();
        
        $this->file_tree = file::file_tree($path);
        
        if (!is_null($this->LimitModel()))
            $this->file_tree = file::arrayFilter($this->file_tree, $this->LimitModel());
        
        if (!is_null($this->LimitScenario()))
            $this->file_tree = file::arrayFilter($this->file_tree, $this->LimitScenario());
        
        $this->getScenarioNames() ;
        $this->getModelNames();
        $this->getModelScenarioMatrix();
        
    }

    
    
    private $result = null;
    /**
     * Called with (null) return value of property<br>
     * Called with ($arg)    set value of property to $arg<br>
     *
     * @return mixed current property value
     *
     */
    public function Result() 
    {
        if (!is_null($this->result)) return $this->result;
        $this->result = $this->ScenarioModelMatrix();
        return $this->result;
    }

    /**
     *
     */
    public function ClearResult() 
    {
        $this->result = null;
    }
    

    /**
     *
     */
    private function getModelScenarioMatrix()
    {
        
        $matrix = array();
        
        foreach ($this->Models() as $modelName => $modelDesc) 
        {
            
            foreach ($this->Scenarios() as $scenarioName => $scenarioDesc) 
            {
                $k = "{$scenarioName}.{$modelName}";
                $f = array_util::ElementsThatContain($this->file_tree, $k);
                $f = file::arrayFilter($f, "run1.run1");
                
                if (count($f) > 0 )
                {     
                    ksort($f);
                    $matrix[$modelName][$scenarioName] = $f;
                }
                    
            }
        }
        
        $this->setPropertyByName('ScenarioModelMatrix', $matrix);
        
    }
    
    
    /**
     * 
     * @see http://www.ipcc-data.org/ddc_gcm_intro.html
     *
     * @see http://www-pcmdi.llnl.gov/ipcc/model_documentation/ipcc_model_documentation.php
     *
     */
    private function getModelNames() 
    {
        $files = file::arrayFilter($this->file_tree, "output");
        $files = file::arrayFilter($files, "asc");
        $files = file::arrayFilterOut($files, "aux");
        $files = file::arrayFilterOut($files, "xml");
        
        $files = array_util::Replace($files, $this->ParentFolder()."/", "");
        $files = array_util::Replace($files, "output/","");
        
        
        $raw_names = util::midStr($files, ".", ".", true);
        $raw_names = array_flip($raw_names);
        $raw_names = array_flip(array_keys($raw_names));
        
        unset($raw_names['asc']);

        
        // here we should link to "Descriptive Data Objects"
        foreach ($raw_names as $key => $value) 
        {
            switch ($key) {

                case "miroc3_2_medres":
                    $raw_names[$key] = "MIROC3.2 (Model for Interdisciplinary Research on Climate) - medium resolution";
                    break;

                case "miroc3_2_hires":
                    $raw_names[$key] = "MIROC3.2 (Model for Interdisciplinary Research on Climate) - high resolution";;
                    break;
                
                case "giss_aom":
                    $raw_names[$key] = "NASA Goddard Institute for Space Studies (NASA/GISS), USA";
                    break;
                
                case "ncar_ccsm3_0":
                    $raw_names[$key] = "National Center for Atmospheric Research (NCAR), Community Climate System Model, version 3.0 (CCSM3)";
                    break;
                
                case "csiro_mk3_5":
                    $raw_names[$key] = "CSIRO Mark 3.5";
                    break;
                
                case "csiro_mk3_0":
                    $raw_names[$key] = "CSIRO Mark 3.0";
                    break;
                
                case "inmcm3_0":
                    $raw_names[$key] = "INMCM3.0 Institute of Numerical Mathematics, Russian Academy of Science, Russia.";
                    break;
                
                case "bccr_bcm2_0":
                    $raw_names[$key] = "Bjerknes Centre for Climate Research (BCCR), Univ. of Bergen, Norway, Bergen Climate Model (BCM) Version 2";
                    break;
    
                
                
                default:
                    break;
            }
        }
        
        $this->setPropertyByName("Models", $raw_names);
        
        
    }

    /**
     *
     */
    private function getScenarioNames() 
    {        
        $files = file::arrayFilter($this->file_tree, "output");
        $files = file::arrayFilter($files, "asc");
        $files = file::arrayFilterOut($files, "aux");
        $files = file::arrayFilterOut($files, "xml");
        
        $files = array_util::Replace($files, $this->ParentFolder()."/", "");
        $files = array_util::Replace($files, "output/","");
        
        
        $raw_names = util::leftStr($files, ".", false);
        $raw_names = array_flip($raw_names);
        $raw_names = array_flip(array_keys($raw_names));
        
        foreach ($raw_names as $key => $value) 
        {
            switch ($key) {
                case "1975":
                    $raw_names[$key] = "Current";
                    break;

                case "sresa2":
                    $raw_names[$key] = "Special Report on Emissions Scenarios A2";
                    break;

                case "sresb1":
                    $raw_names[$key] = "Special Report on Emissions Scenarios B1";
                    break;

                case "sresab1":
                    $raw_names[$key] = "Special Report on Emissions Scenarios AB1 ??";
                    break;
                
                default:
                    break;
            }
        }
        
        
        
        
        $this->setPropertyByName("Scenarios", $raw_names);
        
        
    }

    
    /**
     *
     * Use to limit the file list to only files that contain this model name
     *
     * Called with (null) return value of property<br>
     * Called with ($arg)    set value of property to $arg<br>
     *
     * @return mixed current property value
     *
     */
    public function Species() {
        if (func_num_args() == 0) return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }


    /**
     *
     * Use to limit the file list to only files that contain this model name
     *
     * Called with (null) return value of property<br>
     * Called with ($arg)    set value of property to $arg<br>
     *
     * @return mixed current property value
     *
     */
    public function LimitModel() {
        if (func_num_args() == 0) return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }

    /**
     * Use to limit the file list to only files that contain this scenario name
     *
     * Called with (null) return value of property<br>
     * Called with ($arg)    set value of property to $arg<br>
     *
     * @return mixed current property value
     *
     */
    public function LimitScenario() {
        if (func_num_args() == 0) return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }
    
    
    /**
     * Called with (null) return value of property<br>
     * Called with ($arg)    set value of property to $arg<br>
     *
     * @return mixed current property value
     *
     */
    public function Models() {
        if (func_num_args() == 0) return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }

    /**
     * Called with (null) return value of property<br>
     * Called with ($arg)    set value of property to $arg<br>
     *
     * @return mixed current property value
     *
     */
    public function Scenarios() {
        if (func_num_args() == 0) return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }

    /**
     * Called with (null) return value of property<br>
     * Called with ($arg)    set value of property to $arg<br>
     *
     * @return mixed current property value
     *
     */
    public function ScenarioModelMatrix() {
        if (func_num_args() == 0) return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }
    
    
    /**
     * Called with (null) return value of property<br>
     * Called with ($arg)    set value of property to $arg<br>
     *
     * @return mixed current property value
     *
     */
    public function ParentFolder() {
        if (func_num_args() == 0) return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }

    /**
     * Called with (null) return value of property<br>
     * Called with ($arg)    set value of property to $arg<br>
     *
     * @return mixed current property value
     *
     */
    public function Filter($name,$value) 
    {
        
    }
    
    
    
    
    
}

?>
