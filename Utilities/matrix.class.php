<?php
class matrix
{

        /*
    * @method MatrixCell
    * @param $matrix
    * @param $rowID
    * @param $column
    * @param $noValue = null
    * @return mixed
    */
    public static function Cell($matrix, $rowID,$column, $noValue = null)
    {
        if (is_null($matrix)) return $noValue;
        if (!array_key_exists($rowID, $matrix)) return $noValue;
        if (!array_key_exists($column, $matrix[$rowID])) return $noValue;

        return $matrix[$rowID][$column];

    }


        /*
    * @method displayMatrix
    * @param $src
    * @param $delim = "\t"
    * @return mixed
    */
    public static function display($src, $delim = "\t",$rowCount = null,$max_width = null)
    {
        echo self::printable($src, $delim,$rowCount,$max_width)."\n";
    }

    /*
    * @method printableMatrix
    * @param $src
    * @param $delim = "\t"
    * @return mixed
    */
    public static function printable($src, $delim = "\t",$rowCount = null,$max_width = null,$pad_char = ".")
    {
        $first = util::first_element($src);

        $result = "";
        $count = 0;
        if (!is_array($first))
        {
            foreach ($src as $rowID => $row)
            {
                if (!is_null($rowCount)) if ($count > $rowCount) return $result;
                $result .= "{$rowID}{$delim}{$row}\n";
                $count++;
            }

        }
        else
        {
            $ucn = util::uniqueColumnNames($src);

            if (!is_null($max_width))
            {
                $result .= str_pad("ROW", $max_width, $pad_char, STR_PAD_RIGHT)."{$delim}";

                foreach ($ucn as $column_name)
                    $result .= substr(str_pad($column_name, $max_width, $pad_char, STR_PAD_RIGHT),0,$max_width)."{$delim}";
            }
            else
            {
                $result .= "ROW{$delim}".join("$delim",$ucn).$delim;
            }


            foreach ($src as $rowID => $row)
            {
                if (!is_null($rowCount))  if ($count > $rowCount) return $result;

                // for each row go thru Unioque column names and if we have it add it if we don't have add empty cell  build row into anaary to be written
                $rowToWrite = array();
                foreach ($ucn as $ColumnName)
                {
                    $rowToWrite[$ColumnName] = str_replace("\n"," ",$row[$ColumnName]);

                    if (!is_null($max_width))
                        $rowToWrite[$ColumnName] = substr(str_pad($rowToWrite[$ColumnName],$max_width," ",STR_PAD_RIGHT),0,$max_width);
                }

                if (!is_null($max_width))
                {
                    $result .= "\n".substr(str_pad($rowID, $max_width, " ", STR_PAD_RIGHT),0,$max_width)."{$delim}";
                    $result .= join("$delim",array_values($rowToWrite)).$delim;
                }
                else
                {
                    $result .= "\n{$rowID}{$delim}".join("$delim",array_values($rowToWrite)).$delim;
                }

                $count++;
            }

        }


        return $result;
    }


        /*
    * @method matrix2HTMLTable
    * @param $src
    * @return mixed
    */
    public static function toHTML($src, $style="",$display_row_id = true,$class= "")
    {

        if ($style != "") $style = ' style="'.$style.'" ';
        if ($class != "") $class = ' class="'.$class.'" ';

        $result = "";

        $ucn = util::uniqueColumnNames($src);

        $result .= "\n".'<table  cellspacing="0" cellpadding="0" border="0"'.$style.' '.$class.'>';
        $result .= "\n"."<tr>";

        $rihdr = ($display_row_id) ? "<td class=\"table_td\">ROW</td> " : "";

        $result .= "\n"."{$rihdr}<td class=\"table_td\"><b>".join("</b></td><td class=\"table_td\"><b>",$ucn)."</b></td>\n";
        $result .= "\n"."</tr>";


        foreach ($src as $rowID => $row)
        {

            // for each row go thru Unioque column names and if we have it add it
            // if we don't have add empty cell

            // build row into anaary to be written
            $rowToWrite = array();
            foreach ($ucn as $ColumnName)
            {

                if (array_key_exists($ColumnName, $row))
                {
                    if (is_array($row[$ColumnName]))
                    {
                        $rowToWrite[$ColumnName] = "Array [".count($row[$ColumnName])."]";
                    }
                    else
                        $rowToWrite[$ColumnName] = $row[$ColumnName];

                }
                else
                {
                        $rowToWrite[$ColumnName] = "";
                }


            }

            $ritext = ($display_row_id) ? "<td class=\"table_td\">{$rowID}</td> " : "";
            $result .= "\n"."<tr>";
            $result .= "\n"."{$ritext}<td class=\"table_td\">".join("</td><td class=\"table_td\">",array_values($rowToWrite))."</td>\n";
            $result .= "\n"."</tr>";

        }


        $result .= "\n".'</table>';

        return $result ;

    }


    public static function toHTML_CSS($src, $display_row_id = true)
    {

        $result = "";

        $ucn = util::uniqueColumnNames($src);

        $result .= "\n".'<div class="css_table">';
        $result .= "\n".'<div class="css_row_header">';

        $rihdr = ($display_row_id) ? '<div class="css_cell_header">ROW</div> ' : '';

        $result .= "\n"."{$rihdr}<div class=\"css_cell_header\"><b>".join("</b></div><div class=\"css_cell_header\"><b>",$ucn)."</b></div>\n";
        $result .= "\n"."</div>";


        foreach ($src as $rowID => $row)
        {

            // for each row go thru Unioque column names and if we have it add it
            // if we don't have add empty cell

            // build row into anaary to be written
            $rowToWrite = array();
            foreach ($ucn as $ColumnName)
            {
                $rowToWrite[$ColumnName] = $row[$ColumnName];

            }

            $ritext = ($display_row_id) ? "<div class=\"css_cell_row_header\">{$rowID}</div> " : "";
            $result .= "\n"."<div class=\"css_row\">";
            $result .= "\n"."{$ritext}<div class=\"css_cell\">".join("</div><div class=\"css_cell\">",array_values($rowToWrite))."</div>\n";
            $result .= "\n"."</div>";

        }


        $result .= "\n".'</div>';

        return $result ;

    }



    // GIVEN Matirx
// return : value for each column
// where the cell value is less than or equial to (but not greater than $limit )
 // return array - Column / row id    where  value <= $limit

    /*
    * @method accumLimit
    * @param $src
    * @param $limit
    * @return mixed
    */
    public static function AccumulateWithLimit($src, $limit)
    {

        $srcAccum = self::Accumulate($src);

        $result = array();

        $previousRow = null;

        foreach ($srcAccum as $rowID => $row)
        {

            if ($previousRow == null)  $previousRow = $row;


            foreach ($row as $columnName => $cellValue)
            {
                if (!array_key_exists($columnName, $result)) $result[$columnName] = array();

                // $limit must be >= than the last value and <= current value

                if ( ($limit >= $previousRow[$columnName]) && ( $limit <= $row[$columnName]) )
                {
                    $result[$columnName]['rowID'] = $rowID;     // keep being reset set to new row id until value is over limit
                    $result[$columnName]['value'] = $cellValue;
                    $result[$columnName]['limit'] = $limit;
                }
            }

            $previousRow = $row;

        }

        return $result;

    }

