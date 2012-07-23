<?php
class datetimeutil {

    /*
    * @method now
    * @return mixed
    */
    public static function now()
    {
        date_default_timezone_set("Australia/Queensland");
        $today = getdate();
        $result = sprintf("%02d", $today["hours"]).":".sprintf("%02d", $today["minutes"]).":".sprintf("%02d", $today["seconds"]);

        return $result;
    }

    public static function Today()
    {
        date_default_timezone_set("Australia/Queensland");
        $today = getdate();
        $result = sprintf("%04d", $today["year"])."-".sprintf("%02d", $today["mon"])."-".sprintf("%02d", $today["mday"]);
        return $result;
    }


    public static function NowDateTime()
    {
        $result = self::Today()." ".self::now();
        return $result;
    }


    /**
     *
     * Convert from a string like "30/02/2012"  to 2012-02-30
     * 
     * @param type $src
     * @param type $src_delim
     * @param type $to_delim 
     */
    public static function fromDMY2YMD($src, $to_delim = "-")
    {
        if (is_null($to_delim)) return null;
        
        if (!is_string($src)) return null;
        
        $src = trim($src);
        
        if ($src == "") return "";
        
        
        return substr($src,6,4).$to_delim.substr($src,3,2).$to_delim.substr($src,0,2);
    }
    
    
    public static function fromYMD($YearMonthDay, $delim = "-")
    {
        return strtotime("$YearMonthDay");
    }

    public static function addDays($YearMonthDay, $numDays)
    {
        return date("Y-m-d",strtotime("$YearMonthDay +".($numDays-1)." days"));
    }

    public static function subtractDays($YearMonthDay, $numDays)
    {
        return date("Y-m-d",strtotime("$YearMonthDay -".($numDays)." days"));
    }

    public static function lastDayDate($Year, $month)
    {
        return sprintf("%04d", $Year)."-".sprintf("%02d", $month)."-".sprintf('%02d', self::lastDay($Year, $month));
    }

    public static function lastDay($Year, $month)
    {
        switch ($month) {
            case  1: return 31; break;
            case  2:
                if ($Year % 400 == 0) return 29;
                if ($Year % 4   == 0) return 29;
                return 28;
                break;

            case  3: return 31; break;
            case  4: return 30; break;
            case  5: return 31; break;
            case  6: return 30; break;
            case  7: return 31; break;
            case  8: return 31; break;
            case  9: return 30; break;
            case 10: return 31; break;
            case 11: return 30; break;
            case 12: return 31; break;
        }

        return NULL;
    }


}
?>