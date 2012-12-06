<?php
class array_util
{

    public static function CleanStrings($src,$translation_array = null,$replace_non_printable = true,$default_char = "")
    {
        if (is_null($translation_array))
        {
            $translation_array = array();
            
            foreach (explode("",util::$EXTRA_CHARS) as $char) 
                $translation_array[$char] = $default_char;
            
        }
        else
        {
            if (is_string($translation_array))
            {
                $chars = $translation_array;
                
                $translation_array = array();
                foreach (str_split($chars) as $char) 
                {
                    $translation_array[$char] = $default_char;
                }
                    
                
            }
            
            
        }
        
        
        $result = array();
        
        // loop thru each src element
        foreach ($src as $key => $src_str) 
        {
        
            $result_str = $src_str;
            
            //ErrorMessage::Marker("STARTED:   $result_str");
            
            // for each source element do char translation
            foreach ($translation_array as $trans_key => $trans_value) 
            {
                //ErrorMessage::Marker("Cleaning: [{$trans_key}] to  [{$trans_value}]  $result_str");
                $result_str = str_replace($trans_key, $trans_value, $result_str);
            }
                
            //ErrorMessage::Marker("Cleaned chars:   $result_str");
            
            if ($replace_non_printable)
            {
                for ($asc_num = 0; $asc_num < 32; $asc_num++) 
                {
                    //ErrorMessage::Marker("Cleaning:  [".chr($asc_num)."] to  [{$default_char}]  $result_str");
                    $result_str = str_replace(chr($asc_num), $default_char, $result_str);
                }
                    
                
                for ($asc_num = 128; $asc_num <= 255; $asc_num++) 
                {
                   // ErrorMessage::Marker("Cleaning:  [".chr($asc_num)."] to  [{$default_char}]  $result_str");
                    $result_str = str_replace(chr($asc_num), $default_char, $result_str);
                }
                    
            }
            
            $result[$key] = $result_str;
            
            
        }
        
        
        return $result;
        
        
    }
    
    
    
    /*
    * @method arrayAverage
    * @param $src
    * @param $columnName
    * @return mixed
    */
    public static function Average($src, $column_name = null,$nullValue = null)
    {
        if (is_null($src)) return NULL;
        if (count($src) <= 0) return NULL;
        if (count($src) == 1) return util::first_element($src);

        $sum = self::Sum($src, $column_name,$nullValue);

        $count = self::Count($src, $column_name, $nullValue);

        if (is_null($count )) return NULL; // if count returned NULL then we can't do anything here.

        $avg = $sum / $count;

        return $avg;
    }

    public static function DeviationFromMean($src, $column_name = null,$nullValue = null)
    {
        if (!is_null($column_name)) $src = matrix::Column($src, $column_name);
        if (!is_null($nullValue)) $src = array_util::ExtractNullValues($src,$column_name,$nullValue);

        $mean = self::Average($src, null,$nullValue);

        $dev_result = array();
        foreach ($src as $key => $value)
            $dev_result[$key] = $value - $mean;

        return $dev_result;

    }


        // sum one of the  "columns" - extepcts $array to be an array of arrays
    public static function ExtractNullValues($src, $column_name = NULL,$nullValue = NULL)
    {

        $result = array();
        if (!is_null($column_name))
            $src = matrix::Column($src, $column_name); // if we are given a column name extract that column out

        foreach ($src as $key => $value)
        {
            if ($value == $nullValue || trim($value) == "") continue;
            $result[$key] = $value;
        }


        return $result;

    }




    // sum
    public static function Sum($src, $column_name = null,$nullValue = null)
    {
        if (!is_null($column_name))
            $src = matrix::Column($src, $column_name);

        if (!is_null($nullValue))
            $src = array_util::ExtractNullValues($src,$column_name,$nullValue);


        return array_sum($src);

    }

    public static function RunningSum($src, $column_name = null,$nullValue = null)
    {
        if (!is_null($column_name))
            $src = matrix::Column($src, $column_name);

        if (!is_null($nullValue))
            $src = array_util::ExtractNullValues($src,$column_name,$nullValue);


        $result = array();

        $running_total = 0;
        foreach ($src as $key => $value)
        {
            $running_total += $value;
            $result[$key] = $running_total;
        }

        return $result;

    }


