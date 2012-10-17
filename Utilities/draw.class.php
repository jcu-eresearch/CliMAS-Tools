<?php
class draw {

    public static function canvas($width = 500, $height = 500, $background = 'black')
    {
        $image = imagecreatetruecolor($width, $height);

        $bg_pen = self::addColor($image, $background);
        imageFilledRectangle($image, 0, 0, $width - 1, $height - 1, $bg_pen);

        return $image;
    }

    public static function addColor($image, $color = NULL)
    {
        if (is_integer($color)) return $color;

        if (is_null($color))
            return self::assignColorName($image, 'pink');

        if (is_string($color))
        {
            if (util::first_char($color) == "#")
            {
                list($r,$g,$b) = array(substr($color,1,2),substr($color,3,2),substr($color,5,2)); // #RRGGBB
                return self::assignRGB($image, $r, $g, $b);
            }
            else
                return self::assignColorName($image, $color);// color name
        }

        if (is_array($color))
        {
            return self::assignRGB($image, $color[color::$RED], $color[color::$GREEN], $color[color::$BLUE]);
        }


        return NULL;
    }


    public static function assignRGB($image, $R,$G,$B)
    {
        $allocated = imageColorAllocate($image, $R,$G,$B);
        return $allocated;
    }


    public static function assignColor($image, $color)
    {
        if (!is_a($color, 'color')) return NULL;
        $rgb = $color->RGB();
        $allocated = imageColorAllocate($image, $rgb[color::$RED],$rgb[color::$GREEN],$rgb[color::$BLUE]);
        return $allocated;
    }

    public static function assignColorName($image, $color_name)
    {
        $rgb = color::name($color_name);
        $allocated = imageColorAllocate($image, $rgb[color::$RED],$rgb[color::$GREEN],$rgb[color::$BLUE]);
        return $allocated;
    }

    public static function box($image, $top, $left, $width, $height,$color = 'blue')
    {
        $pen = self::addColor($image, $color);
        imageFilledRectangle($image, $left,$top, $left + $width - 1, $top + $height - 1, $pen);
    }

    public static function line($image, $from_x, $from_y, $to_x, $to_y,$color = 'blue')
    {
        $pen = self::addColor($image, $color);
        imageline($image, $from_x, $from_y, $to_x, $to_y, $pen );
    }

    public static function save($image,$filename)
    {
        $ext = util::fromLastChar($filename, '.');
        $filename = str_replace(".$ext", ".png", $filename);
        imagePNG($image,$filename);  // now send the picture to the client (this outputs all image data directly)
    }




    // ref:: http://stackoverflow.com/questions/1890409/change-hue-of-an-image-with-php-gd-library
    public static function rotateImageHUE(&$image, $angle) {
        if($angle % 360 == 0) return;
        $width = imagesx($image);
        $height = imagesy($image);

        for($x = 0; $x < $width; $x++) {
            for($y = 0; $y < $height; $y++) {
                $rgb = imagecolorat($image, $x, $y);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;
                list($h, $s, $l) = color::rgb2hsl($r, $g, $b);
                $h += $angle / 360;
                if($h > 1) $h--;
                list($r, $g, $b) = color::hsl2rgb($h, $s, $l);
                imagesetpixel($image, $x, $y, imagecolorallocate($image, $r, $g, $b));
            }
        }
    }


    public static function addColorRange($image, $start_color_name = 'green', $color_count = 20,$color_step = 5)
    {
        $result = array();
        for ($index = 1; $index <= $color_count; $index = $index + 1) {
            $color_id = draw::addColor($image,color::rotateHUEfromName($start_color_name, $index * $color_step) );
            $result[$index - 1] = $color_id;
        }
        return $result;
    }


    public static function addStretechedColorRange($image, $min, $max, $start_color_name = 'green', $color_count = 20,$color_step = 5)
    {

        $color_ids = self::addColorRange($image, $start_color_name, $color_count + 1,$color_step);

        $range = $max - $min;
        $numeric_step = $range / $color_count;

        $result = array();
        $index = 0;
        for ($sc = $min; $sc <= $max; $sc = $sc + $numeric_step)
        {
            $result[$sc] = $color_ids[$index];
            $index++;
        }

        $result[$max] = $color_ids[count($color_ids) -1];
        return $result;
    }

}
?>