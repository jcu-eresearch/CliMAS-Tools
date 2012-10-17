<?php

class ascii_grid
{
    //put your code here

    public static $LatLonDelim = "|";

    public function __construct($filename, $headRowCount = 6, $delim = " ", $fixed_width = NULL,$space_replace_count = 0)
    {
        $this->FixedWidth($fixed_width);
        $this->ReplaceSpaceCount($space_replace_count);
        $this->Filename($filename, $headRowCount, $delim);
    }

    public function __destruct()
    {
        unset($this->fileContent);
        unset($this->gridInfo);
        unset($this->latLon);
        unset($this->area);
        unset($this->filename);
        unset($this->tag);
    }


    private function loadGrid()
    {

        //$this->fileContent = null;
        $this->FileContent(file($this->Filename())); // load contents of fie. into an array


        $this->gridInfo = null;
        $this->GridInfo();

        $this->latLon = null;
        $this->latLon = $this->asLatLongValue();

        $this->area = null;

    }

    public function display($limitTo = null)
    {

    }

//    public function display_grid($limitTo = null)
//    {
//
//        // use these characters to represent grey scale
//        // $greyScale = "#$@B%8&WM*oahkbdpqwmZO0QLCJUYXzcvunxrjft/\|()1{}[]?-_+~i!lI;:,^`'.";
//
//        $greyScale = ".8WMoahkbdpqwmZO0QLCJUYXzcvunxrjft1ilI";
//
//        $normalised = array_util::Normalise($this->LatLon(), 'value', 0, strlen($greyScale));
//
//        echo "<div style=\"font-family:courier; font-size:8pt;\">\n";
//        // print_r($normalised);
//
//        $last_lat = "";
//        $line = "";
//        foreach ($normalised as $ll => $value)
//        {
//            $v = round($value['value'],0);
//            $c = substr($greyScale,$v,1);
//
//            if (!is_null($limitTo))
//            {
//                $c = "&nbsp;";
//                if (array_key_exists($ll, $limitTo))
//                   $c = substr($greyScale,$v,1);
//            }
//
//            $line = $line.$c;
//
//            if ($last_lat != $value['lat'])
//            {
//                echo $line."</br>\n";
//                $line = "";
//            }
//
//            $last_lat = $value['lat'];
//        }
//
//
//        echo "</div>\n";
//
//    }


    /*
    * @property Filename
    * @return mixed
    */
    public function UniqueValues($columnName = 'value')
    {
        if (!is_null($this->uniqueValues)) return $this->uniqueValues;

        $this->uniqueValues = array_unique($this->LatLon());
        return $this->uniqueValues;

    }
    private $uniqueValues = null;


    /*
    * @property Filename
    * @return mixed
    */
    public function Tag()
    {
        if (func_num_args() == 0 ) return $this->tag;
        if (func_num_args() == 1 ) return $this->tag[func_get_arg(0)];

        $this->tag[func_get_arg(0)] = func_get_arg(1);
    }
    private $tag = array();


    /*
    * @property Filename
    * @return mixed
    */
    public function Filename()
    {
        if (func_num_args() == 0 ) return $this->filename;

        // if filename changes then we have to reload to whole grid

        $args = func_get_args();

        $this->filename = $args[0];

        // set values for hear and delim - if you are loadeding another grid then you don't need to chnages values you don't have to
        if (array_key_exists("1", $args) ) $this->headerRowCount($args[1]) ;
        if (array_key_exists("2", $args) ) $this->ImportDelimimter($args[2]) ;

        $this->loadGrid();

    }
    private $filename;

    /*
    * @property Filename
    * @return mixed
    */
    public function Name()
    {
        return util::rightStr($this->Filename(), "/", FALSE);
    }

    /*
    * @property HeaderRowCount
    * @return mixed
    */
    public function HeaderRowCount()
    {
        if (func_num_args() == 0 ) return $this->headerRowCount;
        $this->headerRowCount = func_get_arg(0);
        return  $this->headerRowCount;
    }
    private $headerRowCount = 0;


    /*
    * @property ImportDelimimter
    * @return mixed
    */
    public function ImportDelimimter()
    {
        if (func_num_args() == 0 ) return $this->importDelimimter;
        $this->importDelimimter = func_get_arg(0);
    }
    private $importDelimimter = " ";

    /*
    * @property ImportDelimimter
    * @return mixed
    */
    public function FooterRowsStart()
    {
        if (func_num_args() == 0 ) return $this->footerRowsStart;
        $this->footerRowsStart = func_get_arg(0);
    }
    private $footerRowsStart = -1;


