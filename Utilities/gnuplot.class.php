<?php
class gnuplot 
{
    
    public static $RANGE_START = "START";
    public static $RANGE_END = "END";
    public static $TEXT_ROTATE_TRUE = "rotate";
    public static $TEXT_ROTATE_FALSE = "norotate";
    
    private $property = array();
    
    public function __construct() 
    {
        $this->init();
    }

    private function init()
    {
        $this->property['DataSeries']       = array();
        $this->property['OutputImage']      = '//**php_gnuplot_image.png';
        $this->property['ImageWidth']       = 1200;
        $this->property['ImageHeight']      = 1200;

        $this->property['tmargin']          = 4;
        $this->property['bmargin']          = 9;
        $this->property['lmargin']          = 9;
        $this->property['rmargin']          = 4;
        
        $this->property['YScaleInterval']   = 2;
        $this->property['YScaleTextRotate'] = self::$TEXT_ROTATE_FALSE;
        $this->property['YScaleLabel']      = 'Y-AXIS';
        $this->property['YScaleFormat']     = "%3.2f";
        $this->property['XScaleInterval']   = 1;
        $this->property['XScaleTextRotate'] = self::$TEXT_ROTATE_TRUE;
        $this->property['XScaleLabel']      = 'X-AXIS';
        $this->property['XScaleFormat']     = "%3.2f";
        $this->property['Title']            = 'TITLE';
        $this->property['SubTitle']         = '';
        $this->property['YRangeStart']      = 0;
        $this->property['YRangeEnd']        = 100;        
        $this->property['XRangeStart']      = 0 ;
        $this->property['XRangeEnd']        = 100;
        $this->property['DotSize']          = 1;
        $this->property['DotType']          = 59;
        $this->property['AddSpline']        = false;
        $this->property['AddBezier']        = false;

        $this->property['additional_text']  = array();;
        
    }
    
    
    public function plot_single_series($series_name = null, $series_title = null)
    {
        
        if ($this->DataSeriesCount() == 0) 
        {
            echo "##ERROR plot_single_series ... Can't plot data graph with NO DATA\n";
            return null;
        }
        
        $array = null;

        if (is_null($series_name)) 
            $array = util::first_element($this->DataSeries());
        else
        {
            $array = $this->DataSeries($series_name);
            
            if (is_null($array)) 
            {
                echo "##ERROR Can't find data series with name $series_name\n";
                return null;
            }
        }
        
        if (is_null($series_title))
            $series_title = util::first_key($this->DataSeries());
        
        //** temp input filename
        $temp_data_filename = "//**__php_gnu_plot.data";
        
        
        //** convert input array to a temp tab delimited file of column1 = keys , column 2 = values
        $temp_data_filename = file::Array2File($array, $temp_data_filename, "", "","\t");
        
        if (is_null($temp_data_filename) )
        {
            echo "##ERROR could not write to data to $temp_data_filename\n";
            return NULL;
        }
        
        $spline = "";
        if ($this->AddSpline()) 
                $spline = ", '{$temp_data_filename}' using 1:2 smooth csplines title 'spline' with lines";

        $bezier = "";
        if ($this->AddBezier()) 
                $bezier = ", '{$temp_data_filename}' using 1:2 smooth bezier title 'bezier' with lines";
        
        //** might be better at some stage to create folders in tmp to do this
        $gnu_plot_command_filename = "//**php_gnuplot_script.gnu";
        
        $series_title = str_replace('_', '-', $series_title);
        
        //** build additional text
        
//**        $additional_text ="";
//**        if (count($this->property['additional_text']) > 0)
//**        {
//**
//**            foreach ($this->property['additional_text'] as $index => $single_text)
//**            {
//**                $single_text = trim($single_text);
//**                
//**                $plot_offset = ( ($this->MarginTop() - 1) - $index);
//**                $label_num = $index + 1;
//**                $additional_text .= "set  label 11 \"{$single_text}\" at {$this->XRangeStart()}, {$this->YRangeEnd()}, 0 left norotate back textcolor lt 3 nopoint offset character 0, {$plot_offset}, 0";
//**            }
//**            
//**        }
        
        
$gnu_script = <<<CMD
set termoption enhanced
set terminal png transparent nocrop enhanced size {$this->ImageWidth()},{$this->ImageHeight()} 
set output '{$this->OutputImage()}'
set xtics nomirror {$this->XScaleInterval()} {$this->XScaleTextRotate()}
set ytics nomirror {$this->YScaleInterval()} offset character 0, 0, 0 
set format y "{$this->XScaleFormat()}"
set format x "{$this->YScaleFormat()}"
set ylabel "{$this->YScaleLabel()}" offset 0
set xlabel "{$this->XScaleLabel()}" offset 0, -2
set title "{$this->FullTitle()}" left
set key right tmargin
set xrange [ {$this->XRangeAsString()} ] noreverse nowriteback
set yrange [ {$this->YRangeAsString()} ] noreverse nowriteback
set tmargin {$this->MarginTop()}
set bmargin {$this->MarginBottom()}
set lmargin {$this->MarginLeft()}
set rmargin {$this->MarginRight()}
set grid 
set pointsize {$this->DotSize()}
plot '{$temp_data_filename}' using 1:2 with linespoints  pointtype {$this->DotType()} title '{$series_title}' {$spline} {$bezier}    
CMD;
        
        //**logger::text($gnu_script);

        file_put_contents($gnu_plot_command_filename, $gnu_script);

        $gnuplot_result = exec("gnuplot $gnu_plot_command_filename");
        
        if (!file_exists($this->OutputImage())) 
        {
            logger::error("Failed to create output from GNUPLOT script \n--------------\n$gnu_script\n-------------------\n");
            return null;
        }
            
        file::Delete($temp_data_filename);
        file::Delete($gnu_plot_command_filename);
        
        return $this->OutputImage();  //** this should be the path to the image they asked for
        
    }

