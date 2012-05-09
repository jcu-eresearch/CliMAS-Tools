<?php

class datetimeutil {

    /*
    * @method now 
    * @return mixed
    */
    public static function now()
    {
        $today = getdate();    
        $result = sprintf("%02d", $today["hours"]).":".sprintf("%02d", $today["minutes"]).":".sprintf("%02d", $today["seconds"]);

        return $result;
    }

    public static function Today()
    {
        $today = getdate();
        $result = $today["year"]."-".$today["mon"]."-".$today["mday"];
        return $result;
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
