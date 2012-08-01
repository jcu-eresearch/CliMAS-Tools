<?php
/**
 * CLASS: Use to "run" for GUI page 
 *        
 * CLass flow
 *  Create /** retrive  MapServer object  = $MS
 *  create Mapserver with $MS
 * 
 */
class MapserverGUI extends Object {
    
    
    public static function create($mapfilepath,$extent_str = "")
    {
        if (!file_exists($mapfilepath)) return; null;
        $M = new MapserverGUI($mapfilepath,$extent_str);
        
        return $M;
    }
    
    
    private $mapfilepath;
    private $mapObject = null;
    
    public function __construct($mapfilepath,$extent_str) {
        parent::__construct();
        $this->mapfilepath = $mapfilepath;
        
        $this->mapObject = ms_newMapObj($mapfilepath);
        
        $this->Extent($this->spatial_extent_from_ms_rect_obj($this->mapObject->extent));    
        
        $this->setSpatialExtentFromString($extent_str);
        
        $this->setPropertyByName("ImageWidth", $this->mapObject->width);
        $this->setPropertyByName("ImageHeight", $this->mapObject->height);

        $this->setPropertyByName("ImageZoom", $this->mapZoom());
        
        $this->setImageXY();
        
    }
    
    public function __destruct() {    
        parent::__destruct();
    }
    
    
    private function spatial_extent_from_ms_rect_obj($e)
    {
        
        $E = new SpatialExtent();
         $E->West($e->minx);
        $E->South($e->miny);
         $E->East($e->maxx);
        $E->North($e->maxy);

        return $E;        
    }
    
    
    //** Interactive methods ---------------------------------------------- 
    
    public function hasInteractive() 
    {
        return (isset($_POST["mapa_x"]) && isset($_POST["mapa_y"])) ;
    }
    
    public function ZoomAndPan() 
    {
        
        $this->setSpatialExtentFromPost();
        
        $this->MapObject()->setextent($this->Extent()->West(),$this->Extent()->South(),$this->Extent()->East(),$this->Extent()->North());

        
        $my_point = ms_newpointObj();
        
        if ($this->ImageZoom() > 0)
            $my_point->setXY($this->ImageX(),$this->ImageY());
        else
        {
            $my_point->setXY($this->PreviousX(),$this->PreviousY());
            $_POST['mapa_x'] = $this->PreviousX();
            $_POST['mapa_y'] = $this->PreviousY();
        }
        
        
        $my_extent = ms_newrectObj();
        $my_extent->setextent($this->Extent()->West(),$this->Extent()->South(),$this->Extent()->East(),$this->Extent()->North());
                
        $this->MapObject()->zoompoint($this->ImageZoom(),$my_point,$this->ImageWidth(),$this->ImageHeight(),$my_extent);
        
        
    }
    
    
    public function setSpatialExtentFromPost()
    {
        $this->setSpatialExtentFromString(array_util::Value($_POST, 'extent'));
    }

    
    public function setSpatialExtentFromString($str)
    {
        
        if (is_null($str)) return;
        
        $str= trim($str);
        if ($str == "") return;
        
        $e = explode(" ",$str);
        
        $E = new SpatialExtent();        
         $E->West($e[0]);
        $E->South($e[1]);
         $E->East($e[2]);
        $E->North($e[3]);
        
        $this->Extent($E);
        
    }
    
    
    private function setImageXY()
    {
        $x = (array_key_exists("mapa_x", $_POST)) ? $_POST["mapa_x"] : ($this->ImageWidth() / 2);
        $y = (array_key_exists("mapa_y", $_POST)) ? $_POST["mapa_y"] : ($this->ImageHeight() / 2);
        $this->setPropertyByName("ImageX", $x);
        $this->setPropertyByName("ImageY", $y);
        
        $pre_x = (array_key_exists("map_x", $_POST)) ? $_POST["map_x"] : ($this->ImageWidth() / 2);
        $pre_y = (array_key_exists("map_y", $_POST)) ? $_POST["map_y"] : ($this->ImageHeight() / 2);
        $this->setPropertyByName("PreviousX", $pre_x);
        $this->setPropertyByName("PreviousY", $pre_y);
        
        
    }
    

    private function mapZoom()
    {
        $zoom = (array_key_exists("ZoomFactor", $_POST)) ? $_POST["ZoomFactor"] : 0;
        return $zoom;
    }
    
    
    //** Interactive methods ----------------------------------------------     
    
    
    public function Extent() {
        if (func_num_args() == 0) return SpatialExtent::cast($this->getProperty());
        return SpatialExtent::cast($this->setProperty(func_get_arg(0)));
    }
    
    public function ExtentString()
    {
        $extent_to_html = $this->MapObject()->extent->minx." ".$this->MapObject()->extent->miny." ".$this->MapObject()->extent->maxx." ".$this->MapObject()->extent->maxy;
        return $extent_to_html;
    }

    public function ImageWidth() 
    { 
        if (func_num_args() == 0) return $this->getProperty(); 
        if (is_null(func_get_arg(0))) return;
        return $this->setProperty(func_get_arg(0));
    }
    
    public function ImageHeight() 
    {    
        if (func_num_args() == 0) return $this->getProperty(); 
        if (is_null(func_get_arg(0))) return;
        return $this->setProperty(func_get_arg(0));
    }

    public function ImageX() { return $this->getProperty(); }
    
    public function ImageY() { return $this->getProperty();}

    public function PreviousX() { return $this->getProperty();}
    
    public function PreviousY() { return $this->getProperty();}
    
    public function ImageZoom() { return $this->getProperty();}

    
    public function MapObject() { return $this->mapObject; }

    /***
     * @param BOOL RELOAD   True = reload image  False = return current image URL /** or NULL
     * 
     */
    public function MapImageLocation($reload = false) 
    {        
        if ($reload) $this->setPropertyByName("MapImageLocation", null); // if they want to reloadf then clear current value
        
        $value = $this->getProperty();
        if (!is_null($value)) return $this->getProperty();
        
        // the current value is NULL so we have to load anyway
        $image = $this->mapObject->draw();
        $image_url = $image->saveWebImage();                
        $this->setPropertyByName("MapImageLocation", $image_url);
        return $image_url;
    }

    public function MapLegendLocation($reload = false) 
    {        
        if ($reload) $this->setPropertyByName("MapLegendLocation", null); //** if they want to reloadf then clear current value

        $value = $this->getProperty();

        if (!is_null($value)) return $this->getProperty();
        
        $legend_obj = $this->mapObject->drawLegend();
        
        $legend_img = $legend_obj->saveWebImage();
        
        $this->setPropertyByName("MapLegendLocation", $legend_img);
        
         return $legend_img;
    }

    public function MapScaleLocation($reload = false) 
    {        
        if ($reload) $this->setPropertyByName("MapScaleLocation", null); //** if they want to reloadf then clear current value
        
        $value = $this->getProperty();
        if (!is_null($value)) return $this->getProperty();
        
        $scale_obj = $this->mapObject->drawScaleBar();
        $scale_img = $scale_obj->saveWebImage();
        $this->setPropertyByName("MapScaleLocation", $scale_img);
        return $scale_img;
    }
    


    
}
?>