    /* Row by row accumulation - i.e. each row is a running total
    * @method accumMatrix
    * @param $src
    * @return mixed
    */
    public static function Accumulate($src)
    {

        $result = array();

        $lineCount = 0;
        $currentRowID = null;
        $previousRowID = null;
        foreach ($src as $rowID => $row)
        {
            $currentRowID = $rowID;

            $result[$rowID] = array();

            foreach ($row as $columnName => $cellValue)
            {
                if ($previousRowID == null)  // first row
                {
                    $result[$rowID][$columnName] = $cellValue; // copy value
                }
                else
                {
                    $result[$rowID][$columnName] = $result[$previousRowID][$columnName] + $cellValue; // cell value plus previous
                }

            }

            $lineCount++;
            $previousRowID = $rowID;
        }

        return $result;

    }


    /*
    * @method loadMatrix
    * @param $filename
    * @param $delim = "
    * @param "
    * @param $rowID = ""
    * @return mixed
    */
    public static function LoadArray($filenames,$delim = ",",$rowID = "")
    {

        $result = array();
        foreach ($filenames as $filename)
        {
            if (!file_exists($filename))
            {
                echo "### ERROR: Matrix::Load File does not exist .. $filename\n";
                continue;
            }

            $result[$filename] = self::Load($filename,$delim,$rowID);

        }

        return $result;

    }


    /*
    * @method loadMatrix
    * @param $filename
    * @param $delim = "
    * @param "
    * @param $rowID = ""
    * @return mixed
    */
    public static function Load($filename,$delim = ",",$rowID = "")
    {
        if (!file_exists($filename))
        {
            echo "### ERROR: Matrix::Load File does not exist .. $filename\n";
            return NULL;
        }

        return self::fromString(file_get_contents($filename),$delim,$rowID);

    }

    // create a matrix out of a CSV exported from ArcGIS
    public static function LoadArcGISExportedCSV($filename,$delim = ",",$decimal_place = 5)
    {
        if (!file_exists($filename))
        {
            echo "### ERROR: matrix::LoadArcGISExportedCSV File does not exist .. $filename\n";
            return NULL;
        }

        $asciiFile = str_replace('"','',file_get_contents($filename));
        $lines = explode("\n",$asciiFile);

        $result = array();
        $headerNames = array_util::Trim(str_getcsv($lines[0],$delim));

        // find lat and long column names and row indexs
        $lat_index = -1;
        $lon_index = -1;
        $fid_index = -1;
        for ($ns = 0; $ns < count($headerNames); $ns++) {
            $column_name = strtolower(trim($headerNames[$ns]));

            if (util::contains($column_name, 'lat' )      && $lat_index == -1) $lat_index = $ns;
            if (util::contains($column_name, 'latitude' ) && $lat_index == -1) $lat_index = $ns;
            if (util::contains($column_name, 'longitude') && $lon_index == -1) $lon_index = $ns;
            if (util::contains($column_name, 'long')      && $lon_index == -1) $lon_index = $ns;
            if (util::contains($column_name, 'lon' )      && $lon_index == -1) $lon_index = $ns;
            if (util::contains($column_name, 'lng' )      && $lon_index == -1) $lon_index = $ns;
            if (util::contains($column_name, 'fid' )      && $fid_index == -1) $fid_index = $ns;

        }

        for ($index = 1; $index < count($lines); $index++)
        {
            if (trim($lines[$index]) == "") continue;

            $cells = str_getcsv($lines[$index],$delim) ;

            if (count($cells) != count($headerNames))
            {
                echo "incorrect number of cells on line: $index - ignored  " . count($cells) ." != ". count($headerNames);
                continue;
            }

            $rowKey =
                sprintf("%03.".$decimal_place."f",$cells[$lat_index]).
                ascii_grid::$LatLonDelim.
                sprintf("%03.".$decimal_place."f",$cells[$lon_index]); // here we are creating the ROW_ID from the Lat and Long columns

            for ($c = 0; $c < count($cells); $c++) {
                if ($c == $fid_index || $c == $lat_index || $c == $lon_index) continue; // if the we have fiund the FID Lat Or long columns don't write them to the matrix

                $result[$rowKey][$headerNames[$c]] = trim($cells[$c]);
            }


        }

        return $result;


    }

    // create a matrix out of a CSV exported from ArcGIS
    public static function LoadLatLongCSV($filename,$delim = ",",$decimal_place = 5)
    {
        if (!file_exists($filename))
        {
            echo "### ERROR: matrix::LoadLatLongCSV File does not exist .. $filename\n";
            return NULL;
        }

        $asciiFile = str_replace('"','',file_get_contents($filename));
        $lines = explode("\n",$asciiFile);

        $result = array();
        $headerNames = array_util::Trim(str_getcsv($lines[0],$delim));

        // find lat and long column names and row indexs
        $lat_index = -1;
        $lon_index = -1;
        for ($ns = 0; $ns < count($headerNames); $ns++) {
            $column_name = strtolower(trim($headerNames[$ns]));

            if (util::contains($column_name, 'lat' )      && $lat_index == -1) $lat_index = $ns;
            if (util::contains($column_name, 'latitude' ) && $lat_index == -1) $lat_index = $ns;
            if (util::contains($column_name, 'longitude') && $lon_index == -1) $lon_index = $ns;
            if (util::contains($column_name, 'long')      && $lon_index == -1) $lon_index = $ns;
            if (util::contains($column_name, 'lon' )      && $lon_index == -1) $lon_index = $ns;
            if (util::contains($column_name, 'lng' )      && $lon_index == -1) $lon_index = $ns;

        }

        for ($index = 1; $index < count($lines); $index++)
        {
            if (trim($lines[$index]) == "") continue;

            $cells = str_getcsv($lines[$index],$delim);

            if (count($cells) != count($headerNames))
            {
                echo "incorrect number of cells on line: $index - ignored  " . count($cells) ." != ". count($headerNames);
                continue;
            }

            $rowKey =
                sprintf("%03.".$decimal_place."f",$cells[$lat_index]).
                ascii_grid::$LatLonDelim.
                sprintf("%03.".$decimal_place."f",$cells[$lon_index]); // here we are creating the ROW_ID from the Lat and Long columns

            for ($c = 0; $c < count($cells); $c++) {
                if ($c == $lat_index || $c == $lon_index) continue; // if the we have Lat Or long columns don't write them to the matrix

                $result[$rowKey][$headerNames[$c]] = trim($cells[$c]);
            }


        }

        return $result;


    }


    public static function fromString($str,$delim = ",",$rowID = "")
    {
        
        $lines = explode("\n",$str);

        $result = array();
        $headerNames = array_util::Trim(str_getcsv($lines[0],$delim,'"') );
        
        
        for ($index = 1; $index < count($lines); $index++)
        {

            if (trim($lines[$index]) == "") continue; //

            $cells = str_getcsv($lines[$index],$delim,'"');

            // echo " cell count = ".count($cells)."  header count = ".count($headerNames)."\n";
            
            if (count($cells) > count($headerNames))
            {
                echo "\nincorrect number of cells on line: $index - ignored  " . count($cells) ." > ". count($headerNames);
                continue;
            }

            $rowKey = $index;
            if ($rowID != "")
                $rowKey = $cells[array_search($rowID, $headerNames)];

            foreach ($cells as $key => $value)
            {
                // this lines stops the generation of a "ROW ID" column - if thyey have asked to convert the "row in the file into the row key"
                if ($rowID != "" && $headerNames[$key] == $rowID)  continue;

                $result[$rowKey][$headerNames[$key]] = trim($value);
            }

        }

        return $result;

    }


