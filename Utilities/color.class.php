<?php

class color
{

    public static $RED   = 'red';
    public static $GREEN = 'green';
    public static $BLUE  = 'blue';

    /*
    * @property Filename
    * @return mixed
    */
    public static function RGB()
    {
        if (func_num_args() == 0 ) return $this->RGB;

        if (func_num_args() == 1 && is_array(func_get_arg(0)))
        {
            $p = func_get_arg(0);
            $this->RGB[self::$RED]   = is_null($p[0]) ? 0 : $p[0];
            $this->RGB[self::$GREEN] = is_null($p[1]) ? 0 : $p[1];
            $this->RGB[self::$BLUE]  = is_null($p[2]) ? 0 : $p[2];
            return $this->RGB;
        }

        if (func_num_args() == 1 && is_string(func_get_arg(0)))
        {
            $this->RGB = self::name(func_get_arg(0));
            return $this->RGB;
        }


        if (func_num_args() == 3 )
        {
            $this->RGB[self::$RED]   = is_null(func_get_arg(0)) ? 0 : func_get_arg(0) ;
            $this->RGB[self::$GREEN] = is_null(func_get_arg(1)) ? 0 : func_get_arg(1) ;
            $this->RGB[self::$BLUE]  = is_null(func_get_arg(2)) ? 0 : func_get_arg(2) ;
            return $this->RGB;
        }

        return $this->RGB;

    }


    public static function asHex()
    {
        return sprintf("%02X%02X%02X%02X", $this->RGB[self::$RED], $this->RGB[self::$GREEN], $this->RGB[self::$BLUE]);
    }


