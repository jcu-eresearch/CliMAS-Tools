<?php

class image_location
{

    public static function LatLong($imageFilename)
    {
        $result = "";
        if (util::last_char($imageFilename) == "/")
        {
            $files = file::folder_files($imageFilename);
            $files = file::arrayFilter($files, ".jpg");

            foreach ($files as $fn)
                $result .= "".$fn.','.join(',',self::getLatLong($fn))."\n";

        }
        else
            $result = "".$imageFilename.','.join(',',self::getLatLong($imageFilename))."\n";

        return $result;

    }

    private static function getLatLong($imageFilename)
    {

        if (!file_exists($imageFilename))
        {
            return FALSE;
        }

        // Reading EXIF data:
        $exif = exif_read_data($imageFilename,0,TRUE);
        //if ($exif === FALSE) return FALSE;

        $fileDT = $exif['FILE']['FileDateTime'];
        
        //print_r($exif);
        $dt = explode(' ',$exif['EXIF']['DateTimeOriginal']);
      
        $img_date = "";
        $img_time = "";
        if (count($dt) == 2) 
        {
           $img_date = $dt[0];
           $img_time = $dt[1];
        }


        $lat =
        (self::d($exif["GPS"]["GPSLatitude"][0])) +
        (self::d($exif["GPS"]["GPSLatitude"][1])/60) +
        (self::d($exif["GPS"]["GPSLatitude"][2])/3600);
        if ($exif["GPS"]["GPSLatitudeRef"] == 'S') $lat = $lat * -1.0; // if south then it's negative

        $lon =
        (self::d($exif["GPS"]["GPSLongitude"][0])) +
        (self::d($exif["GPS"]["GPSLongitude"][1])/60) +
        (self::d($exif["GPS"]["GPSLongitude"][2])/3600);

        if ($exif["GPS"]["GPSLongitudeRef"] == 'W') $lon = $lon * -1.0;  // if west then it's negative

        $dir = self::d($exif["GPS"]["GPSImgDirection"]);

        $result = array();
        $result['latitude']  = $lat;
        $result['longitude'] = $lon;
        $result['altitude']  = self::d($exif["GPS"]["GPSAltitude"]);
        $result['date']  = str_replace(':',"-",$img_date);
        $result['time']  = $img_time;
        $result['file_time']  = $fileDT;
        $result['direction']  = $dir;
        

        return $result;

    }

    private static function d($GPSData)
    {
        $temp = explode('/',$GPSData);
        if (!is_array($temp)) return 0.0;
        
        if ($temp[1] == 0) return 0;
        
        return $temp[0] / $temp[1];
    }


    public static function image2KML($imageFilename)
    {
        $loc = self::getLatLong($imageFilename);

        $name =  util::rightStr($imageFilename, '/',FALSE);
        $result  = htmlutil::KMLHeader($name);
        $result .= htmlutil::KMLPlacemarker($name, $name, $loc['latitude'], $loc['longitude'], $loc['altitude']);
        $result .= htmlutil::KMLFooter();
        return $result;

    }


    public static function folder2KML($folder,$url_prefix = "")
    {

        $files = file::folder_files($folder);
        $files = file::arrayFilter($files, ".jpg");

        $result  = htmlutil::KMLHeader(str_replace('/', '_', $folder) );

        foreach ($files as $fn)
        {
            $loc = self::getLatLong($fn);
            $name = util::rightStr($fn, '/',false);
            $result .= htmlutil::KMLPlacemarker($name, $name, $loc['latitude'], $loc['longitude'], $loc['altitude']);
        }
        
        $result .= htmlutil::KMLFooter();
        return $result;

    }


}
?>
