<?php
/**
 * Description of Mapfile
 *
 * @author Adam Fakes (James Cook University)
 */
class Mapfile extends Object{

    public static function save(MapServerWrapper $src)
    {
        $MF = new Mapfile($src);
        return $MF->write();
    }

    public static function create(MapServerWrapper $src)
    {
        $MF = new Mapfile($src);
        return $MF;
    }
    
    private $source_wrapper = null;
    
    public function __construct(MapServerWrapper $src) { 
        parent::__construct();
        $this->source_wrapper = $src;
        $this->Extent($this->Wrapper()->Extent());
    }
    
    public function __destruct() {    
        parent::__destruct();
        // TODO:: might be a good spot to remove mapfile ?
        
    }
    
    private function Wrapper()
    {
        $this->source_wrapper instanceof MapServerWrapper;
        return $this->source_wrapper;   
    }
    
    public function Extent() {        
        if (func_num_args() == 0)  return SpatialExtent::cast($this->getProperty());
        return SpatialExtent::cast($this->setProperty(func_get_arg(0)));
    }
    
    public function write() 
    {
        file::reallyDelete($this->Wrapper()->MapfilePathname());
        file_put_contents($this->Wrapper()->MapfilePathname(), $this->Text());
        if (!file_exists($this->Wrapper()->MapfilePathname())) return "";
        return $this->Wrapper()->MapfilePathname();
    }

    
    private function extent_string()
    {
        $result = $this->Extent()->asFormattedString(MapServerConfiguration::CoordinatesFormat());
        return $result;
    }
    
    private function size() 
    {
        return $this->Wrapper()->OutputImageWidth()." ".$this->Wrapper()->OutputImageHeight();
    }
    
    private function shapepath() 
    {
        return $this->Wrapper()->Shapepath();
    }
    
    private function image_color() 
    {
        
        return $this->Wrapper()->OutputImageBackgroundColour()->asFormattedString(MapServerConfiguration::ColourFormat());
    }

    private function fontset() 
    {
        return MapServerConfiguration::pathToMapFonts();
    }
    
    private function symbolset() 
    {
        return MapServerConfiguration::pathToMapSymbols();
    }

    private function imagePath() 
    {
        return MapServerConfiguration::pathToImages();
    }

    private function imageURL() 
    {
        return MapServerConfiguration::pathToImagesWeb();
    }
    
    
    private function layers() 
    {
        
        $result = "";
        foreach ($this->Wrapper()->Layers()->Layers() as $layer_name => $layer) 
        {
            $layer instanceof MapServerLayer;
            $result .= "\n\t".$this->layer($layer);
        }
        
        return $result;
        
    }
    
    
    private function layer(MapServerLayer $layer) 
    {
        
        $output = "";
        
        if ($layer instanceof MapServerLayerRaster)
            $output = $this->layer_raster($layer);
        
        if ($layer instanceof MapServerLayerVector)
            $output = $this->layer_vector($layer);
        
        return $output;
    
    }
    
    
    private function raster_color_classes(MapServerLayerRaster $layer)
    {

        $result = "";
        
        if (is_null($layer->ColorTable())) return "";
        
        $rgbs = array_values($layer->ColorTable());
        $values = array_keys($layer->ColorTable());
        
        // need to be able to calculate the "Value" translation to the colour indexs
        
        $min = $layer->Minimum();
        $max = $layer->Maximum();
        
        $range = $max - $min;
        
        $step = $range / $layer->HistogramBuckets();
        
        for ($index = 1; $index < count($values); $index++) 
        {
            
            $proper_value1 = (($index - 1) * $step) + $min;
            $proper_value2 = ( $index      * $step) + $min;
            
            
            $value1 = number_format( $proper_value1, 5, '.', '' );
            $value2 = number_format( $proper_value2, 5, '.', '' );
            
            $rgb = $rgbs[$index - 1]; 
            if (is_null($rgb)) $rgb = "-1 -1 -1"; // make null transparent
            
            $expression_name = "";
            if ($layer->ColorUniqueValues())
            {
                $expression_name = "NAME \"{$value1}\"";
                $expression = "EXPRESSION ([pixel] = {$value1})";
            }
            else
            {
                $expression_name = "NAME \"{$value1} .. {$value2}\"";
                $expression = "EXPRESSION ([pixel] >= {$value1} and [pixel] < {$value2})";    
            }
            
            
$c = <<<CLASS
        CLASS
            {$expression_name}
            {$expression}
            STYLE
              COLOR {$rgb}
            END
        END
CLASS;
        
              
              $result .= "\n".$c;
              
        }

        return $result;
        
    }
    

    private function raster_color_classes_NullValue(MapServerLayerRaster $layer)
    {
        
        
    }
    
    
    private function layer_raster(MapServerLayerRaster $layer)
    {
        
        $min = sprintf("%01.9f", $layer->Minimum());
        $max = sprintf("%01.9f", $layer->Maximum());
        
        
$r = <<<R
    LAYER 
        NAME         "{$layer->LayerName()}"
        DATA         "{$layer->Filename()}"
        STATUS       {$layer->Status()}
        TYPE         {$layer->LayerType()}
        
        PROCESSING "SCALE={$min},{$max}"
        PROCESSING "SCALE_BUCKETS={$layer->HistogramBuckets()}"
        
        PROCESSING   "BANDS=1"
        OFFSITE      -1 -1 -1

        CLASSITEM "[pixel]"
        
        {$this->raster_color_classes($layer)}
        
        
        
    END # end raster layer - {$layer->LayerName()}
R;
        
        return $r;
    }
    