    //
    public static function Count($src, $column_name = null,$nullValue = null)
    {
        if (!is_null($column_name))
            $src = matrix::Column($src, $column_name);

        if (!is_null($nullValue))
        {
            $src = array_util::ExtractNullValues($src,$column_name,$nullValue);
            if (count($src) == 0) return NULL; // if we have extract all NULL values and we have zero count then return NULL;
        }

        return count($src);

    }



    // Count Occurences of $toCount
    public static function CountAtDepth($src)
    {
        $result = array();
        foreach ($src as $key => $down_level)
        {
            $result[$key] = -1;
            if (is_array($down_level)) $result[$key] = count($down_level);
        }
        return $result;

    }

    // Count Occurences of that are Not Equalt to $toCount
    public static function CountNotValue($src, $toCount, $column_name = null)
    {
        if (!is_null($column_name))
            $src = matrix::Column($src, $column_name);

        if (count($src) == 0) return NULL; // if we have extract all NULL values and we have zero count then return NULL;

        $result = 0;
        foreach ($src as $value)
            if ($value != $toCount)  $result++;

        return $result;

    }

    // Count Occurences of that are Not Equalt to $toCount
    public static function CountValue($src, $toCount, $column_name = null)
    {
        if (!is_null($column_name))
            $src = matrix::Column($src, $column_name);

        if (count($src) == 0) return NULL; // if we have extract all NULL values and we have zero count then return NULL;

        $result = 0;
        foreach ($src as $value)
            if ($value == $toCount)  $result++;

        return $result;

    }



    // get the key of the first elemant that has a value ie. != $nullValue
    public static function FirstValueKey($src, $column_name = null,$nullValue = null)
    {
        if (!is_null($column_name))
            $src = matrix::Column($src, $column_name);

        if (!is_null($nullValue))
            $src = array_util::ExtractNullValues($src,$column_name,$nullValue);

        return util::first_key($src);

    }

    // get the key of the last elemant that has a value ie. != $nullValue
    public static function LastValueKey($src, $column_name = null,$nullValue = null)
    {
        if (!is_null($column_name))
            $src = matrix::Column($src, $column_name);

        if (!is_null($nullValue))
            $src = array_util::ExtractNullValues($src,$column_name,$nullValue);

        return util::last_key($src);

    }


    // Maximum valuie of array or column from matrix
    public static function Maximum($src, $column_name = NULL,$nullValue = NULL)
    {
        if (!is_null($column_name))
            $src = matrix::Column($src, $column_name);

        if (!is_null($nullValue))
            $src = array_util::ExtractNullValues($src,$column_name,$nullValue);

        rsort($src);

        return $src[0];

    }


    // sum one of the  "columns" - extepcts $array to be an array of arrays
    public static function Minimum($src, $column_name = NULL,$nullValue = NULL)
    {

        if (!is_null($column_name))
            $src = matrix::Column($src, $column_name);

        if (!is_null($nullValue))
            $src = array_util::ExtractNullValues($src,$column_name,$nullValue);

        sort($src);

        return $src[0];
    }


    // Unique Values from array
    public static function Unique($src, $column_name = null)
    {

        if (!is_null($column_name))
            $src = matrix::Column($src, $column_name);

        if (!is_null($nullValue))
            $src = array_util::ExtractNullValues($src,$column_name,$nullValue);

        return unique($src);


    }

    // conversion array key = old value, value = new value
    // $src key = values to normalise
    public static function Normalise($src, $columnName,$toMin = 0,$toMax = 100)
    {

        $unique = array();

        foreach ($src as $key => $value)
            $unique[$value[$columnName]] = $unique[$value[$columnName]] + 1;

        ksort($unique); // we can get min and max this way

        $fromMin = util::first_key($unique);
        $fromMax = util::last_key($unique);


        $fromRange = $fromMax - $fromMin;
        $toRange   = $toMax   - $toMin;


        $conversion = array();
        foreach ($unique as $old_value => $value_count)
            $conversion[$old_value] =  (( ($old_value - $fromMin) / $fromRange) * $toRange)  + $toMin ; // store old value with new normalise value


        $result = array();
        foreach ($src as $rowKey => $rowValue)
        {
            foreach ($rowValue as $cellKey => $cellValue)
            {
                $result[$rowKey][$cellKey] = $cellValue;

                // converst this value to it's normalised form
                if ($cellKey == $columnName)
                    $result[$rowKey][$columnName] = $conversion[$cellValue];

            }

        }

        return $result;

    }