    /*
     * READONLY
     * @property LatLon t
     * @return mixed
     */
    public function LatLon()
    {
        return $this->latLon;
    }
    private $latLon;

    /*
     * READONLY
     * @property LatLon t
     * @return mixed
     */
    public function Area()
    {
        if (!is_null($this->area)) return $this->area;

        $this->area = null;
        $this->area = $this->calculateSpheroidGridArea($this->LatLon(), $this->cellsize());

        return $this->area;
    }
    private $area = null;


    /*
     * READONLY
     * @property FileContent
     * @return mixed
     */
    public function FileContent()
    {

        if (func_num_args() == 0 ) return $this->fileContent;
        $this->fileContent = func_get_arg(0);

        if ($this->ReplaceSpaceCount() > 0)
        {
            // replace Double space with single  'ReplaceSpaceCount'  times
            // this allow us to process ascii grids that have been sperated bu more than one space
            for ($rc = 0; $rc < $this->ReplaceSpaceCount(); $rc++)
                $this->fileContent = str_replace('  ', ' ', $this->fileContent);
        }

        // echo $this->fileContent;

        return $this->fileContent;
    }
    private $fileContent = NULL;

    /*
    * @property ReplaceSpaceCount
    * how many times should we process the originbal file contewnt and replaces "double" spaces
    */
    public function ReplaceSpaceCount()
    {
        if (func_num_args() == 0 ) return $this->replaceSpaceCount;
        $this->replaceSpaceCount = func_get_arg(0);
        return $this->replaceSpaceCount;
    }
    private $replaceSpaceCount = 0;



    /*
     * READONLY
     * @property FileContent
     * @return mixed
     */
    public function LineCount()
    {
        if (is_array($this->FileContent()))
                return count($this->FileContent());

        return 0;
    }

    /*
     * READONLY
     * @property FileContent
     * @return mixed
     */
    public function CellCount()
    {
        if (is_array($this->latLon))
                return count($this->latLon);

        return 0;
    }


    /*
     * READONLY
     * @property Maximum
     * @return mixed
     */
    public function Maximum()
    {
        if (!is_null($this->maximum)) return $this->maximum;

        if (is_array($this->LatLon()))
               $this->maximum = array_util::Maximum($this->LatLon());

        return $this->maximum;
    }
    private $maximum = null;

    /*
     * READONLY
     * @property Minimum
     * @return mixed
     */
    public function Minimum()
    {
        if (!is_null($this->minimum)) return $this->minimum;

        if (is_array($this->LatLon()))
               $this->minimum = array_util::Minimum ($this->LatLon());

        return $this->minimum;
    }
    private $minimum = null;



    /*
     *
     * @return array of arrays
     *
     * result[lat,lon] = ['lat'] ['lon'] ['value']
     *
     */

    private function asLatLongValue($decimalPlaces = 2)
    {

        $f = $this->FileContent();

        $headRows = $this->HeaderRowCount();
        $delim    = $this->ImportDelimimter();
        $footerStarts = $this->FooterRowsStart();

        $info = $this->GridInfo();

        $result = array();  // lat lon value

        $lat = $info['yll'] + ($info['cellsize'] * ( $info['nrows'] - 1) );

        for ($row = 0; $row < $info['nrows']; $row++)
        {

            // echo "Processing $row <br>\n";
            $rowIndex = $headRows + $row;
            $cells = split($delim, trim($f[$rowIndex]));

            if (is_null($this->FixedWidth()))
                if (count($cells) !=  $info['ncols'])
                {
                    //echo "Column Count Mismatch [$rowIndex] ".count($cells)  ." != ".$info['ncols']." <br> ";
                    continue;
                }

            $lon = $info['xll'];
            for ($col = 0; $col < $info['ncols']; $col++)
            {

                $key = trim(sprintf("%02.".$decimalPlaces."f", $lat)).
                       self::$LatLonDelim.
                       trim(sprintf("%02.".$decimalPlaces."f", $lon));

                if (!is_null($this->FixedWidth()))
                    $result[$key] = trim(substr($f[$rowIndex],$col * $this->FixedWidth(),$this->FixedWidth()));
                else
                    $result[$key] = trim($cells[$col]);  // dynamic width


                $lon = $lon + $info['cellsize'];

            }

            $lat = $lat + (-1.0 * $info['cellsize']);

        }

        return $result;

    }