    public static function name($color_name)
    {
        $color_name = strtolower($color_name);

        switch ($color_name) {
            case "black": return array( self::$RED=>0x00,  self::$GREEN=>0x00,  self::$BLUE=>0x00);
            case "maroon": return array( self::$RED=>0x80,  self::$GREEN=>0x00,  self::$BLUE=>0x00);
            case "green": return array( self::$RED=>0x00,  self::$GREEN=>0x80,  self::$BLUE=>0x00);
            case "olive": return array( self::$RED=>0x80,  self::$GREEN=>0x80,  self::$BLUE=>0x00);
            case "navy": return array( self::$RED=>0x00,  self::$GREEN=>0x00,  self::$BLUE=>0x80);
            case "purple": return array( self::$RED=>0x80,  self::$GREEN=>0x00,  self::$BLUE=>0x80);
            case "teal": return array( self::$RED=>0x00,  self::$GREEN=>0x80,  self::$BLUE=>0x80);
            case "gray": return array( self::$RED=>0x80,  self::$GREEN=>0x80,  self::$BLUE=>0x80);
            case "silver": return array( self::$RED=>0xC0,  self::$GREEN=>0xC0,  self::$BLUE=>0xC0);
            case "red": return array( self::$RED=>0xFF,  self::$GREEN=>0x00,  self::$BLUE=>0x00);
            case "lime": return array( self::$RED=>0x00,  self::$GREEN=>0xFF,  self::$BLUE=>0x00);
            case "yellow": return array( self::$RED=>0xFF,  self::$GREEN=>0xFF,  self::$BLUE=>0x00);
            case "blue": return array( self::$RED=>0x00,  self::$GREEN=>0x00,  self::$BLUE=>0xFF);
            case "fuchsia": return array( self::$RED=>0xFF,  self::$GREEN=>0x00,  self::$BLUE=>0xFF);
            case "aqua": return array( self::$RED=>0x00,  self::$GREEN=>0xFF,  self::$BLUE=>0xFF);
            case "white": return array( self::$RED=>0xFF,  self::$GREEN=>0xFF,  self::$BLUE=>0xFF);
            case "aliceblue": return array( self::$RED=>0xF0,  self::$GREEN=>0xF8,  self::$BLUE=>0xFF);
            case "antiquewhite": return array( self::$RED=>0xFA,  self::$GREEN=>0xEB,  self::$BLUE=>0xD7);
            case "aquamarine": return array( self::$RED=>0x7F,  self::$GREEN=>0xFF,  self::$BLUE=>0xD4);
            case "azure": return array( self::$RED=>0xF0,  self::$GREEN=>0xFF,  self::$BLUE=>0xFF);
            case "beige": return array( self::$RED=>0xF5,  self::$GREEN=>0xF5,  self::$BLUE=>0xDC);
            case "blueviolet": return array( self::$RED=>0x8A,  self::$GREEN=>0x2B,  self::$BLUE=>0xE2);
            case "brown": return array( self::$RED=>0xA5,  self::$GREEN=>0x2A,  self::$BLUE=>0x2A);
            case "burlywood": return array( self::$RED=>0xDE,  self::$GREEN=>0xB8,  self::$BLUE=>0x87);
            case "cadetblue": return array( self::$RED=>0x5F,  self::$GREEN=>0x9E,  self::$BLUE=>0xA0);
            case "chartreuse": return array( self::$RED=>0x7F,  self::$GREEN=>0xFF,  self::$BLUE=>0x00);
            case "chocolate": return array( self::$RED=>0xD2,  self::$GREEN=>0x69,  self::$BLUE=>0x1E);
            case "coral": return array( self::$RED=>0xFF,  self::$GREEN=>0x7F,  self::$BLUE=>0x50);
            case "cornflowerblue": return array( self::$RED=>0x64,  self::$GREEN=>0x95,  self::$BLUE=>0xED);
            case "cornsilk": return array( self::$RED=>0xFF,  self::$GREEN=>0xF8,  self::$BLUE=>0xDC);
            case "crimson": return array( self::$RED=>0xDC,  self::$GREEN=>0x14,  self::$BLUE=>0x3C);
            case "darkblue": return array( self::$RED=>0x00,  self::$GREEN=>0x00,  self::$BLUE=>0x8B);
            case "darkcyan": return array( self::$RED=>0x00,  self::$GREEN=>0x8B,  self::$BLUE=>0x8B);
            case "darkgoldenrod": return array( self::$RED=>0xB8,  self::$GREEN=>0x86,  self::$BLUE=>0x0B);
            case "darkgray": return array( self::$RED=>0xA9,  self::$GREEN=>0xA9,  self::$BLUE=>0xA9);
            case "darkgreen": return array( self::$RED=>0x00,  self::$GREEN=>0x64,  self::$BLUE=>0x00);
            case "darkkhaki": return array( self::$RED=>0xBD,  self::$GREEN=>0xB7,  self::$BLUE=>0x6B);
            case "darkmagenta": return array( self::$RED=>0x8B,  self::$GREEN=>0x00,  self::$BLUE=>0x8B);
            case "darkolivegreen": return array( self::$RED=>0x55,  self::$GREEN=>0x6B,  self::$BLUE=>0x2F);
            case "darkorange": return array( self::$RED=>0xFF,  self::$GREEN=>0x8C,  self::$BLUE=>0x00);
            case "darkorchid": return array( self::$RED=>0x99,  self::$GREEN=>0x32,  self::$BLUE=>0xCC);
            case "darkred": return array( self::$RED=>0x8B,  self::$GREEN=>0x00,  self::$BLUE=>0x00);
            case "darksalmon": return array( self::$RED=>0xE9,  self::$GREEN=>0x96,  self::$BLUE=>0x7A);
            case "darkseagreen": return array( self::$RED=>0x8F,  self::$GREEN=>0xBC,  self::$BLUE=>0x8F);
            case "darkslateblue": return array( self::$RED=>0x48,  self::$GREEN=>0x3D,  self::$BLUE=>0x8B);
            case "darkslategray": return array( self::$RED=>0x2F,  self::$GREEN=>0x4F,  self::$BLUE=>0x4F);
            case "darkturquoise": return array( self::$RED=>0x00,  self::$GREEN=>0xCE,  self::$BLUE=>0xD1);
            case "darkviolet": return array( self::$RED=>0x94,  self::$GREEN=>0x00,  self::$BLUE=>0xD3);
            case "deeppink": return array( self::$RED=>0xFF,  self::$GREEN=>0x14,  self::$BLUE=>0x93);
            case "deepskyblue": return array( self::$RED=>0x00,  self::$GREEN=>0xBF,  self::$BLUE=>0xFF);
            case "dimgray": return array( self::$RED=>0x69,  self::$GREEN=>0x69,  self::$BLUE=>0x69);
            case "dodgerblue": return array( self::$RED=>0x1E,  self::$GREEN=>0x90,  self::$BLUE=>0xFF);
            case "firebrick": return array( self::$RED=>0xB2,  self::$GREEN=>0x22,  self::$BLUE=>0x22);
            case "floralwhite": return array( self::$RED=>0xFF,  self::$GREEN=>0xFA,  self::$BLUE=>0xF0);
            case "forestgreen": return array( self::$RED=>0x22,  self::$GREEN=>0x8B,  self::$BLUE=>0x22);
            case "gainsboro": return array( self::$RED=>0xDC,  self::$GREEN=>0xDC,  self::$BLUE=>0xDC);
            case "ghostwhite": return array( self::$RED=>0xF8,  self::$GREEN=>0xF8,  self::$BLUE=>0xFF);
            case "gold": return array( self::$RED=>0xFF,  self::$GREEN=>0xD7,  self::$BLUE=>0x00);
            case "goldenrod": return array( self::$RED=>0xDA,  self::$GREEN=>0xA5,  self::$BLUE=>0x20);
            case "greenyellow": return array( self::$RED=>0xAD,  self::$GREEN=>0xFF,  self::$BLUE=>0x2F);
            case "honeydew": return array( self::$RED=>0xF0,  self::$GREEN=>0xFF,  self::$BLUE=>0xF0);
            case "hotpink": return array( self::$RED=>0xFF,  self::$GREEN=>0x69,  self::$BLUE=>0xB4);
            case "indianred": return array( self::$RED=>0xCD,  self::$GREEN=>0x5C,  self::$BLUE=>0x5C);
            case "indigo": return array( self::$RED=>0x4B,  self::$GREEN=>0x00,  self::$BLUE=>0x82);
            case "ivory": return array( self::$RED=>0xFF,  self::$GREEN=>0xFF,  self::$BLUE=>0xF0);
            case "khaki": return array( self::$RED=>0xF0,  self::$GREEN=>0xE6,  self::$BLUE=>0x8C);
            case "lavender": return array( self::$RED=>0xE6,  self::$GREEN=>0xE6,  self::$BLUE=>0xFA);
            case "lavenderblush": return array( self::$RED=>0xFF,  self::$GREEN=>0xF0,  self::$BLUE=>0xF5);
            case "lawngreen": return array( self::$RED=>0x7C,  self::$GREEN=>0xFC,  self::$BLUE=>0x00);
            case "lemonchiffon": return array( self::$RED=>0xFF,  self::$GREEN=>0xFA,  self::$BLUE=>0xCD);
            case "lightblue": return array( self::$RED=>0xAD,  self::$GREEN=>0xD8,  self::$BLUE=>0xE6);
            case "lightcoral": return array( self::$RED=>0xF0,  self::$GREEN=>0x80,  self::$BLUE=>0x80);
            case "lightcyan": return array( self::$RED=>0xE0,  self::$GREEN=>0xFF,  self::$BLUE=>0xFF);
            case "lightgoldenrodyellow": return array( self::$RED=>0xFA,  self::$GREEN=>0xFA,  self::$BLUE=>0xD2);
            case "lightgreen": return array( self::$RED=>0x90,  self::$GREEN=>0xEE,  self::$BLUE=>0x90);
            case "lightgrey": return array( self::$RED=>0xD3,  self::$GREEN=>0xD3,  self::$BLUE=>0xD3);
            case "lightpink": return array( self::$RED=>0xFF,  self::$GREEN=>0xB6,  self::$BLUE=>0xC1);
            case "lightsalmon": return array( self::$RED=>0xFF,  self::$GREEN=>0xA0,  self::$BLUE=>0x7A);
            case "lightseagreen": return array( self::$RED=>0x20,  self::$GREEN=>0xB2,  self::$BLUE=>0xAA);
            case "lightskyblue": return array( self::$RED=>0x87,  self::$GREEN=>0xCE,  self::$BLUE=>0xFA);
            case "lightslategray": return array( self::$RED=>0x77,  self::$GREEN=>0x88,  self::$BLUE=>0x99);
            case "lightsteelblue": return array( self::$RED=>0xB0,  self::$GREEN=>0xC4,  self::$BLUE=>0xDE);
            case "lightyellow": return array( self::$RED=>0xFF,  self::$GREEN=>0xFF,  self::$BLUE=>0xE0);
            case "limegreen": return array( self::$RED=>0x32,  self::$GREEN=>0xCD,  self::$BLUE=>0x32);
            case "linen": return array( self::$RED=>0xFA,  self::$GREEN=>0xF0,  self::$BLUE=>0xE6);
            case "mediumaquamarine": return array( self::$RED=>0x66,  self::$GREEN=>0xCD,  self::$BLUE=>0xAA);
            case "mediumblue": return array( self::$RED=>0x00,  self::$GREEN=>0x00,  self::$BLUE=>0xCD);
            case "mediumorchid": return array( self::$RED=>0xBA,  self::$GREEN=>0x55,  self::$BLUE=>0xD3);
            case "mediumpurple": return array( self::$RED=>0x93,  self::$GREEN=>0x70,  self::$BLUE=>0xD0);
            case "mediumseagreen": return array( self::$RED=>0x3C,  self::$GREEN=>0xB3,  self::$BLUE=>0x71);
            case "mediumslateblue": return array( self::$RED=>0x7B,  self::$GREEN=>0x68,  self::$BLUE=>0xEE);
            case "mediumspringgreen": return array( self::$RED=>0x00,  self::$GREEN=>0xFA,  self::$BLUE=>0x9A);
            case "mediumturquoise": return array( self::$RED=>0x48,  self::$GREEN=>0xD1,  self::$BLUE=>0xCC);
            case "mediumvioletred": return array( self::$RED=>0xC7,  self::$GREEN=>0x15,  self::$BLUE=>0x85);
            case "midnightblue": return array( self::$RED=>0x19,  self::$GREEN=>0x19,  self::$BLUE=>0x70);
            case "mintcream": return array( self::$RED=>0xF5,  self::$GREEN=>0xFF,  self::$BLUE=>0xFA);
            case "mistyrose": return array( self::$RED=>0xFF,  self::$GREEN=>0xE4,  self::$BLUE=>0xE1);
            case "moccasin": return array( self::$RED=>0xFF,  self::$GREEN=>0xE4,  self::$BLUE=>0xB5);
            case "navajowhite": return array( self::$RED=>0xFF,  self::$GREEN=>0xDE,  self::$BLUE=>0xAD);
            case "oldlace": return array( self::$RED=>0xFD,  self::$GREEN=>0xF5,  self::$BLUE=>0xE6);
            case "olivedrab": return array( self::$RED=>0x6B,  self::$GREEN=>0x8E,  self::$BLUE=>0x23);
            case "orange": return array( self::$RED=>0xFF,  self::$GREEN=>0xA5,  self::$BLUE=>0x00);
            case "orangered": return array( self::$RED=>0xFF,  self::$GREEN=>0x45,  self::$BLUE=>0x00);
            case "orchid": return array( self::$RED=>0xDA,  self::$GREEN=>0x70,  self::$BLUE=>0xD6);
            case "palegoldenrod": return array( self::$RED=>0xEE,  self::$GREEN=>0xE8,  self::$BLUE=>0xAA);
            case "palegreen": return array( self::$RED=>0x98,  self::$GREEN=>0xFB,  self::$BLUE=>0x98);
            case "paleturquoise": return array( self::$RED=>0xAF,  self::$GREEN=>0xEE,  self::$BLUE=>0xEE);
            case "palevioletred": return array( self::$RED=>0xDB,  self::$GREEN=>0x70,  self::$BLUE=>0x93);
            case "papayawhip": return array( self::$RED=>0xFF,  self::$GREEN=>0xEF,  self::$BLUE=>0xD5);
            case "peachpuff": return array( self::$RED=>0xFF,  self::$GREEN=>0xDA,  self::$BLUE=>0xB9);
            case "peru": return array( self::$RED=>0xCD,  self::$GREEN=>0x85,  self::$BLUE=>0x3F);
            case "pink": return array( self::$RED=>0xFF,  self::$GREEN=>0xC0,  self::$BLUE=>0xCB);
            case "plum": return array( self::$RED=>0xDD,  self::$GREEN=>0xA0,  self::$BLUE=>0xDD);
            case "powderblue": return array( self::$RED=>0xB0,  self::$GREEN=>0xE0,  self::$BLUE=>0xE6);
            case "rosybrown": return array( self::$RED=>0xBC,  self::$GREEN=>0x8F,  self::$BLUE=>0x8F);
            case "royalblue": return array( self::$RED=>0x41,  self::$GREEN=>0x69,  self::$BLUE=>0xE1);
            case "saddlebrown": return array( self::$RED=>0x8B,  self::$GREEN=>0x45,  self::$BLUE=>0x13);
            case "salmon": return array( self::$RED=>0xFA,  self::$GREEN=>0x80,  self::$BLUE=>0x72);
            case "sandybrown": return array( self::$RED=>0xF4,  self::$GREEN=>0xA4,  self::$BLUE=>0x60);
            case "seagreen": return array( self::$RED=>0x2E,  self::$GREEN=>0x8B,  self::$BLUE=>0x57);
            case "seashell": return array( self::$RED=>0xFF,  self::$GREEN=>0xF5,  self::$BLUE=>0xEE);
            case "sienna": return array( self::$RED=>0xA0,  self::$GREEN=>0x52,  self::$BLUE=>0x2D);
            case "skyblue": return array( self::$RED=>0x87,  self::$GREEN=>0xCE,  self::$BLUE=>0xEB);
            case "slateblue": return array( self::$RED=>0x6A,  self::$GREEN=>0x5A,  self::$BLUE=>0xCD);
            case "slategray": return array( self::$RED=>0x70,  self::$GREEN=>0x80,  self::$BLUE=>0x90);
            case "snow": return array( self::$RED=>0xFF,  self::$GREEN=>0xFA,  self::$BLUE=>0xFA);
            case "springgreen": return array( self::$RED=>0x00,  self::$GREEN=>0xFF,  self::$BLUE=>0x7F);
            case "steelblue": return array( self::$RED=>0x46,  self::$GREEN=>0x82,  self::$BLUE=>0xB4);
            case "tan": return array( self::$RED=>0xD2,  self::$GREEN=>0xB4,  self::$BLUE=>0x8C);
            case "thistle": return array( self::$RED=>0xD8,  self::$GREEN=>0xBF,  self::$BLUE=>0xD8);
            case "tomato": return array( self::$RED=>0xFF,  self::$GREEN=>0x63,  self::$BLUE=>0x47);
            case "turquoise": return array( self::$RED=>0x40,  self::$GREEN=>0xE0,  self::$BLUE=>0xD0);
            case "violet": return array( self::$RED=>0xEE,  self::$GREEN=>0x82,  self::$BLUE=>0xEE);
            case "wheat": return array( self::$RED=>0xF5,  self::$GREEN=>0xDE,  self::$BLUE=>0xB3);
            case "whitesmoke": return array( self::$RED=>0xF5,  self::$GREEN=>0xF5,  self::$BLUE=>0xF5);
            case "yellowgreen": return array( self::$RED=>0x9A,  self::$GREEN=>0xCD,  self::$BLUE=>0x32);

        }

        return array( self::$RED=>0x00,  self::$GREEN=>0x00,  self::$BLUE=>0x00);

    }