    // search find and try to see if findIn contains the value in one of the elemnts of the array
    // return the value that it matcherd

    /*
    * @method arrayGetKeyForValueContain
    * @param $array
    * @param $find
    * @return mixed
    */
    public static function GetKeyForValueContain($array, $find)
    {
        $result = array();
        foreach ($array as $key => $value)
            if ( (util::contains($value,$find )) )
               return $key;

        return FALSE;

    }



    // search find and try to see if findIn contains the value in one of the elemnts of the array
    // return the value that it matcherd

    /*
    * @method arraySearch
    * @param $inArray
    * @param $find
    * @return mixed
    */
    public static function Search($inArray, $find)
    {

        foreach ($inArray as $key => $value)
            if ( (util::contains($value,$find)) or  (util::contains($key,$find)))
               return $value;

        return FALSE;

    }


    /*
     * filter the $src so that the result contains only elements that have the same keys as $find
     * WARNING: elements are links NOT COPIED
     */
    public static function FilterKeys($src, $find)
    {

        $result = array();
        foreach ($src as $key => $llv)
            if (array_key_exists($key, $find))
                $result[$key] = $llv;

        return $result;

    }

    /*
     * take a single column from an array of arrays
     */
    public static function Column($src, $column_name)
    {

        $result = array();
        foreach ($src as $key => $llv)
            $result[$key] = $llv[$column_name];

        return $result;

    }

    /*
     * multiply the "same" element from each side oif a single dimension array
     */
    public static function MultiplyOnKeys($lhs, $rhs)
    {

        $result = array();
        foreach ($lhs as $key => $value)
            $result[$key] = $lhs[$key] * $rhs[$key];

        return $result;

    }

    /*
     * multiply the "same" element from each side oif a single dimension array
     */
    public static function MultiplyByScalar($src, $scalar)
    {

        $result = array();
        foreach ($src as $key => $value)
            $result[$key] = $value * $scalar;

        return $result;

    }

    /*
     * multiply the "same" element from each side oif a single dimension array
     */
    public static function DivideByScalar($src, $scalar)
    {

        $result = array();
        foreach ($src as $key => $value)
            $result[$key] = $value / $scalar;

        return $result;

    }



    /*
    * @method Round
    * @param $fileArr
    * @param $min
    * @param $max
    * @return mixed
    */
    public static function Round($src, $places = 2)
    {
        $result = array();
        foreach ($src as $key => $value)
            $result[$key] = round($value,$places);

        return $result;
    }

    /*
    * @method Round
    * @param $fileArr
    * @param $min
    * @param $max
    * @return mixed
    */
    public static function Trim($src)
    {
        $result = array();
        foreach ($src as $key => $value)
            $result[$key] = trim($value);

        return $result;
    }

    // copy keyed values from
    public static function CopyTo($source, &$dest = null,$these_keys_only = null)
    {
        if (is_null($dest)) $dest = array();

        if (is_null($these_keys_only))
        {
            foreach ($source as $key => $value)
                $dest[$key] = $value;
        }
        else
        {
            $these_keys = (is_array($these_keys_only)) ? $these_keys_only : explode(",",$these_keys_only);
            foreach ($these_keys as $key)
                $dest[$key] = $source[$key];

        }

    }


    public static function Replace($src,$search, $replace)
    {
        $result = array();
        foreach ($src as $key => $value)
            $result[$key] = str_replace($search, $replace, $value);


        return $result;
    }

    public static function ReplaceInKey($src,$search, $replace)
    {
        $result = array();
        foreach ($src as $key => $value)
        {
            $key = str_replace($search, $replace, $key);
            $result[$key] = $value;
        }
            


        return $result;
    }

    
    

