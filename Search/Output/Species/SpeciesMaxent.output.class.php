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
        
       $result = OutputFactory::Find($this->speciesMaxent());
        
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
            if (!file_exists($localCombintationFilename))
            {
                $mapImage =   configuration::IconSource()."wait.gif";
            }
            else
            {
                $mapImage =   MapServerImage::FromFile($localCombintationFilename);     // this is a calculated grid file
            }
            
            
            return '<img class="probabilityMap" src="'.$mapImage.'">';
        }
        
        
        //$vis
        
    }
    
    
    public function PreProcess()
    {
        
        $this->maxentResults = $this->speciesMaxent()->Result();
        
    }


}


?>