    public function GridInfo()
    {
        if (!is_null($this->gridInfo)) return $this->gridInfo;
        $this->gridInfo = self::GetGridInfo($this->FileContent(),$this->HeaderRowCount());
        return $this->gridInfo;
    }
    private $gridInfo = null;

    public static function GetGridInfo($filecontent,$headRows = 6)
    {


//        ncols 886
//        nrows 691
//        xllcenter 112.0000
//        yllcenter -44.5000
//        cellsize 0.0500
//        nodata_value -99.9900

        $f = $filecontent;

        $headerLines = array();
        for ($index = 0; $index < $headRows; $index++)
            $headerLines[$index] = $f[$index];


        $result["ncols"]        = trim(util::rightStr(array_util::Search($headerLines, "ncols"), " ")) ;
        $result["nrows"]        = trim(util::rightStr(array_util::Search($headerLines, "nrows"), " ")) ;
        $result["xllcenter"]    = trim(util::rightStr(array_util::Search($headerLines, "xllcenter"), " ")) ;
        $result["yllcenter"]    = trim(util::rightStr(array_util::Search($headerLines, "yllcenter"), " ")) ;
        $result["cellsize"]     = trim(util::rightStr(array_util::Search($headerLines, "cellsize"), " ")) ;
        $result["nodata_value"] = trim(util::rightStr(array_util::Search($headerLines, "nodata_value"), " ")) ;

        $result["xllcorner"]    = util::rightStr(array_util::Search($headerLines, "xllcorner"), " ") ;
        $result["yllcorner"]    = util::rightStr(array_util::Search($headerLines, "yllcorner"), " ") ;

        $result["measure_from"] = "corner";
        if (isset($result["xllcenter"])) $result["measure_from"] = "center";

        if ($result["xllcenter"] != "") $result["xll"] = $result["xllcenter"];
        if ($result["yllcenter"] != "") $result["yll"] = $result["yllcenter"];

        if ($result["xllcorner"] != "") $result["xll"] = $result["xllcorner"];
        if ($result["yllcorner"] != "") $result["yll"] = $result["yllcorner"];

        return $result;

    }


    public function ncols()
    {
        if (is_null($this->gridInfo)) $this->GridInfo();
        return $this->gridInfo['ncols'];
    }

    public function nrows()
    {
        if (is_null($this->gridInfo)) $this->GridInfo();
        return $this->gridInfo['nrows'];
    }

    public function xllcenter()
    {
        if (is_null($this->gridInfo)) $this->GridInfo();
        return $this->gridInfo['xllcenter'];
    }

    public function yllcenter()
    {
        if (is_null($this->gridInfo)) $this->GridInfo();
        return $this->gridInfo['yllcenter'];
    }

    public function cellsize()
    {
        if (is_null($this->gridInfo)) $this->GridInfo();
        return $this->gridInfo['cellsize'];
    }

    public function nodata_value()
    {
        if (is_null($this->gridInfo)) $this->GridInfo();
        return $this->gridInfo['nodata_value'];
    }

    public function xllcorner()
    {
        if (is_null($this->gridInfo)) $this->GridInfo();
        return $this->gridInfo['xllcorner'];
    }

    public function yllcorner()
    {
        if (is_null($this->gridInfo)) $this->GridInfo();
        return $this->gridInfo['yllcorner'];
    }


    public function measure_from()
    {
        if (is_null($this->gridInfo)) $this->GridInfo();
        return $this->gridInfo['measure_from'];
    }

    public function xll()
    {
        if (is_null($this->gridInfo)) $this->GridInfo();
        return $this->gridInfo['xll'];
    }

    public function yll()
    {
        if (is_null($this->gridInfo)) $this->GridInfo();
        return $this->gridInfo['yll'];
    }


    public function FixedWidth()
    {
        if (func_num_args() == 0 ) return $this->fixedWidth;
        $this->fixedWidth = func_get_arg(0);
        return $this->isfixedWidth;
    }
    private $fixedWidth;