    /*
    * @method arrayElements
    * @param $srcArr
    * @param $start = 0
    * @param $end = -1
    * @return mixed
    */
    public static function Elements($srcArr, $start = 0, $end = -1)
    {
        if ($end == -1) $end = count($srcArr);

        $elemCount = 0;

        $result = array();
        foreach ($srcArr as $value)
        {
            if ( ($elemCount >=  $start) && ($elemCount < $end ) )
            {
               $result[$elemCount] =  $value;
            }

            $elemCount++;
        }

        return $result;
    }


    public static function FirstElementsThatContain($array, $find)
    {
        $elements = self::ElementsThatContain($array, $find);
        
        if (is_null($elements)) return null;
        
        if (count($elements) == 0 ) return null;
        
        $vals = array_values($elements);

        return $vals[0];
    }


    /*
    * @method arrayElementsThatContain
    * @param $array
    * @param $find
    * @return mixed
    */
    public static function ElementsThatContain($array, $find)
    {
        //logger::called();

        $result = array();
        foreach ($array as $key => $value)
            if ( (util::contains($key, $find))  or (util::contains($value, $find)) )
               $result[$key]  = $value;

        return $result;

    }

    public static function Contains($array, $find)
    {

        return (count(self::ElementsThatContain($array, $find)) > 0);
    }



    // search find and try to see if findIn contains the value in one of the elemnts of the array
    // return the value that it matcherd

    /*
    * @method arrayValueThatContain
    * @param $array
    * @param $findIn
    * @return mixed
    */
    public static function ValueThatContain($array, $findIn)
    {
        foreach ($array as $key => $value)
            if ( (util::contains($findIn, $value )) ) return $value;

        return FALSE;
    }

    /*
    * @method averageDistance
    * @param $array
    * @param $findIn
    * @return mixed
    */
    public static function AverageDistanceOfValues($array)
    {
        if (count($array) < 2) return NULL;

        $vals = array_values($array);

        $sumdist = 0;
        for ($index = 1; $index < count($vals); $index++)
        {
            $sumdist += $array[$index] - $array[$index - 1];
        }

        return $sumdist;

    }

        /*
    * @method CountOfValuesDistance
    * @param $array
    * Go though array and calculate the distace between index and index-1 save in array
    * then sort array by the count  and select the value with the highest count.
    * @return value
    */
    public static function CountOfValuesDistance($array)
    {
        if (count($array) < 2) return NULL;
        $vals = array_values($array);

        $counts = array();
        for ($index = 1; $index < count($vals); $index++)
        {
             $ans = abs( $vals[$index] - $vals[$index - 1] );
             $counts["$ans"]++;
        }
        arsort($counts);
        return $counts;

    }



    /*
    * @method arrayValue or default
    * @param $array
    * @param $findIn
    * @return mixed
    */
    public static function Value($array, $key,$default = null,$trim_string = false)
    {
        if (!is_array($array)) return $default;
        
        if (!array_key_exists($key, $array))  return $default;
                
        if (!$trim_string) return $array[$key];
        
        return trim($array[$key]);
        
    }

    
    

    /*
     * 
     * return key where value in array matches $findValue
     * 
    * @method findArrayKey
    * @param $findIn
    * @param $findValue
    * @return mixed
    */
    public static function ArrayKey($findIn, $findValue)
    {
        foreach ($findIn as $key => $value)
            if ($value == $findValue) return $key;

        return false;
    }


        /*
    * @method arrayMerge
    * @param $src
    * @param $mergeInto
    * @return mixed
    */
    public static function Merge($src, $mergeInto)
    {
        if (is_array($src) && is_array($mergeInto)) return array_merge($src,$mergeInto);

        if (is_array($src))
        {
            $src[] = $mergeInto;
            return $src;
        }

        if (is_array($mergeInto))
        {
            $mergeInto[] = $src;
            return $mergeInto;
        }

        $result = array();
        $result[] = $src;
        $result[] = $mergeInto;

        return $result;

    }