    // $rowID  = the name of the column to use as the ROWID
    // $findRowID = if set search file where  $rowID column value equals $findRowID
    public static function LoadRow($filename,$delim = ",",$rowID = "",$index = NULL, $findRowID = NULL)
    {

        if (!file_exists($filename))
        {
            echo "File does not exist: ".$filename."\n";
            return FALSE;
        }

        if ($index < 1)
        {
            echo "Row 0 of a file refers to the headers please select a different row\n";
            return FALSE;
        }


        // echo "Open $filename\n";

        $findRowID = trim($findRowID);

        $fh = fopen($filename,'r');
        if (!$fh)
        {
            echo "can't open $filename\n";
            return FALSE;
        }

        // get the first line - as the headings
        $first = fgets($fh);
        $first = trim(str_replace('"','',$first));
        $headerNames = str_getcsv($first, $delim);
        
        // print_r($headerNames);

        $lineCount = 1;
        if (!is_null($index))
        {

            // echo "Finding index: $index\n";

            // read lines and discard until you get to index
            for ($ra = 0; $ra < $index -1 ; $ra++) {
                fgets($fh); // loater remove assignemnt as this will speedu and it's not needed
                // echo "$lineCount.. ";
                $lineCount++;
            }
            //echo "\n";

            $line = fgets($fh);
            $line = str_replace('"','',$line);
            $cells = str_getcsv($line, $delim);

            // print_r($cells);

            if (count($cells) != count($headerNames))
            {
                debug::this("incorrect number of cells on line: $lineCount - ignored  " . count($cells) ." != ". count($headerNames));
                fclose($fh);
                return FALSE;
            }


            $row_key = $index;
            if ($rowID != "") $row_key = $cells[ util::findArrayKey($headerNames, $rowID)];

            foreach ($cells as $key => $value)
            {
                // this lines stops the generation of a "ROW ID" column - if thyey have asked to convert the "row in the file into the row key"
                if ($rowID != "" && $headerNames[$key] == $rowID)  continue;
                $result[$row_key][$headerNames[$key]] = $value;
            }

            fclose($fh);
            return $result;

        }
        else
        {
            // we are searching for a  $findRowID ==   $rowID

            if ($rowID == "")
            {
                echo "Searching by ROWID, rowID must define what column you are matching\n";
                fclose($fh);
                return FALSE;
            }

            if (is_null($findRowID))
            {
                echo "Searching by ROWID, findRowID must have a value\n";
                fclose($fh);
                return FALSE;
            }

            // echo "rowID = $rowID ... findRowID = $findRowID\n";

            $row_key_cell_index = util::findArrayKey($headerNames, $rowID);

            $lineCount = 0;
            while (!feof($fh))
            {
                $line = fgets($fh);
                $cells = str_getcsv($line,$delim) ;

                $lineCount++;

                if (count($cells) != count($headerNames))
                {
                    echo "incorrect number of cells on line: $lineCount - ignored  " . count($cells) ." != ". count($headerNames);
                    continue;
                }

                $row_key = $cells[ $row_key_cell_index];

                // echo "$lineCount... $row_key\n";

                if ($row_key == $findRowID)
                {
                    foreach ($cells as $key => $value)
                    {
                        // this lines stops the generation of a "ROW ID" column - if thyey have asked to convert the "row in the file into the row key"
                        if ($rowID != "" && $headerNames[$key] == $rowID)  continue;

                        $result[$row_key][$headerNames[$key]] = $value;
                    }

                    fclose($fh);
                    return $result; // return as quick as possible
                }

            }

        }

    }



    /*
     * @method saveMatrix
     * @param $src
     * @param $filename
     * @param $delim = "
     * @param $id_column_name - if null don't save the row_id as the first column
     * @param $selected_columns
     * @return mixed
    */
    public static function Save($src,$filename,$delim = ",",$id_column_name = "ROW",$selected_columns = null,$write_header = true, $append = false)
    {

        if (is_null($src) || !is_array($src))
        {
            echo "### ERROR Matrix::Save - src passed to Save matrix is NOT a matrix\n";
            return NULL;
        }


        $first = util::first_element($src);
        if (!is_array($first))
        {
            echo "Is not matrix saving as matrix format\n"; // try to save it like it s a keyed aray and makeit look like a matrix.
            file::Array2File($src, $filename, "$id_column_name,VALUE\n", '');

            if (file_exists($filename))
                return $src;
            else
                return NULL;

        }

        $ucn = util::uniqueColumnNames($src);

        if (is_null($src) || !is_array($src))
        {
            echo "### ERROR Matrix::Save - Could not get a list of Unique Column Names\n";
            return NULL;
        }

        // supports appending to a file
        $handle = ($append)? fopen($filename, "a",FILE_APPEND) : fopen($filename, "w");

        if ($handle == false)
        {
            echo "##ERROR: Could not write to $filename\n";
            return null;
        }

        // usually if we are appending we may want to not add another header row.
        if ($write_header)
        {
            $rowToWrite = array();
            if (!is_null($id_column_name))  $rowToWrite[] = util::CleanStr($id_column_name, $delim);

            if (is_null($selected_columns))
                foreach ($ucn as $ColumnName) $rowToWrite[] = util::CleanStr($ColumnName, $delim);
            else
                foreach ($selected_columns as $ColumnName) $rowToWrite[] = util::CleanStr($ColumnName, $delim);

            fwrite($handle,join("$delim",$rowToWrite)."\n");

        }


        foreach ($src as $rowID => $row)
        {
            // for each row go thru Unique column names and if we have it add it if we don't have add empty cell
            $rowToWrite = array();
            if (!is_null($id_column_name))
                $rowToWrite[] = util::CleanStr($rowID, $delim);

            if (is_null($selected_columns))
                foreach ($ucn as $ColumnName) $rowToWrite[] = util::CleanStr($row[$ColumnName], $delim);
            else
                foreach ($selected_columns as $ColumnName)
                    $rowToWrite[] = util::CleanStr($row[$ColumnName], $delim);

            fwrite($handle,join("$delim",$rowToWrite)."\n");
        }

        fclose($handle);

        if (file_exists($filename)) return $filename;
        
        return NULL; // if it did not save
    }

    public static function SaveWithColumnSort($src,$filename,$delim = ",",$id_column_name = "ROW")
    {
        if (is_null($src) || !is_array($src))
        {
            echo "### ERROR Matrix::Save - src passed to Save matrix is NOT a matrix\n";
            return NULL;
        }

        $first = util::first_element($src);
        if (!is_array($first))
        {
            echo "Is not matrix saving as matrix format\n";
            // try to save it like it s a keyed aray and makeit look like a matrix.

            file::Array2File($src, $filename, "ROW,VALUE\n", '');

            if (file_exists($filename))
                return $src;
            else
                return NULL;

        }

        $ucn = util::uniqueColumnNames($src);


        if (is_null($src) || !is_array($src))
        {
            echo "### ERROR Matrix::Save - Could not get a list of Unique Column Names\n";
            return NULL;
        }

        sort($ucn);

        $handle = fopen($filename, "w");

        // write header in different way - ie need to skip id column
        $rowToWrite = array();
        $rowToWrite[] = util::CleanStr($id_column_name, $delim);
        foreach ($ucn as $ColumnName) $rowToWrite[] = util::CleanStr($ColumnName, $delim);
        fwrite($handle,join("$delim",$rowToWrite));

        foreach ($src as $rowID => $row)
        {
            // for each row go thru Unique column names and if we have it add it if we don't have add empty cell
            // build row into array to be written
            $rowToWrite = array();
            $rowToWrite[] = util::CleanStr($rowID, $delim);
            foreach ($ucn as $ColumnName) $rowToWrite[] = util::CleanStr($row[$ColumnName], $delim);
            fwrite($handle,"\n".join("$delim",$rowToWrite));
        }

        fclose($handle);

        if (file_exists($filename)) return $src;

        return NULL; // if it did not save


    }

