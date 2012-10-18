<?php
class htmlutil {

    public static function AsJavaScriptSimpleVariable($src,$variableName)
    {
        if (is_null($src)) return "";
        if (is_null($variableName)) return "";

        if (is_array($src)) return self::AsJavaScriptArray($src,$variableName);
        
        return "var {$variableName} = '{$src}';\n";
    }    
    
    public static function AsJavaScriptArray($src,$variableName)
    {

        if (is_null($src)) return "";
        if (is_null($variableName)) return "";

        if (!is_array($src)) return "/* DATA ERROR \n ".  print_r($src, true)." \n*/\n";
        
        $values = array();
        
        foreach ($src as $value) $values[] = '"'.$value.'"';
        $result = "var {$variableName} = [".join(",",$values)."];";
        
        unset($values);
        return $result."\n";
    }

    public static function AsJavaScriptArrayFromFile($filename,$variableName,$sort = false,$exclude_list = null)
    {

        if (is_null($filename)) return "";
        if (is_null($variableName)) return "";
        
        if (!file_exists($filename)) return "";
        
        $values = array_util::Trim(file($filename));
        
        if ($sort) sort($values);
        
        $result = "var {$variableName} = ['".join("','",$values)."'];";
        
        unset($values);
        return $result."\n";
    }
    
    
    
    /**
     * 
     * 
     * @param type $src - BiDimensiaonal array 
     * @param type $keyColumn - name of column to use as key
     * @param type $valueColumn - name of column to use as value
     * @return string Javascript arra of objectes
     */
    public static function AsJavaScriptObjectArray($src,$keyColumn = null ,$valueColumn = null,$variableName = null)
    {

        //[ { label: "Choice1", value: "value1" }, ... ]
        if (is_null($variableName) ) $variableName = 'fred';
        
        if (!is_array($src)) return "/* DATA ERROR \n ".  print_r($src, true)." \n*/\n";
        
        $values = array();
        foreach ($src as $index => $row) 
        {
            $label = (is_null($keyColumn)) ? $index : $row[$keyColumn];
            $value = (is_null($valueColumn)) ? $row : $row[$valueColumn];
            
            $values[] = "{ label: \"{$label}\", value: \"{$value}\" }";
        }
            
        $result = "var {$variableName} = [".join(",",$values)."];";
        unset($values);
        return $result."\n";
    }
    
    
    
    public static function ValueFromGet($key , $default = null) 
    {
        return array_util::Value($_GET, $key, $default);        
    }
    
    public static function ValueFromPost($key , $default = null) 
    {
        return array_util::Value($_POST, $key, $default);        
    }
    
    
    /*
    * @method table
    * @param $data
    * @param $showValue = true {
    * @return mixed
    */
    public static function table($data, $showKey = true) {

        if (count($data) == 0) return "NO DATA<br>";

        if (!is_array($data)) return str_replace ("\n", '<br/>', $data)."<br>";

        $result = "\n".'<table border="0" >';

        foreach ($data as $key => $value)
        {
            if (is_array($value))
            {
                $row = array();
                foreach ($value as $col_id => $cell) $row[] = "<td>$cell</td>";

                $result .= "\n"."<tr>";
                if ($showKey) $result .= "\n"."<td class=\"rowheader\">$key</td>";
                $result .= "\n".join("\n",$row);
                $result .= "\n"."</tr>"."\n";
            }
            else
            {
                $result .= "\n"."<tr>";
                if ($showKey) $result .= "\n"."<td class=\"rowheader\">$key</td>";

                if (substr($value,0,4) == "http")
                    $result .= "\n".'<td class="rowvalue"><a href="'.$value.'">'.$value.'</a></td>';
                else
                    $result .= "\n"."<td class=\"rowvalue\">$value</td>";

                $result .= "\n"."</tr>"."\n";

            }


        }

        $result .= "\n"."</table>"."\n";

        return $result;
    }