    private function layer_vector(MapServerLayerVector $layer)
    {
        
        $classes = $this->classes($layer->Classes());

        $classItem = "";
        if (!(is_null($layer->ClassItem()) || $layer->ClassItem() == ""))
            $classItem = "CLASSITEM    '{$layer->ClassItem()}'";    
        
        $labelItem = "";
        if (!(is_null($layer->LabelItem()) || $layer->LabelItem() == ""))
            $labelItem = "LABELITEM    '{$layer->LabelItem()}'";    
        
$r = <<<R
   
    LAYER 
        NAME   "{$layer->LayerName()}"
        DATA   "{$layer->Filename()}"
        STATUS {$layer->Status()}
        TYPE   {$layer->LayerType()}
        {$classItem}
        {$labelItem}
        {$classes}
    END # end layer {$layer->LayerName()}
R;
        
        
        return $r;
    }

    
    private function classes(MapServerLayerClasses $src)
    {
        $result = "";
        foreach ($src->ClassNames() as $name) 
            $result .= "\n".$this->class_text($src->ByName($name));
        
        return $result;
        
    }
    
    
    private function class_text(MapServerLayerClass $src)
    {
        
$r = <<<OUTPUT
        CLASS
            NAME        "{$src->ClassName()}"
            ## EXPRESSION  "{$src->Expression()}"
            STYLE
                {$this->style_text($src->Style())}
            END
            LABEL
                {$this->label_text($src->Label())}
            END
        END # end class {$src->ClassName()}
OUTPUT;
            
        return $r;
            
    }


    private function caption()
    {
        $caption = $this->Wrapper()->Caption();
        if (is_null($caption)) return "";
        
        //** get the left most edge of image /** coordinate
        $point = $this->Extent()->asFormattedString("{West} {South}");
        
        //**TODO:: Add support for Caption for Each layer
        
        $text = $caption;
        $point_size = 12;
        $colour = "0 0 0";    //** TODO:: Default Caption Colour
        
        if (VisualText::isVisualText($caption))
        {
            $caption instanceof VisualText;
            $text = $caption->Text();
            $point_size = $caption->PointSize();
            $colour = $caption->Colour()->asFormattedString(MapServerConfiguration::ColourFormat());
        }
        
        
$r = <<<OUTPUT
   
    LAYER
        NAME   MAP_CAPTION
        TYPE   POINT
        STATUS DEFAULT
        FEATURE
            POINTS
                {$point}
            END
            TEXT "{$text}"
        END
        CLASS
            STYLE
                COLOR 0 0 250
                SYMBOL 'circle'
                SIZE 0
            END
            LABEL
                COLOR		{$colour}
                SHADOWCOLOR	0 0 0
                SHADOWSIZE	0 0
                TYPE		TRUETYPE
                FONT		arial
                SIZE		{$point_size}
                ANTIALIAS	TRUE
                POSITION	ur
                PARTIALS	TRUE
                MINDISTANCE	100
                BUFFER		4     
            END
        
        END    
    END

OUTPUT;
            
        return $r;
            
    }
    
    
    private function style_text(MapServerLayerClassStyle $src)
    {

        //**TODO: Better to setup some soirt of Array that can return only "Value" properties
        $result = "";        
        $result .= "\n\t\t".$src->asFormattedString("COLOR {Color}");
        $result .= "\n\t\t".$src->asFormattedString("WIDTH {Width}");
        
        
        return $result;
        
    }
    
    private function label_text(MapServerLayerClassLabel $src)
    {

        if (!$src->Display()) return "";
        
        $a = array();
        
        $a['Color'] = "COLOR";
        $a['ShadowColor']  = "SHADOWCOLOR";
        $a['ShadowSizeString'] = "SHADOWSIZE";
        $a['Type'] = "TYPE";
        $a['Font'] = "FONT";
        $a['Size'] = "SIZE";
        $a['AntiAlias'] = "ANTIALIAS";
        $a['Position'] = "POSITION";
        $a['Partials'] = "PARTIALS";
        $a['MinDistance'] = "MINDISTANCE";
        $a['Buffer'] = "BUFFER";
        
        $result = "";        
        
        foreach ($a as $PropertyName => $MapfileLabel) 
        {
            $result .= "\n\t\t".$src->asFormattedString("{$MapfileLabel}\t\t{{$PropertyName}}");
        }
        
        return $result;
        
    }
    
    
    
    
    public function Text() {
        
$output = <<<OUTPUT
MAP
    IMAGETYPE     {$this->Wrapper()->OutputImageType()}
    EXTENT        {$this->extent_string()}
    SIZE          {$this->size()}
    SHAPEPATH    "{$this->shapepath()}"
    IMAGECOLOR    {$this->image_color()}
    FONTSET      "{$this->fontset()}"
    SYMBOLSET    "{$this->symbolset()}"

    WEB
        IMAGEPATH "{$this->imagePath()}"
        IMAGEURL  "{$this->imageURL()}"
    END
        
    {$this->layers()}
    {$this->caption()}
        
END # end of mapfile
OUTPUT;
        
        return $output;
    }
    
    
    
    
}
?>