    /*
    * @method orderMatrixByRow
    * @param $src
    * @return mixed
    */
    public static function sortRows($src)
    {
        $result = array();

        foreach ($src as $rowID => $row)
        {
            sort($row);
            foreach ($row as $value) $result[$rowID][] = $value;
        }

        return $result;
    }


    /*
     * @method ros Statistics
     * @param $src - matrix
     * @return array of arrays
     * [count] = num values used in row (if $nullValue != null)
     * [sum]   = sum of all values (excluding nulls)
    */
    public static function RowStatistics($src,$nullValue = null)
    {
        $result = array();

        foreach ($src as $rowID => $row)
        {
            // echo "Row stats $rowID\n";

            $result[$rowID]['count']   = array_util::Count($row, null, $nullValue);
            $result[$rowID]['sum']     = array_util::Sum($row, null, $nullValue);
            $result[$rowID]['average'] = $nullValue;
            $result[$rowID]['stddev'] = 0;

            if ($result[$rowID]['count'] != 0)
            {
                $result[$rowID]['average'] = $result[$rowID]['sum'] / $result[$rowID]['count'];

                if ($result[$rowID]['count'] > 1)
                    $result[$rowID]['stddev'] = stats::StandardDeviation($row, null, $nullValue);
            }


            $result[$rowID]['value_count'] = array_util::CountNotValue($row, $nullValue); // count NOT nulls
            $result[$rowID]['null_count'] = array_util::CountValue($row, $nullValue);     // count nulls

            $result[$rowID]['value_count_percent'] = $result[$rowID]['value_count'] / ($result[$rowID]['value_count'] + $result[$rowID]['null_count']);   // as this approaches 1 we have more data

            $result[$rowID]['value_nullvalue_ratio'] =  0;
            if ($result[$rowID]['null_count'] != 0)
                $result[$rowID]['value_nullvalue_ratio'] =  $result[$rowID]['value_count'] / $result[$rowID]['null_count'];

            $result[$rowID]['min'] = array_util::Minimum($row, null, $nullValue); // count NOT nulls
            $result[$rowID]['max'] = array_util::Maximum($row, null, $nullValue); // count NOT nulls

            $result[$rowID]['range'] = $result[$rowID]['max'] - $result[$rowID]['min'];
            $result[$rowID]['median'] = stats::Median($row, $nullValue);

            // first and last non_null value
            $result[$rowID]['data_start'] = array_util::FirstValueKey($row, null, $nullValue);
            $result[$rowID]['data_end']   = array_util::LastValueKey($row, null, $nullValue);


            $lr = stats::linear_regression_with_keys($row,$nullValue);
            $result[$rowID]['regression_slope']     = $lr['slope'];
            $result[$rowID]['regression_intercept'] = $lr['intercept'];

        }
        return $result;
    }

    // All column names of matrix
    public static function ColumnNames($src,$use_first_row = true)
    {

        if (count($src) == 0)
        {
            echo "ERROR: matrix::ColumnNames src matrix has no rows\n";
            return null;
        }

        // very quick - but if matrix is badly formed then this will miss column names
        if ($use_first_row)
            return array_keys(util::first_element($src));


        // scan entire Matrix for column names
        $columns = array();
        foreach ($src as $rowID => $row)
        {
            foreach ($row as $columnName => $cellValue)
                $columns[$columnName] = "";
        }

        return array_keys($columns);

    }


    /*
    * @method arrayAverage
    * @param $src
    * @param $columnName
    * @return mixed
    */
    public static function ColumnAverage($src, $nullValue = null)
    {
        $result = array();

        foreach (self::ColumnNames($src) as  $columnName)
        {
            $result[$columnName] = array_util::Average($src, $columnName,$nullValue);
            // echo "Column acverage $columnName ".$result[$columnName]."\n";
        }


        return $result;
    }

    // sum one of the  "columns" - extepcts $array to be an array of arrays
    public static function ColumnSum($src,$nullValue = null)
    {
        $result = array();
        foreach (self::ColumnNames($src) as $columnName)
            $result[$columnName] = array_util::Sum($src, $columnName,$nullValue);

        return $result;
    }

    // Count of columns
    public static function ColumnCount($src,$nullValue = null)
    {
        // assume ffirst row is actually same size as rest
        $first = util::first_element($src);
        return count($first);
    }


    // Maximum valuie of array or column from matrix
    public static function ColumnMaximum($src, $nullValue = null)
    {
        $result = array();
        foreach (self::ColumnNames($src) as $columnName)
            $result[$columnName] = array_util::Maximum($src, $columnName,$nullValue);

        return $result;

    }


    // sum one of the  "columns" - extepcts $array to be an array of arrays
    public static function ColumnMinimum($src, $nullValue = null)
    {
        $result = array();
        foreach (self::ColumnNames($src) as $columnName)
            $result[$columnName] = array_util::Minimum($src, $columnName,$nullValue);

        return $result;

    }

    // sum one of the  "columns" - extepcts $array to be an array of arrays
    public static function ColumnMedian($src, $nullValue = null)
    {
        $result = array();
        foreach (self::ColumnNames($src) as $columnName)
            $result[$columnName] = stats::median(array_util::Column($src, $columnName),$nullValue);

        return $result;
    }


    // Unique Values from array
    public static function ColumnUnique($src,$column_name = null)
    {
        $result = array();

        if (is_null($column_name))
            foreach (self::ColumnNames($src) as $columnName)
                $result[$columnName] = array_util::UniqueValues($src, $columnName);
        else
            $result = array_util::UniqueValues($src, $column_name);

        return $result;


    }

    // Unique Values from array
    public static function ColumnHistogram($src,$column_name = null)
    {
        return self::ColumnUnique($src, $column_name);
    }

    // column names of matrix
    // for the monent assume for row has all column names
    public static function RowNames($src)
    {
        return array_unique(array_keys($src));
    }

    // work on each row of matrix - se are not loadin the whole lot into memeory
    public static function RowAverageLargeMatrix($filename, $nullValue = null ,$delim = ",",$rowID = "",$debug = FALSE)
    {
        if (!file_exists($filename))
        {
            echo "##ERROR:: $filename does not exist!\n";
            return NULL;
        }

        $line_count = file::lineCount($filename);

        if ($line_count <= 1)
        {
            echo "##ERROR:: $filename does not have engouh lines to be processed!\n";
            return NULL;
        }

        $f = fopen($filename,'rb');
        $result = array();

        $count = 0;
        fgets($f); // read first line - skip first line
        while (!feof($f))
        {
            
            $cells = fgetcsv($f, 10000, $delim);
            if (count($cells) <= 1) continue;

            $rowID = trim($cells[0]);
            unset($cells[0]);

            $result[$rowID] = array_util::Average($cells);

            if ($debug && $count % 1000 == 0)
                echo ($count + 1)."/".$line_count." $rowID === {$result[$rowID]}\n";

            $count++;
        }

        fclose($f);

        return $result;
    }