    /**
     *
     */
    public static function TableRowTemplate($data, $template = null,$horizontal = false,$pre_first_row = "",$class = "",$caption = "")
    {

        if (count($data) == 0) return "NO DATA<br>";

        if (!is_array($data)) return str_replace ("\n", "<br/>\n", $data)."<br>\n";

        if (!is_null($template)) $template = trim($template);

        if (!is_null($template) && $template == "") $template = null;


        $caption_class = "";
        if ($class != "") $caption_class = ' class="'.$class.' caption" ';
        $result = "<div ".$caption_class." >{$caption}</div><br>";


        if ($class != "") $class = ' class="'.$class.'" ';
        $result .= '<table '.$class.' >';

        if ($horizontal) 
        {
            $result .= $pre_first_row;
            $result .= "<tr>";
        }


        foreach ($data as $row_id => $row)
        {

            $handled = false;
            if (!$horizontal) 
            {
                $result .= $pre_first_row;
                $result .= "<tr>";
            }


            if (!$handled && Object::isObject($row))
            {
                // if we find an object is passed as "row" then treat the properties of the object as the cells

                $handled = true;
                $row instanceof Object;

                if (is_null($template))
                    $sub_result = "<td>".$row->asFormattedString()."</td>";
                else
                {
                    $sub_result = str_replace("{#row_id#}",  $row->DataName(), $template);


                    foreach ($row->PropertyNames() as $propertyName)
                        $sub_result = str_replace("{{$propertyName}}", $row->getPropertyByName($propertyName, NULL), $sub_result);

                    $sub_result = "<td>".$sub_result."</td>";
                }

            }



            if (!$handled && is_array($row))
            {
                $handled = true;

                if (is_null($template))
                    $sub_result = "<td>".join(" ", $row)."</td>";
                else
                {

                    $sub_result = str_replace("{#row_id#}",  $row_id, $template);
                    $sub_result = str_replace("{#key#}",  $row_id, $template);

                    foreach ($row as $column_id => $cell_value)
                        $sub_result = str_replace("{{$column_id}}",  $cell_value, $sub_result);

                    $sub_result = "<td>".$sub_result."</td>";
                }

            }


            if (!$handled)
            {
                $handled = true;

                if (is_null($template))
                {
                    $sub_result = "<td>[".$row_id."] => [".$row."]</td>";
                }
                else
                {
                    $sub_result = str_replace("{#row_id#}",  $row_id, $template);
                    $sub_result = str_replace("{#key#}",  $row_id, $sub_result);
                    $sub_result = str_replace("{#value#}",  $row, $sub_result);
                    $sub_result = "<td>".$sub_result."</td>";
                }

            }

            $result .= $sub_result;

            if (!$horizontal) $result .= "</tr>"."\n";

            
        }

        if ($horizontal) $result .= "</tr>"."\n";

        $result .= "</table>"."\n";

        return $result;
    }



