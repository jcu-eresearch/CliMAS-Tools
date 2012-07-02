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

        if (is_numeric($color_name))
            $color_name = $color_name % 135;

        switch ($color_name) {
            case "black":
            case "0":
                return array(self::$RED => 0x00, self::$GREEN => 0x00, self::$BLUE => 0x00);
            case "maroon": case "1": return array(self::$RED => 0x80, self::$GREEN => 0x00, self::$BLUE => 0x00);
            case "green": case "2": return array(self::$RED => 0x00, self::$GREEN => 0x80, self::$BLUE => 0x00);
            case "olive": case "3": return array(self::$RED => 0x80, self::$GREEN => 0x80, self::$BLUE => 0x00);
            case "navy": case "4": return array(self::$RED => 0x00, self::$GREEN => 0x00, self::$BLUE => 0x80);
            case "purple": case "5": return array(self::$RED => 0x80, self::$GREEN => 0x00, self::$BLUE => 0x80);
            case "teal": case "6": return array(self::$RED => 0x00, self::$GREEN => 0x80, self::$BLUE => 0x80);
            case "gray": case "7": return array(self::$RED => 0x80, self::$GREEN => 0x80, self::$BLUE => 0x80);
            case "silver": case "8": return array(self::$RED => 0xC0, self::$GREEN => 0xC0, self::$BLUE => 0xC0);
            case "red": case "9": return array(self::$RED => 0xFF, self::$GREEN => 0x00, self::$BLUE => 0x00);
            case "lime": case "10": return array(self::$RED => 0x00, self::$GREEN => 0xFF, self::$BLUE => 0x00);
            case "yellow": case "11": return array(self::$RED => 0xFF, self::$GREEN => 0xFF, self::$BLUE => 0x00);
            case "blue": case "12": return array(self::$RED => 0x00, self::$GREEN => 0x00, self::$BLUE => 0xFF);
            case "fuchsia": case "13": return array(self::$RED => 0xFF, self::$GREEN => 0x00, self::$BLUE => 0xFF);
            case "aqua": case "14": return array(self::$RED => 0x00, self::$GREEN => 0xFF, self::$BLUE => 0xFF);
            case "white": case "15": return array(self::$RED => 0xFF, self::$GREEN => 0xFF, self::$BLUE => 0xFF);
            case "aliceblue": case "16": return array(self::$RED => 0xF0, self::$GREEN => 0xF8, self::$BLUE => 0xFF);
            case "antiquewhite": case "17": return array(self::$RED => 0xFA, self::$GREEN => 0xEB, self::$BLUE => 0xD7);
            case "aquamarine": case "18": return array(self::$RED => 0x7F, self::$GREEN => 0xFF, self::$BLUE => 0xD4);
            case "azure": case "19": return array(self::$RED => 0xF0, self::$GREEN => 0xFF, self::$BLUE => 0xFF);
            case "beige": case "20": return array(self::$RED => 0xF5, self::$GREEN => 0xF5, self::$BLUE => 0xDC);
            case "blueviolet": case "21": return array(self::$RED => 0x8A, self::$GREEN => 0x2B, self::$BLUE => 0xE2);
            case "brown": case "22": return array(self::$RED => 0xA5, self::$GREEN => 0x2A, self::$BLUE => 0x2A);
            case "burlywood": case "23": return array(self::$RED => 0xDE, self::$GREEN => 0xB8, self::$BLUE => 0x87);
            case "cadetblue": case "24": return array(self::$RED => 0x5F, self::$GREEN => 0x9E, self::$BLUE => 0xA0);
            case "chartreuse": case "25": return array(self::$RED => 0x7F, self::$GREEN => 0xFF, self::$BLUE => 0x00);
            case "chocolate": case "26": return array(self::$RED => 0xD2, self::$GREEN => 0x69, self::$BLUE => 0x1E);
            case "coral": case "27": return array(self::$RED => 0xFF, self::$GREEN => 0x7F, self::$BLUE => 0x50);
            case "cornflowerblue": case "28": return array(self::$RED => 0x64, self::$GREEN => 0x95, self::$BLUE => 0xED);
            case "cornsilk": case "29": return array(self::$RED => 0xFF, self::$GREEN => 0xF8, self::$BLUE => 0xDC);
            case "crimson": case "30": return array(self::$RED => 0xDC, self::$GREEN => 0x14, self::$BLUE => 0x3C);
            case "darkblue": case "31": return array(self::$RED => 0x00, self::$GREEN => 0x00, self::$BLUE => 0x8B);
            case "darkcyan": case "32": return array(self::$RED => 0x00, self::$GREEN => 0x8B, self::$BLUE => 0x8B);
            case "darkgoldenrod": case "33": return array(self::$RED => 0xB8, self::$GREEN => 0x86, self::$BLUE => 0x0B);
            case "darkgray": case "34": return array(self::$RED => 0xA9, self::$GREEN => 0xA9, self::$BLUE => 0xA9);
            case "darkgreen": case "35": return array(self::$RED => 0x00, self::$GREEN => 0x64, self::$BLUE => 0x00);
            case "darkkhaki": case "36": return array(self::$RED => 0xBD, self::$GREEN => 0xB7, self::$BLUE => 0x6B);
            case "darkmagenta": case "37": return array(self::$RED => 0x8B, self::$GREEN => 0x00, self::$BLUE => 0x8B);
            case "darkolivegreen": case "38": return array(self::$RED => 0x55, self::$GREEN => 0x6B, self::$BLUE => 0x2F);
            case "darkorange": case "39": return array(self::$RED => 0xFF, self::$GREEN => 0x8C, self::$BLUE => 0x00);
            case "darkorchid": case "40": return array(self::$RED => 0x99, self::$GREEN => 0x32, self::$BLUE => 0xCC);
            case "darkred": case "41": return array(self::$RED => 0x8B, self::$GREEN => 0x00, self::$BLUE => 0x00);
            case "darksalmon": case "42": return array(self::$RED => 0xE9, self::$GREEN => 0x96, self::$BLUE => 0x7A);
            case "darkseagreen": case "43": return array(self::$RED => 0x8F, self::$GREEN => 0xBC, self::$BLUE => 0x8F);
            case "darkslateblue": case "44": return array(self::$RED => 0x48, self::$GREEN => 0x3D, self::$BLUE => 0x8B);
            case "darkslategray": case "45": return array(self::$RED => 0x2F, self::$GREEN => 0x4F, self::$BLUE => 0x4F);
            case "darkturquoise": case "46": return array(self::$RED => 0x00, self::$GREEN => 0xCE, self::$BLUE => 0xD1);
            case "darkviolet": case "47": return array(self::$RED => 0x94, self::$GREEN => 0x00, self::$BLUE => 0xD3);
            case "deeppink": case "48": return array(self::$RED => 0xFF, self::$GREEN => 0x14, self::$BLUE => 0x93);
            case "deepskyblue": case "49": return array(self::$RED => 0x00, self::$GREEN => 0xBF, self::$BLUE => 0xFF);
            case "dimgray": case "50": return array(self::$RED => 0x69, self::$GREEN => 0x69, self::$BLUE => 0x69);
            case "dodgerblue": case "51": return array(self::$RED => 0x1E, self::$GREEN => 0x90, self::$BLUE => 0xFF);
            case "firebrick": case "52": return array(self::$RED => 0xB2, self::$GREEN => 0x22, self::$BLUE => 0x22);
            case "floralwhite": case "53": return array(self::$RED => 0xFF, self::$GREEN => 0xFA, self::$BLUE => 0xF0);
            case "forestgreen": case "54": return array(self::$RED => 0x22, self::$GREEN => 0x8B, self::$BLUE => 0x22);
            case "gainsboro": case "55": return array(self::$RED => 0xDC, self::$GREEN => 0xDC, self::$BLUE => 0xDC);
            case "ghostwhite": case "56": return array(self::$RED => 0xF8, self::$GREEN => 0xF8, self::$BLUE => 0xFF);
            case "gold": case "57": return array(self::$RED => 0xFF, self::$GREEN => 0xD7, self::$BLUE => 0x00);
            case "goldenrod": case "58": return array(self::$RED => 0xDA, self::$GREEN => 0xA5, self::$BLUE => 0x20);
            case "greenyellow": case "59": return array(self::$RED => 0xAD, self::$GREEN => 0xFF, self::$BLUE => 0x2F);
            case "honeydew": case "60": return array(self::$RED => 0xF0, self::$GREEN => 0xFF, self::$BLUE => 0xF0);
            case "hotpink": case "61": return array(self::$RED => 0xFF, self::$GREEN => 0x69, self::$BLUE => 0xB4);
            case "indianred": case "62": return array(self::$RED => 0xCD, self::$GREEN => 0x5C, self::$BLUE => 0x5C);
            case "indigo": case "63": return array(self::$RED => 0x4B, self::$GREEN => 0x00, self::$BLUE => 0x82);
            case "ivory": case "64": return array(self::$RED => 0xFF, self::$GREEN => 0xFF, self::$BLUE => 0xF0);
            case "khaki": case "65": return array(self::$RED => 0xF0, self::$GREEN => 0xE6, self::$BLUE => 0x8C);
            case "lavender": case "66": return array(self::$RED => 0xE6, self::$GREEN => 0xE6, self::$BLUE => 0xFA);
            case "lavenderblush": case "67": return array(self::$RED => 0xFF, self::$GREEN => 0xF0, self::$BLUE => 0xF5);
            case "lawngreen": case "68": return array(self::$RED => 0x7C, self::$GREEN => 0xFC, self::$BLUE => 0x00);
            case "lemonchiffon": case "69": return array(self::$RED => 0xFF, self::$GREEN => 0xFA, self::$BLUE => 0xCD);
            case "lightblue": case "70": return array(self::$RED => 0xAD, self::$GREEN => 0xD8, self::$BLUE => 0xE6);
            case "lightcoral": case "71": return array(self::$RED => 0xF0, self::$GREEN => 0x80, self::$BLUE => 0x80);
            case "lightcyan": case "72": return array(self::$RED => 0xE0, self::$GREEN => 0xFF, self::$BLUE => 0xFF);
            case "lightgoldenrodyellow": case "73": return array(self::$RED => 0xFA, self::$GREEN => 0xFA, self::$BLUE => 0xD2);
            case "lightgreen": case "74": return array(self::$RED => 0x90, self::$GREEN => 0xEE, self::$BLUE => 0x90);
            case "lightgrey": case "75": return array(self::$RED => 0xD3, self::$GREEN => 0xD3, self::$BLUE => 0xD3);
            case "lightpink": case "76": return array(self::$RED => 0xFF, self::$GREEN => 0xB6, self::$BLUE => 0xC1);
            case "lightsalmon": case "77": return array(self::$RED => 0xFF, self::$GREEN => 0xA0, self::$BLUE => 0x7A);
            case "lightseagreen": case "78": return array(self::$RED => 0x20, self::$GREEN => 0xB2, self::$BLUE => 0xAA);
            case "lightskyblue": case "79": return array(self::$RED => 0x87, self::$GREEN => 0xCE, self::$BLUE => 0xFA);
            case "lightslategray": case "80": return array(self::$RED => 0x77, self::$GREEN => 0x88, self::$BLUE => 0x99);
            case "lightsteelblue": case "81": return array(self::$RED => 0xB0, self::$GREEN => 0xC4, self::$BLUE => 0xDE);
            case "lightyellow": case "82": return array(self::$RED => 0xFF, self::$GREEN => 0xFF, self::$BLUE => 0xE0);
            case "limegreen": case "83": return array(self::$RED => 0x32, self::$GREEN => 0xCD, self::$BLUE => 0x32);
            case "linen": case "84": return array(self::$RED => 0xFA, self::$GREEN => 0xF0, self::$BLUE => 0xE6);
            case "mediumaquamarine": case "85": return array(self::$RED => 0x66, self::$GREEN => 0xCD, self::$BLUE => 0xAA);
            case "mediumblue": case "86": return array(self::$RED => 0x00, self::$GREEN => 0x00, self::$BLUE => 0xCD);
            case "mediumorchid": case "87": return array(self::$RED => 0xBA, self::$GREEN => 0x55, self::$BLUE => 0xD3);
            case "mediumpurple": case "88": return array(self::$RED => 0x93, self::$GREEN => 0x70, self::$BLUE => 0xD0);
            case "mediumseagreen": case "89": return array(self::$RED => 0x3C, self::$GREEN => 0xB3, self::$BLUE => 0x71);
            case "mediumslateblue": case "90": return array(self::$RED => 0x7B, self::$GREEN => 0x68, self::$BLUE => 0xEE);
            case "mediumspringgreen": case "91": return array(self::$RED => 0x00, self::$GREEN => 0xFA, self::$BLUE => 0x9A);
            case "mediumturquoise": case "92": return array(self::$RED => 0x48, self::$GREEN => 0xD1, self::$BLUE => 0xCC);
            case "mediumvioletred": case "93": return array(self::$RED => 0xC7, self::$GREEN => 0x15, self::$BLUE => 0x85);
            case "midnightblue": case "94": return array(self::$RED => 0x19, self::$GREEN => 0x19, self::$BLUE => 0x70);
            case "mintcream": case "95": return array(self::$RED => 0xF5, self::$GREEN => 0xFF, self::$BLUE => 0xFA);
            case "mistyrose": case "96": return array(self::$RED => 0xFF, self::$GREEN => 0xE4, self::$BLUE => 0xE1);
            case "moccasin": case "97": return array(self::$RED => 0xFF, self::$GREEN => 0xE4, self::$BLUE => 0xB5);
            case "navajowhite": case "98": return array(self::$RED => 0xFF, self::$GREEN => 0xDE, self::$BLUE => 0xAD);
            case "oldlace": case "99": return array(self::$RED => 0xFD, self::$GREEN => 0xF5, self::$BLUE => 0xE6);
            case "olivedrab": case "100": return array(self::$RED => 0x6B, self::$GREEN => 0x8E, self::$BLUE => 0x23);
            case "orange": case "101": return array(self::$RED => 0xFF, self::$GREEN => 0xA5, self::$BLUE => 0x00);
            case "orangered": case "102": return array(self::$RED => 0xFF, self::$GREEN => 0x45, self::$BLUE => 0x00);
            case "orchid": case "103": return array(self::$RED => 0xDA, self::$GREEN => 0x70, self::$BLUE => 0xD6);
            case "palegoldenrod": case "104": return array(self::$RED => 0xEE, self::$GREEN => 0xE8, self::$BLUE => 0xAA);
            case "palegreen": case "105": return array(self::$RED => 0x98, self::$GREEN => 0xFB, self::$BLUE => 0x98);
            case "paleturquoise": case "106": return array(self::$RED => 0xAF, self::$GREEN => 0xEE, self::$BLUE => 0xEE);
            case "palevioletred": case "107": return array(self::$RED => 0xDB, self::$GREEN => 0x70, self::$BLUE => 0x93);
            case "papayawhip": case "108": return array(self::$RED => 0xFF, self::$GREEN => 0xEF, self::$BLUE => 0xD5);
            case "peachpuff": case "109": return array(self::$RED => 0xFF, self::$GREEN => 0xDA, self::$BLUE => 0xB9);
            case "peru": case "110": return array(self::$RED => 0xCD, self::$GREEN => 0x85, self::$BLUE => 0x3F);
            case "pink": case "111": return array(self::$RED => 0xFF, self::$GREEN => 0xC0, self::$BLUE => 0xCB);
            case "plum": case "112": return array(self::$RED => 0xDD, self::$GREEN => 0xA0, self::$BLUE => 0xDD);
            case "powderblue": case "113": return array(self::$RED => 0xB0, self::$GREEN => 0xE0, self::$BLUE => 0xE6);
            case "rosybrown": case "114": return array(self::$RED => 0xBC, self::$GREEN => 0x8F, self::$BLUE => 0x8F);
            case "royalblue": case "115": return array(self::$RED => 0x41, self::$GREEN => 0x69, self::$BLUE => 0xE1);
            case "saddlebrown": case "116": return array(self::$RED => 0x8B, self::$GREEN => 0x45, self::$BLUE => 0x13);
            case "salmon": case "117": return array(self::$RED => 0xFA, self::$GREEN => 0x80, self::$BLUE => 0x72);
            case "sandybrown": case "118": return array(self::$RED => 0xF4, self::$GREEN => 0xA4, self::$BLUE => 0x60);
            case "seagreen": case "119": return array(self::$RED => 0x2E, self::$GREEN => 0x8B, self::$BLUE => 0x57);
            case "seashell": case "120": return array(self::$RED => 0xFF, self::$GREEN => 0xF5, self::$BLUE => 0xEE);
            case "sienna": case "121": return array(self::$RED => 0xA0, self::$GREEN => 0x52, self::$BLUE => 0x2D);
            case "skyblue": case "122": return array(self::$RED => 0x87, self::$GREEN => 0xCE, self::$BLUE => 0xEB);
            case "slateblue": case "123": return array(self::$RED => 0x6A, self::$GREEN => 0x5A, self::$BLUE => 0xCD);
            case "slategray": case "124": return array(self::$RED => 0x70, self::$GREEN => 0x80, self::$BLUE => 0x90);
            case "snow": case "125": return array(self::$RED => 0xFF, self::$GREEN => 0xFA, self::$BLUE => 0xFA);
            case "springgreen": case "126": return array(self::$RED => 0x00, self::$GREEN => 0xFF, self::$BLUE => 0x7F);
            case "steelblue": case "127": return array(self::$RED => 0x46, self::$GREEN => 0x82, self::$BLUE => 0xB4);
            case "tan": case "128": return array(self::$RED => 0xD2, self::$GREEN => 0xB4, self::$BLUE => 0x8C);
            case "thistle": case "129": return array(self::$RED => 0xD8, self::$GREEN => 0xBF, self::$BLUE => 0xD8);
            case "tomato": case "130": return array(self::$RED => 0xFF, self::$GREEN => 0x63, self::$BLUE => 0x47);
            case "turquoise": case "131": return array(self::$RED => 0x40, self::$GREEN => 0xE0, self::$BLUE => 0xD0);
            case "violet": case "132": return array(self::$RED => 0xEE, self::$GREEN => 0x82, self::$BLUE => 0xEE);
            case "wheat": case "133": return array(self::$RED => 0xF5, self::$GREEN => 0xDE, self::$BLUE => 0xB3);
            case "whitesmoke": case "134": return array(self::$RED => 0xF5, self::$GREEN => 0xF5, self::$BLUE => 0xF5);
            case "yellowgreen": case "135": return array(self::$RED => 0x9A, self::$GREEN => 0xCD, self::$BLUE => 0x32);
        }

        return array( self::$RED=>0x00,  self::$GREEN=>0x00,  self::$BLUE=>0x00);

    }

    public static function names()
    {

        $result = array();

        $result["black"] = array( self::$RED=>0x00,  self::$GREEN=>0x00,  self::$BLUE=>0x00);
        $result[ "maroon"] = array( self::$RED=>0x80,  self::$GREEN=>0x00,  self::$BLUE=>0x00);
        $result[ "green"] = array( self::$RED=>0x00,  self::$GREEN=>0x80,  self::$BLUE=>0x00);
        $result[ "olive"] = array( self::$RED=>0x80,  self::$GREEN=>0x80,  self::$BLUE=>0x00);
        $result[ "navy"] = array( self::$RED=>0x00,  self::$GREEN=>0x00,  self::$BLUE=>0x80);
        $result[ "purple"] = array( self::$RED=>0x80,  self::$GREEN=>0x00,  self::$BLUE=>0x80);
        $result[ "teal"] = array( self::$RED=>0x00,  self::$GREEN=>0x80,  self::$BLUE=>0x80);
        $result[ "gray"] = array( self::$RED=>0x80,  self::$GREEN=>0x80,  self::$BLUE=>0x80);
        $result[ "silver"] = array( self::$RED=>0xC0,  self::$GREEN=>0xC0,  self::$BLUE=>0xC0);
        $result[ "red"] = array( self::$RED=>0xFF,  self::$GREEN=>0x00,  self::$BLUE=>0x00);
        $result[ "lime"] = array( self::$RED=>0x00,  self::$GREEN=>0xFF,  self::$BLUE=>0x00);
        $result[ "yellow"] = array( self::$RED=>0xFF,  self::$GREEN=>0xFF,  self::$BLUE=>0x00);
        $result[ "blue"] = array( self::$RED=>0x00,  self::$GREEN=>0x00,  self::$BLUE=>0xFF);
        $result[ "fuchsia"] = array( self::$RED=>0xFF,  self::$GREEN=>0x00,  self::$BLUE=>0xFF);
        $result[ "aqua"] = array( self::$RED=>0x00,  self::$GREEN=>0xFF,  self::$BLUE=>0xFF);
        $result[ "white"] = array( self::$RED=>0xFF,  self::$GREEN=>0xFF,  self::$BLUE=>0xFF);
        $result[ "aliceblue"] = array( self::$RED=>0xF0,  self::$GREEN=>0xF8,  self::$BLUE=>0xFF);
        $result[ "antiquewhite"] = array( self::$RED=>0xFA,  self::$GREEN=>0xEB,  self::$BLUE=>0xD7);
        $result[ "aquamarine"] = array( self::$RED=>0x7F,  self::$GREEN=>0xFF,  self::$BLUE=>0xD4);
        $result[ "azure"] = array( self::$RED=>0xF0,  self::$GREEN=>0xFF,  self::$BLUE=>0xFF);
        $result[ "beige"] = array( self::$RED=>0xF5,  self::$GREEN=>0xF5,  self::$BLUE=>0xDC);
        $result[ "blueviolet"] = array( self::$RED=>0x8A,  self::$GREEN=>0x2B,  self::$BLUE=>0xE2);
        $result[ "brown"] = array( self::$RED=>0xA5,  self::$GREEN=>0x2A,  self::$BLUE=>0x2A);
        $result[ "burlywood"] = array( self::$RED=>0xDE,  self::$GREEN=>0xB8,  self::$BLUE=>0x87);
        $result[ "cadetblue"] = array( self::$RED=>0x5F,  self::$GREEN=>0x9E,  self::$BLUE=>0xA0);
        $result[ "chartreuse"] = array( self::$RED=>0x7F,  self::$GREEN=>0xFF,  self::$BLUE=>0x00);
        $result[ "chocolate"] = array( self::$RED=>0xD2,  self::$GREEN=>0x69,  self::$BLUE=>0x1E);
        $result[ "coral"] = array( self::$RED=>0xFF,  self::$GREEN=>0x7F,  self::$BLUE=>0x50);
        $result[ "cornflowerblue"] = array( self::$RED=>0x64,  self::$GREEN=>0x95,  self::$BLUE=>0xED);
        $result[ "cornsilk"] = array( self::$RED=>0xFF,  self::$GREEN=>0xF8,  self::$BLUE=>0xDC);
        $result[ "crimson"] = array( self::$RED=>0xDC,  self::$GREEN=>0x14,  self::$BLUE=>0x3C);
        $result[ "darkblue"] = array( self::$RED=>0x00,  self::$GREEN=>0x00,  self::$BLUE=>0x8B);
        $result[ "darkcyan"] = array( self::$RED=>0x00,  self::$GREEN=>0x8B,  self::$BLUE=>0x8B);
        $result[ "darkgoldenrod"] = array( self::$RED=>0xB8,  self::$GREEN=>0x86,  self::$BLUE=>0x0B);
        $result[ "darkgray"] = array( self::$RED=>0xA9,  self::$GREEN=>0xA9,  self::$BLUE=>0xA9);
        $result[ "darkgreen"] = array( self::$RED=>0x00,  self::$GREEN=>0x64,  self::$BLUE=>0x00);
        $result[ "darkkhaki"] = array( self::$RED=>0xBD,  self::$GREEN=>0xB7,  self::$BLUE=>0x6B);
        $result[ "darkmagenta"] = array( self::$RED=>0x8B,  self::$GREEN=>0x00,  self::$BLUE=>0x8B);
        $result[ "darkolivegreen"] = array( self::$RED=>0x55,  self::$GREEN=>0x6B,  self::$BLUE=>0x2F);
        $result[ "darkorange"] = array( self::$RED=>0xFF,  self::$GREEN=>0x8C,  self::$BLUE=>0x00);
        $result[ "darkorchid"] = array( self::$RED=>0x99,  self::$GREEN=>0x32,  self::$BLUE=>0xCC);
        $result[ "darkred"] = array( self::$RED=>0x8B,  self::$GREEN=>0x00,  self::$BLUE=>0x00);
        $result[ "darksalmon"] = array( self::$RED=>0xE9,  self::$GREEN=>0x96,  self::$BLUE=>0x7A);
        $result[ "darkseagreen"] = array( self::$RED=>0x8F,  self::$GREEN=>0xBC,  self::$BLUE=>0x8F);
        $result[ "darkslateblue"] = array( self::$RED=>0x48,  self::$GREEN=>0x3D,  self::$BLUE=>0x8B);
        $result[ "darkslategray"] = array( self::$RED=>0x2F,  self::$GREEN=>0x4F,  self::$BLUE=>0x4F);
        $result[ "darkturquoise"] = array( self::$RED=>0x00,  self::$GREEN=>0xCE,  self::$BLUE=>0xD1);
        $result[ "darkviolet"] = array( self::$RED=>0x94,  self::$GREEN=>0x00,  self::$BLUE=>0xD3);
        $result[ "deeppink"] = array( self::$RED=>0xFF,  self::$GREEN=>0x14,  self::$BLUE=>0x93);
        $result[ "deepskyblue"] = array( self::$RED=>0x00,  self::$GREEN=>0xBF,  self::$BLUE=>0xFF);
        $result[ "dimgray"] = array( self::$RED=>0x69,  self::$GREEN=>0x69,  self::$BLUE=>0x69);
        $result[ "dodgerblue"] = array( self::$RED=>0x1E,  self::$GREEN=>0x90,  self::$BLUE=>0xFF);
        $result[ "firebrick"] = array( self::$RED=>0xB2,  self::$GREEN=>0x22,  self::$BLUE=>0x22);
        $result[ "floralwhite"] = array( self::$RED=>0xFF,  self::$GREEN=>0xFA,  self::$BLUE=>0xF0);
        $result[ "forestgreen"] = array( self::$RED=>0x22,  self::$GREEN=>0x8B,  self::$BLUE=>0x22);
        $result[ "gainsboro"] = array( self::$RED=>0xDC,  self::$GREEN=>0xDC,  self::$BLUE=>0xDC);
        $result[ "ghostwhite"] = array( self::$RED=>0xF8,  self::$GREEN=>0xF8,  self::$BLUE=>0xFF);
        $result[ "gold"] = array( self::$RED=>0xFF,  self::$GREEN=>0xD7,  self::$BLUE=>0x00);
        $result[ "goldenrod"] = array( self::$RED=>0xDA,  self::$GREEN=>0xA5,  self::$BLUE=>0x20);
        $result[ "greenyellow"] = array( self::$RED=>0xAD,  self::$GREEN=>0xFF,  self::$BLUE=>0x2F);
        $result[ "honeydew"] = array( self::$RED=>0xF0,  self::$GREEN=>0xFF,  self::$BLUE=>0xF0);
        $result[ "hotpink"] = array( self::$RED=>0xFF,  self::$GREEN=>0x69,  self::$BLUE=>0xB4);
        $result[ "indianred"] = array( self::$RED=>0xCD,  self::$GREEN=>0x5C,  self::$BLUE=>0x5C);
        $result[ "indigo"] = array( self::$RED=>0x4B,  self::$GREEN=>0x00,  self::$BLUE=>0x82);
        $result[ "ivory"] = array( self::$RED=>0xFF,  self::$GREEN=>0xFF,  self::$BLUE=>0xF0);
        $result[ "khaki"] = array( self::$RED=>0xF0,  self::$GREEN=>0xE6,  self::$BLUE=>0x8C);
        $result[ "lavender"] = array( self::$RED=>0xE6,  self::$GREEN=>0xE6,  self::$BLUE=>0xFA);
        $result[ "lavenderblush"] = array( self::$RED=>0xFF,  self::$GREEN=>0xF0,  self::$BLUE=>0xF5);
        $result[ "lawngreen"] = array( self::$RED=>0x7C,  self::$GREEN=>0xFC,  self::$BLUE=>0x00);
        $result[ "lemonchiffon"] = array( self::$RED=>0xFF,  self::$GREEN=>0xFA,  self::$BLUE=>0xCD);
        $result[ "lightblue"] = array( self::$RED=>0xAD,  self::$GREEN=>0xD8,  self::$BLUE=>0xE6);
        $result[ "lightcoral"] = array( self::$RED=>0xF0,  self::$GREEN=>0x80,  self::$BLUE=>0x80);
        $result[ "lightcyan"] = array( self::$RED=>0xE0,  self::$GREEN=>0xFF,  self::$BLUE=>0xFF);
        $result[ "lightgoldenrodyellow"] = array( self::$RED=>0xFA,  self::$GREEN=>0xFA,  self::$BLUE=>0xD2);
        $result[ "lightgreen"] = array( self::$RED=>0x90,  self::$GREEN=>0xEE,  self::$BLUE=>0x90);
        $result[ "lightgrey"] = array( self::$RED=>0xD3,  self::$GREEN=>0xD3,  self::$BLUE=>0xD3);
        $result[ "lightpink"] = array( self::$RED=>0xFF,  self::$GREEN=>0xB6,  self::$BLUE=>0xC1);
        $result[ "lightsalmon"] = array( self::$RED=>0xFF,  self::$GREEN=>0xA0,  self::$BLUE=>0x7A);
        $result[ "lightseagreen"] = array( self::$RED=>0x20,  self::$GREEN=>0xB2,  self::$BLUE=>0xAA);
        $result[ "lightskyblue"] = array( self::$RED=>0x87,  self::$GREEN=>0xCE,  self::$BLUE=>0xFA);
        $result[ "lightslategray"] = array( self::$RED=>0x77,  self::$GREEN=>0x88,  self::$BLUE=>0x99);
        $result[ "lightsteelblue"] = array( self::$RED=>0xB0,  self::$GREEN=>0xC4,  self::$BLUE=>0xDE);
        $result[ "lightyellow"] = array( self::$RED=>0xFF,  self::$GREEN=>0xFF,  self::$BLUE=>0xE0);
        $result[ "limegreen"] = array( self::$RED=>0x32,  self::$GREEN=>0xCD,  self::$BLUE=>0x32);
        $result[ "linen"] = array( self::$RED=>0xFA,  self::$GREEN=>0xF0,  self::$BLUE=>0xE6);
        $result[ "mediumaquamarine"] = array( self::$RED=>0x66,  self::$GREEN=>0xCD,  self::$BLUE=>0xAA);
        $result[ "mediumblue"] = array( self::$RED=>0x00,  self::$GREEN=>0x00,  self::$BLUE=>0xCD);
        $result[ "mediumorchid"] = array( self::$RED=>0xBA,  self::$GREEN=>0x55,  self::$BLUE=>0xD3);
        $result[ "mediumpurple"] = array( self::$RED=>0x93,  self::$GREEN=>0x70,  self::$BLUE=>0xD0);
        $result[ "mediumseagreen"] = array( self::$RED=>0x3C,  self::$GREEN=>0xB3,  self::$BLUE=>0x71);
        $result[ "mediumslateblue"] = array( self::$RED=>0x7B,  self::$GREEN=>0x68,  self::$BLUE=>0xEE);
        $result[ "mediumspringgreen"] = array( self::$RED=>0x00,  self::$GREEN=>0xFA,  self::$BLUE=>0x9A);
        $result[ "mediumturquoise"] = array( self::$RED=>0x48,  self::$GREEN=>0xD1,  self::$BLUE=>0xCC);
        $result[ "mediumvioletred"] = array( self::$RED=>0xC7,  self::$GREEN=>0x15,  self::$BLUE=>0x85);
        $result[ "midnightblue"] = array( self::$RED=>0x19,  self::$GREEN=>0x19,  self::$BLUE=>0x70);
        $result[ "mintcream"] = array( self::$RED=>0xF5,  self::$GREEN=>0xFF,  self::$BLUE=>0xFA);
        $result[ "mistyrose"] = array( self::$RED=>0xFF,  self::$GREEN=>0xE4,  self::$BLUE=>0xE1);
        $result[ "moccasin"] = array( self::$RED=>0xFF,  self::$GREEN=>0xE4,  self::$BLUE=>0xB5);
        $result[ "navajowhite"] = array( self::$RED=>0xFF,  self::$GREEN=>0xDE,  self::$BLUE=>0xAD);
        $result[ "oldlace"] = array( self::$RED=>0xFD,  self::$GREEN=>0xF5,  self::$BLUE=>0xE6);
        $result[ "olivedrab"] = array( self::$RED=>0x6B,  self::$GREEN=>0x8E,  self::$BLUE=>0x23);
        $result[ "orange"] = array( self::$RED=>0xFF,  self::$GREEN=>0xA5,  self::$BLUE=>0x00);
        $result[ "orangered"] = array( self::$RED=>0xFF,  self::$GREEN=>0x45,  self::$BLUE=>0x00);
        $result[ "orchid"] = array( self::$RED=>0xDA,  self::$GREEN=>0x70,  self::$BLUE=>0xD6);
        $result[ "palegoldenrod"] = array( self::$RED=>0xEE,  self::$GREEN=>0xE8,  self::$BLUE=>0xAA);
        $result[ "palegreen"] = array( self::$RED=>0x98,  self::$GREEN=>0xFB,  self::$BLUE=>0x98);
        $result[ "paleturquoise"] = array( self::$RED=>0xAF,  self::$GREEN=>0xEE,  self::$BLUE=>0xEE);
        $result[ "palevioletred"] = array( self::$RED=>0xDB,  self::$GREEN=>0x70,  self::$BLUE=>0x93);
        $result[ "papayawhip"] = array( self::$RED=>0xFF,  self::$GREEN=>0xEF,  self::$BLUE=>0xD5);
        $result[ "peachpuff"] = array( self::$RED=>0xFF,  self::$GREEN=>0xDA,  self::$BLUE=>0xB9);
        $result[ "peru"] = array( self::$RED=>0xCD,  self::$GREEN=>0x85,  self::$BLUE=>0x3F);
        $result[ "pink"] = array( self::$RED=>0xFF,  self::$GREEN=>0xC0,  self::$BLUE=>0xCB);
        $result[ "plum"] = array( self::$RED=>0xDD,  self::$GREEN=>0xA0,  self::$BLUE=>0xDD);
        $result[ "powderblue"] = array( self::$RED=>0xB0,  self::$GREEN=>0xE0,  self::$BLUE=>0xE6);
        $result[ "rosybrown"] = array( self::$RED=>0xBC,  self::$GREEN=>0x8F,  self::$BLUE=>0x8F);
        $result[ "royalblue"] = array( self::$RED=>0x41,  self::$GREEN=>0x69,  self::$BLUE=>0xE1);
        $result[ "saddlebrown"] = array( self::$RED=>0x8B,  self::$GREEN=>0x45,  self::$BLUE=>0x13);
        $result[ "salmon"] = array( self::$RED=>0xFA,  self::$GREEN=>0x80,  self::$BLUE=>0x72);
        $result[ "sandybrown"] = array( self::$RED=>0xF4,  self::$GREEN=>0xA4,  self::$BLUE=>0x60);
        $result[ "seagreen"] = array( self::$RED=>0x2E,  self::$GREEN=>0x8B,  self::$BLUE=>0x57);
        $result[ "seashell"] = array( self::$RED=>0xFF,  self::$GREEN=>0xF5,  self::$BLUE=>0xEE);
        $result[ "sienna"] = array( self::$RED=>0xA0,  self::$GREEN=>0x52,  self::$BLUE=>0x2D);
        $result[ "skyblue"] = array( self::$RED=>0x87,  self::$GREEN=>0xCE,  self::$BLUE=>0xEB);
        $result[ "slateblue"] = array( self::$RED=>0x6A,  self::$GREEN=>0x5A,  self::$BLUE=>0xCD);
        $result[ "slategray"] = array( self::$RED=>0x70,  self::$GREEN=>0x80,  self::$BLUE=>0x90);
        $result[ "snow"] = array( self::$RED=>0xFF,  self::$GREEN=>0xFA,  self::$BLUE=>0xFA);
        $result[ "springgreen"] = array( self::$RED=>0x00,  self::$GREEN=>0xFF,  self::$BLUE=>0x7F);
        $result[ "steelblue"] = array( self::$RED=>0x46,  self::$GREEN=>0x82,  self::$BLUE=>0xB4);
        $result[ "tan"] = array( self::$RED=>0xD2,  self::$GREEN=>0xB4,  self::$BLUE=>0x8C);
        $result[ "thistle"] = array( self::$RED=>0xD8,  self::$GREEN=>0xBF,  self::$BLUE=>0xD8);
        $result[ "tomato"] = array( self::$RED=>0xFF,  self::$GREEN=>0x63,  self::$BLUE=>0x47);
        $result[ "turquoise"] = array( self::$RED=>0x40,  self::$GREEN=>0xE0,  self::$BLUE=>0xD0);
        $result[ "violet"] = array( self::$RED=>0xEE,  self::$GREEN=>0x82,  self::$BLUE=>0xEE);
        $result[ "wheat"] = array( self::$RED=>0xF5,  self::$GREEN=>0xDE,  self::$BLUE=>0xB3);
        $result[ "whitesmoke"] = array( self::$RED=>0xF5,  self::$GREEN=>0xF5,  self::$BLUE=>0xF5);
        $result[ "yellowgreen"] = array( self::$RED=>0x9A,  self::$GREEN=>0xCD,  self::$BLUE=>0x32);

        return $result;

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

          $del_R = ( ( ( $var_Max - $var_R ) / 6 ) + ( $del_Max / 2 ) ) / $del_Max;
          $del_G = ( ( ( $var_Max - $var_G ) / 6 ) + ( $del_Max / 2 ) ) / $del_Max;
          $del_B = ( ( ( $var_Max - $var_B ) / 6 ) + ( $del_Max / 2 ) ) / $del_Max;

          if      ($var_R == $var_Max) $h = $del_B - $del_G;
          else if ($var_G == $var_Max) $h = ( 1 / 3 ) + $del_R - $del_B;
          else if ($var_B == $var_Max) $h = ( 2 / 3 ) + $del_G - $del_R;

          if ($h < 0) $h++;
          if ($h > 1) $h--;
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
        return array(intval($r), intval($g), intval($B));

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


    public static function ColorHistogramKeyedByCount($filename, $max_entries = null)
    {
        $hist = self::ColorHistogramKeyedByColor($filename, $max_entries);
        if (is_null($hist)) return null;


        $result = array();
        foreach ($hist as $hex_color => $arr)
        {
            $result[$arr['COUNT']] = array();
            $result[$arr['COUNT']]['R'] = $arr['R'];
            $result[$arr['COUNT']]['G'] = $arr['G'];
            $result[$arr['COUNT']]['B'] = $arr['B'];
        }

        krsort($result);

        return $result;

    }

    public static function ColorHistogramKeyedByColor($filename, $max_entries = null)
    {


        if (!is_null($max_entries))
            $max_entries = "| head -n {$max_entries}";
        else
            $max_entries = "";

        $cmd_result = array();
        $cmd = "convert {$filename} -format %c histogram:info:- | sort -n -r {$max_entries}";
        exec($cmd, $cmd_result);

        if (count($cmd_result) == 0 ) return null;
        if (!util::contains($cmd_result[0],"(")) return null;  // does not look like out from $cmd

        $result = array();

        foreach ($cmd_result as $index => $value)
        {
            if (trim($value) == "") continue;

            $hex = util::midStr($value, "#", " ");

            if (!util::contains($value, "(")) continue;
            if (!util::contains($value, ")")) continue;

            $rgb_raw = util::midStr($value, "(", ")");
            $rgb_arr = explode(",",$rgb_raw);

            $result[$hex]['R'] = trim($rgb_arr[0]);
            $result[$hex]['G'] = trim($rgb_arr[1]);
            $result[$hex]['B'] = trim($rgb_arr[2]);
            $result[$hex]['COUNT'] = trim(util::leftStr($value, ":",false));

        }

        return $result;

    }


}
?>