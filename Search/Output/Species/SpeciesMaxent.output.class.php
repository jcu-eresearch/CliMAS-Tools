<?php

/**
 * 
 *
 * @author Adam Fakes (James Cook University)
 */
class SpeciesMaxentOutput extends Output
{

    private $maxentResults = null;


    public function __construct() {
        parent::__construct();
        $this->OutputName(__CLASS__);

    }

    public function __destruct() {
        parent::__destruct();

    }

    private function speciesMaxent()
    {
        $result = $this->Source();
        $result  instanceof SpeciesMaxent;
        return  $result;
    }


    public function Title()
    {
        return configuration::ApplicationName()."::SpeciesMaxent";
    }


    public function Head()
    {
        $result = "";
        
        $result .= htmlutil::includeLocalHeadCodeFromPathPrefix(file::currentScriptFolder(__FILE__),"Species",configuration::osPathDelimiter(),configuration::osExtensionDelimiter());
        
        return $result;
    }

    public function Content()
    {

        // $this->maxentResults - holds array of .asc grids to be display to the user
        // we now have them to process other functions as well.

        // this is where we build an output for for the user 
        // table of images ?
        
        
        $result = array();
        
        if (!is_array($this->maxentResults))
        {
            $result = "Waiting for server Grid<br>"; 
        }
        else
        {

            foreach ($this->maxentResults as $speciesID => $combintations) 
            {

                foreach ($combintations as $combintation => $combintationFilename) 
                {

                    if (substr($combintationFilename,0,1) == configuration::osPathDelimiter())
                    {
                        $localCombintationFilename = configuration::Maxent_Species_Data_folder().$combintationFilename;
                        
                        $localCombintationFilename = str_replace(configuration::osPathDelimiter().configuration::osPathDelimiter(),configuration::osPathDelimiter() , $localCombintationFilename);
                        

                        if ($combintationFilename == "")
                        {
                            $result[$speciesID][$combintation] = "Calculating ......";
                        }
                        else
                        {
                            
                            $vis = $this->getVisualVersion($speciesID,$combintation,$localCombintationFilename);
                            
                            $result[$speciesID][$combintation] = $vis;
                        }
                        
                    }
                    else
                    {
                        $result[$speciesID][$combintation] = $combintationFilename;
                    }


                }

            }

            $result = OutputFactory::Find($result) ;     
            
        }
        
        
       

       return $result;

    }

    private function getVisualVersion($speciesID,$combintation,$localCombintationFilename) 
    {
        
        
        if ( util::endsWith(strtolower($localCombintationFilename), "html") )
        {
            $shortName = util::fromLastSlash($localCombintationFilename);

            $srcfolder = util::toLastSlash($localCombintationFilename);
            
            // copy to output so we can look / make accessible and make downloadabale
            
            $dest_folder = configuration::FilesDownloadFolder().$speciesID.  configuration::osPathDelimiter();
            
            file::mkdir_safe($dest_folder);
            file::mkdir_safe($dest_folder."/plots");
            
            $dest = $dest_folder.$shortName;
            
            file::copy($localCombintationFilename, $dest,true);
            
            // copy all source plots to dest plots
            $cmd  = "cp  {$srcfolder}/plots/*  '{$dest_folder}plots/' ";
            
            exec($cmd);
            
            $webLink = configuration::WebDownloadFolder().$speciesID.  configuration::osPathDelimiter().$shortName;
            
            return '<a target="_data" href="'.$webLink.'">'.$shortName.'</a>';
        }
        
        
        if ( util::endsWith(strtolower($localCombintationFilename), "asc") )
        {
            // this is a calculated grid file
            $mapImage =   MapServerImage::FromFile($localCombintationFilename);
            return '<div class="probabilityMapContainer"><img class="probabilityMap" src="'.$mapImage.'"></div>';
        }
        
        
        
        //$vis
        
        
    }
    
    
    public function PreProcess()
    {
        
        $this->maxentResults = $this->speciesMaxent()->Result();
        
    }


}


?>

