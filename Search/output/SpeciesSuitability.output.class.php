<?php
include_once 'anOutput.class.php';

class SpeciesSuitability extends anOutput{
    
    public function __construct() {
        parent::__construct();
        
    }
    
    public function __destruct() {
        
    }
    
    private $finder = null;
    public function Finder(DataFinder $src) 
    {
        $this->finder = $src;
        $src->Find();

        $title = util::string($this->finder->LimitModel()) . " / " . util::string($this->finder->LimitScenario());
        
        $this->setPropertyByName("Title", "Climate Summary:: {$title}");
        
    }
    
    public function Title()
    {
        return $this->getProperty();
    }
    
    
    public function Layout() 
    {
        $this->finder instanceof ClimateDataFinder;
        
        $matrix = $this->finder->Result();
        
        $modelOutputFilenames = $matrix[$this->finder->LimitModel()][$this->finder->LimitScenario()];
        
        $result = array();
        $map_images = array();
        foreach ($modelOutputFilenames as $modelOutputFilename) 
        {
            $imageFilename = MapServerImage::RasterOnVector($this->SpatialBackground(), NULL, $modelOutputFilename,$this->Extent());
            $map_images[] = "/www".$imageFilename;
            
            if (!is_null($imageFilename))
                $result[] = htmlutil::img($imageFilename );
        }

        
        // $animate_output = $this->Animate($map_images);
        
        //$layout  =  htmlutil::href($animate_output, htmlutil::img($animate_output), "_NEW") ;
        $layout = join("\n", $result);
        
        $this->setPropertyByName("Result", $layout );
        
    }
    
    
    public function Animate($filenames)
    {

        $outputFilename = "/www/test/test.gif";
        
        $cmd  = "convert -delay 50 -dispose Background ";
        
        $pages = join("' '",$filenames);
        
        $cmd .= "'".$pages."'";
        
        $cmd .= " '{$outputFilename}'";

        exec($cmd);
        if (!file_exists($outputFilename)) return null;
        
        return str_replace("/www", "", $outputFilename) ;
        
    }
    
    
    
    public function Result() 
    {
        return $this->getProperty();
    }
    
    /*
     * Filepath to Spatial background - gives context to foreground data
     */
    public function SpatialBackground() {
        if (func_num_args() == 0) return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }

    public function Extent() 
    {        
        if (func_num_args() == 0) return SpatialExtent::cast($this->getProperty());
        return SpatialExtent::cast($this->setProperty(func_get_arg(0)));
    }

    public function Source() 
    {
        
    }
    
    
    
}

?>