    /*
    * @method arrayAverage
    * @param $src
    * @param $columnName
    * @param $replaceNullWithNullValue - if average returns NULL then replace with $nullValue
    * @return mixed
    */
    public static function RowAverage($src, $nullValue = null, $replaceNullWithNullValue = TRUE)
    {
        $result = array();
        foreach ($src as $rowID => $row)
        {
            $avg = array_util::Average($row, null,$nullValue);

            if (is_null($avg) && $replaceNullWithNullValue)
                $avg = $nullValue;

            $result[$rowID] = $avg;
        }

        return $result;
    }

    public static function RowAverageColumnSubset($src, $column_names = null, $nullValue = null, $replaceNullWithNullValue = TRUE)
    {
        if (is_null($column_names)) return NULL;

        $result = array();
        foreach ($src as $rowID => $row)
        {
            // build a subset of val;ues from this row that match $column_names;
            $subset_row = array();
            foreach ($column_names as $subset_column_name) {
                $subset_row[$subset_column_name] = $row[$subset_column_name];
            }

            $avg = array_util::Average($subset_row, null,$nullValue);

            if (is_null($avg) && $replaceNullWithNullValue)
                $avg = $nullValue;

            $result[$rowID] = $avg;
        }

        return $result;
    }


    public static function RowMovingAverage($src, $window,$nullValue = null)
    {
        $result = array();
        foreach ($src as $rowID => $row)
            $result[$rowID] = stats::MovingAverage($row, $window,$nullValue);

        return $result;
    }





    // sum one of the  "columns" - extepcts $array to be an array of arrays
    public static function RowSum($src,$nullValue = null)
    {
        $result = array();
        foreach ($src as $rowID => $row)
            $result[$rowID] = array_util::Sum($row, null,$nullValue);

        return $result;
    }

    // sum one of the  "columns" - extepcts $array to be an array of arrays
    public static function RowCount($src,$nullValue = null)
    {
        return count($src);
    }


    // Maximum valuie of array or column from matrix
    public static function RowMaximum($src, $nullValue = null)
    {
        $result = array();
        foreach ($src as $rowID => $row)
            $result[$rowID] = array_util::Maximum($row, null,$nullValue);

        return $result;
    }

    // sum one of the  "columns" - extepcts $array to be an array of arrays
    public static function RowMinimum($src, $nullValue = null)
    {
        $result = array();
        foreach ($src as $rowID => $row)
            $result[$rowID] = array_util::Minimum($row, null,$nullValue);

        return $result;

    }

    // sum one of the  "columns" - extepcts $array to be an array of arrays
    public static function RowMedian($src, $nullValue = null)
    {
        $result = array();
        foreach ($src as $rowID => $row)
            $result[$rowID] = stats::median($row, $nullValue);

        return $result;

    }


    // Unique Values from array
    public static function RowUnique($src)
    {
        $result = array();
        foreach ($src as $rowID => $row)
            $result[$rowID] = array_util::Unique($row, null,$nullValue);

        return $result;

    }

    // Unique Values from array
    public static function RowHistogram($src)
    {
        return self::RowUnique($src);

    }


    // use these for very big matrix that wont fit two in memory
    public static function SumWholeMatrix($m, $outputFilename,$null_value)
    {
        $row_label_src = file($m[0]);

        $start = 1;
        $end   = count($row_label_src);

        for ($ri = $start; $ri <= $end; $ri++)
        {
            if ($ri % 1000 == 0) echo " $ri/$end ";
            $row_label = util::leftStr($row_label_src[$ri], ',',FALSE);
            $result[$row_label] = self::MultiMatrixRowSum($m, $ri, NULL);
        }

        echo "\n Saving $outputFilename\n";
        matrix::Save($result, $outputFilename);
    }

    public static function StandardDeviationWholeMatrix($filenames,$null_value = NULL)
    {

        $mats = matrix::LoadArray($filenames,",","ROW");

        // echo "Counts = ".count($mats)."\n";

        // what we need to make sure we catch all rowids and column ids for all matrix
        // we need a unique list of column names and row id's

        $allColumnNames = array();
        foreach ($mats as $filename => $matrix) {
            $current_names = matrix::ColumnNames($matrix);
            $allColumnNames = array_merge($allColumnNames, $current_names);
        }
        $allColumnNames = array_unique($allColumnNames);


        $allRowNames = array();
        foreach ($mats as $filename => $matrix) {
            $current_names = matrix::RowNames($matrix);
            $allRowNames = array_merge($allRowNames, $current_names);
        }
        $allRowNames = array_unique($allRowNames);


        // process matching rowID and column id and get an array thru the Cube

        $result_sd = array();
        $result_count = array();
        foreach ($allRowNames as $rowName) {
            // echo "$rowName ";
            foreach ($allColumnNames as $columnNameName) {

                $single_cell_array_for_sd = array();

                // collect the values of each matrix
                foreach ($mats as $filename => $single_matrix)
                    if (array_key_exists($rowName, $single_matrix))  // does this row exists in each matrix
                        if (array_key_exists($columnNameName, $single_matrix[$rowName]))
                                if ($single_matrix[$rowName][$columnNameName] != $null_value)
                                    $single_cell_array_for_sd[] = $single_matrix[$rowName][$columnNameName];

                   $result['sd'][$rowName][$columnNameName] = stats::StandardDeviation($single_cell_array_for_sd,NULL, $null_value);
                $result['count'][$rowName][$columnNameName] = count($single_cell_array_for_sd);

            }

        }

        return $result;

    }



    public static function MultiMatrixRowSum($filenames, $row_index = NULL, $row_key = NULL)
    {
        if (is_null($row_index) && is_null($row_key))
        {
            echo "You must define a RowIndex or RowKey\n";
            return FALSE;
        }

        foreach ($filenames as $filename)
        {
            $m[$filename] = matrix::LoadRow($filename, ',', 'ROW',$row_index , $row_key);
            if (!is_array($m[$filename]))
            {
                echo "Could not load $filename\n";
                return FALSE;
            }
        }

        $sums = array();
        foreach ($m as $filename => $mini_matrix)
            foreach (util::first_element($mini_matrix) as $column_name => $cell_value)
                $sums[$column_name] += $cell_value;

        return $sums;

    }


    public static function SaveRowAverage($folder,$input,$null_value,$key_column,$output,$file_header = "",$file_footer = "")
    {
        $src = matrix::Load($folder.'/'.$input, ',', $key_column);
        $row_average = matrix::RowAverage($src,$null_value);

        echo "Row Average: $input\n";
        file::Array2File($row_average, $folder.'/'.$output,$file_header,$file_footer);

        return file_exists($folder.'/'.$output);

    }


    function SubtractUsingKeyedArray($matrix_filename,$values_filename, $null_value = NULL,$row_id = "ROW",$delim = ",")
    {

        $matrix = matrix::Load($matrix_filename,$delim,$row_id);
        $values = file::File2KeyedArray($values_filename,$delim);

        return self::SubtractUsingArray($matrix,$values, $key_column,$null_value);
    }