    /*
    * @method toArray
    * @param $src
    * @return mixed
    */
    public static function toArray($src)
    {
        if (is_array($src)) return $src;

        $result = array();
        $result[] = $src;
        return $result;

    }

    /*
    * @method getUniqueValues
    * @param $result
    * @return mixed
    */
    public static function UniqueValues($src, $columnName = null)
    {
        $unique = array();

        $first = util::first_element($src);

        if (is_array($first))
        {
            // array of arrays and they passed a column name and NO default value
            foreach ($src as $value)
            {
                if (!array_key_exists($value[$columnName], $unique))
                {
                    $unique[$value[$columnName]] = 1;
                }
                else
                {
                    $unique[$value[$columnName]] = $unique[$value[$columnName]] + 1;
                }
                
                
            }
                

        }
        else
        {
            foreach ($src as $value)
                $unique[$value] = $unique[$value] + 1;

        }

        return $unique;
    }




    // convert array of dates in YYYY-MM-DD or DD/MM/YYYY foirmat into a decimal of a year
    public static function dates2DecimalYear($dates)
    {
        $result = array();
        $first = util::first_element($dates);
        if (util::contains($first, '/'))          // dates are in format dd/mm/yyyy
        {

            foreach ($dates as $value)
            {
                $result[] =
                    (trim(util::rightStr($value, '/',FALSE))) +    // year
                    (trim(util::midStr($value, '/', '/')) / 12); // +  // month
                    (trim(util::leftStr($value, '/')) / 365) ;     // day
            }

            return $result;
        }

        if (util::contains($first, '-'))   // dates are in format yyyy-mm-dd
        {
            foreach ($dates as $value)
            {
                $result[] =
                    (trim(util::leftStr($value, '-')) )           +  // year
                    (trim(util::midStr($value, '-', '-')) / 12); //   +  // month
                    (trim(util::rightStr($value, '-',FALSE)) /365);  // day
            }

            return $result;
        }


        return NULL;

    }


    public static function DisplayKeyedArray($array,$delim = "\t")
    {
        if (is_null($array))
        {
            //echo "##ERROR: array_util::DisplayKeyedArray array passed is NULL\n";
            return NULL;
        }

        foreach ($array as $key => $value)
        {
            echo "$key{$delim}$value\n";
        }

    }

    public static function KeyedArrayToString($array,$delim = ",")
    {
        if (!is_array($array)) return null;
        if (is_null($array)) return NULL;

        $result = "";
        foreach ($array as $key => $value)
            $result .= "$key{$delim}$value\n";

        return $result;
    }



    public static function countdim($array)
    {
        if (is_array(reset($array)))
            $return = countdim(reset($array)) + 1;
        else
            $return = 1;

        return $return;
    }

    public static function histogram($array,$bucket_size)
    {
        $min = min($array);
        $max = max($array);
        $range = $max - $min;

        $bucket_count = floor($range / $bucket_size);

        $buckets = array(); // create bucket ID's
        for ($index = $min; $index < $max; $index += $bucket_size) $buckets= $index;
        $buckets[] = $max;

        // loop thru buckets
        $result = array();
        foreach ($buckets as $bucketID )
        {
            // for each bucket find array_values that are great than this buck but less than bucketID + $bucket_size
            foreach ($src as $src_value)
            {
                if ($src_value >= $bucketID && ($src_value < $bucketID + $bucket_size)) $result[$bucketID]++;
                if ($src_value == $max) $result[$max]++; // capture maximum value(s)
            }
        }

        unset($buckets);

        return $result;

    }


    // explode an array by delim and make first part a key and the second part the vlaue
    public static function explode($array,$delim = "=")
    {
        $result = array();
        foreach ($array as $value)
        {
            $pair = explode($delim, $value);
            $result[trim($pair[0])] = (isset($pair[1])) ? $pair[1] : null;
        }

        return $result;
    }

    // double explode an arrya - one issue is that if there are two keys of the same name then the loast value will be taken
    public static function double_explode($array,$delim_pairs = " ",$delim_KeyValue = "=")
    {
        $result = array();
        foreach ($array as $line)
        {
            $pairs = explode($delim_pairs, $line);

            $row = array();
            foreach ($pairs as $KeyValue)
            {
                $split_key_value = explode($delim_KeyValue,$KeyValue);
                $row[$split_key_value[0]] = (!array_key_exists(1, $split_key_value)) ? null :  $split_key_value[1];
            }
            $result[] = $row;
        }

        return $result;
    }



