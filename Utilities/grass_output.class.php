<?php
class grass_output
{
    
    private $GRASS = null;
    private $debug = false;
    
    private $setup_commands = array();    
    private $raster_commands = array();    
    private $vector_commands = array();    
    private $text_commands   = array();    
    
     public static function connect($GRASS)
     {
         $GO = new grass_output($GRASS);
         return $GO;
     }
    
     public function __destruct()
     {
        unset($this->setup_commands);
        unset($this->raster_commands);
        unset($this->vector_commands);
        unset($this->text_commands);
     }
     
    
    public function __construct(GRASS $GRASS)
    {        
        $this->GRASS = $GRASS;
        $this->debug = $GRASS->debug;
        if ($this->debug) echo "init: grass_output\n";
    }
    
    public function Monitor()
    {
        if (func_num_args() == 0 ) return $this->monitor;
        $this->monitor = func_get_arg(0);
    }
    private $monitor = "x5";
    
    public function AddPolygonLines($name = null, $width = 2, $color = "black")
    {        
        if (is_null($name)) return null;
        
        $cmd = "d.vect {$name} type=boundary width=$width color=$color";
        
        if ($this->debug)  echo "Add polygon $cmd\n";
        
        $this->vector_commands[] = $cmd;
        return $cmd;
    }

    public function AddPolygonArea($name = null, $width = 2, $color = "black",$fill_color = "orange")
    {        
        if (is_null($name)) return null;
        
        $cmd = "d.vect {$name} type=area width=$width color=$color fcolor=$fill_color";
        
        if ($this->debug)  echo "Add polygon $cmd\n";
        
        $this->vector_commands[] = $cmd;
        return $cmd;
    }    
    
    public function AddPolygon($name = null, $width = 2, $color = "black")
    {        
        if (is_null($name)) return null;
        
        $cmd = "d.vect {$name} type=boundary width=$width color=$color";
        
        if ($this->debug)  echo "Add polygon $cmd\n";
        
        $this->vector_commands[] = $cmd;
        return $cmd;
    }
    
    
    public function AddPoints($name, $size = 4, $color = "red", $fill_color = null, $icon = null )
    {        
        if (is_null($name)) return null;                
        if (is_null($icon)) $icon = "basic/**circle";        
        if (is_null($fill_color)) $fill_color = $color;
        if (is_null($size)) $size = 4;
        
        
        $cmd = "d.vect {$name} icon={$icon} size=$size color=$color fcolor=$fill_color";
        
        if ($this->debug)  echo "Add points $cmd\n";
        
        $this->vector_commands[] = $cmd;
        return $cmd;
    }

    public function AddRaster($name = null)
    {        
        if (is_null($name)) return null;
        $cmd = "d.rast {$name}";
        
        if ($this->debug)  echo "Add rast $cmd\n";
        
        $this->raster_commands[] = $cmd;
        return $cmd;
    }
    
    public function AddRasterLegend($name = null)
    {        
        $this->rasterLegendName  = $name;
    }
    private $rasterLegendName = null;
    
    public function AddText($text, $point_size = 16,$color = "black" ,$from_left = 10, $from_top = 10,  $rotation = 0)
    {        
        if (is_null($text)) return null;
        
        if (is_array($text))
        {
            $line_count = 0;
            foreach ($text as $key => $text_line)
            {
                $to_write = (is_numeric($key)) ? $text_line : "$key: $text_line";
                $adjusted_from_top = $from_top + ($line_count * ($point_size * 1.8));
                $cmd = "d.text.freetype  path='//**openoffice.org//**share//**truetype/**DejaVuSansMono.ttf' -p -s 'text={$to_write}' at=$from_left,{$adjusted_from_top} color=$color size={$point_size} align=ul rotation={$rotation} linespacing=1.1";
                $this->text_commands[] = $cmd;
                $line_count++;
            }            
        }
        else
        {
            $cmd = "d.text.freetype  path='//**openoffice.org//**share//**truetype/**DejaVuSansMono.ttf' -p -s 'text={$text}' at=$from_left,$from_top color=$color size={$point_size} align=ul rotation={$rotation} linespacing=1.1";
            $this->text_commands[] = $cmd;
        }
        
        if ($this->debug)  echo "Add text ".util::toString($this->text_commands[]);
        
        return $cmd;
    }
    
    
    public function Grid()
    {
        if (func_num_args() == 0 ) return $this->addGrid;
        $this->addGrid = func_get_arg(0);
    }
    private $addGrid = 1;

    
    public function Save($filename = null,$width = 1024, $height=768)
    {
        if (is_null($filename)) return null;

        //** clean of extension
        if (util::contains($filename, ".")) $filename = util::toLastChar($filename, ".");
                
        $filename = $filename .".png";
        
        $cmd = array();
        
        $cmd[] = "d.mon start={$this->Monitor()}";
        $cmd[] = "d.monsize setmonitor={$this->Monitor()} setwidth={$width} setheight={$height}";
        $cmd[] = "d.erase";
        
        foreach ($this->raster_commands as $command) 
                $cmd[] = $command;
        
        foreach ($this->vector_commands as $command) 
                $cmd[] = $command;        
        
        if (!is_null($this->Grid())) 
                $cmd[] = "d.grid -b size={$this->Grid()}";        
        
        if (!is_null($this->rasterLegendName))
                $cmd[] = "d.legend -s map={$this->rasterLegendName}";
        
        foreach ($this->text_commands as $command) 
                $cmd[] = $command;
        
        if ($this->debug) 
        {
            echo "To run to create image\n";
            print_r($cmd);
        }
        
        $cmd[] = "d.out.png output='{$filename}'";
        $cmd[] = "d.mon stop={$this->Monitor()}";
        
        $grass_cmd = join(';',$cmd);
        
        if ($this->debug) echo "Save output of Grass monitor\n";
        if ($this->debug) echo "$grass_cmd\n";
        
        $this->GRASS->GRASS_COMMAND($grass_cmd);
        
        if (!file_exists($filename)) return false;
        
        return $filename;
        
    }
    
}

?>