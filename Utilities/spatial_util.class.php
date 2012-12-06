<?php
class spatial_util
{

    public static function isVector($filename)
    {
        $result = array();
        $cmd = "ogrinfo -so  -al {$filename} | grep 'Extent:'";
        exec($cmd, $result);

        if (count($result) == 0 )return false;

        if (util::contains($result[0],"FAILURE:"))
                return false; // raster (NOT Vector)
        else
                return true;  // is Vector

    }

    /*
     * We can read it via "gdalinfo"
     */
    public static function isRaster($filename)
    {
        //print_r($filename);
        if (!file_exists($filename))  return null;

        if (util::contains($filename, "shp")) return false;
        
        $result = array();
        $cmd = "gdalinfo '{$filename}' | head -n1";
        exec($cmd, $result);

        if (count($result) == 0 )return false;
        if (!util::contains($result[0],"Driver:")) return false;

        return true;
    }



    /*
     * Output from OGR
     *
     *  LINE    = "Line String"
     *  POLYGON = "Polygon"
     *  POINT   = "Point"
     */
    public static function VectorType($filename,$layer_number = "1")
    {
        $result = array();
        $cmd = "ogrinfo  '{$filename}' | grep '{$layer_number}:'";
        exec($cmd, $result);

        if (count($result) == 0) return null;
        if (!util::contains($result[0], "1:")) return null;

        $type_raw = trim(util::midStr($result[0], "(", ")", true));

        return $type_raw;
    }



    /*
     * We can read it via "gdalinfo"
     * - and take the driver we used to read it with
     */
    public static function RasterType($filename)
    {
        
        if (util::contains($filename, "shp")) return null;
        
        $result = array();
        $cmd = "gdalinfo '{$filename}' | head -n1";
        exec($cmd, $result);

        if (!util::contains($result[0],"Driver:")) return null;

        return trim(str_replace("Driver:", "", $result[0]));

    }


    public static function ArrayRasterStatistics($filenames,$band = "1",$recalculate = false,$leave_out_invalid_files = false)
    {
        
        //ErrorMessage::Marker(__METHOD__);
        
        $result = array();
        foreach ($filenames as $key => $filename) 
        { 
            if (!file_exists($filename)) continue;
        
            //ErrorMessage::Marker(__METHOD__." filename = $filename ");
            
            $stats = self::RasterStatisticsBasic($filename,$band = "1",$recalculate);
            
            //ErrorMessage::Marker(__METHOD__." STATS ");
            //ErrorMessage::Marker($stats);
            
            if (is_null($stats))
            {
                ErrorMessage::Marker(__METHOD__." stats is NULL");
            }
            
            
            if ($leave_out_invalid_files)
            {
                //ErrorMessage::Marker(__METHOD__." leave_out_invalid_files");
                
                if (!is_null($stats))
                {
                    //ErrorMessage::Marker(__METHOD__." added $key");
                    $result[$key] = $stats; 
                }
                    
            }
            else
            {
                //ErrorMessage::Marker(__METHOD__." DON'T  leave_out_invalid_files");
                $result[$key] = $stats;     
            }
            
            
        }
        
        return $result;
        
    }
    
    
    public static function RasterStatisticsPrecision($filename,$band = "1")
    {

        if (util::contains($filename, "shp")) return null;
        
        $result = array();
        $cmd = "gdalinfo -stats {$filename} | grep 'Band {$band}' -A 8 | grep 'STATISTICS_'";
        exec($cmd, $result);

        if (count($result) == 0 ) return null;

        //    STATISTICS_MINIMUM=1.7737500002113e-07
        //    STATISTICS_MAXIMUM=0.83669799566269
        //    STATISTICS_MEAN=0.03642983808888
        //    STATISTICS_STDDEV=0.11281570693398



        $min_raw = array_util::FirstElementsThatContain($result, "STATISTICS_MINIMUM");
        if (is_null($min_raw)) return null;
        $min = trim(util::fromLastChar($min_raw,"="));

        $max_raw = array_util::FirstElementsThatContain($result, "STATISTICS_MAXIMUM");
        if (is_null($max_raw)) return null;
        $max = trim(util::fromLastChar($max_raw,"="));

        $mean_raw = array_util::FirstElementsThatContain($result, "STATISTICS_MEAN");
        if (is_null($mean_raw)) return null;
        $mean = trim(util::fromLastChar($mean_raw,"="));

        $sd_raw = array_util::FirstElementsThatContain($result, "STATISTICS_STDDEV");
        if (is_null($sd_raw)) return null;
        $sd = trim(util::fromLastChar($sd_raw,"="));
        
        $out = array();
        $out[self::$STAT_MINIMUM] = $min;
        $out[self::$STAT_MAXIMUM] = $max;
        $out[self::$STAT_MEAN]    = $mean;
        $out[self::$STAT_STDDEV]  = $sd;
        $out[self::$STAT_RANGE]   = $max - $min;
        
        
        return $out;

    }