    // ref:: http://stackoverflow.com/questions/1890409/change-hue-of-an-image-with-php-gd-library
    public static function rgb2hsl($r, $g, $b) {
       $var_R = ($r / 255);
       $var_G = ($g / 255);
       $var_B = ($b / 255);

       $var_Min = min($var_R, $var_G, $var_B);
       $var_Max = max($var_R, $var_G, $var_B);
       $del_Max = $var_Max - $var_Min;

       $v = $var_Max;

       if ($del_Max == 0) {
          $h = 0;
          $s = 0;
       } else {
          $s = $del_Max / $var_Max;

          $del_R = ( ( ( $max - $var_R ) / 6 ) + ( $del_Max / 2 ) ) / $del_Max;
          $del_G = ( ( ( $max - $var_G ) / 6 ) + ( $del_Max / 2 ) ) / $del_Max;
          $del_B = ( ( ( $max - $var_B ) / 6 ) + ( $del_Max / 2 ) ) / $del_Max;

          if      ($var_R == $var_Max) $h = $del_B - $del_G;
          else if ($var_G == $var_Max) $h = ( 1 / 3 ) + $del_R - $del_B;
          else if ($var_B == $var_Max) $h = ( 2 / 3 ) + $del_G - $del_R;

          if ($H < 0) $h++;
          if ($H > 1) $h--;
       }

       return array($h, $s, $v);
    }

