<?php
/**
 * Description of GenusData
 *
 * 
 */
class GenusData extends Object 
{

    
    

    public static function GetProjectedFiles($genus = null ,$filetype = null ,$scenario = null, $model = null, $time = null) 
    {
        if (!is_null($genus))    $genus    = " and genus_name=".util::dbq($genus,true)." ";
        if (!is_null($filetype)) $filetype = " and   filetype=".util::dbq($filetype,true)." ";
        if (!is_null($scenario)) $scenario = " and s.dataname=".util::dbq($scenario,true)." ";
        if (!is_null($model))    $model    = " and m.dataname=".util::dbq($model,true)." ";
        if (!is_null($time))     $time     = " and t.dataname=".util::dbq($time,true)." ";
        
        $q = "select  
                 genus_id
                ,genus_name
                ,m.dataname as model
                ,s.dataname as scenario
                ,t.dataname as time
                ,filetype         
                ,file_unique_id
                from  modelled_genus_climates mc
                    ,models m
                    ,scenarios s
                    ,times t        
                where mc.models_id     = m.id
                and mc.scenarios_id  = s.id
                and mc.times_id      = t.id
                {$genus}
                {$filetype}
                {$scenario}
                {$model}
                {$time}
                ";

        // ErrorMessage::Marker("GetProjectedFiles q  = $q");
                
        $modelled_genus_climates_result = DBO::Query($q,'file_unique_id');

        if ($modelled_genus_climates_result instanceof ErrorMessage) 
            return ErrorMessage::Stacked(__METHOD__,__LINE__,"Failed to read modelled_genus_climates  sql=[{$q}]  \n",true,$modelled_genus_climates_result);


        
        return $modelled_genus_climates_result;
        
    }
    
    
    