    /*
     *
     */
    public static function RasterStatisticsBasic($filename,$band = "1",$recalculate = false)
    {

        if (util::contains(strtolower($filename), "shp")) return null;

        //ErrorMessage::Marker(__METHOD__." filename = $filename ");
        
        
        if (is_null($filename)) return new Exception("Filename passed as null");

        $filename = trim($filename);
        if ($filename == "") return new Exception("Filename passed as EMPTY");
        
        if (!file_exists($filename)) return new Exception("File not found .. $filename ");
        
        
        if ($recalculate)
            if (file_exists($filename.".aux.xml")) 
                file::Delete ($filename.".aux.xml");
        
        
            
            
        // try to get a better / higher precision version first
        $precision = self::RasterStatisticsPrecision($filename,$band);
        
        
        //ErrorMessage::Marker(__METHOD__." from precision ");
        //ErrorMessage::Marker($precision);
        
        if (!is_null($precision)) return $precision;

        
        $result = array();
        $cmd = "gdalinfo -stats {$filename} | grep 'Band {$band}' -A 3";
        exec($cmd, $result);

        if (count($result) == 0 ) return null;

        
        //ErrorMessage::Marker(__METHOD__." from basic ");
        //ErrorMessage::Marker($precision);
        
        
        // Minimum=0.000, Maximum=255.000, Mean=65.600, StdDev=99.236

        $stats_line_raw = array_util::FirstElementsThatContain($result, "Minimum");
        if (is_null($stats_line_raw)) return null;

        $stats_line_raw = array_util::FirstElementsThatContain($result, "Maximum");
        if (is_null($stats_line_raw)) return null;

        $stats_line_raw = array_util::FirstElementsThatContain($result, "Mean");
        if (is_null($stats_line_raw)) return null;

        
        $key_values = array_util::explode(explode(",",$stats_line_raw), "=");

        $out = array();  //   just stats
        $out[self::$STAT_MINIMUM] = array_util::Value($key_values, "Minimum", null);
        $out[self::$STAT_MAXIMUM] = array_util::Value($key_values, "Maximum", null);
        $out[self::$STAT_MEAN]    = array_util::Value($key_values, "Mean"   , null);
        $out[self::$STAT_STDDEV]  = array_util::Value($key_values, "StdDev" , null);
        
        return $out;

    }

    public static $STAT_MINIMUM = "Minimum";
    public static $STAT_MAXIMUM = "Maximum";
    public static $STAT_MEAN    = "Mean";
    public static $STAT_STDDEV  = "StdDev";
    public static $STAT_RANGE   = "Range";


    public static function VectorLayers($filename)
    {

        if (!self::isVector($filename)) return null;

        $result = array();
        $cmd = "ogrinfo -so {$filename} | grep ':' | grep -v 'INFO'";
        exec($cmd, $result);

        if (count($result) == 0 ) return null;

        return util::midStrArray($result, ": ", "(",true);

    }


    /*
     * RETURN Array  Attribute Name = Type
     *
     * @param: $filename =
     *
     */

    public static function VectorAttributeNames($filename,$layername, $generic_types = false)
    {

        if (!self::isVector($filename)) return null;

        $result = array();
        $cmd = "ogrinfo  -so {$filename} {$layername} | grep '(' | grep ':' | grep -v 'Extent'";
        exec($cmd, $result);

        if (count($result) == 0 ) return null;

        $attribs = array();
        foreach ($result as $line)
        {
            $key = trim(util::leftStr($line, ":",false));
            $value = trim(util::midStr($line, ":", "(", true));
            $attribs[$key] = $value;
        }

        $result = $attribs;

        if ($generic_types)
        {
            $result = array();
            foreach ($attribs as $key => $value)
            {
                $new_type = $value;
                if (util::contains($value, "Real")) $new_type = self::$FIELD_TYPE_DOUBLE;
                if (util::contains(strtolower($value), "string")) $new_type = self::$FIELD_TYPE_STRING;
                if (util::contains(strtolower($value), "varchar")) $new_type = self::$FIELD_TYPE_STRING;

                $result[$key] = $new_type;
            }

        }


        return $result;

    }