    // ref:: http://stackoverflow.com/questions/1890409/change-hue-of-an-image-with-php-gd-library
    public static function hsl2rgb($h, $s, $v) {
        if($s == 0) {
            $r = $g = $B = $v * 255;
        } else {
            $var_H = $h * 6;
            $var_i = floor( $var_H );
            $var_1 = $v * ( 1 - $s );
            $var_2 = $v * ( 1 - $s * ( $var_H - $var_i ) );
            $var_3 = $v * ( 1 - $s * (1 - ( $var_H - $var_i ) ) );

            if       ($var_i == 0) { $var_R = $v     ; $var_G = $var_3  ; $var_B = $var_1 ; }
            else if  ($var_i == 1) { $var_R = $var_2 ; $var_G = $v      ; $var_B = $var_1 ; }
            else if  ($var_i == 2) { $var_R = $var_1 ; $var_G = $v      ; $var_B = $var_3 ; }
            else if  ($var_i == 3) { $var_R = $var_1 ; $var_G = $var_2  ; $var_B = $v     ; }
            else if  ($var_i == 4) { $var_R = $var_3 ; $var_G = $var_1  ; $var_B = $v     ; }
            else                   { $var_R = $v     ; $var_G = $var_1  ; $var_B = $var_2 ; }

            $r = $var_R * 255;
            $g = $var_G * 255;
            $B = $var_B * 255;
        }
        return array($r, $g, $B);

    }