    /*
     * return: array from a file in LAT,LONG,VALUE format return an array of arrays
     */
    public static function fromLatLongValue($llFilename, $delim = ",", $numberFormat = "02.2f")
    {
        $points_file = file($llFilename);

        $latlon = array();

        $decimalPlaces = util::rightStr($numberFormat, ".",false);
        $decimalPlaces = trim($numberFormat,"f");

        foreach ($points_file as $row_id => $line)
        {
            if (trim($line) == "") continue;

            $split = explode($delim,$line);

            $lon = sprintf("%".$numberFormat, round($split[0],$decimalPlaces));
            $lat = sprintf("%".$numberFormat, round($split[1],$decimalPlaces));

            if ($lon == 0.0 || $lat == 0.0) continue;

                    // lat|lon  e.g -16.15|145.5
            $latlon["$lat".self::$LatLonDelim."$lon"] = trim($split[2]);

        }

        return $latlon;

    }

    /*
     * filter the lot longs so that the result only contais the LatLons that pre4sent in the find
     */
    public static function filterLatLong($src, $find)
    {

        $result = array();
        foreach ($src as $key => $llv)
            if (array_key_exists($key, $find))
                $result[$key] = $llv;

        return $result;

    }


    // Approximatation area calcuation of a gridSize cell on earth surface
    // return: KM squared
    public static function calculateSpheroidGridArea($src = NULL,$gridSize = 0.05, $limitLatLon = NULL)
    {

        if (is_null($src))
        {
            //echo "##ERROR ascii_grid::calculateSpheroidGridArea src passed as null \n";
            return NULL;
        }

        if (!is_array($src))
        {
            //echo "##ERROR ascii_grid::calculateSpheroidGridArea src passed is NOT array \n";
            return NULL;
        }


        $Re   = 6378000;   // Rayon de la Terre (m)
        $Re2  = $Re * $Re; // $Re squared
        $dlon = $gridSize * pi()/180;  // pas en longitude
        $dlat = $gridSize * pi()/180;  // pas en latitude

        $result = array();
        foreach ($src as $rowKey => $rowValue)
        {

            $lat_lon = explode(self::$LatLonDelim,$rowKey);
            $lat = $lat_lon[0];
            $lon = $lat_lon[1];

            if (!is_null($limitLatLon) ) // if we have a limit on lat and lon
                if (!array_key_exists($rowKey, $limitLatLon)) continue;  // if we don't have current lat lon in the limit list then continue

            $result[$rowKey] = cos( pi() * ($lat/180) ) * $dlon * $dlat * $Re2 * 0.000001; // pow(1,-6);;

        }

        return $result;

    }


    public function SaveFixedWidth($filename = NULL)
    {
        if (is_null($filename)) $filename = $this->Filename ();

        $newFilename = str_replace('.asc', '_fixed.asc', $filename);

        //echo "writing fixed with version to ".$newFilename."\n";

        $file_handle = fopen($newFilename,'w');
        self::SaveFixedWidthHeader($file_handle);
        self::SaveFixedWidthData($file_handle);
        self::SaveFixedWidthBOMFooter($file_handle);
        fclose($file_handle);

        return $newFilename;

    }


    private function SaveFixedWidthHeader($file_handle)
    {

        $format = "%10s";

        $result = "";

        $G = $this->GridInfo();
        $result .= "ncols        ".sprintf($format, $G["ncols"]."\n");
        $result .= "nrows        ".sprintf($format, $G["nrows"]."\n");

        if ($G["measure_from"] == "corner")
        {
            $result .= "xllcorner    ".sprintf($format, $G["xllcorner"]."\n");
            $result .= "yllcorner    ".sprintf($format, $G["yllcorner"]."\n");
        }
        else
        {
            $result .= "xllcenter    ".sprintf($format, $G["xllcenter"]."\n");
            $result .= "yllcenter    ".sprintf($format, $G["yllcenter"]."\n");
        }

        $result .= "cellsize     ".sprintf($format, $G["cellsize"]."\n");
        $result .= "nodata_value ".sprintf($format, $G["nodata_value"]."\n");

        fwrite($file_handle,$result);
    }


    private function SaveFixedWidthData($file_handle)
    {

        $G = $this->GridInfo();

        $format = "%04.2f";
        // assume every cell is  "nnnnn.nn "   5 numbers period 2 nunmbers space
        foreach ($this->FileContent() as $line_index => $line)
        {
            if ($line_index < $this->HeaderRowCount()) continue;

            if ($line_index > ($this->HeaderRowCount() - 1) + $this->nrows()) continue;

            $new_line = "";
            foreach (explode(' ',$line) as $cell_index => $cell)
            {
                $new_line .=  sprintf("%8s",sprintf("%04.2f", $cell));
            }
            $new_line .= "\n";
            fwrite($file_handle,$new_line);
        }


    }