    public static function InsertProjectedFile($genus,$filename,$filetype,$scenario, $model, $time,$compressed = false,$remove_first = false) 
    {
     
        if (!file_exists($filename)) 
            return new ErrorMessage(__METHOD__,__LINE__,"Failed to Insert Genus Data filename =  $filename Not found  \n  genus = $genus \nfiletype = $filetype \n scenario  = $scenario \n model = $model \n time = $time\n");
        
        
        $current_files = self::GetProjectedFiles($genus,$filetype ,$scenario , $model , $time);
        if ($current_files instanceof ErrorMessage)  return ErrorMessage::Stacked (__METHOD__,__LINE__,"", true, $current_files);
        
        if (count($current_files) > 0)
        {
            if ($remove_first)
            {
                ErrorMessage::Marker("Removed files arleady there and added this one {$filename}");
                self::RemoveProjectedFiles($genus ,$filetype ,$scenario , $model , $time ,true);
                
            }             
        }
        
        $file_unique_id = DatabaseFile::InsertFile($filename, "Future projection of {$genus} Scenario:$scenario Time:$time",$filetype,$compressed);
        if ($file_unique_id instanceof ErrorMessage)  return ErrorMessage::Stacked (__METHOD__,__LINE__,"", true, $file_unique_id);
        
        $genus_id = SpeciesData::GenusID($genus);
        
        
        $q  = "insert into modelled_genus_files 
                ( genus_id
                 ,genus_name
                 ,filetype
                 ,file_unique_id
                ) values (
                   {$genus_id}
                 ,".util::dbq($genus,true)."
                 ,".util::dbq($filetype,true)."
                 ,".util::dbq($file_unique_id,true)."
                )";
        

        $result = DBO::Insert($q);
        if ($result instanceof ErrorMessage)  return ErrorMessage::Stacked (__METHOD__,__LINE__,"", true, $result);
        
        if (!is_numeric($result))  return new ErrorMessage(__METHOD__,__LINE__,"Failed to Insertinto modelled_genus_files \n q= {$q} \ngenus = $genus \n filename =  $filename \n filetype = $filetype \n");
        
        
        // give model output reference data $scenario , $model , $time 
        if (!is_null($scenario) && !is_null($model) && !is_null($time) )
        {

            $scenario_id = DatabaseClimate::getScenarioID($scenario);
            if ($scenario_id instanceof ErrorMessage)  return new ErrorMessage(__METHOD__,__LINE__,"Failed to Id's for scenario = [{$scenario_id}]");
            
            $model_id  = DatabaseClimate::getModelID($model);
            if ($model_id instanceof ErrorMessage)  return new ErrorMessage(__METHOD__,__LINE__,"Failed to Id's for model = [{$model}]");
            
            $time_id = DatabaseClimate::getTimeID($time);
            if ($model_id instanceof ErrorMessage)  return new ErrorMessage(__METHOD__,__LINE__,"Failed to Id's for time = [{$time}]");

            
            $q = "insert into modelled_genus_climates
                  (  genus_id
                    ,genus_name
                    ,models_id
                    ,scenarios_id
                    ,times_id
                    ,filetype         
                    ,file_unique_id
                  ) values ( 
                    {$genus_id}
                   ,".util::dbq($genus,true)."
                   ,{$model_id}
                   ,{$scenario_id}
                   ,{$time_id}
                   ,".util::dbq($filetype,true)."
                   ,".util::dbq($file_unique_id,true)."
                   ) ;
                 ";
            
                   
           $modelled_genus_climates_result = DBO::Insert($q);

            if ($modelled_genus_climates_result instanceof ErrorMessage) 
                return ErrorMessage::Stacked(__METHOD__,__LINE__,"Failed to insert into modelled_genus,climates \n",true,$modelled_genus_climates_result);
           
           
        }
        
        
        return $file_unique_id;
        
    }
    
    
    public static function RemoveProjectedFiles($genus = null,$filetype = null ,$scenario = null, $model = null, $time = null,$mustExist = true) 
    {
        
        $data = self::GetProjectedFiles($genus,$filetype,$scenario, $model , $time );
        
        
        if ($mustExist  && count($data) == 0)
            if ($data instanceof ErrorMessage)
                return ErrorMessage::Stacked(__METHOD__,__LINE__,"Failed to remove files \n",true,$data);
        

        $error = array();
        foreach ($data as $file_unique_id => $row) 
        {
            //ErrorMessage::Marker("Remove Genus Climate Database file file {$file_unique_id}");
            
            $remove_result_file = DatabaseFile::RemoveFile($file_unique_id);
            if ($remove_result_file instanceof ErrorMessage)
            {
                $error[] = $remove_result_file;
                //ErrorMessage::Marker($remove_result_file);
                
                continue;
            }
            
            // remove the climate entry for this Genus 
            
            //ErrorMessage::Marker("Remove modelled_genus_climates  {$file_unique_id}");
            
            $remove_result_climate = DBO::Delete("modelled_genus_climates", "file_unique_id=".util::dbq($file_unique_id,true));
            if ($remove_result_climate instanceof ErrorMessage)  
            {
                $error[] = $remove_result_climate;
                //ErrorMessage::Marker($remove_result_climate);
                
                continue;
            }
            
            //ErrorMessage::Marker("Remove modelled_genus_files  {$file_unique_id}");
            
            $remove_result_climate = DBO::Delete("modelled_genus_files", "file_unique_id=".util::dbq($file_unique_id,true));
            if ($remove_result_climate instanceof ErrorMessage)  
            {
                $error[] = $remove_result_climate;
                //ErrorMessage::Marker($remove_result_climate);
                
                continue;
            }
            
        }
        
        if (count($error) > 0 ) return $error;

        if (count($data) > 0 )
            ErrorMessage::Marker("Remove Completed");
        

        return count($data);
        
    }
    
    
    
    
    /**
    *
    * @param type $src_grid_filename      - Maxent ASC Grid with filename in format of      (Scenario)_(model)_(time).asc
    * @param type $output_image_filename  - Where you want to output mage to end up 
    * @param type $transparency           - transparency of all colors 
    * @param type $background_colour      - background colour   use 0 0 0 255 = Full balck   0 0 0 0 = Full Transparent
    * @return null|String                 - Output filename 
    */
    public static function CreateImage($genus,$src_grid_filename,$min = null,$max = null,$output_image_filename = null ,$transparency = 255,$background_colour = "0 0 0 255")
    {
    
        
        if (!file_exists($src_grid_filename)) 
            new ErrorMessage(__METHOD__, __LINE__, "$src_grid_filename File Does not exist \n");
        
        
        if (is_null($output_image_filename)) $output_image_filename = str_replace("asc","png",$src_grid_filename);
        
        if (file_exists($output_image_filename)) return $output_image_filename;
        
        
        if (is_null($min) || is_null($max))
        {
            $stats = spatial_util::RasterStatisticsBasic($src_grid_filename);
            
            if (is_null($stats) || $stats instanceof ErrorMessage)
            {
                $min = null;
                $max = null;
            }
            else
            {
                if (is_null($min)) $min = $stats[spatial_util::$STAT_MINIMUM];
                if (is_null($max)) $max = $stats[spatial_util::$STAT_MAXIMUM];
            }
            
        }
        
        if (is_null($min) ||  is_null($max) )
        {            
            $min = null;
            $max = null;
        }

        
        list($scenario, $model, $time) =  explode("_",str_replace('.asc','',basename($src_grid_filename)));    
        
        $histogram_buckets = $max + 1;
        if ($histogram_buckets > 100) $histogram_buckets = 100;
        
        $gradient = RGB::ReverseGradient(RGB::GradientYellowOrangeRed());
        
        $ramp = null;
        if (!is_null($min) &&  !is_null($max) )
        {
            if ($max - $min > 0)
                // only create ramp if we have a valid min and max.
                $ramp = RGB::Ramp($min, $max, $histogram_buckets,$gradient);     
            else
            {
                $ramp = array();
                $ramp[$min] = util::first_element($gradient);
                $ramp[$max] = util::last_element($gradient);
            }
                
        }
        

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
        
        if (!is_null($ramp))
        {
            // we have a ramp so create percentage colour gradient.
            $pcent = 0;
            $step = round( 100 / count($ramp),0);
            foreach ($ramp as $index => $rgb) 
            {
                $rgb instanceof RGB;
                $color_table .= $pcent."% ".$rgb->Red()." ".$rgb->Green()." ".$rgb->Blue()." {$transparency}\n";        
                $pcent += $step;
            }
            
        }

        // save the colour lookup table 
        $fpc = file_put_contents($colour_txt, $color_table);
        if ($fpc === false) return new ErrorMessage(__METHOD__,__LINE__,"Failed to write to colour_txt {$colour_txt}");
            
        
        $cmd = "gdaldem  color-relief {$src_grid_filename} {$colour_txt} -nearest_color_entry -alpha -of PNG {$colour_png}";
        exec($cmd);  // generate a coloured image using colour lookup 

        
        
        // create backgound to put coloured image on top of
        $fpc = file_put_contents($colour_zero_txt, "nv 0 0 0 0\n0% {$background_colour}\n100% {$background_colour}\n"); // default is ALL Values = $background_colour  & No Value  = transparent  
        if ($fpc === false) return new ErrorMessage(__METHOD__,__LINE__,"Failed to create backgound to put coloured image on {$colour_zero_txt}");
        
        
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
-draw 'text  10,20 "Genus:{$genus}"' \
-draw 'text  10,40 "Species Richness"' \
-draw 'text 200,20 "Scenario: {$scenario}"' \
-draw 'text 400,20 "Model: {$model}"' \
-draw 'text 600,20 "Time: {$time}"' \
{$header_png};
HEADER;

        exec($header);


        if (!is_null($ramp))
        {
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

            
        }
        

        if (file_exists($colour_legend_png))
            $cmd = "convert {$header_png} {$colour_combined_png} {$colour_legend_png} -append {$output_image_filename}";    
        else 
            $cmd = "convert {$header_png} {$colour_combined_png} -append {$output_image_filename}";    
        
        
        exec($cmd);

        // might be better here to convert to a tmp image
        // and then copy back to the $output_image_filename
        

        file::Delete($colour_txt);
        file::Delete($colour_png);
        file::Delete($colour_zero_txt);
        file::Delete($colour_background_png);
        file::Delete($colour_combined_png);
        file::Delete($colour_legend_png);

        if (!file_exists($output_image_filename))
            return new ErrorMessage(__METHOD__,__LINE__,"Failed to write/ create  outputfile as {$output_image_filename}"); 
            
        
        return $output_image_filename; // filename of png that can be used - 

    }    
    
    
    