    // Subtract a value from each element across a row
    // use the row_id of the matrix to find the appropriate value in the values to subtract
    function SubtractUsingArray($matrix,$values, $null_value = NULL)
    {

        $result = array();
        foreach ($matrix as $row_id => $row)
        {
            foreach ($row as $column_id => $cell)
            {
                if (is_null($null_value))
                {
                    $result[$row_id][$column_id] = $cell - $values[$row_id];
                    continue;
                }

                // we have a null-va;ue so we must honour it
                // if the cell value is equal to the null_value then just return null_value
                // otherwise return subtraction
                if ($values[$row_id] == $null_value || $cell == $null_value)
                    $result[$row_id][$column_id] = $null_value;
                 else
                    $result[$row_id][$column_id] = $cell - $values[$row_id];

            }

        }

        return $result;

    }


    //
    public static function ReplaceValue($src,$from,$to)
    {
        $result = array();
        foreach ($src as $rowID => $row)
        {
            foreach ($row as $columnid => $cell)
            {
                $result[$rowID][$columnid] = $cell;
                if ($cell == $from) $result[$rowID][$columnid] = $to;
            }

        }

        return $result;
    }

    public static function ReplaceStringValue($src,$from,$to)
    {
        $result = array();
        foreach ($src as $rowID => $row)
        {
            foreach ($row as $columnid => $cell)
            {
                $result[$rowID][$columnid] = str_replace($from,$to,$cell);
            }

        }

        return $result;
    }


    public static function ScalarDivide($src,$scalar,$null_value = NULL)
    {
        $result = array();
        foreach ($src as $rowID => $row)
        {
            foreach ($row as $columnid => $cell)
            {
                if (is_null($null_value))
                {
                    $result[$rowID][$columnid] = $cell / $scalar;
                    continue;
                }

                if ($cell == $null_value)
                    $result[$rowID][$columnid] = $null_value;
                else
                    $result[$rowID][$columnid] = $cell / $scalar;
            }

        }

        return $result;
    }

    public static function ScalarProduct($src,$scalar,$null_value)
    {
        $result = array();
        foreach ($src as $rowID => $row)
        {
            foreach ($row as $columnid => $cell)
            {
                if ($cell == $null_value)
                    $result[$rowID][$columnid] = $null_value;
                else
                    $result[$rowID][$columnid] = $cell * $scalar;
            }

        }

        return $result;
    }

    public static function ScalarPlus($src,$scalar,$null_value)
    {
        $result = array();
        foreach ($src as $rowID => $row)
        {
            foreach ($row as $columnid => $cell)
            {
                if ($cell == $null_value)
                    $result[$rowID][$columnid] = $null_value;
                else
                    $result[$rowID][$columnid] = $cell + $scalar;
            }

        }

        return $result;
    }

    public static function ScalarSubtract($src,$scalar,$null_value)
    {
        $result = array();
        foreach ($src as $rowID => $row)
        {
            foreach ($row as $columnid => $cell)
            {
                if ($cell == $null_value)
                    $result[$rowID][$columnid] = $null_value;
                else
                    $result[$rowID][$columnid] = $cell - $scalar;
           }

        }

        return $result;
    }

    public static function ScalarPower($src,$scalar,$null_value)
    {
        $result = array();
        foreach ($src as $rowID => $row)
        {
            foreach ($row as $columnid => $cell)
            {
                if ($cell == $null_value)
                    $result[$rowID][$columnid] = $null_value;
                else
                    $result[$rowID][$columnid] = pow($cell, $scalar);
           }

        }

        return $result;
    }


    public static function SubtractByKeyedArray($matrix,$array,$null_value = NULL,$debug = FALSE)
    {
        if (is_null($matrix))
        {
            echo "##Error matrix::SubtractByKeyedArray matrix passed as null \n";
            return NULL;
        }

        if (is_null($array))
        {
            echo "##Error matrix::SubtractByKeyedArray ARRAY passed as null \n";
            return NULL;
        }

        if ($debug) echo "\n";

        $result = array();
        foreach ($matrix as $rowID => $row)
        {
            if ($debug) echo $rowID." ";

            foreach ($row as $columnID => $value)
            {

                // simple don't need to check
                if (is_null($null_value))
                {
                    $result[$rowID][$columnID] = $value - $array[$rowID];
                    continue;
                }

                // at this point we have a null_value - so we need to check it.
                if ($value == $null_value || $array[$rowID] == $null_value)
                {
                    $result[$rowID][$columnID] = $null_value;  // value is null_value - so result is null_value
                    continue;                                  // or the subtract value is null_value so result is null_value
                }

                // we have two actual values to subtract
                $result[$rowID][$columnID] = $value - $array[$rowID]; // simple don't need to check

            }

        }

        if ($debug) echo "\n";

        return $result;

    }


    public static function Subtract($lhs,$rhs,$result_null_value, $lhs_null_value = NULL,$rhs_null = NULL,$check_histogram = TRUE)
    {

        if (is_null($lhs))
        {
            echo "##Error matrix::Subtract LHS passed as null \n";
            return NULL;
        }

        if (is_null($rhs))
        {
            echo "##Error matrix::Subtract RHS passed as null \n";
            return NULL;
        }

        // count does not matter as we doing matching keys
//        $lhs_count = self::CellCount($lhs);
//        $rhs_count = self::CellCount($rhs);
//
//        if ($lhs_count != $rhs_count)
//        {
//            echo "##Error matrix::Subtract cell count of LHS($lhs_count)  not equal to RHS($rhs_count)\n";
//            return NULL;
//        }


        if ($check_histogram)
        {
            // need to check that lhs and rhs don't contain result_null
            echo "Histogram LHS\n";
            $lhs_hist = self::Histogram($lhs);
            if (array_key_exists($result_null_value, $lhs_hist))
            {
                echo "##Error matrix::Subtract LHS contains null_value..$result_null_value \n";
                return NULL;
            }

            echo "Histogram RHS\n";
            $rhs_hist = self::Histogram($rhs);
            if (array_key_exists($result_null_value, $rhs_hist))
            {
                echo "##Error matrix::Subtract RHS contains null_value..$result_null_value \n";
                return NULL;
            }

        }



        // if they pass just single null value then set the rhs_null to same
        if (!is_null($lhs_null_value) && is_null($rhs_null_value))
            $rhs_null_value = $lhs_null_value;


        echo "Subtract\n";
        $result = array();
        foreach ($lhs as $lhs_rowID => $lhs_row)
        {
            // echo "$lhs_rowID ";

            // does this row exists in RHS
            if (!array_key_exists($lhs_rowID, $rhs)) continue;

            foreach ($lhs_row as $lhs_columnid => $lhs_cell)
            {

                if (!array_key_exists($lhs_columnid, $rhs[$lhs_rowID])) continue;

                if ($lhs_cell == $lhs_null_value) continue;
                if ($rhs[$lhs_rowID][$lhs_columnid] == $rhs_null_value) continue;

                // echo $lhs_cell." - ".$rhs[$lhs_rowID][$lhs_columnid]." ";

                //both LHS & RHS are not NULL
                $result[$lhs_rowID][$lhs_columnid] = $lhs_cell - $rhs[$lhs_rowID][$lhs_columnid];

           }

        }

        return $result;
    }

