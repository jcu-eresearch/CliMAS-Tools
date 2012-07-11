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
        
        $result = "";
        
        $images = array();
        
        $server_results = $this->speciesMaxent()->Result();
        foreach ($server_results as $speciesID => $combinations) 
        {
            $images[$speciesID] = array();
            
            foreach ($combinations as $combination => $file_id) 
            {
                
                if (!is_null($file_id))
                {
                    $image_url = configuration::ApplicationFolderWeb()."Search/file.php?id={$file_id}";
                    $images[$speciesID][$combination] =  '<li id="out'.$speciesID.':'.$combination.'" class="ui-widget-content SpeciesRangeImageContainer"><img class="SpeciesRangeImage" src="'.$image_url.'" /></li>';
                }
                
            }
                        
        }
        
        // compare how many images we have with how many we should have and give percent done
        
        foreach ($server_results as $speciesID => $combinations) 
        {
            $items_required = count($combinations);
            $items_have = count($images[$speciesID]);
            
            $result .= $this->progress($speciesID,($items_have/$items_required) * 100);
            
            if ($items_required == $items_have)
                foreach ($combinations as $combination => $file_id) {
                    $result .= $images[$speciesID][$combination];
                }
            
        }
        
        
       return $result;

    }

    
    private function progress($speciesID,$percent)
    {
        
        $name = SpeciesData::SpeciesQuickInformation($speciesID);
        
$script = <<<SCRIPT
<li class="ui-widget-content" style="clear: both; float: none;">
<div id="progressbar__name_$speciesID">$name</div><div id="progressbar_$speciesID"></div>
<script>
	$(function() {
		$( "#progressbar_{$speciesID}" ).progressbar({
			value: {$percent}
		});
	});
</script>
</li>
SCRIPT;
       
    
        return $script;
                        
        
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