    public static function rotateHUEfromName($color_name,$angle)
    {
        $rgb = self::name($color_name);
        return self::rotateHUEfromRGB($rgb[self::$RED],$rgb[self::$GREEN],$rgb[self::$BLUE],$angle);
    }

    public static function rotateHUEfromRGB($r,$g,$b,$angle) {

        if($angle % 360 == 0) return;

        list($h, $s, $l) = self::rgb2hsl($r, $g, $b);
        $h += $angle / 360;
        if($h > 1) $h--;
        list($r, $g, $b) = self::hsl2rgb($h, $s, $l);

        return array( self::$RED => $r, self::$GREEN => $g, self::$BLUE => $b);
    }

    public static function saturationFromName($color_name,$saturation_change)
    {
        $rgb = self::name($color_name);
        $sat = self::saturationFromRGB($rgb[self::$RED],$rgb[self::$GREEN],$rgb[self::$BLUE],$saturation_change);
        return $sat;
    }

    public static function saturationFromRGB($r,$g,$b,$saturation_change) {

        if ($saturation_change <=   0) $saturation_change = 1;
        if ($saturation_change >= 100) $saturation_change = 100;

        list($h, $s, $l) = self::rgb2hsl($r, $g, $b);
        $s = ($saturation_change / 100) * $s;
        list($r, $g, $b) = self::hsl2rgb($h, $s, $l);
        return array( self::$RED => $r, self::$GREEN => $g, self::$BLUE => $b);
    }


}

?>