    /**
     *
     * CReta e "Table" from css
     *
     * @param type $data  array of data used
     * @param type $cellTemplate - template to be warpped arounf Cell data
     * @param type $tableClass - class wrapped around table
     * @param type $rowClass  - class wrapped around row
     * @param type $cellClass  - class wrapped around cell
     * @return string complete HTML
     */
    public static function TableByCSS($data, $cellTemplate = null,$tableClass = "aTable", $rowClass = "aRow",$cellClass = "aCell",$rowHeader = false)
    {

        if (count($data) == 0) return "NO DATA<br>";

        if (!is_array($data)) return str_replace ("\n", "<br/>\n", $data)."<br>\n";

        $result = '<div class="'.$tableClass.'">';


        foreach ($data as $row_id => $row)
        {

            $handle = false;
            
            if (!$handle)
            {

                if (is_array($row))  // if $row is an array then this is basically a matrix
                {

                    $cells = array();

                    foreach ($row as $cell_id => $cell)
                    {

                        if (is_null($cellTemplate))
                        {
                            $rowHeaderClass = (count($cells) == 0 ) ? " rowHeader " :"";
                            $cells[] = '<div class="'.$cellClass.$rowHeaderClass.'">'.$cell.'</div>';
                        }
                        else
                        {


                            $sub_result = $cellTemplate;   // do a replacement from all values from this row

                            foreach ($row as $column_id => $cell_value) 
                                $sub_result = str_replace("{{$column_id}}",  $cell_value, $sub_result);


                            $sub_result = str_replace("{#row_id#}",  $row_id, $sub_result);
                            $sub_result = str_replace("{#key#}",  $row_id, $sub_result);
                            $sub_result = str_replace("{#column_id#}",  $cell_id, $sub_result);
                            $sub_result = str_replace("{#column#}",  $cell_id, $sub_result);
                            $sub_result = str_replace("{#value#}",  $cell, $sub_result);

                            $rowHeaderClass = (count($cells) == 0 ) ? " rowHeader " :"";
                            $cells[] = '<div class="'.$cellClass.$rowHeaderClass.'">'.$sub_result.'</div>';

                        }

                    }
                    
                    $result .= "\n".'<div class="'. $rowClass .'">'.join("\n",$cells).'</div>'."\n";

                    $handle = true;

                }

                

            }


            if (!$handle)
            {

                // treat properties as cells
                if (Object::isObject($row))
                {

                    $row instanceof Object;

                    $cells = array();
                    foreach ($row->PropertyNames() as $propertyName)
                    {

                        $cell = $row->getPropertyByName($propertyName, NULL);

                        if (is_null($cellTemplate))
                            $cells[] = $cell;
                        else
                        {
                            $sub_result = $cellTemplate;   // do a replacement from all values from this object
                            foreach ($row->PropertyNames() as $propertyName)
                                $sub_result = str_replace("{{$propertyName}}", $row->getPropertyByName($propertyName, NULL), $sub_result);

                            $sub_result = str_replace("{#row_id#}",  $row_id, $sub_result);
                            $sub_result = str_replace("{#key#}",  $row_id, $sub_result);

                        }


                    }

                    $cells_str = '<div class="'.$cellClass.'">'.join("\n",$cells).'</div>';
                    $row = "\n".'<div class="'. $rowClass .'">'.$cells_str.'</div>'."\n";
                    $result .= $row ;

                    $handle = true;

                }


                
            }


            if (!$handle)
            {

                $cells = array();
                if (is_null($cellTemplate))
                {
                    $cells[] = $row;
                }
                else
                {
                    //  just $row_id => $row
                    $sub_result = $cellTemplate;   // do a replacement key and vbalue
                    $sub_result = str_replace("{#row_id#}",  $row_id, $sub_result);
                    $sub_result = str_replace("{#key#}",  $row_id, $sub_result);
                    $sub_result = str_replace("{#value#}",  $row, $sub_result);

                    $cells[] = $sub_result;
                }

                $handle = true;

                $cells_str = '<div class="'.$cellClass.'">'.join("\n",$cells).'</div>';
                $row = "\n".'<div class="'. $rowClass .'">'.$cells_str.'</div>'."\n";
                $result .= $row ;


            }



        } //end foreach



        $result .= '</div>';


        return $result;

    }





    /*
    * @method img
    * @param $data
    * @param $height = 100
    * @param $width = 100 {
    * @return mixed
    */
    public static function img($data,$height = 100, $width = 100) {


        $result  = '<img src="';
        $result .= $data;
        $result .= '" height="'.$height.'" ';
        $result .= ' width="'.$width.'" ';
        $result .= ' />';

        return $result;
    }


    /*
    * @method imgArray
    * @param $array
    * @param $height = 100
    * @param $width = 100 {
    * @return mixed
    */
    public static function imgArray($array,$height = 100, $width = 100) {

        $result = array();

        foreach ($array as $key => $value) {
            $result[$key] = htmlutil::img($value,$height, $width);
        }

        return $result;
    }



    /*
    * @method imgClick
    * @param $imageFilename
    * @param $height = 100
    * @param $width = 100
    * @param $hrefPrefix = ""
    * @param $caption = ""
    * @return mixed
    */
    public static function imgClick($imageFilename,$height = 100, $width = 100,$hrefPrefix = "",$caption = "")
    {

        $href = $imageFilename;
        if ($hrefPrefix != "") $href = $hrefPrefix.$imageFilename;

        $result  = '<a href="'.$href.'" target="_new">';
        $result .= '<img src="'.$imageFilename.'"';
        $result .= ' height="'.$height.'" ';
        $result .= ' width="'.$width.'" ';
        $result .= ' title="'. basename($imageFilename) .'" ';
        $result .= ' alt="'. basename($imageFilename) .'" ';
        $result .= '" />';
        $result .= '</a>';
        if ($caption != "" ) $result .= '<br>'.$caption;
        $result .= "\n";

        return $result;
    }