    private function SaveFixedWidthBOMFooter($file_handle)
    {
        $fc = $this->FileContent();
        for ($index = 697; $index <= 714; $index++)
        {
            fwrite($file_handle,$fc[$index]);
        }
    }


    public static function folder2FixedWitdth($folder)
    {
        $files = file::folder_files($folder);
        $files = file::arrayFilter($files,'.asc');
        $files = file::arrayFilterOut($files,'_fixed');
        sort($files);
        foreach ($files as $filename)
        {
            $nfn = str_replace('.asc', '_fixed.asc', $filename);
            if (file_exists($nfn)) continue;

            $AG = new ascii_grid($filename);
            $AG->SaveFixedWidth();
            unset($AG);
        }

    }


    public static function PROJECTION_FILE_WGS84()
    {
        $r  = "Projection    GEOGRAPHIC\n";
        $r .= "Datum         WGS84\n";
        $r .= "Spheroid      WGS84\n";
        $r .= "Units         DD\n";
        $r .= "Zunits        NO\n";
        $r .= "Parameters\n\n";

        return $r;
    }


    public static function ESRI_STATS($single_grid_src,$scale_factor = 1,$null_value = 9999.00)
    {

        $min  = sprintf("%05.5f", array_util::Minimum($single_grid_src,NULL,$null_value) * $scale_factor);
        $max  = sprintf("%05.5f", array_util::Maximum($single_grid_src,NULL,$null_value) * $scale_factor);
        $mean = sprintf("%05.5f", array_util::Average($single_grid_src,NULL,$null_value) * $scale_factor);
        $sd   = sprintf("%05.5f", stats::StandardDeviation($single_grid_src,NULL,$null_value) * $scale_factor);

$xml = <<<STR
<PAMDataset>
  <PAMRasterBand band="1">
    <Metadata>
      <MDI key="STATISTICS_MINIMUM">$min</MDI>
      <MDI key="STATISTICS_MAXIMUM">$max</MDI>
      <MDI key="STATISTICS_MEAN">$mean</MDI>
      <MDI key="STATISTICS_STDDEV">$sd</MDI>
    </Metadata>
  </PAMRasterBand>
</PAMDataset>

STR;
        return $xml;

    }



