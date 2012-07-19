<?php
/**
 * Description of SpeciesMaxentQuickLook
 *
 * @author jc166922
 */
class SpeciesMaxentQuickLook {
/**
    *
    * @param type $src_grid_filename      - Maxent ASC Grid with filename in format of      (Scenario)_(model)_(time).asc
    * @param type $output_image_filename  - Where you want to output mage to end up 
    * @param type $low_threshold          - display value start from   - to be read from maxentResults.csv - "Equate entropy of thresholded and original distributions logistic threshold"
    * @param type $transparency           - transparency of all colors 
    * @param type $background_colour      - background colour   use 0 0 0 255 = Full balck   0 0 0 0 = Full Transparent
    * @return null|String                 - Output filename 
    */
    public static function CreateImage($species_id,$src_grid_filename,$output_image_filename = null ,$low_threshold = null,$transparency = 255,$background_colour = "0 0 0 255")
    {
        
        if (is_null($output_image_filename)) $output_image_filename = str_replace("asc","png",$src_grid_filename);
        
        if (file_exists($output_image_filename)) return $output_image_filename;
        
        list($scenario, $model, $time) =  explode("_",str_replace('.asc','',basename($src_grid_filename)));    

        $histogram_buckets = 10;
        
        $ramp = RGB::Ramp(0, 1, $histogram_buckets,RGB::ReverseGradient(RGB::GradientYellowOrangeRed())); 

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
        
        
        $db = new PGDB();
        

    //    echo "\ncolour_txt = ".$colour_txt ;
    //    echo "\ncolour_png = ".$colour_png ;
    //    echo "\ncolour_zero_txt = ".$colour_zero_txt ;
    //    echo "\ncolour_background_png = ".$colour_background_png ;
    //    echo "\ncolour_combined_png = ".$colour_combined_png ;
    //    echo "\ncolour_legend_png = ".$colour_legend_png ;


        $indexes = array_keys($ramp);
        if (is_null($low_threshold))   // try to get value from  Maxent Results self::$DisplayThresholdFieldName
        {
            $low_threshold = $indexes[1]; // if they did not pass low end threshold then just show everything above lowest value    
            $maxent_threshold = DatabaseMaxent::GetMaxentResult(   $species_id,DatabaseMaxent::$DisplayThresholdFieldName);
            if (!is_null($maxent_threshold))  $low_threshold = $maxent_threshold;
        }
        
        // create colour "lookup table"    

        $color_table = "nv 0 0 0 0\n";  // no value
        $pcent = 0;
        $step = round( 100 / count($ramp),0);
        foreach ($ramp as $index => $rgb) 
        {
            $rgb instanceof RGB;
            if ($index < $low_threshold)
            {
                $color_table .= $pcent."% 0 0 0 0\n";
            }
            else
            {
                $color_table .= $pcent."% ".$rgb->Red()." ".$rgb->Green()." ".$rgb->Blue()." {$transparency}\n";    
                
            }
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

        
        $species_info = SpeciesData::SpeciesInfoByID($species_id);
        $species_info = array_util::Replace($species_info, "'", "");
        
        
        list($width, $height, $type, $attr) = getimagesize($colour_png);     
        
        // - add parameters  to the image  $scenario, $model, $time

$header = <<<HEADER
convert \
-size  {$width}x60 xc:white -font DejaVu-Sans-Book -fill black \
-draw 'text  10,20 "{$species_info['scientific_name']}"' \
-draw 'text  10,40 "{$species_info['common_name']}"' \
-draw 'text 200,20 "Scenario: {$scenario}"' \
-draw 'text 400,20 "Model: {$model}"' \
-draw 'text 600,20 "Time: {$time}"' \
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
        
        unset($db);
        

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