    /*
    * @method imgHref
    * @param $imageFilename
    * @param $height = 100
    * @param $width = 100
    * @param $href = ""
    * @return mixed
    */
    public static function imgHref($imageFilename,$height = 100, $width = 100,$href = "")
    {

        if ($href == "") $href = $imageFilename;

        $result  = '<a href="'.$href.'" target="_new">';
        $result .= '<img src="'.$imageFilename.'"';
        $result .= ' height="'.$height.'" ';
        $result .= ' width="'.$width.'" ';
        $result .= ' title="'. basename($imageFilename) .'" ';
        $result .= ' alt="'. basename($imageFilename) .'" ';
        $result .= '" />';
        $result .= '</a>'."\n";

        return $result;
    }




    /*
    * @method href
    * @param $data
    * @param $linkText = ""
    * @param $target="" {
    * @return mixed
    */
    public static function href($data, $linkText = "" , $target="") {

        if ($target != "") $target = ' target="'.$target.'" ';

        if ($linkText == "") $linkText = basename($data);


        $result  = '<a href="'.$data.'" '.$target.' >';
        $result .= $linkText;
        $result .= '</a>';

        return $result;
    }


    /*
    * @method hrefArray
    * @param $array {
    * @return mixed
    */
    public static function hrefArray($array) {

        $result = array();

        foreach ($array as $key => $value) {
            $result[$key] = htmlutil::href($value);
        }

        return $result;
    }



    /*
    * @method div
    * @param $data
    * @param $class = ""
    * @param $style = ""
    * @param $alt = ""
    * @param $title = "" {
    * @return mixed
    */
    public static function div($data, $class = "",$style = "", $alt = "", $title = "") {

        if ($class != "") $class = ' class="'.$class.'" ';
        if ($style != "") $style = ' style="'.$style.'" ';
        if ($alt   != "") $alt   = ' alt="'  .$alt  .'" ';
        if ($title != "") $title = ' title="'.$title.'" ';


        $result  = "\n".'<div '.$class.' '.$style.' '.$alt.' '.$title.'>';
        $result .= $data;
        $result .= '</div>'."\n";

        return $result;
    }


    /*
    * @method br
    * @param $class = ""
    * @param $style = "" {
    * @return mixed
    */
    public static function br($class = "",$style = "") {

        if ($class != "") $class = ' class="'.$class.'" ';
        if ($style != "") $style = ' style="'.$style.'" ';

        $result  = '<br '.$class.' '.$style.'>'."\n";

        return $result;
    }


    /*
    * @method brRowBreak
    * @param {
    * @return mixed
    */
    public static function brRowBreak() {

        return htmlutil::br("","clear: left;") ;

    }



    /*
    * @method browser_info
    * @param $agent=null
    * @return mixed
    */
    function browser_info($agent=null)
    {
        // reference: http://php.net/manual/en/function.get-browser.php

      if (trim($_SERVER['HTTP_USER_AGENT']) == "") return false;

      // Declare known browsers to look for
      $known = array('chrome','msie', 'firefox', 'safari', 'webkit', 'opera', 'netscape','konqueror', 'gecko');

      // Clean up agent and build regex that matches phrases for known browsers
      // (e.g. "Firefox/2.0" or "MSIE 6.0" (This only matches the major and minor
      // version numbers.  E.g. "2.0.0.6" is parsed as simply "2.0"
      $agent = strtolower($agent ? $agent : $_SERVER['HTTP_USER_AGENT']);
      $pattern = '#(?<browser>' . join('|', $known) .
        ')[/ ]+(?<version>[0-9]+(?:\.[0-9]+)?)#';

      // Find all phrases (or return empty array if none found)
      if (!preg_match_all($pattern, $agent, $matches)) return array();

      // Since some UAs have more than one phrase (e.g Firefox has a Gecko phrase,
      // Opera 7,8 have a MSIE phrase), use the last one found (the right-most one
      // in the UA).  That's usually the most correct.
      $i = count($matches['browser'])-1;


      return array($matches['browser'][$i] => $matches['version'][$i]);
    }