    public static function ESRI_METADATA($filename)
    {
$meta=<<<META
<?xml version="1.0"?>
<!--<!DOCTYPE metadata SYSTEM "http://www.esri.com/metadata/esriprof80.dtd">-->
<metadata xml:lang="en">
	<Esri>
		<MetaID>{AE5684C3-2998-401C-906A-D5DF365C7306}</MetaID>
		<CreaDate>20110315</CreaDate>
		<CreaTime>16310700</CreaTime>
		<SyncOnce>FALSE</SyncOnce>
		<SyncDate>20110315</SyncDate>
		<SyncTime>16310800</SyncTime>
		<ModDate>20110315</ModDate>
		<ModTime>16310800</ModTime>
	</Esri>
	<idinfo>
		<native Sync="TRUE">Microsoft Windows XP Version 5.1 (Build 2600) Service Pack 3; ESRI ArcCatalog 9.3.0.1770</native>
		<descript>
			<langdata Sync="TRUE">en</langdata>
			<abstract>REQUIRED: A brief narrative summary of the data set.</abstract>
			<purpose>REQUIRED: A summary of the intentions with which the data set was developed.</purpose>
		</descript>
		<citation>
			<citeinfo>
			<origin>REQUIRED: The name of an organization or individual that developed the data set.</origin>
			<pubdate>REQUIRED: The date when the data set is published or otherwise made available for release.</pubdate>
			<title Sync="TRUE">Grace-SMAvg_regression.asc</title>
			<ftname Sync="TRUE">Grace-SMAvg_regression.asc</ftname>
			<onlink Sync="TRUE">Z:\projects\GRACE\code\summary\grid\Grace-SMAvg_regression.asc</onlink>
			<geoform Sync="TRUE">raster digital data</geoform>
			</citeinfo>
		</citation>
		<timeperd>
			<current>REQUIRED: The basis on which the time period of content information is determined.</current>
			<timeinfo>
				<sngdate>
					<caldate>REQUIRED: The year (and optionally month, or month and day) for which the data set corresponds to the ground.</caldate>
				</sngdate>
			</timeinfo>
		</timeperd>
		<status>
			<progress>REQUIRED: The state of the data set.</progress>
			<update>REQUIRED: The frequency with which changes and additions are made to the data set after the initial data set is completed.</update>
		</status>
		<spdom>
			<bounding>
				<westbc Sync="TRUE">REQUIRED: Western-most coordinate of the limit of coverage expressed in longitude.</westbc>
				<eastbc Sync="TRUE">REQUIRED: Eastern-most coordinate of the limit of coverage expressed in longitude.</eastbc>
				<northbc Sync="TRUE">REQUIRED: Northern-most coordinate of the limit of coverage expressed in latitude.</northbc>
				<southbc Sync="TRUE">REQUIRED: Southern-most coordinate of the limit of coverage expressed in latitude.</southbc>
			</bounding>
			<lboundng>
				<leftbc Sync="TRUE">-179.500000</leftbc>
				<rightbc Sync="TRUE">179.500000</rightbc>
				<bottombc Sync="TRUE">-55.500000</bottombc>
				<topbc Sync="TRUE">82.500000</topbc>
			</lboundng>
		</spdom>
		<keywords>
			<theme>
				<themekt>REQUIRED: Reference to a formally registered thesaurus or a similar authoritative source of theme keywords.</themekt>
				<themekey>REQUIRED: Common-use word or phrase used to describe the subject of the data set.</themekey>
			</theme>
		</keywords>
		<accconst>REQUIRED: Restrictions and legal prerequisites for accessing the data set.</accconst>
		<useconst>REQUIRED: Restrictions and legal prerequisites for using the data set after access is granted.</useconst>
		<natvform Sync="TRUE">Raster Dataset</natvform>
	</idinfo>
	<dataIdInfo>
		<envirDesc Sync="TRUE">Microsoft Windows XP Version 5.1 (Build 2600) Service Pack 3; ESRI ArcCatalog 9.3.0.1770</envirDesc>
		<dataLang>
			<languageCode Sync="TRUE" value="en"></languageCode>
		</dataLang>
		<idCitation>
			<resTitle Sync="TRUE">Grace-SMAvg_regression.asc</resTitle>
			<presForm>
				<PresFormCd Sync="TRUE" value="005"></PresFormCd>
			</presForm>
		</idCitation>
		<spatRpType>
			<SpatRepTypCd Sync="TRUE" value="002"></SpatRepTypCd>
		</spatRpType>
		<dataExt>
			<geoEle>
				<GeoBndBox esriExtentType="native">
					<westBL Sync="TRUE">-179.5</westBL>
					<eastBL Sync="TRUE">179.5</eastBL>
					<northBL Sync="TRUE">82.5</northBL>
					<southBL Sync="TRUE">-55.5</southBL>
					<exTypeCode Sync="TRUE">1</exTypeCode>
				</GeoBndBox>
			</geoEle>
		</dataExt>
	</dataIdInfo>
	<metainfo>
		<langmeta Sync="TRUE">en</langmeta>
		<metstdn Sync="TRUE">FGDC Content Standards for Digital Geospatial Metadata</metstdn>
		<metstdv Sync="TRUE">FGDC-STD-001-1998</metstdv>
		<mettc Sync="TRUE">local time</mettc>
		<metextns>
			<onlink Sync="TRUE">http://www.esri.com/metadata/esriprof80.html</onlink>
			<metprof Sync="TRUE">ESRI Metadata Profile</metprof>
		</metextns>
		<metc>
			<cntinfo>
				<cntorgp>
					<cntper>REQUIRED: The person responsible for the metadata information.</cntper>
					<cntorg>REQUIRED: The organization responsible for the metadata information.</cntorg>
				</cntorgp>
				<cntaddr>
					<addrtype>REQUIRED: The mailing and/or physical address for the organization or individual.</addrtype>
					<city>REQUIRED: The city of the address.</city>
					<state>REQUIRED: The state or province of the address.</state>
					<postal>REQUIRED: The ZIP or other postal code of the address.</postal>
				</cntaddr><cntvoice>REQUIRED: The telephone number by which individuals can speak to the organization or individual.</cntvoice>
			</cntinfo>
		</metc>
		<metd Sync="TRUE">20110315</metd>
	</metainfo>
	<mdLang>
		<languageCode Sync="TRUE" value="en"></languageCode>
	</mdLang>
	<mdStanName Sync="TRUE">ISO 19115 Geographic Information - Metadata</mdStanName>
	<mdStanVer Sync="TRUE">DIS_ESRI1.0</mdStanVer>
	<mdChar>
		<CharSetCd Sync="TRUE" value="004"></CharSetCd>
	</mdChar>
	<mdHrLv>
		<ScopeCd Sync="TRUE" value="005"></ScopeCd>
	</mdHrLv>
	<mdHrLvName Sync="TRUE">dataset</mdHrLvName>
	<distInfo>
		<distributor>
			<distorTran>
				<onLineSrc>
					<linkage Sync="TRUE">file://Z:\projects\GRACE\code\summary\grid\Grace-SMAvg_regression.asc</linkage>
					<protocol Sync="TRUE">Local Area Network</protocol>
					<orDesc Sync="TRUE">002</orDesc>
				</onLineSrc>
			</distorTran>
			<distorFormat>
				<formatName Sync="TRUE">Raster Dataset</formatName>
			</distorFormat>
		</distributor>
	</distInfo>
	<distinfo>
		<resdesc Sync="TRUE">Downloadable Data</resdesc>
	</distinfo>
	<spdoinfo>
		<direct Sync="TRUE">Raster</direct>
		<rastinfo>
			<rasttype Sync="TRUE">Pixel</rasttype>
			<rowcount Sync="TRUE">138</rowcount>
			<colcount Sync="TRUE">359</colcount>
			<rastxsz Sync="TRUE">1.000000</rastxsz>
			<rastysz Sync="TRUE">1.000000</rastysz>
			<rastbpp Sync="TRUE">32</rastbpp>
			<vrtcount Sync="TRUE">1</vrtcount>
			<rastorig Sync="TRUE">Upper Left</rastorig>
			<rastcmap Sync="TRUE">FALSE</rastcmap>
			<rastcomp Sync="TRUE">None</rastcomp>
			<rastband Sync="TRUE">1</rastband>
			<rastdtyp Sync="TRUE">pixel codes</rastdtyp>
			<rastifor Sync="TRUE">AAIGrid</rastifor>
			<rastplyr Sync="TRUE">FALSE</rastplyr>
		</rastinfo>
	</spdoinfo>
	<spref>
		<horizsys>
			<planar>
				<planci>
					<plance Sync="TRUE">row and column</plance>
					<coordrep>
						<absres Sync="TRUE">1.000000</absres>
						<ordres Sync="TRUE">1.000000</ordres>
					</coordrep>
				</planci>
			</planar>
		</horizsys>
	</spref>
	<mdDateSt Sync="TRUE">20110315</mdDateSt>
</metadata>
META;

        return $meta;

    }