    public static $FIELD_TYPE_DOUBLE = "DOUBLE";
    public static $FIELD_TYPE_STRING = "STRING";

    public static $SPATIAL_TYPE_POLYGON  = "POLYGON";
    public static $SPATIAL_TYPE_LINE     = "LINE";
    public static $SPATIAL_TYPE_POINT    = "POINT";

    public static $SPATIAL_TYPE_RASTER   = "RASTER";


    public static function subtract($lhs,$rhs,$output_filename = null, $null_value = null,$path_sep = "/",$ext_sep = ".")
    {   
        
        if (!file_exists($lhs)) return new ErrorMessage (__METHOD__, __LINE__, "LHS does not exist", false);
        if (!file_exists($rhs)) return new ErrorMessage (__METHOD__, __LINE__, "RHS does not exist", false);

        if (is_null($output_filename)) 
        {
            
            $output_filename =  dirname($lhs)
                               .$path_sep
                               .util::leftStr(basename($lhs),$ext_sep) 
                               ."-"
                               .util::leftStr(basename($rhs),$ext_sep) 
                               .$ext_sep
                               .file::getFileExtension($lhs);        
                               ;
        }
            
        
        $output_filename = trim($output_filename);
        if ($output_filename == "") return new ErrorMessage (__METHOD__, __LINE__, "output_filename is empty", false);

        $dir = dirname($output_filename);
        if (!is_dir($dir)) return new ErrorMessage (__METHOD__, __LINE__, "$dir is not a folder ", false);
        
        
        // try to find the null data from the lhsfile        
        if (is_null($null_value))
        {
            $null_value = self::asciigrid_nodata_value($lhs);
        }

        $lineCount_lhs = file::lineCount($lhs);  
        $lineCount_rhs = file::lineCount($rhs);  
        
        if ($lineCount_lhs != $lineCount_rhs) 
            return new ErrorMessage (__METHOD__, __LINE__, "Line counts are different  LHS = $lineCount_lhs RHS = $lineCount_rhs ", false);
        
        
        try {
            $handle_lhs = fopen($lhs, "rb");    
        } catch (Exception $exc) {
            return new ErrorMessage (__METHOD__, __LINE__, "Failed to open [{$lhs}] for reading\n".$exc->getTraceAsString(), false);
        }


        try {
            $handle_rhs = fopen($rhs, "rb");
        } catch (Exception $exc) {
            return new ErrorMessage (__METHOD__, __LINE__, "Failed to open [{$rhs}] for reading\n".$exc->getTraceAsString(), false);
        }
        
        
        
        // ASCII files have 6 rowsa of "metadata" skip those
        for ($index = 0; $index < 6; $index++) {
                $line = fgets($handle_lhs); // read lines from both files 
                $line = fgets($handle_rhs); // read lines from both files 
        }

        
        $result = array();
        for ($lineNum = 6; $lineNum < $lineCount_lhs ; $lineNum++) 
        {
            
            $lhs_cells = explode(" ",fgets($handle_lhs));  
            $rhs_cells = explode(" ",fgets($handle_rhs));  
            
            $result_cells = array();
            
            foreach (array_keys($lhs_cells) as $xIndex) 
            {
                if ($lhs_cells[$xIndex] == $null_value || $rhs_cells[$xIndex] == $null_value)
                {
                    $result_cells[$xIndex] = $null_value;   // one of the values i null so result  is null
                }
                else
                {
                    $result_cells[$xIndex] = $lhs_cells[$xIndex] - $rhs_cells[$xIndex];    
                }
                
                
            }
            
            $result[] = implode(" ",$result_cells);
            
            unset($result_cells);
        }
        
        
        fclose($handle_lhs);  // close files
        fclose($handle_rhs);  // close files
        
        $file_result =   implode("\n",file::Head($lhs, 6))."\n"   
                        .implode("\n",$result).
                         "\n";

        
        
        $fw = file_put_contents($output_filename, $file_result);
        if (!$fw)
            return new ErrorMessage (__METHOD__, __LINE__, "Failed to write result to output file [{$output_filename}]", false);

            
        if (!file_exists($output_filename))
            return new ErrorMessage (__METHOD__, __LINE__, "[{$output_filename}] does not exist ", false);
            
            
        return $output_filename;
        
    }
    

    
    
    
    public static function median($asciiGridFilenameArray,$output_filename, $null_value = null)
    {

        // assume all files are standard and all align
        // make want to build check for this soon
        
        // try to find the null data from the first file
        if (is_null($null_value))
        {
            $null_value = self::asciigrid_nodata_value(util::first_element($asciiGridFilenameArray));
        }
        
        $handles = array();

        $lineCounts = file::lineCounts($asciiGridFilenameArray,true);  // we can use this check if any file is not right
        
        
        
        $lineCountFirst = util::first_element($lineCounts) ;
        
        $result = array();
        
        // open all files
        foreach ($asciiGridFilenameArray as $key => $value)  
        {
            if (!file_exists($value))  return new Exception("Could not open for Median {$value}");
                
            $handles[$key] = fopen($value, "rb");
        }
            
        
        // ASCII files have 6 rowsa of "metadata" skip those
        for ($index = 0; $index < 6; $index++) {
            foreach ($handles as $key => $handle)  
                $line = fgets($handle); // read lines from all files 
        }
        
        
        // we can only read one line at a time from each file 
        // as we don't have enough memory to to read all files in memory at one time
        for ($lineNum = 6; $lineNum < $lineCountFirst ; $lineNum++) {
            
            $cells = array();
            foreach ($handles as $key => $handle) 
                $cells[$key] = explode(" ",fgets($handle));  // load a line from each file - and convert to cells

            // $ cells is now a matrix with rowID is the filename and the "column name" is an index
            $result[] = implode(" ",matrix::ColumnMedian($cells, $null_value));
            
        }
        
        foreach ($handles as $key => $handle) fclose($handle);  //close all files

                                      // use the Metadata of the first file as the header for the output
        $file_result =   implode("\n",file::Head(util::first_element($asciiGridFilenameArray), 6))."\n"   
                        .implode("\n",$result).
                        "\n";
        
        file_put_contents($output_filename, $file_result);

        if (!file_exists($output_filename)) 
            return new Exception("Failed to Write file for Median {$output_filename}");

            
        $outputFileLineCount  = file::lineCount($output_filename);

        // check to see if output file line count = $lineCountFirst
        if ($lineCountFirst != $outputFileLineCount)
            return new Exception("Failed to create Median number of input and output lines don't match  $lineCountFirst != $outputFileLineCount ");
        
        
        return $output_filename;
        
    }

    
    public static function asciigrid_nodata_value($asciiGridFilename)
    {
        if (!file_exists($asciiGridFilename)) return null;
        
        $result = exec("cat '{$asciiGridFilename}' | grep NODATA_value");
        if (!util::contains($result, 'NODATA_value')) return null;
        
        
        
        return trim(str_replace('NODATA_value', '', $result));
        
    }
    
    
    
