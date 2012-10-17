<?php

class stats
{
    // Function to calculate square of value - mean
    public static function sd_square($x, $mean) { return pow($x - $mean,2); }

    // Function to calculate standard deviation (uses sd_square)
    public static function sd($array)
    {
        if (count($array) <= 1 ) return 0; // deviation away from a single number is zero

        // square root of sum of squares devided by N-1
        return sqrt(array_sum(array_map("stats::sd_square", $array, array_fill(0,count($array), (array_sum($array) / count($array)) ) ) ) / (count($array)-1) );
    }

    public static function StandardDeviation($src, $columnName = null,$nullValue = null)
    {
        $useableValues = array_util::ExtractNullValues($src, $columnName, $nullValue);
        return self::sd($useableValues);
    }


    // Function to calculate square of value - mean
    public static function median($src, $nullValue = null)
    {
        if (count($src) == 0) return NULL;
        if (count($src) == 1) array_util::Sum($src);

        $useableValues = (is_null($nullValue))? $src : array_util::ExtractNullValues($src, null, $nullValue);

        if (count($useableValues) == 0) return $nullValue; // if we have no useable values that means all values where nullValue
        
        sort($useableValues);

        $div2 = (count($useableValues) / 2);
        if (count($useableValues) % 2  == 0)
            $result = ($useableValues[$div2] + $useableValues[$div2 - 1]) / 2;
        else
            $result = $useableValues[ceil($div2) - 1]; // odd

        return $result;
    }

     /* Moving Average -
     * Array     - $src
     * window    - number of elements to average at one time
     * start     - element to start with
     * key_ofset - if NOT = 0  then we need to restructure the result so that the first average value is connectoed to the original key ofset by  $key_offset
     *
     */
    public static function MovingAverageAtMiddleWindow($src,$window, $null_value)
    {

        $values = array_values($src);
        $keys = array_keys($src);


        // if we have less values then window return FALSE
        if (count($values) <= $window) return FALSE;

        $result = array();

        // fill first bit of result wioth null value as this can't be used with a  window mean
        for ($fill = 0; $fill < floor($window/2); $fill++)
             $result[$fill] = $null_value;

        $to = (count($src) - floor($window/2));

        for ($outer = floor($window/2); $outer < $to ; $outer++)
        {

            $windowSum = 0;
            $inner_from = ($outer - floor($window/2));
            $inner_to = $inner_from+$window;
            for ($inner = $inner_from ; $inner < $inner_to; $inner++)
            {
                $windowSum += $values[$inner];
            }

            $result[$outer] = ($windowSum/$window);

        }

        // fill the last bit of result with null_value
        for ($fill = (count($src) - (floor($window/2))); $fill < count($src); $fill++)
             $result[$fill] = $null_value;

         $keyed_result = array();
         for ($k = 0; $k < count($keys); $k++)
            $keyed_result[$keys[$k]] = $result[$k];

        return $keyed_result;

    }



    // given $x the non-chnage / statuc variable you are comparing agaiants - usually X axis
    public static function MatrixRow_linear_regression($matrix,$x)
    {
        $regression = array();
        foreach ($matrix as $rowid => $row)
            $regression[$rowid] = self::linear_regression($x, array_values($row));

        return $regression;
    }


    public static function linear_regression_with_keys($src,$null_value)
    {
        $data = array_util::ExtractNullValues($src, null, $null_value);

        if (count($data) <= 1) return array("slope" => 0, "intercept" => 0);

        return self::linear_regression(array_keys($data), array_values($data));
    }