    public static function MatrixColumn2Grid($matrix, $column_name,$null_value,$outputfilename,$scale_factor = 1,$cell_size = NULL)
    {
        $matcol = matrix::Column($matrix, $column_name);

        self::LatLonKeyedArray2Grid($matcol,$null_value,$outputfilename,$scale_factor ,$cell_size);
    }


    /*
     * $matrix         - source matrix
     * $column_name    - name of column to use,
     * $null_value     - null_value,
     * $outputfilename - output filename without etension
     */
    public static function LatLonKeyedArray2Grid($keyedArray,$null_value,$outputfilename,$scale_factor = 1,$cellsize = NULL)
    {

        $outputfilename = str_replace('.asc', '', $outputfilename);
        $outputfilename = str_replace('.csv', '', $outputfilename);

        $grid_dims = self::CalculateGridDimesions($keyedArray);

        // if they pass in a grid size then us that instead of our calculated version
        if (!is_null($cell_size)) $grid_dims['cellsize'] = $cellsize  * 1.0;

        $grid_dims['scale_factor'] = $scale_factor  * 1.0;
        $grid_dims['nodata_value'] = $null_value  * 1.0;

        // echo "grid_dims = [".$grid_dims['cellsize']."]\n";

        $output = self::Header($grid_dims);

        $get_lat = $grid_dims['lat_max'] * 1.0;
        for ($row = 0; $row < $grid_dims['nrows']; $row++) {

            $get_lon = $grid_dims['lon_min'] * 1.0;
            for ($col = 0; $col < $grid_dims['ncols']; $col++) {

                $llkey = sprintf("%02.2f", $get_lat).ascii_grid::$LatLonDelim.sprintf("%02.2f", $get_lon);

                $v = sprintf("%05.2f", $null_value).' '; // assume Null value

                // echo "$llkey\n";
                if (array_key_exists($llkey, $keyedArray))
                {

                    $v = sprintf("%05.5f", ($keyedArray[$llkey] * $scale_factor)).' ';
                }

                $output .= $v;

                $get_lon = $get_lon + $grid_dims['cellsize'];
            }

            $output .= "\n";
            $get_lat = $get_lat - $grid_dims['cellsize'];

        }


        file_put_contents($outputfilename.'.asc', $output);
        file_put_contents($outputfilename.'.prj', ascii_grid::PROJECTION_FILE_WGS84());
        file_put_contents($outputfilename.'.asc.aux.xml', ascii_grid::ESRI_STATS($keyedArray,$scale_factor,$null_value));

        return file_exists($outputfilename.'.asc');

    }


