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

        return $out;

    }


    /*
     *
     */
    public static function RasterStatisticsBasic($filename,$band = "1")
    {

        // try to get a better / higher precision version first
        $precision = self::RasterStatisticsPrecision($filename,$band);
        if (!is_null($precision)) return $precision;

        if (util::contains($filename, "shp")) return null;
        
        $result = array();
        $cmd = "gdalinfo -stats {$filename} | grep 'Band {$band}' -A 3";
        exec($cmd, $result);

        if (count($result) == 0 ) return null;

        // Minimum=0.000, Maximum=255.000, Mean=65.600, StdDev=99.236

        $stats_line_raw = array_util::FirstElementsThatContain($result, "Minimum");
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



    // ogrinfo -so Australia_states.shp | grep ":" | grep -v "INFO"

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


}
?>