    // sum matrix as long as two whole matrix can fit into memeory + anothet whole result
    public static function SumArray($filenames,$null_value,$delim = ',', $row_id = 'ROW')
    {

        $current = matrix::Load($filenames[0], $delim, $row_id);
        echo str_repeat('.', count($filenames)).chr(13);
        for ($index = 1; $index < count($filenames); $index++)
        {
            // echo "#";
             echo $filenames[$index]."\n";
            $next = matrix::Load($filenames[$index], $delim, $row_id);
            $current = matrix::SumTwo($current, $next, $null_value);
        }
        echo "\n";
        return $current;

    }


    public static function SumTwo($lhs,$rhs,$null_value)
    {
        $result = array();
        foreach ($lhs as $lhs_rowID => $lhs_row)
        {
            foreach ($lhs_row as $lhs_columnid => $lhs_cell)
            {
                $result[$lhs_rowID][$lhs_columnid] = $null_value;

                // does this row exists in RHS
                if (array_key_exists($lhs_rowID, $rhs))
                {
                    // the column exists in the RHS row
                    if (array_key_exists($lhs_columnid, $rhs[$lhs_rowID]))
                    {
                        // make sure that both LHS & RHS are not NULL
                        if ( !($lhs_cell == $null_value || $rhs[$lhs_rowID][$lhs_columnid] == $null_value) )
                            $result[$lhs_rowID][$lhs_columnid] = $lhs_cell + $rhs[$lhs_rowID][$lhs_columnid];

                    }

                }


           }

        }

        return $result;
    }


    // remove rows that average to $target_row_average
    public static function LimitRowsBasedOnRowAverage($src,$target_row_average)
    {
        $avgs = self::RowAverage($src);

        $result = array();
        foreach ($avgs as $rowID => $avg)
        {
            if (trim($avg) == trim($target_row_average)) continue;
            $result[$rowID] = $src[$rowID];

        }
        return $result;

    }

    // remove rows that average to $target_row_average
    public static function RemoveRowsBasedColumnValue($src,$column_name,$value)
    {
        $result = array();
        foreach ($src as $rowID => $row)
        {
            if ($row[$column_name] == $value) continue;
            $result[$rowID] = $src[$rowID];

        }
        return $result;

    }


    //
    public static function LimitRowsBasedOnRowSum($src,$target_row_sum)
    {
        $sums = self::RowSum($src);

        $result = array();
        foreach ($sums as $rowID => $sum)
        {
            if ($sum <> $target_row_sum)
               $result[$rowID] = $src[$rowID];

        }
        return $result;

    }

    public static function LimitRowsBasedOnArrayKey($src,$limit)
    {
        if (is_null($src))
        {
            echo "##ERROR matrix::LimitRowsBasedOnArrayKey  src passed as NULL\n";
            return NULL;
        }

        if (is_null($limit))
        {
            echo "##ERROR matrix::LimitRowsBasedOnArrayKey  limit passed as NULL\n";
            return NULL;
        }

        $result = array();
        foreach ($limit as $rowID => $discard)
            if (array_key_exists($rowID, $src))
                $result[$rowID] = $src[$rowID];

        return $result;

    }

    public static function LimitRowsBasedOnArrayValue($src,$limit)
    {

        $result = array();
        foreach ($limit as  $rowID)
        {
            $result[$rowID] = $src[$rowID];
        }
        return $result;

    }


    public static function MultiValueCountByRow($filenames,$value_to_count,$row_id = 'ROW',$delim = ',')
    {

        $result = array();
        foreach ($filenames as $filename)
        {
            $r = self::ValueCountByRow(matrix::Load($filename,$delim,$row_id),$value_to_count);

            foreach ($r as $row_id => $value_count)
            {
                $result[$filename][$row_id] = $value_count;
            }

        }

        return self::Transpose($result);
    }

    public static function ValueCountByRow($matrix,$value_to_count)
    {

        $result = array();
        foreach ($matrix as $matrix_rowID => $matrix_row)
        {
            $result[$matrix_rowID] = 0;
            foreach ($matrix_row as $matrix_columnid => $matrix_cell)
            {
                if ($matrix_cell != $value_to_count) continue;
                $result[$matrix_rowID]++;
           }
        }

        return $result;
    }


    public static function Transpose($matrix)
    {
        $result = array();
        foreach ($matrix as $matrix_rowID => $matrix_row)
            foreach ($matrix_row as $matrix_columnid => $matrix_cell)
                $result[$matrix_columnid][$matrix_rowID] = $matrix_cell;

        return $result;
    }


    /*
     * take a single column from an array of arrays
     */
    public static function Column($src, $column_name)
    {
        
        if (!is_array($src)) return null;

        $first = util::first_element($src);

        if (is_array($first))
            if (!array_key_exists($column_name,array_flip(array_keys($first))))
            {
                //echo "##Error:: matrix::Column the column named [$column_name] does not exists in matrix";
                return NULL;
            }

        $result = array();
        foreach ($src as $row_id => $row)
            $result[$row_id] = $row[$column_name];

        return $result;

    }


    public static function Histogram($matrix, $column_name = NULL)
    {
        if (is_null($matrix))
        {
            echo "##ERROR: matrix::Histogram matrix passed is NULL\n";
            return NULL;
        }

        $result = array();

        foreach ($matrix as $matrix_rowID => $matrix_row)
            if (is_null($column_name)) // no column selected - get all values
            {
                foreach ($matrix_row as $matrix_columnid => $matrix_cell)
                    $result[$matrix_cell]++;
            }
            else
            {
                $result[$matrix_row[$column_name]]++; // only process a single column
            }


        ksort($result);
        return $result;
    }

    public static function CellCount($matrix)
    {
        $cell_count = 0;
        foreach ($matrix as $matrix_rowID => $matrix_row)
            foreach ($matrix_row as $matrix_columnid => $matrix_cell)
                $cell_count++;

        return $cell_count;
    }

    // se;ect subsets of rows based on the $grouping_column
    // and rows that have the same value in the $grouping_column are considered to be in the same group
    // use the value column in the averaging
    public static function GroupedMean($matrix,$grouping_column, $value_column,$null_value = '')
    {
        // get unique values of Grouping Column
        // $unique_values = matrix::ColumnUnique($src,$grouping_column);

        $group_sum = array();
        $group_count = array();
        foreach ($matrix as $matrix_rowID => $matrix_row)
        {
            if ( $matrix_row[$value_column] == $null_value) continue;

            $col_name = $matrix_row[$grouping_column];

              $group_sum[$col_name] = $matrix_row[$value_column];
            $group_count[$col_name]++;

        }

        $result = array();

        foreach ($group_sum as $group_name => $value)
            $result[$group_name] = $value / $group_count[$group_name];


        return $result;
    }


    // leave out $column_name  and this will take the rowIDs
    public static function ColumnTypeForDB($src,$column_name = NULL)
    {

        if (!is_null($column_name))
            $column_values = matrix::Column($src, $column_name);
        else
            $column_values = matrix::RowNames($src);


        $valid_rowCount = 0;
        $maxStringLength = 0;
        $numberCount = 0;
        foreach ($column_values as $value)
        {
            if (is_array($value))
                $value = print_r($value,true);

            if (trim($value) == "") continue; // skip spaces

                                        /// strlen($value)
            $maxStringLength = max( strlen($value),$maxStringLength);
            if (is_numeric($value)) $numberCount++;

            $valid_rowCount++; // count rows that actually have a value
        }

        if ( $numberCount > ($valid_rowCount * 0.8) ) return "DOUBLE"; // if 80% of values are numbers its a number column

        $maxStringLength = round($maxStringLength * 3,0);

        return "varchar($maxStringLength)";

    }