    public static function CalculateGridDimesions($keyedArray,$cell_size = NULL)
    {
        // get unique list of lats and lons
        $Lats = array();
        $Lons = array();
        foreach ($keyedArray as $row_id => $row)
        {
            $latLon = explode(ascii_grid::$LatLonDelim,$row_id);
            $lat = $latLon[0];
            $lon = $latLon[1];
            $Lats[$lat]++;
            $Lons[$lon]++;
        }
        $Lats = array_unique(array_keys($Lats));
        $Lons = array_unique(array_keys($Lons));

        sort($Lats);
        sort($Lons);


        if (is_null($cell_size))
        {
            // cellsize - is the most common difference betewwen lats or lons
            $latKeys = array_keys(array_util::CountOfValuesDistance($Lats));
            $lonKeys = array_keys(array_util::CountOfValuesDistance($Lons));
            $cell_size = ($latKeys[0] + $lonKeys[0])/2;
        }

        // get min and max values for Lat and Lon
        // to find the actual number of columns and rows we need to calculate
        // based on min and max


        $result = array();
        $result['cellsize'] = $cell_size;

        $result['lon_min'] = array_util::Minimum($Lons) * 1.0;
        $result['lon_max'] = array_util::Maximum($Lons) * 1.0;
        $result['lon_range'] = (($result['lon_max'] - $result['lon_min']) + $cell_size) * 1.0;

        $result['lat_min'] = array_util::Minimum($Lats)  * 1.0;
        $result['lat_max'] = array_util::Maximum($Lats)  * 1.0;
        $result['lat_range'] = (($result['lat_max'] - $result['lat_min']) + $cell_size) * 1.0;

        $result['ncols'] = ($result['lon_range'] / $cell_size) * 1.0;
        $result['nrows'] = ($result['lat_range'] / $cell_size) * 1.0;
        $result['xllcorner'] = util::first_element($Lons) * 1.0;
        $result['yllcorner'] = util::first_element($Lats) * 1.0;

        $result['TopCornerX'] = util::first_element($Lons) * 1.0;
        $result['TopCornerY'] = util::last_element($Lats) * 1.0;

        return $result;

    }


    public static function Header($grid_dims)
    {
        $output = "";

        $output .= 'ncols '.$grid_dims['ncols']."\n";
        $output .= 'nrows '.$grid_dims['nrows']."\n";
        $output .= 'xllcorner '.sprintf("%02.2f", $grid_dims['xllcorner'])."\n";
        $output .= 'yllcorner '.sprintf("%02.2f", $grid_dims['yllcorner'])."\n";
        $output .= 'cellsize '.sprintf("%02.2f", $grid_dims['cellsize'])."\n";
        $output .= 'scale_factor '.sprintf("%02.2f", $grid_dims['scale_factor'])."\n";
        $output .= 'nodata_value '.sprintf("%02.2f", $grid_dims['nodata_value'])."\n";

        return $output ;

    }

    function toLatLonFile($ascii_filename,$output_filename,$header_rows = 6,$remove_nulls = FALSE,$file_header = "")
    {

        $grid = new ascii_grid($ascii_filename, $header_rows);
        $ll = $grid->LatLon();

        $nd = $grid->nodata_value();

        // collect keys that need to be removed - ie the value is Null $nd

        if ($remove_nulls)
        {
            $to_unset = array();
            foreach ($ll as $key => $value) if ($value == $nd) $to_unset[] = $key;
            foreach ($to_unset as $key_to_unset) unset($ll[$key_to_unset]);
        }

        if ($file_header == "") $file_header = "LatLon,".util::toLastChar(util::fromLastSlash($ascii_filename), '.')."\n";

        file::Array2File($ll,$output_filename,$file_header);

        unset($ll);
        unset($grid);

        if (file_exists($output_filename)) return $output_filename;

        return NULL;

    }




}
?>