    /**
     *
     * @param type $src_grid_filename
     * @param type $output_image_filename
     * @param type $transparency          
     * @param type $background_colour
     * @param type $color_gradient        
     * @return null|string 
     */
    /**
     *
     * @param type $src_grid_filename
     * @param type $output_image_filename
     * @param int $transparency
     * @param string $background_colour
     * @param type $histogram_buckets
     * @param type $color_gradient
     * @param type $min
     * @param type $max
     * @return null|string 
     */
    public static function CreateImage($src_grid_filename,$output_image_filename = null ,$transparency = 255,$background_colour = "0 0 0 255",$histogram_buckets = 100,$color_gradient = null,$min = null,$max = null,$title = "")
    {
        
        if(is_null($transparency)) $transparency = 255;
        if(is_null($background_colour)) $background_colour = "0 0 0 255";
        if(is_null($histogram_buckets)) $histogram_buckets = 100;
        
        if(is_null($color_gradient)) $color_gradient = RGB::ReverseGradient(RGB::GradientYellowOrangeRed());
        
        
        if (is_null($output_image_filename)) $output_image_filename = str_replace("asc","png",$src_grid_filename);
        
        if (file_exists($output_image_filename)) return $output_image_filename;
        
        
        // ErrorMessage::Marker("CreateImage $src_grid_filename, $output_image_filename ,$transparency $background_colour ,$histogram_buckets ,$color_gradient ,$min ,$max ");        
        
        if (is_null($max) || is_null($min))
        {
            $stats = self::RasterStatisticsBasic($src_grid_filename);
        
            if (is_null($min)) $min = $stats[self::$STAT_MINIMUM];
            if (is_null($max)) $max = $stats[self::$STAT_MAXIMUM];
        }
        
        
        $ramp = RGB::Ramp($min, $max, $histogram_buckets,$color_gradient); 

        $colour_txt = file::random_filename().".txt"; // list of colours to use - will bne generated
        file::Delete($colour_txt);

        $colour_png = file::random_filename().".png"; // colourized ASC file as a png
        file::Delete($colour_png);

        $colour_zero_txt = file::random_filename().".txt"; // used to create black background
        file::Delete($colour_zero_txt);

        $colour_background_png = file::random_filename().".png"; // background image
        file::Delete($colour_background_png);

        $colour_combined_png = file::random_filename().".png"; // background + coloured image 
        file::Delete($colour_combined_png);

        $colour_legend_png = file::random_filename().".png"; // legend  image together
        file::Delete($colour_legend_png);

        $header_png = file::random_filename().".png"; // legend  image together
        file::Delete($header_png);
        
        
        if (is_null($output_image_filename))
            $output_image_filename = file::random_filename().".png"; // return image filename
        
        
        // create colour "lookup table"    

        $color_table = "nv 0 0 0 0\n";  // no value
        $pcent = 0;
        $step = round( 100 / count($ramp),0);
        foreach ($ramp as $index => $rgb) 
        {
            $rgb instanceof RGB;
            $color_table .= $pcent."% ".$rgb->Red()." ".$rgb->Green()." ".$rgb->Blue()." {$transparency}\n";    
            
            $pcent += $step;
        }

        // save the colour lookup table 
        file_put_contents($colour_txt, $color_table);
        
        $cmd = "gdaldem  color-relief {$src_grid_filename} {$colour_txt} -nearest_color_entry -alpha -of PNG {$colour_png}";
        exec($cmd);  // generate a coloured image using colour lookup 

        // create backgound to put coloured image on top of
        file_put_contents($colour_zero_txt, "nv 0 0 0 0\n0% {$background_colour}\n100% {$background_colour}\n"); // default is ALL Values = $background_colour  & No Value  = transparent  
        $cmd = "gdaldem  color-relief {$src_grid_filename} $colour_zero_txt -nearest_color_entry -alpha -of PNG {$colour_background_png}";
        exec($cmd);


        // order here is important first is lowest
        $cmd = "convert {$colour_background_png} {$colour_png} -layers flatten {$colour_combined_png}";
        exec($cmd);
        
        
        list($width, $height, $type, $attr) = getimagesize($colour_png);     
        
        // - add parameters  to the image  $scenario, $model, $time

$header = <<<HEADER
convert \
-size  {$width}x60 xc:white -font DejaVu-Sans-Book -fill black \
-draw 'text  10,20 "{$title}"' \
{$header_png};
HEADER;

        exec($header);


        // create a legend image
        // # rectangle left,top right,bottom" \
        $swatch_height = 20;
        $swatch_width = 20;
        $swatch_width_padding = 10;
        $text_align_up = -2;

        
        $height = count($ramp) * $swatch_height + (2 * $swatch_height);  // heioght of the legend is a cal of the number of legend items + 2 for top and bittom padding
        $box_top = 10;
        $box_left = 20;
        
        $legend  = "convert -size  {$width}x{$height} xc:white ";
        $legend .= "-font DejaVu-Sans-Book ";

        foreach (array_reverse($ramp, true) as $index => $rgb) 
        {
            $rgb instanceof RGB;

            $box_right = $box_left + $swatch_width;
            $box_bottom = $box_top + $swatch_height;

            $text_left = $box_left + $swatch_width + $swatch_width_padding;
            $text_top  = $box_top + $swatch_height + $text_align_up;

            $text = sprintf("%01.2f", $index);

            $legend .= "-fill '#{$rgb->asHex()}' -draw 'rectangle {$box_left},{$box_top} {$box_right},{$box_bottom}' ";
            $legend .= "-fill black -draw 'text {$text_left},{$text_top} \"{$text}\"' ";

            $box_top += $swatch_height;

        }

        $legend .= " {$colour_legend_png}";
        
        exec($legend); // create legend

        $cmd = "convert {$header_png} {$colour_combined_png} {$colour_legend_png} -append {$output_image_filename}";
        exec($cmd);

        // might be better here to convert to a tmp image
        // and then copy back to the $output_image_filename
        

        file::Delete($colour_txt);
        file::Delete($colour_png);
        file::Delete($colour_zero_txt);
        file::Delete($colour_background_png);
        file::Delete($colour_combined_png);
        file::Delete($colour_legend_png);

        if (!file_exists($output_image_filename)) return null;
        
        return $output_image_filename; // filename of png that can be used - 

    }
    
    
    
    
    
    
}
?>