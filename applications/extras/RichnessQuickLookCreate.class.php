<?php
/**
 * CLASS: RichnessQuickLookCreate
 *        
 * Assume that file list is a list 
 *   
 */
class RichnessQuickLookCreate extends Object {
    //**put your code here
    
    public function __construct($filelist = null) { 
        parent::__construct();
        $this->ImageTitle("Richness");
        $this->Recreate(false);  
        
        if (!is_null($filelist)) $this->SourceFileList($filelist);
        
    }
    
    public function __destruct() {    
        parent::__destruct();
    }

    public function execute() 
    {    
        if (is_null($this->SourceFileList())) return;        
        
        ErrorMessage::Marker("UN GZ files if required");
        $this->uncompressed_filelist();
        print_r($this->UncompressedFileList());
        
        
        ErrorMessage::Marker("Get Min and Max for this file set");
        $this->getMinMax();
        
        echo "MIN = ".$this->Min()."\n";
        echo "MAX = ".$this->Max()."\n";
        
        
        $this->create_quicklooks();   // from  $this->UncompressedFileList();
        
        
        ErrorMessage::Marker("remove .asc (make sure .GZ is there)");
        
        
    }
    
    
    
    /**
     * Use source files list - assuming that if we remove .gz from filename that will giive proper output name
     *  
     */
    private function uncompressed_filelist() 
    {

        $uncompressed = array();
        
        foreach ($this->SourceFileList() as $pathname) 
        {
            
            if (!util::contains($pathname, 'gz'))
            {
                // assume not compressed
                $uncompressed[basename($pathname)] = $pathname;
            }
            else
            {
                $uncompressed_filename = str_replace('.gz', '', $pathname);
                if (!file_exists($uncompressed_filename))
                {
                    //ErrorMessage::Marker("uncompress {$pathname} to {$uncompressed_filename}");
                    
                    $cmd = "gunzip -c {$pathname} >  {$uncompressed_filename}";
                    
                    //ErrorMessage::Marker("cmd = [{$cmd}]");
                    
                    exec($cmd);                    
                    $uncompressed[basename($uncompressed_filename)] = $uncompressed_filename;
                }
                else
                {
                    $uncompressed[basename($uncompressed_filename)] = $uncompressed_filename;
                }
            }
            
        }

        $this->UncompressedFileList($uncompressed);
        
    }
    
    
    private function getMinMax() 
    {
        $stats = spatial_util::ArrayRasterStatistics($this->UncompressedFileList(), 1, false, true);
        
        $maxs = matrix::ColumnMaximum($stats);
        $mins = matrix::ColumnMinimum($stats);
        
        $this->Min($mins[spatial_util::$STAT_MINIMUM]);
        $this->Max($maxs[spatial_util::$STAT_MAXIMUM]);
        
    }
    

    private function create_quicklooks() 
    {
        
        
        foreach ($this->UncompressedFileList()  as $basename => $pathname) 
        {
            
            $output_image_filename = str_replace(".asc", ".png", $pathname);            

            $simple_title = $this->ImageTitle().':: '.str_replace(".asc", "", $basename);
            
            
            if ($this->Recreate())
                file::Delete($output_image_filename);
                                      
            // ErrorMessage::Marker("Created quicklook [{$output_image_filename}]  {$this->Min()}  {$this->Max()}");
            
            $output_image_filename 
                = spatial_util::CreateImage($pathname
                                           ,$output_image_filename
                                           ,null
                                           ,null
                                           ,$this->Max()
                                           ,RGB::ReverseGradient(RGB::GradientBlueGreenRed())
                                           ,$this->Min()
                                           ,$this->Max()
                                           ,$simple_title
                                           );
            
            if (is_null($output_image_filename))
            {
                ErrorMessage::Marker("FAILED:: Could not create Quick oook for [{$pathname}]");
            }
            
            ErrorMessage::Marker("Created quicklook [{$output_image_filename}]");
            
        }
        
    }

    
    private function create_single_quicklook() 
    {
        
    }
    
    
    
    
    
    private function remove_uncompress() 
    {
        
    }
    
    
    
    public function SourceFileList() {
        if (func_num_args() == 0) return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }

    public function UncompressedFileList() {
        if (func_num_args() == 0) return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }
    
    
    public function Min() {
        if (func_num_args() == 0) return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }

    public function Max() {
        if (func_num_args() == 0) return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }
    
    public function ImageTitle() {
        if (func_num_args() == 0) return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }
    
    public function Recreate() {
        if (func_num_args() == 0) return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }
    
}

?>