    public static function KMLPlacemarker($name,$desc ,$lat, $lon, $alt,$url_prefix)
    {

$placeMark = <<<PLACEMARK
<Placemark>
    <name>$name</name>
    <description>$desc</description>
    <LookAt>
            <longitude>$lon</longitude>
            <latitude>$lat</latitude>
            <altitude>$alt</altitude>
            <heading>0.0</heading>
            <tilt>0</tilt>
            <range>100</range>
            <altitudeMode>relativeToGround</altitudeMode>
            <gx:altitudeMode>relativeToSeaFloor</gx:altitudeMode>
    </LookAt>
    <styleUrl>#msn_ylw-pushpin</styleUrl>
    <Point>
            <altitudeMode>absolute</altitudeMode>
            <coordinates>$lon,$lat,100</coordinates>
    </Point>
</Placemark>
PLACEMARK;

        return $placeMark."\n";

    }


    public static function KMLHeader($documentName)
    {
$result=<<<KML
<?xml version="1.0" encoding="UTF-8"?>
<kml xmlns="http://www.opengis.net/kml/2.2" xmlns:gx="http://www.google.com/kml/ext/2.2" xmlns:kml="http://www.opengis.net/kml/2.2" xmlns:atom="http://www.w3.org/2005/Atom">
<Document>
<name>$documentName</name>
<StyleMap id="msn_ylw-pushpin">
        <Pair>
                <key>normal</key>
                <styleUrl>#sn_ylw-pushpin</styleUrl>
        </Pair>
        <Pair>
                <key>highlight</key>
                <styleUrl>#sh_ylw-pushpin</styleUrl>
        </Pair>
</StyleMap>
<Style id="sh_ylw-pushpin">
        <IconStyle>
                <scale>1.3</scale>
                <Icon>
                        <href>http://maps.google.com/mapfiles/kml/pushpin/ylw-pushpin.png</href>
                </Icon>
                <hotSpot x="20" y="2" xunits="pixels" yunits="pixels"/>
        </IconStyle>
</Style>
<Style id="sn_ylw-pushpin">
        <IconStyle>
                <scale>1.1</scale>
                <Icon>
                        <href>http://maps.google.com/mapfiles/kml/pushpin/ylw-pushpin.png</href>
                </Icon>
                <hotSpot x="20" y="2" xunits="pixels" yunits="pixels"/>
        </IconStyle>
</Style>
KML;

    return $result;

    }


    public static function KMLFooter()
    {
        return "</Document></kml>";

    }


    public static function KMLPhotoHeader($name)
    {

$result = <<<STRING
<?xml version="1.0" encoding="UTF-8"?>
<kml xmlns="http://www.opengis.net/kml/2.2" xmlns:gx="http://www.google.com/kml/ext/2.2" xmlns:kml="http://www.opengis.net/kml/2.2" xmlns:atom="http://www.w3.org/2005/Atom">
STRING;

    return $result;

    }

    public static function KMLPhotoSingle($filename,$lat,$lon, $alt,$href = NULL)
    {

        $name = util::rightStr($filename, '/',FALSE);

$result = <<<STRING
<PhotoOverlay>
	<name>$name</name>
	<Camera>
		<longitude>$lon</longitude>
		<latitude>$lat</latitude>
		<altitude>$alt</altitude>
		<heading>0.0</heading>
		<tilt>0</tilt>
		<roll>0</roll>
		<altitudeMode>relativeToGround</altitudeMode>
		<gx:altitudeMode>relativeToSeaFloor</gx:altitudeMode>
	</Camera>
	<Style>
		<IconStyle>
			<Icon>
				<href>:/camera_mode.png</href>
			</Icon>
		</IconStyle>
		<ListStyle>
			<listItemType>check</listItemType>
			<ItemIcon>
				<state>open closed error fetching0 fetching1 fetching2</state>
				<href>http://maps.google.com/mapfiles/kml/shapes/camera-lv.png</href>
			</ItemIcon>
			<bgColor>00ffffff</bgColor>
			<maxSnippetLines>2</maxSnippetLines>
		</ListStyle>
	</Style>
	<Icon>
		<href>$filename</href>
	</Icon>
	<ViewVolume>
		<leftFov>-25.005</leftFov>
		<rightFov>25.005</rightFov>
		<bottomFov>-19.205</bottomFov>
		<topFov>19.205</topFov>
		<near>1.05</near>
	</ViewVolume>
	<Point>
		<altitudeMode>relativeToGround</altitudeMode>
		<gx:altitudeMode>relativeToSeaFloor</gx:altitudeMode>
		<coordinates>$lon,$lat,$alt</coordinates>
	</Point>
</PhotoOverlay>
STRING;

    return $result;
    }


