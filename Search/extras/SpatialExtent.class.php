<?php

/**
 * Description of SpatialExtent
 *
 * Mutli value item
 * - Spatial Extent of a Map.
 * 
 * @author jc166922
 */

class SpatialExtent extends Object {
    
    
    public static function createFromFilename($filename) 
    {
        if (!file_exists($filename)) return null;
        
        $SE = new SpatialExtent($filename);
        
        return $SE;
    }
    

    private $source = null;
    
    /*
     * Overloads : $north = null,$south = null,$east = null,$west = null
     *             array (4 elements) will be taken as north,south,east,west
     *             keyed array KEYS = north,south,east,west 
     * 
     *             String = Spatial filename 
     * 
     * 
     */
    public function __construct($source = null) {
        parent::__construct();        

        $this->source = $source;
        $this->init();
    }
    
    public function init() 
    {
        
        if (is_null($this->source)) return;
        
        if (is_string($this->source) && file_exists($this->source)) 
            return $this->initByFilename($this->source);
        
        if (is_array($this->source))
        {
            switch (count($this->source)) {

                case 0:
                    return $this->initByNSEW(null,null,null,null);
                    break;
                
                case 1:
                    if (is_string($this->source[0]) && file_exists($this->source[0])) $this->initByFilename($this->source[0]);
                    break;

                case 4:
                    $this->initByArray($this->source);
                    break;

            }
            
        }
        
    }


    private function initByNSEW($north = null,$south = null,$east = null,$west = null) 
    {
        $this->setNSEW($north, $south, $east, $west);
        
    }

    private function initByArray($array) 
    {
        if (count($array) !=4) throw new Exception();   //TODO -- Proper exepction array incorrect 

        if (
            array_key_exists(configuration::NORTH(), $array) &&
            array_key_exists(configuration::SOUTH(), $array) &&
            array_key_exists(configuration::EAST() , $array) &&
            array_key_exists(configuration::WEST() , $array)
           )    
        {
            // all named keys    
            $north = $array[configuration::NORTH()];
            $south = $array[configuration::SOUTH()];
            $east  = $array[configuration::EAST()];
            $west  = $array[configuration::WEST()];
        }
        else
        {
            $north = $array[0];
            $south = $array[1];
            $east  = $array[2];
            $west  = $array[3];
        }
        
        $this->setNSEW($north, $south, $east, $west);
        
    }

    
    private function initByFilename($filename)
    {
        if (!file_exists($filename)) throw new Exception(); //TODO:  Proper Execption - Filename doesn n ott exist

        $this->Filename($filename);
        
        if (spatial_util::isRaster($filename))
            $this->fromRaster($filename);
        else
            $this->fromVector($filename);
        
    }
    
    
    private function fromRaster($filename) 
    {
        // GDALINFO

        $north = null;
        $south = null;
        $east  = null;
        $west  = null;
        
        $result = array();
        $cmd = "gdalinfo {$filename} | grep 'Coordinates:' -A 4";        
        exec($cmd, $result);

        if (!util::contains($result[0],"Corner Coordinates:")) 
                throw new Exception(); //TODO:: Proper Exception - NOT GDAL File


        // Lower Left 
        $ll_raw = $result[2];
        $ll_raw = str_replace("Lower Left  (", "", $ll_raw);
        $ll_raw = str_replace("}", "", $ll_raw);
        $ll_split= explode(",", trim($ll_raw));

        if (count($ll_split) != 2) throw new Exception(); //TODO:: Proper Exception - Can't get Lower Left

        $west  = str_replace(")", "", trim($ll_split[0]));
        $south = str_replace(")", "", trim($ll_split[1]));

        // upper right
        $ur_raw = $result[3];
        $ur_raw = str_replace("Upper Right (", "", $ur_raw);
        $ur_raw = str_replace(")", "", $ur_raw);
                
        $ur_split = explode(",", trim($ur_raw));
        if (count($ur_split) != 2) throw new Exception(); //TODO:: Proper Exception - Can't get Upper Right
        
        $east  = str_replace(")", "", trim($ur_split[0]));
        $north = str_replace(")", "", trim($ur_split[1]));
        
        $this->setNSEW(trim($north), trim($south), trim($east), trim($west));                
                
    }

    private function fromVector($filename) 
    {
        
        $result = array();
        $cmd = "ogrinfo -so  -al {$filename} | grep 'Extent:'";
        exec($cmd, $result);
        
        if (!util::contains($result[0],"Extent:"))
            throw new Exception(); //TODO:: Proper Exception - NOT OGR

        // Extent: (113.156360, -43.642890) - (153.638340, -10.687530)                
        // 
        // OGR can handle this file format
        $raw = $result[0];
        $raw = str_replace("Extent: (", "", $raw);
        $raw = str_replace(") - (", ",", $raw);
        $raw = str_replace(")", "", $raw);
        $raw = str_replace(" ", "", $raw);

        //113.156360,-43.642890,153.638340,-10.687530
        list($west,$south,$east,$north) = explode(",", $raw);
     
        $this->setNSEW(trim($north), trim($south), trim($east), trim($west));                      
        
    }


    public function __destruct() {
        parent::__destruct();
    }

    public function isValid()
    {
        if (is_null($this->North()) ) return false;
        if (is_null($this->South()) ) return false;
        if (is_null($this->East()) )  return false;
        if (is_null($this->West()) )  return false;
        
        if ($this->North() < $this->South()) return false;
        if ($this->West()  < $this->East()) return false;

        if ($this->North() >   90.0) return false;
        if ($this->South() <  -90.0) return false;
        if ($this->East()  >  180.0) return false;
        if ($this->West()  < -180.0) return false;
        
        return true;
    }
    
    
    public function Filename($value = null)
    {
        if (is_null($value)) return $this->getProperty();
        return $this->setProperty($value);
    }
    
    
    public function North($value = null)
    {
        if (is_null($value)) return $this->getProperty();
        return $this->setProperty($value);
    }
    
    public function South($value = null)
    {
        if (is_null($value)) return $this->getProperty();
        return $this->setProperty($value);
    }
    
    public function East($value = null)
    {
        if (is_null($value)) return $this->getProperty();
        return $this->setProperty($value);
    }
    
    public function West($value = null)
    {
        if (is_null($value)) return $this->getProperty();
        return $this->setProperty($value);
    }

    public function setNSEW($north,$south,$east,$west) {
        $this->North($north);
        $this->South($south);
        $this->East($east);
        $this->West($west);
    }
    
    
    public function copy() 
    {
        $result = new SpatialExtent();        
        parent::copy($result);
        return $result;
    }
    
    public function __toString() {
        return parent::asFormattedString("{North} {South} {East} {West}");
        
    }
    
    public static function cast($src)
    {
        $result = $src;
        $result instanceof SpatialExtent;
        return  $result;
    }
    
    
    
}

?>
