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
        
        if (!is_array($this->maxentResults)) return "Waiting for a response from the GRID"; 

        $result = array();
        
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
                        // echo "$localCombintationFilename<br>";
                        
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
        
        
        $r = '<table width="100%" border="0">';

        
        
        foreach ($result as $speciesID => $combintations) 
        {


            $fk = util::first_key($combintations);
            $fe = util::first_element($combintations);
            
            $r .= "\n".'<tr>';
            $r .= "\n".'<td colspan="'.count($combintations).'">';
            $r .= SpeciesData::SpeciesQuickInformation(str_replace("_"," ",$speciesID));
            $r .= "<br>".$fe;
            $r .= '</td>';
            $r .= "\n".'</tr>';
            
            
            $r .= "\n".'<tr>';
            
            foreach ($combintations as $combintation => $visualElement) 
            {
                
                if ($combintation == $fk) continue;
                
                $smt = explode("_",$combintation);
                
                $info = "";
                if (count($smt) == 3)
                {
                    
                    $info .= "<br>time: ".$smt[2];
                    $info .= "<br>scenario: ".$smt[0];
                    $info .= "<br>model: ".$smt[1];
                }
                
                $r .= "\n".'<td>';
                $r .= $visualElement;
                $r .= '<div class="info">'.$info.'</div>' ;
                $r .= '</td>';
                
            }
            
            $r .= "\n".'</tr>';
            
        }
       $r .= "\n".'</table>';

       return $r;

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