    public static function data_folder($genus)
        {

            file::mkdir_safe(configuration::Maxent_Species_Data_folder()."genus");

            $folder =    configuration::Maxent_Species_Data_folder()
                        ."genus"
                        .configuration::osPathDelimiter()
                        .$genus
                        .configuration::osPathDelimiter()
                        ;

            file::mkdir_safe($folder);

            return $folder;

        }

    
    /**
s     * Get list of Genus names where each species has the appropriate number of occurence records
     * 
     * @param type $minimum_modelled_count
     * @return type 
     */
    public static function ModelledForAllGenusNamesOnly($minimum_modelled_count = 10) 
    {
        
        $sql = " select 
                    g.name as genus
                 from modelled_climates mc  
                 left join taxa_lookup tl on (mc.species_id = tl.species_id ) 
                 left join genus g on (tl.genus_id = g.id)  
                 group by 
                    g.name  
                 having count(*) >= {$minimum_modelled_count}
                 order by 
                     g.name
               ";
        
        $result = DBO::Query($sql, 'species_id');
        if ($result instanceof ErrorMessage) 
            return ErrorMessage::Stacked(__METHOD__, __LINE__, "", true, $result);
        
        return matrix::Column($result, 'genus');
        
        
    }    
        
        
    /**
     *
     *  List of premodeeled data available in the Richness Folder
     *  
     */
    public static function RichnessPremodelledDataList() 
    {
        $result = array_util::Replace(file::Commandline2BasenamePath("find ".configuration::Maxent_Species_Data_folder()."richness/ByGenus -type d | sort"), 
                                        configuration::Maxent_Species_Data_folder(), 
                                        "")  ;
        
        unset($result['ByGenus']);
        
        return $result;
        
    }
    
    
    
}

?>