    public function AddText($text)
    {
        $this->property['additional_text'][] = $text;        
    }

        
    public function AddSeries($name,$data)
    {
        $this->DataSeries($name,$data);
    }
    
    public function DataSeries()
    {
        if (func_num_args() == 0 ) return $this->property['DataSeries'];
        if (func_num_args() == 1 ) return $this->property['DataSeries'][func_get_arg(0)];
        if (func_num_args() == 2 ) $this->property['DataSeries'][func_get_arg(0)] = func_get_arg(1);
    }

    public function DataSeriesCount()
    {
        return count($this->property['DataSeries']);
    }

    
    public function OutputImage()
    {
        if (func_num_args() == 0 ) return $this->property['OutputImage'];
        if (func_num_args() == 1 ) $this->property['OutputImage'] = func_get_arg(0);
    }

    public function ImageWidth()
    {
        if (func_num_args() == 0 ) return $this->property['ImageWidth'];
        if (func_num_args() == 1 ) $this->property['ImageWidth'] = func_get_arg(0);
    }

    public function ImageHeight()
    {
        if (func_num_args() == 0 ) return $this->property['ImageHeight'];
        if (func_num_args() == 1 ) $this->property['ImageHeight'] = func_get_arg(0);
    }
    
    public function MarginTop()
    {
        if (func_num_args() == 0 ) return $this->property['tmargin'];
        if (func_num_args() == 1 ) $this->property['tmargin'] = func_get_arg(0);
    }
    
    public function MarginBottom()
    {
        if (func_num_args() == 0 ) return $this->property['bmargin'];
        if (func_num_args() == 1 ) $this->property['tmargin'] = func_get_arg(0);
    }

    public function MarginLeft()
    {
        if (func_num_args() == 0 ) return $this->property['lmargin'];
        if (func_num_args() == 1 ) $this->property['tmargin'] = func_get_arg(0);
    }

    public function MarginRight()
    {
        if (func_num_args() == 0 ) return $this->property['rmargin'];
        if (func_num_args() == 1 ) $this->property['tmargin'] = func_get_arg(0);
    }
    
    
    public function YScaleInterval()
    {
        if (func_num_args() == 0 ) return $this->property['YScaleInterval'];
        if (func_num_args() == 1 ) $this->property['YScaleInterval'] = func_get_arg(0);
    }

    public function YScaleTextRotate()
    {
        if (func_num_args() == 0 ) return $this->property['YScaleTextRotate'];
        if (func_num_args() == 1 ) $this->property['YScaleTextRotate'] = func_get_arg(0);
    }

    public function YScaleLabel()
    {
        if (func_num_args() == 0 ) return $this->property['YScaleLabel'];
        if (func_num_args() == 1 ) $this->property['YScaleLabel'] = func_get_arg(0);        
    }