    public static function ColumnTypeForDB_ALL($src)
    {
        $result = array();
        foreach (self::ColumnNames($src) as $column_name)
        {
            $result[$column_name] = self::ColumnTypeForDB($src, $column_name);
        }
        return $result;

    }


    // count
    public static function RowNullCount($matrix,$null_value)
    {
        $result = array();

        foreach ($matrix as $rowID => $row)
        {
            $result[$rowID]['null_count'] = array_util::CountValue($row, $null_value);
            $result[$rowID]['not_null_count'] = array_util::CountNotValue($row, $null_value);
        }
        return $result;
    }

    // extract two columns make $key_column the key and vaule_column the value
    // - a check would be that the count of the sult will be the same caount as the matrix.
    // - if the counts don't matrch the value will  be the last value for that key
    public static function toKeyValue($matrix,$key_column,$value_column,$counts_must_match = true)
    {
        $result = array();

        foreach ($matrix as $rowID => $row)
            $result[$row[$key_column]] = $row[$value_column];

        if ($counts_must_match)
            if (count($matrix) != count($result)) $result = null;


        return $result;
    }




    // grpup data from $value_column based on the value in $key_column
    // returns: Jagged keyed array
    public static function ColumnGroupBy($matrix,$grouping_column,$key_column,$value_column)
    {
        $result = array();

        // get unique list of values from key_column
        foreach ($matrix as $rowID => $row)
        {
            $result[$row[$grouping_column]][$row[$key_column]] = $row[$value_column];
        }
        return $result;
    }

    // group by a unique value and then gice me the first rows value
    public static function ColumnGroupBy_First($matrix,$grouping_column,$key_column,$value_column)
    {
        $sub_result = self::ColumnGroupBy($matrix,$grouping_column,$key_column,$value_column);

        foreach ($sub_result as $row_id => $row)
        {
            $result[$row_id] = util::first_element($row);
        }

        return $result;

    }

    // $columns = comma delimited string of column namesstring
    //   or
    //$columns = array of column names
    public static function SelectedColumns($matrix,$columns,$delim = ',')
    {

        if (is_string($columns))
            $columns = str_getcsv($columns,$delim);

        $result = array();
        foreach ($matrix as $row_id => $row)
            foreach ($columns as $column)
                $result[$row_id][$column] = $row[$column];

        return $result ;
    }

    // $columns = comma delimited string of column namesstring
    //   or
    //$columns = array of column names
    public static function ColumnAdd($matrix,$column_name,$default_value = null,$delim = ',')
    {
        if (!is_array($column_name))
            $column_names = str_getcsv( $column_name,$delim);
        else
            $column_names = $column_name;


        $result = array();
        foreach ($matrix as $row_id => $row)
        {
            foreach ($row as $column_id => $cell)
                $result[$row_id][$column_id] = $cell; // copy row

            foreach ($column_names as $column)
                $result[$row_id][$column] = $default_value; // copy addon
        }

        return $result;
    }

    /*
     * Merger matrix where the row_ids are the same
     */
    public static function ColumnAppend($lhs,$rhs,$AppendPrefix = "")
    {

        $result = array();
        foreach ($lhs as $lhs_row_id => $lhs_row)
        {
            foreach ($lhs_row as $lhs_column_id => $lhs_cell)
                $result[$lhs_row_id][$lhs_column_id] = $lhs_cell; // copy row

            if (is_array($rhs[$lhs_row_id]))
            {
                // rhs is a matrix
                foreach ($rhs[$lhs_row_id] as $rhs_column_id => $rhs_cell)
                    $result[$lhs_row_id][$AppendPrefix.$rhs_column_id] = $rhs_cell; // copy row
            }
            else
            {
                // rhs is a keyed array;
                $result[$lhs_row_id][$AppendPrefix] = $rhs[$lhs_row_id]; // copy row
            }


        }

        return $result;
    }



    // return array of matrix for this filename each matrix is a sheet
    public static function FromXLS($filename)
    {
        if (!file_exists($filename)) return null;

        echo "Import Excel CSheets to Arry of Matrix {$filename}\n";

        $sheets = self::XLS_SheetNames($filename);

        foreach ($sheets as $index => $sheet_info)
        {
            $matrix_name = util::leftStr(util::fromLastSlash($filename), ".")."_".util::CleanStr($sheet_info['Name'],NULL,"[]{}_- !@#$%^&*(),~'\"\\");
            $result[$matrix_name] = self::XLS_ExtractSheet($filename,$sheet_info['Page']);
        }

        return $result;

    }

    public static function XLS_ExtractSheet($filename,$sheet_number = 0)
    {

        $lines = array();
        exec("xlhtml -asc -xp:{$sheet_number} '{$filename}'", $lines);

        $result = matrix::fromString(join("\n",$lines), "\t");

        return $result;

    }

    public static function XLS_SheetNames($filename)
    {
        // count - sheets xlhtml -asc -dp  "caracteristique piezo.xls"

        $return = array();
        exec("xlhtml -asc -dp  '{$filename}'", $return);

        // example output
        //There are 4 pages total.
        //Page:0 Name:Feuil1 MaxRow:108 MaxCol:11
        //Page:1 Name:Feuil2 MaxRow:108 MaxCol:11
        //Page:2 Name:Feuil3 MaxRow:108 MaxCol:11
        //Page:3 Name:caracteristique piezo MaxRow:108 MaxCol:10

        foreach ($return as $line)
        {
            if (!util::contains($line, "Page:")) continue;

            $row = array();
            $row['Page'] = trim(util::midStr($line, "Page:", "Name:"));
            $row['Name'] = trim(util::midStr($line, "Name:", "MaxRow:"));
            $row['MaxRow'] = trim(util::midStr($line, "MaxRow:", "MaxCol:"));
            $row['MaxCol'] = trim(util::midStr($line, "MaxCol:", "\n"));

            $result[] = $row;
        }

        return $result;

    }


    /*
     * Convert each cell value as per the template
     *
     * @param String $cellTemplate - String with the following components
     *
     * {ColumnName} = changes this the current column name
     * {RowName}    = changes this the current row name (id)
     * {CellValue}  = changes this the current cell value
     *
     */
    public static function FromTemplate($matrix,$cellTemplate,$replacementSet = null)
    {

        if (is_null($replacementSet)) $replacementSet = array();

        $result = array();
        foreach ($matrix as $matrix_rowID => $matrix_row)
            foreach ($matrix_row as $matrix_columnid => $matrix_cell)
            {
                $newCellValue = $cellTemplate;
                $newCellValue = str_replace("{ColumnName}", $matrix_columnid, $newCellValue);
                $newCellValue = str_replace("{RowName}",    $matrix_rowID,    $newCellValue);

                if (!is_array($matrix_cell))
                    $newCellValue = str_replace("{CellValue}",  $matrix_cell,     $newCellValue);

                foreach ($replacementSet as $replaceKey => $replaceValue)
                {
                    $newCellValue = str_replace("{{$replaceKey}}",$replaceValue,$newCellValue);
                }


                $result[$matrix_rowID][$matrix_columnid] = $newCellValue;

            }

        return $result;
    }

}
?>