    public static function KMLPhotoFooter()
    {
$result = <<<STRING
</kml>
STRING;
    return $result;

    }

    public static function locate($find,$contains = null,$drive_letter = null)
    {
        // need to cconvert /data/data1/  /data/data2/ and /data/data3/
        // to /areas/
        if (!is_null($contains ))
        {
            $contains = trim($contains);
            if ($contains == "") $contains = null;
        }

        $prefix = "";
        if (!is_null($drive_letter))
            $prefix = "file:///$drive_letter:";

        foreach (file::locate($find,$contains ) as $line)
        {
            $line = str_replace('/data/data1/', '/areas/', $line);
            $line = str_replace('/data/data2/', '/areas/', $line);
            $line = str_replace('/data/data3/', '/areas/', $line);

            if (!util::contains($line, '/areas/')) continue;

            $path = $line;
            $folder = util::toLastSlash($line);
            $file = util::fromLastSlash($line);

            $folder_link = '<span><a style="text-decoration:none; color:green;" href="'.$folder.'">'.$folder.'</a></span>';
            $file_link   = '<span><a style="text-decoration:none; color:red;"   href="'.$path.'">'.$file.'</a></span>';

            $pdf_text_link = "";
            if (util::contains($file, '.pdf',FALSE))
            {
                $p2t = "http://sal-cairns.jcu.edu.au/projects/code/scripts/search/pdf2text?fn=";
                $pdf_text_link  = ' <span><a style="text-decoration:none; color:purple; font-size: 80%"   href="'.$p2t.$path.'">&nbsp;quick view</a></span>';
            }

            $result .= $folder_link.'&nbsp/&nbsp'.$file_link.$pdf_text_link.'<br>';
        }

        return $result;

    }


    public static function toFile($html,$filename)
    {
        if (is_array($html))
        {
            $html = "<html>\n".html_entity_decode(join("\n",$html))."\n</html>\n";
            file_put_contents($filename, $html);
        }
        else
        {
            $html = "<html>\n". html_entity_decode($html)."\n</html>\n";
            file_put_contents($filename, $html);

        }

    }


    public static function CSS($filename)
    {
        if (!file_exists($filename)) return "";
        return "\n".'<style type="text/css">'."\n". file_get_contents($filename)."\n</style>\n";
    }

    public static function javascript($filename)
    {
        if (!file_exists($filename)) return "";
        return "\n".'<script type="text/javascript">'."\n".  file_get_contents($filename)."\n</script>\n";
    }

    
    /**
     * Include CSS and JS in same folder as calling script that hase the same basename
     * 
     * e.g.    $filename = /code/fred.html
     * 
     * would try to include  CSS from fred.css and javascript from fred.js
     * 
     * @param type $filename 
     */
    public static function includeLocalHeadCode($filename,$pathDelim = "/")
    {
        $path_parts = pathinfo($filename);

        $simpleFilename = $path_parts['dirname'].$pathDelim. $path_parts['filename'];
        $css = self::CSS("{$simpleFilename}.css");
        $js = self::javascript("{$simpleFilename}.js");

        return "$css\n$js\n";

    }

    public static function includeLocalHeadCodeFromPathPrefix($folder,$prefix,$pathDelim = "/",$osExtensionSep = ".")
    {
        $simpleFilename = $folder.$pathDelim.$prefix;

        $css = self::CSS("{$simpleFilename}{$osExtensionSep}css");
        $js = self::javascript("{$simpleFilename}{$osExtensionSep}js");

        return "$css\n$js\n";

    }


    /**
     * Return HTML string for Page refresh via metatag  only if time is not null
     * - if timne is null return "" empty string
     *
     * @param type $time
     * @param type $url
     *
     * @return string [HTML tag for refresh] || [empty string]
     */
    public static function RefreshPageMetatag($time = null, $url = null )
    {
        if (is_null($time)) return "";
        if (is_null($url)) return "";
        if ($time < 0 ) return "";


        $result = '<meta http-equiv="refresh" content="'.$time.'; url='.$url.' " />';

        return $result;

    }

    
    
    
    
}
?>