    /**
     * linear regression function
     * @param $x array x-coords
     * @param $y array y-coords
     * @returns array() m=>slope, b=>intercept
     *
     * linear_regression(array(1, 2, 3, 4), array(1.5, 1.6, 2.1, 3.0)) );
     * REF: http://richardathome.wordpress.com/2006/01/25/a-php-linear-regression-function/
     */
    public static function linear_regression($x_values, $y_values) {

      $x = array_values($x_values);
      $y = array_values($y_values);

      // calculate number points
      $n = count($x);

      // ensure both arrays of points are the same size
      if ($n != count($y)) {
        trigger_error("linear_regression(): Number of elements in coordinate arrays do not match.", E_USER_ERROR);
      }

      // calculate sums
      $x_sum = array_sum($x);
      $y_sum = array_sum($y);

      $xx_sum = 0;
      $xy_sum = 0;

      for($i = 0; $i < $n; $i++)
      {
        $xy_sum+=($x[$i]*$y[$i]);
        $xx_sum+=($x[$i]*$x[$i]);
      }

      // calculate slope
      $m = (($n * $xy_sum) - ($x_sum * $y_sum)) / (($n * $xx_sum) - ($x_sum * $x_sum));

      // calculate intercept
      $b = ($y_sum - ($m * $x_sum)) / $n;

      // return result
      return array("slope"=>$m, "intercept"=>$b);

    }


    // very slow - but uses R - but can handle lots of data
    public static function linear_regression_R($x, $y)
    {

        $Rfilename = "/tmp/r_glm.r";

        $out =  "a = c(".join(',',$x).")"."\n".
                "b = c(".join(',',$y).")"."\n".
                "glm(b~a)"."\n";

        file::reallyDelete($Rfilename);
        file_put_contents($Rfilename, $out);

        if (!file_exists($Rfilename)) return NULL;

        $output = array();
        exec('R --vanilla -q < '.$Rfilename,$output);

        $slope_intercept = explode(' ',trim(str_replace('  ', '', $output[8])));

        $result = array();
        $result['slope'] = trim($slope_intercept[1]);
        $result['intercept'] = trim($slope_intercept[0]);

        unset($output);

        return $result;

    }

    public static function histogram_buckets($min,$max,$bucket_size)
    {
        $range = $max - $min;
        $bucket_count = floor($range / $bucket_size);
        $buckets = array(); // create bucket ID's
        for ($index = $min; $index < $max; $index += $bucket_size) $buckets[] = $index;
        $buckets[] = $max;
        return $buckets;
    }


    public static function find_histogram_bucket($buckets, $value_to_place)
    {
        $first = util::first_element($buckets);
        $last  = util::last_element($buckets);

        if ($value_to_place <= $first) return $first;
        if ($value_to_place >= $last) return $last;

        for ($bucket_index = 0; $bucket_index < count($buckets) - 1; $bucket_index++)
        {
            $bucket_start_value = $buckets[$bucket_index];
            $bucket_end_value   = $buckets[$bucket_index + 1];

            // "searching  $bucket_start_value < $value_to_place > $bucket_end_value \n";
            // for each bucket find array_values that are great than this buck but less than bucketID + $bucket_size
            if ($value_to_place >= $bucket_start_value && $value_to_place < $bucket_end_value)
                return $bucket_start_value;
        }

        return null;

    }


    public static function normal_distribution($mean, $sd, $min = null, $max = null, $count = 12.0)
    {
        if (is_null($min)) $min = $mean - ($sd * 3.0);
        if (is_null($max)) $max = $mean + ($sd * 3.0);

        $max = max($min,$max);
        $min = min($min,$max);

        $step = ($max - $min) / $count;

        $result = array();

        for ($x = $min; $x <= $max; $x += $step)
        {
            $p = self::normal($x, $mean, $sd);
            $result["{$x}"] = $p;
        }

        return $result;

    }

    private static function normal($x, $mean, $sd)
    {

        $pis = sqrt(2 * pi());
        $p1 = 1 / ($sd * $pis );
        $p2t = pow(($x - $mean),2 );
        $p2b = 2 * (pow($sd,2)) ;

        $p2 = -1 * ( $p2t/$p2b )  ;

        $f = $p1 * exp($p2);

        $result = $f;
        return $result;
    }


}
?>