<?php
class image_location
{

    public static function LatLong2CSV($pattern)
    {
        
        $files = file::LS("{$pattern}*", null, true);
        
        $header['latitude']  = '';
        $header['longitude'] = '';
        $header['altitude']  = '';
        $header['date']  = '';
        $header['time']  = '';
        $header['file_time']  = '';
        $header['direction']  = '';
        $header['filename']  = '';
        $header['pathname']  = '';
        
        $result =  join(",",array_keys($header))."\n";
        
        foreach ($files as $basename => $filename)
        {
            if (file_exists($filename))
                $result .= join(',',self::getLatLong($filename))."{$basename},{$filename}\n";    
        }

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
            $result .= htmlutil::KMLPlacemarker($name, $name, $loc['latitude'], $loc['longitude'], $loc['altitude'],$url_prefix);
        }

        $result .= htmlutil::KMLFooter();
        return $result;

    }

}
?>