    public function YScaleFormat()
    {
        if (func_num_args() == 0 ) return $this->property['YScaleFormat'];
        if (func_num_args() == 1 ) $this->property['YScaleFormat'] = func_get_arg(0);        
    }
    
    
    public function XScaleInterval()
    {
        if (func_num_args() == 0 ) return $this->property['XScaleInterval'];
        if (func_num_args() == 1 ) $this->property['XScaleInterval'] = func_get_arg(0);        
    }
    
    public function XScaleTextRotate()
    {
        if (func_num_args() == 0 ) return $this->property['XScaleTextRotate'];
        if (func_num_args() == 1 ) $this->property['XScaleTextRotate'] = func_get_arg(0);        
    }
    
    public function XScaleLabel()
    {
        if (func_num_args() == 0 ) return $this->property['XScaleLabel'];
        if (func_num_args() == 1 ) $this->property['XScaleLabel'] = func_get_arg(0);        
    }

    public function XScaleFormat()
    {
        if (func_num_args() == 0 ) return $this->property['XScaleFormat'];
        if (func_num_args() == 1 ) $this->property['XScaleFormat'] = func_get_arg(0);        
    }
    
    
    public function Title()
    {
        if (func_num_args() == 0 ) return $this->property['Title'];
        if (func_num_args() == 1 ) $this->property['Title'] = func_get_arg(0);                
    }
    
    public function SubTitle()
    {
        if (func_num_args() == 0 ) return $this->property['SubTitle'];
        if (func_num_args() == 1 ) $this->property['SubTitle'] = func_get_arg(0);                
    }

    private function FullTitle()
    {
        $result  = "";
        $result .= ($this->Title() == "") ? "" : $this->Title();
        
        if (($this->Title() != "") && ($this->SubTitle() != "")) 
                $result .= "\\n";  //** add CR after title if it's not empty and we have a substtitle
        
        $result .= ($this->SubTitle() == "") ? "" : $this->SubTitle();
        
        $result = str_replace('_', '-', $result);
        
        return $result ;
    }

    public function XRangeStart()
    {
        if (func_num_args() == 0 ) return $this->property['XRangeStart'];
        if (func_num_args() == 1 ) $this->property['XRangeStart'] = func_get_arg(0);                        
    }

    public function XRangeEnd()
    {
        if (func_num_args() == 0 ) return $this->property['XRangeEnd'];
        if (func_num_args() == 1 ) $this->property['XRangeEnd'] = func_get_arg(0);                        
    }

    private function XRangeAsString()
    {        
        return $this->XRangeStart()." : ".$this->XRangeEnd();
    }

    public function YRangeStart()
    {
        if (func_num_args() == 0 ) return $this->property['YRangeStart'];
        if (func_num_args() == 1 ) $this->property['YRangeStart'] = func_get_arg(0);                        
    }

    public function YRangeEnd()
    {
        if (func_num_args() == 0 ) return $this->property['YRangeEnd'];
        if (func_num_args() == 1 ) $this->property['YRangeEnd'] = func_get_arg(0);                        
    }

    private function YRangeAsString()
    {        
        return $this->YRangeStart()." : ".$this->YRangeEnd();
    }
    
    public function DotSize()
    {
        if (func_num_args() == 0 ) return $this->property['DotSize'];
        if (func_num_args() == 1 ) $this->property['DotSize'] = func_get_arg(0);                        
    }
    
    public function DotType()
    {
        if (func_num_args() == 0 ) return $this->property['DotType'];
        if (func_num_args() == 1 ) $this->property['DotType'] = func_get_arg(0);                        
    }
    
    public function AddSpline()
    {
        if (func_num_args() == 0 ) return $this->property['AddSpline'];
        if (func_num_args() == 1 ) $this->property['AddSpline'] = func_get_arg(0);                        
    }

    public function AddBezier()
    {
        if (func_num_args() == 0 ) return $this->property['AddBezier'];
        if (func_num_args() == 1 ) $this->property['AddBezier'] = func_get_arg(0);                        
    }
 
    public function Properties()
    {
        return $this->property;
    }
    
    public function toString()
    {
        $result = "";
        foreach ($this->property as $key => $value) {
            $result .= "$key => $value\n";
        }
        
        return $result;
    }
    
}
?>