    // explode an array by delim and make first part a key and the second part the vlaue
    public static function JoinKeys($array,$delim = ",")
    {
        return join($delim,array_keys($array)) ;
    }


    /*
    * @method hostname
    * @return mixed
    */
    public static function ShowCount($src)
    {
        echo "array count: ".count($src)."\n";;
    }

    // need basin list to be in order of longest first
    public static function SortByLength($src,$ascending = true)
    {
        if (count($src) <= 1) return;

        $lengths = array();
        foreach ($src as $key => $value)
            $lengths[$key] = strlen($value);

        if ($ascending)
            asort($lengths);
        else
            arsort($lengths);


        $result = array();
        foreach ($lengths as $key => $length)
            $result[$key] = $src[$key];

        return $result;

    }


    public static function StripTags($src,$allowed_tags = null)
    {
        $result = array();
        foreach ($src as $key => $value)
        {
            $result[$key] = strip_tags($value,$allowed_tags);
        }

        return $result;

    }

    public static function ConvertTags($src,$tag,$to)
    {
        $result = array();
        foreach ($src as $key => $value)
        {
            $result[$key] = str_replace("<{$tag}>", $to, $value);
        }

        return $result;

    }


    public static function html_entity_decode($src)
    {
        $result = array();
        foreach ($src as $key => $value)
        {
            $result[$key] = html_entity_decode($value);
        }

        return $result;

    }

    public static function urldecode($src)
    {
        $result = array();
        foreach ($src as $key => $value)
        {
            $result[$key] = urldecode($value);
        }

        return $result;

    }


    /**
     *
     * $src = Bidimensional Array  $src[$row_id] = array($column_id => $column_value, $column_id => $column_value, ...    )
     *
     * Loop thru each row in $src and process template for each row
     *
     * Special Templates
     *  {#join, }  means a string = Key1,Value1 Key2,Value2 Key3,Value3
     *  {#join|_}  means a string = Key1|Value1_Key2|Value2_Key3|Value3
     *
     *  {#ROW_ID#} = is the key to the row
     *
     * default template = {row_id}=[{#join, }]
     *
     *
     */
    public static function FromTemplate($src,$rowTemplate = '{#row_id#}=[{#join, }]')
    {

        if (is_null($src)) return array();

        $result = array();
        foreach ($src as $src_rowID => $src_row)
        {

            if (is_null($src_row)) continue;
            if (!is_array($src_row)) 
            {
                $sub_result = $rowTemplate;
                $sub_result = str_replace('{#row_id#}', $src_rowID, $sub_result);
                $sub_result = str_replace('{#key#}', $src_rowID, $sub_result);
                $sub_result = str_replace('{#value#}', $src_row, $sub_result);
                
                $result[$src_rowID] = $sub_result;

            }
            else
            {


                if (util::contains($sub_result,'{#join',false))
                {
                    $delims = str_split(util::midStr($sub_result, '{#join', '}', true,false));

                    $d1 = $delims[0];
                    $d2 = $d1;
                    if (array_key_exists(1, $delims)) $d2 = $delims[1];

                    $KeyValues = "";
                    foreach ($src_row as $key => $value)
                    {
                        if (is_null($value)) $value = "NULL";
                        $KeyValues .= "{$key}{$d1}{$value}{$d2}";
                    }


                    util::trim_end($KeyValues, $d2);

                    $to_replace = '{#join'.util::midStr($sub_result, "{#join", "}", true, true)."}";

                    $sub_result = str_replace($to_replace,  $KeyValues, $sub_result);

                }


                foreach ($src_row as $key => $value)
                {
                    $sub_result = str_replace("{{$key}}",  $value, $sub_result);
                }

                $result[$src_rowID] = $sub_result;

            }



        }



        return $result;
    }


    public static function RemoveElements($from,$exclude_list) {
        
    }
    
    

}
?>