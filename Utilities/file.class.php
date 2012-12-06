<?php
class file {
    /*
    * @method getTitleTagValue
    * @param $filename
    * @return mixed
    */
    public static function getTitleTagValue($filename)
    {
        $isHTML = util::contains($filename, '.htm');
        if (!$isHTML) return basename($filename);

        $file = file_get_contents($filename);
        $file = strtolower($file);
        $start = strpos($file, "<title>");
        $end = strpos($file, "</title>");

        $len = $end - $start - 7;

        $title = trim(substr($file,$start + 7,$len));

        return $title;
    }

    
//    public static function mime2extension($mimetype)
//    {
//        // http://svn.apache.org/repos/asf/httpd/httpd/trunk/docs/conf/mime.types
//        
//        $file = file("http://svn.apache.org/repos/asf/httpd/httpd/trunk/docs/conf/mime.types");
//        $file = file::arrayFilterOut($file, "#");
//        
//        $elements = array_util::ElementsThatContain($file, $mimetype);
//        
//        if (count($elements) == 0) return null;
//        
//        $element = util::first_element($elements);
//        
//        $exts = explode(" ",trim(util::fromLastChar($element, " ")));
//        
//        if (count($exts) < 1) return null;
//        
//        return $exts[0];
//        
//        
//    }




    /*
    * @method reallyDelete
    * @param $Filename
    * @return mixed
    */
    public static function reallyDelete($Filename)
    {
        if (self::reallyExists($Filename) == FALSE) return TRUE; // it did not exists therefor delete is TRUE

        try {
                if (file_exists($Filename)) @unlink($Filename);
            } catch (Exception $exc) {
                if (file_exists($Filename))
                    throw new Exception("Can't delete file {$Filename}");

            }
        
        return !file_exists($Filename);
    }

    public static function Delete($Filename)
    {
        return self::reallyDelete($Filename);
    }




    /*
    * @method reallyExists
    * @param $Filename
    * @return mixed
    */
    public static function reallyExists($Filename)
    {
        if (file_exists($Filename))
        {
            return TRUE;
        }

        $ss = str_replace('/', '\\', $Filename);

        if (file_exists($ss) == TRUE)
        {
            return TRUE;
        }


        return FALSE;
    }



    /*
    * @method getFileExtension
    * @param $srcPathname
    * @param $ext_sep = "."
    * @return mixed
    */
    public static function getFileExtension($srcPathname, $ext_sep = ".")
    {

        // find last extension sep - usually dot  .
        $pos = strrpos($srcPathname, $ext_sep);
        if ($pos === false)  return ""; // no sep - so no extension

        // very basic - get basename
        $result = substr($srcPathname,$pos + 1);

        return $result;
    }



    /*
    * @method mkdir_safe
    * @param $pathname
    * @return mixed
    */
    public static function mkdir_safe($pathname,$replace_space = null)
    {
        if (!is_null($replace_space))
            $pathname = str_replace (" ", $replace_space, $pathname);
        
        if (is_dir($pathname)) return; // if it already exists then don't do it
        @mkdir($pathname,0777,true);
        
        if (!is_dir($pathname)) return null;
        
        return $pathname;
    }


    /*
    * @method Array2File
    * @param $src
    * @param $dest
    * @return mixed
    */
    public static function Array2File($src,$dest,$file_header = "",$file_footer = "",$delim = ",",$clean_data = false)
    {
        $fh = fopen($dest, 'w') or die("can't write metadata to ($dest) ");

        fwrite($fh, $file_header);
        foreach ($src as $key => $value)
            if ($clean_data)
                fwrite($fh, util::CleanStr($key,$delim).$delim.util::CleanStr($value,$delim)."\n");
            else
                fwrite($fh, "{$key}{$delim}{$value}\n");

        fwrite($fh, $file_footer);
        fclose($fh);

        if (file_exists($dest)) return $dest;

        return NULL;
    }




    /*
    * @method ArrayValues2File
    * @param $src
    * @param $dest
    * @return mixed
    */
    public static function ArrayValues2File($src,$dest)
    {
        $fh = fopen($dest, 'w') or die("can't write metadata to ($dest) ");
        foreach ($src as $value) fwrite($fh, "$value\n");
        fclose($fh);
    }

    /*
    * @method ArrayValues2File
    * @param $src
    * @param $dest
    * @return mixed
    */
    public static function ArrayKeys2File($src,$dest)
    {
        $fh = fopen($dest, 'w') or die("can't write metadata to ($dest) ");
        foreach ($src as $key => $value) fwrite($fh, "$key\n");
        fclose($fh);
    }



    /*
    * @method Array2HTML
    * @param $src
    * @param $dest
    * @return mixed
    */
    public static function Array2HTML($src,$dest)
    {

        $fh = fopen($dest, 'w') or die("can't write metadata to ($dest) ");

        fwrite($fh, "\n".'<html>');
        fwrite($fh, "\n".'<head><title>'.basename($dest).'</title></head>');
        fwrite($fh, "\n".'<body>');
        fwrite($fh, util::matrix2HTMLTable($src));
        fwrite($fh, "\n".'</body>');
        fwrite($fh, "\n".'</html>');

        fclose($fh);
    }




    /*
    * @method File2Array
    * @param $srcFilename
    * @return mixed
    */
    public static function File2Array($srcFilename)
    {
        return file($srcFilename);

    }

    public static function File2KeyedArray($srcFilename,$delim = ',')
    {
        if (!file_exists($srcFilename))
        {
            echo "### ERROR: File2KeyedArray: File does not exist .. $srcFilename\n";
            return NULL;
        }

        $src = file($srcFilename);

        $result = array();

        foreach ($src as $src_line)
        {
            if ($src_line == $src[0]) continue;
            $split = explode($delim,$src_line);
            $result[trim($split[0])] = trim($split[1]);
        }



        return $result;

    }


    /*
    * @method file_sizes
    * @param $fileArr
    * @param $divisor = 1
    * @return mixed
    */
    public static function file_sizes($fileArr, $divisor = 1)
    {

        $result = array();
        foreach ($fileArr as $key => $value)
        {
            $result[$key] = filesize($value) / $divisor;
        }

        return $result;
    }



    /*
    * @method folder_folders
    * @param $path
    * @param $fs_folder_sep = "/"
    * @return mixed
    */
    public static function folder_folders($path, $fs_folder_sep = "/",$basenameAsKey = false)
    {
        
        if (is_null($fs_folder_sep)) $fs_folder_sep = "/";
        
        $path = util::trim_end($path, $fs_folder_sep);
        $path = $path.$fs_folder_sep;

        $result = array();

        $dir_handle = @opendir($path) or die("Unable to open $path");
        while (false !== ($file = readdir($dir_handle))) {
            $dir = $path.$file;
            if(is_dir($dir) && $file != '.' && $file !='..' )
            {

                if ($basenameAsKey)
                    $result[util::fromLastSlash(util::trim_end($dir, $fs_folder_sep),$fs_folder_sep)] = $dir;
                else
                    $result[$dir] = $dir;


            }
            elseif ($file != '.' && $file !='..')
            {
            }
        }

        //closing the directory
        closedir($dir_handle);

        ksort($result);


        return $result;
    }

    public static function filenames($src)
    {

        $result = array();
        foreach ($src as $key => $filename)
        {
            $result[$key] = util::rightStr($filename, '/',FALSE);
        }

        return $result;
    }


    public static function folder_with_extension($path, $extension ,$sep = "/", $basenameAsKey = false )
    {
        if (is_null($sep)) $sep = "/";
        $extension = str_replace('.', '', $extension );
        $files = self::folder_files($path,$sep,$basenameAsKey);
        $files = self::arrayFilter($files, '.'.$extension);
        return $files;
    }

    public static function folder_csv($path, $sep = "/", $basenameAsKey = false )
    {
        return self::folder_with_extension($path, 'csv' ,$sep, $basenameAsKey);
    }


    public static function folder_jpgs($path, $sep = "/", $basenameAsKey = false )
    {
        return self::folder_with_extension($path, 'jpg' ,$sep, $basenameAsKey);
    }

    public static function folder_pngs($path, $sep = "/", $basenameAsKey = false )
    {
        return self::folder_with_extension($path, 'png' ,$sep, $basenameAsKey );
    }

    public static function folder_asc($path, $sep = "/", $basenameAsKey = false )
    {
        return self::folder_with_extension($path, 'asc' ,$sep, $basenameAsKey );
    }

    public static function folder_gz($path, $sep = "/", $basenameAsKey = false )
    {
        return self::folder_with_extension($path, 'gz' ,$sep, $basenameAsKey );
    }
    

    /*
    * @method folder_files
    * @param $path
    * @param $sep = "/"
    * @param $basenameAsKey = false
    * @return mixed
    */
    public static function folder_files($path, $sep = "/", $basenameAsKey = false )
    {
        if (is_null($sep)) $sep = "/";
        $result = array();

        $dir_handle = @opendir($path) or die("Unable to open $path");
        while (false !== ($file = readdir($dir_handle))) {
            $dir = $path.$sep.$file;

            $dir = str_replace($sep.$sep, $sep, $dir);

            if((is_dir($dir) == FALSE) && $file != '.' && $file !='..' )
            {
                if ($basenameAsKey == true)
                {
                    $result[basename($dir)] = $dir;
                }
                else
                {
                    $result[$dir] = $dir;
                }


            }
            elseif ($file != '.' && $file !='..')
            {
            }
        }

        //closing the directory
        closedir($dir_handle);


        return $result;
    }


    public static function find_files_with_links($path)
    {
        $result = array();
        exec('find -L '.$path,$result);
        sort($result);
        return $result;
    }

    public static function find_files($path,$filter = "")
    {
        $result = array();

        $cmd = "find '$path' | grep -i '{$filter}'";
        exec($cmd, $result);
        return $result;
    }




    /*
    * @method arrayFilter
    * @param $src
    * @param $mustHave
    * @return mixed
    */
    public static function arrayFilter($src, $mustHave)
    {

        if (is_array($mustHave)) return self::arrayFilterMustHaveArray($src, $mustHave);

        $result = array();

        foreach ($src as $key => $value)
        {

            $pos = stripos($value, $mustHave);
            if ($pos === FALSE) continue;   // if we don't find it then go again.

            $result[$key] = $value; // it contains filter
        }


        return $result;
    }


    private static function arrayFilterMustHaveArray($src, $mustHaves)
    {
        $sub_result = $src;
        foreach ($mustHaves as $mustHave)
            $sub_result = self::arrayFilter($sub_result, $mustHave);

    }


    /*
    * @method arrayFilterOut
    * @param $src
    * @param $mustNotHave
    * @return mixed
    */
        public static function arrayFilterOut($src, $mustNotHave)
    {
        $result = array();

        foreach ($src as $key => $value)
        {

            $pos = stripos($value, $mustNotHave) ;
            if ($pos) continue;   // if we don't find it then go again.

            $result[$key] = $value; // it contains filter
        }


        return $result;
    }


    public static function ClassFiles($startPath,$path_sep = "/")
    {
        $file_tree = self::file_tree_filtered($startPath, $path_sep, ".class.php");
        return $file_tree;
    }


    // i am filtereing out .svn  as well

    /*
    * @method file_tree_filtered
    * @param $startPath
    * @param $path_sep = "/"
    * @param $filter = ""
    * @return mixed
    */
    public static function file_tree_filtered($startPath,$path_sep = "/",$filter = "")
    {
        $result = array();
        $all = file::file_tree($startPath,"/");

        foreach ($all as $key => $value)
        {
            $posSVN = strpos($value, ".svn");
            if ($posSVN !== FALSE) continue;  // we don't care about svn folders

            $testsPos = strpos($value, "test");
            if ($testsPos !== FALSE) continue;  // we don't care about tests folders

            if ($filter != "")
            {
                $pos = strpos($value, $filter);
                if ($pos === FALSE) continue;   // if we don't find it then go again.
            }

            $result[$key] = $value; // it contains filter
        }

        return $result;

    }



    /*
    * @method file_tree
    * @param $startPath
    * @param $path_sep = "/"
    * @return mixed
    */
    public static function file_tree($startPath,$path_sep = "/")
    {
        $startPath = util::trim_end($startPath,$path_sep);
        $result = array();
        self::list_dir($startPath,$result,$path_sep);
        return $result;
    }




    /*
    * @method list_dir
    * @param $path
    * @param &$result
    * @param $path_sep = "/"
    * @return mixed
    */
    public static function list_dir($path, &$result,$path_sep = "/")
    {
        $dir_handle = @opendir($path) or die("Unable to open $path");
        while (false !== ($file = readdir($dir_handle))) {
            $dir = $path.$path_sep.$file;
            if(is_dir($dir) && $file != '.' && $file !='..' )
            {
                $handle = @opendir($dir) or die("unable to open file $file");
                self::list_dir($dir, $result,$path_sep);
            }
            elseif ($file != '.' && $file !='..')
            {
                $result[$path.$path_sep.$file] = $path.$path_sep.$file;
            }
        }

        //closing the directory
        closedir($dir_handle);

    }

    public static function nthLines($filename, $start_line = 1,$num_lines = 10)
    {
        $result = array();
        exec("sed -n {$start_line},{$num_lines}p '{$filename}' ",$result);

        return $result;
    }


    public static function nthLine($filename, $lineNumber)
    {
        return exec("head -n $lineNumber $filename | tail -n 1");
    }

    public static function lineCount($filename)
    {
        if (!file_exists($filename)) return -1;
        
        $result = trim(util::leftStr(trim(exec("wc '$filename'")), ' ')) ;
        return $result;
    }


    public static function lineCounts($filenames,$basenameAsKey = false)
    {

        $result= array();
        foreach ($filenames as $filename) {
            
            if ($basenameAsKey)
            {
                $result[basename($filename)] = self::lineCount($filename);
            }
            else
            {
                $result[$filename] = self::lineCount($filename);    
            }
            
            
        }
        return $result;
    }


    // append lines
    // append all lines 1's
    // append all lines 2's
    // etc
    public static function appendSideways($filenames,$output_filename,$delim = ",")
    {

        $counts = self::lineCounts($filenames);
        $avg_line_count = array_util::Average($counts);

        foreach ($counts as $filename => $line_count)
        {
            if ($line_count != $avg_line_count)
            {
                echo "##Error:: inconsitent number of lines avg($avg_line_count) != ($line_count) - $filename \n";
                return NULL;
            }
        }

        // open files
        $fh = array();
        foreach ($filenames as $filename) $fh[$filename] = fopen($filename,'rb');

        // read a lines from each file and append them
        for ($index = 0; $index < $avg_line_count; $index++)
        {
            $out = "";
            foreach ($filenames as $filename) $out .= trim(fgets($fh[$filename])).$delim;

            file_put_contents($output_filename, util::trim_end($out, $delim)."\n", FILE_APPEND);
        }


        // close files
        foreach ($filenames as $filename) fclose($fh[$filename]);

    }


    // append lines
    // append all lines 1's
    // append all lines 2's
    // same as above - though we are going to trim to extra column ones
    // we assue that the subsequnt files have the same row header
    // we are joining them because the column headers continue.
    public static function appendSidewaysTrimExtraColumnOne($filenames,$output_filename,$delim = ",",$debug = FALSE)
    {

        if ($debug) echo "appendSidewaysTrimExtraColumnOne\n";

        $counts = self::lineCounts($filenames);
        $avg_line_count = array_util::Average($counts);

        if ($debug)
        {
            echo "avg_line_count\n";
            print_r($avg_line_count);
        }

        foreach ($counts as $filename => $line_count)
        {
            if ($line_count != $avg_line_count)
            {
                echo "##Error:: inconsitent number of lines avg($avg_line_count) != ($line_count) - $filename \n";
                return NULL;
            }
        }

        // open files
        $fh = array();
        if ($debug) echo "Open files \n";
        foreach ($filenames as $filename) $fh[$filename] = fopen($filename,'rb');

        // read a lines from each file and append them
        for ($index = 0; $index < $avg_line_count; $index++)
        {
            $out = "";
            $first_bit_count = 0;

            if ($debug && $index % 100 == 0) echo "index = $index\n";

            foreach ($filenames as $filename)
            {
                $fs = trim(fgets($fh[$filename]));
                $first_bit = util::leftStr($fs, $delim);

                if ($first_bit_count == 0)
                    $out .= $first_bit;

                $out .= str_replace($first_bit,'',$fs); // .$delim;

                $first_bit_count++;
            }



            file_put_contents($output_filename, util::trim_end($out, $delim)."\n", FILE_APPEND);
        }


        if ($debug) echo "Close files \n";
        // close files
        foreach ($filenames as $filename) fclose($fh[$filename]);

    }

    public static function currentWorkingDirectory()
    {
        return exec("pwd");
    }

    /*
     * Returns the folder(directory) of the script $src
     * Call with NO parameters will return the folder where THIS script is located
     *
     * best way to call this method is   file::currentScriptFolder(__FILE__)
     * it will then return the folder of the script you are running
     *
     */
    public static function currentScriptFolder($src = __FILE__)
    {
        return dirname($src);
    }


    public static function matlab2ascii($input,$output)
    {
        // create freemat script
        $freemat  = "";
        $freemat .= "var = NaN\n";
        $freemat .= "ans = NaN\n";
        $freemat .= "load '$input'\n";
        $freemat .= "save '$output' var -ASCII\n";
        $freemat .= "var = NaN\n";
        $freemat .= "ans = NaN\n";

        file_put_contents("FreematExport.m", $freemat); // write script to ".m" file so freemat can see it as a"command"
        $exec_result = exec("freemat -nogui -e -f FreematExport");  // execute freemat script via shell command
        unlink("FreematExport.m");
        return (file_exists($output));
    }

    public static function wget($url,$output_filename,$debug = false)
    {
        $cmd =  "wget -q -O -L1 \"$output_filename\" \"$url\"";

        if ($debug) echo " WGET command : $cmd \n";

        exec($cmd);
        if (file_exists($output_filename)) return $output_filename;

        return NULL;
    }

    public static function url2file($url,$filename)
    {
        file_put_contents($filename, file::get_page_using_curl($url));
        return file_exists($filename);
    }


    public static function get_page_using_curl($url)
    {
        $ch = curl_init();
        $timeout = 30;
        $userAgent = "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)";
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_REFERER, "http://www.google.com/");
        // curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,$timeout);
        curl_setopt($ch, CURLOPT_CERTINFO,true);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

    public static function get_page_using_curl_post_data($url,$data)
    {
        $ch = curl_init();

        $userAgent = "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)";

        curl_setopt($ch, CURLOPT_HEADER,true);
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        curl_setopt($ch, CURLOPT_REFERER, "http://www.google.com/");


        $data = curl_exec($ch);
        print_r(curl_getinfo($ch));

        curl_close($ch);
        return $data;
    }



    public static function gunzip($filename)
    {
        if (is_null($filename))
        {
            echo "##ERROR: file::gunzip filename passed as NULL\n";
            return NULL;
        }

        $gunzip_result = exec("gunzip -l $filename");
        $unzipped_name = trim(util::leftStrFrom($gunzip_result, '/', FALSE));

        exec("gunzip $filename");
        if (file_exists($unzipped_name)) return $unzipped_name;

        return NULL;
    }

    public static function zip_folder($folder,$zip_file)
    {
        $zip_file = str_replace(".zip",'',$zip_file);

        if (!is_dir($folder))
        {
            logger::error("$folder is not a folder");
            return NULL;
        }

        exec("zip -r '{$zip_file}.zip' '$folder'");

        return file_exists($zip_file);
    }


    public static function grepped_text_file($filename,$grep)
    {
        $cmd = "cat {$filename} | grep {$grep}";
        $result_lines = array();
        $locate_result = exec($cmd ,$result_lines);
        return $result_lines;
    }


    public static function locate($str,$grep = null)
    {
        if (is_null($str))
            return "##ERROR: Nothing to find\n";

        if (!is_null($grep) )
            $grep = " grep '$grep' |";
        else
            $grep = "";

        $cmd = "locate $str | $grep sort ";

        $result_lines = array();
        $locate_result = exec($cmd ,$result_lines);

        return $result_lines;
    }

    public static function pdf2text($pdf_filename)
    {
        if (!file_exists($pdf_filename))
            return "##ERROR: Can't find $pdf_filename\n";

        $real_name = realpath($pdf_filename);
        $cmd = "pdftotext '$real_name' -  -layout -htmlmeta ";

        $result_lines = array();
        $locate_result = exec($cmd ,$result_lines);

        $result = "";
        foreach ($result_lines as $line) {
            $result .= $line."<br/>";
        }

        return $result;
    }


    public static function random_filename($folder = "/tmp")
    {
        $result = $folder."/".uniqid();
        return $result;
    }

    public static function copy($source, $dest,$overwrite)
    {
        //logger::text("$source to  $dest");
        copy($source, $dest);

        if (!file_exists($dest))
        {
            echo "ERROR: could not copy $source to $dest\n";
            return false;
        }

        return true;
    }

    public static function move($source, $dest,$overwrite)
    {
        if (!file_exists($source))
        {
            echo "EERROR: file $source does not exist\n";
            return null;
        }

        if ($overwrite)  self::reallyDelete ($dest);

        if (file_exists($dest))
        {
            echo "ERROR: file $dest exists\n";
            return null;
        }

        // logger::text("$source to  $dest");

        copy($source, $dest);

        if (!file_exists($dest))
        {
            echo "ERROR: could not move $source to $dest\n";
            return null;
        }

        unlink($source);

        return $dest;

    }




    public static function ftp_upload($host,$usr,$pwd,$local_file,$ftp_path,$port = 21)
    {

        $conn_id = ftp_connect($host, $port);  // connect to FTP server (port 21)
        if (!$conn_id)
        {
            logger::error("failed to connect to ftp server ... $host");
            return null;
        }

        $login_ok = @ftp_login($conn_id, $usr, $pwd);  // send access parameters
        if (!$login_ok)
        {
            logger::error("failed to login to ftp server ... $host with $usr $pwd");
            return null;
        }


        ftp_pasv ($conn_id, true);  // turn on passive mode transfers (some servers need this)
        $upload = ftp_put($conn_id, $ftp_path, $local_file, FTP_BINARY); // perform file upload
        if (!$upload)
        {
            logger::error("failed to upload file to $host with local_file = $local_file, ftp_path = $ftp_path ");
            return null;
        }

        $chmod = ftp_chmod($conn_id, 0666, $ftp_path); // Chmod the file (just as example)
        if (!$chmod)
            logger::error("warning could not chnage to writeable ftp_path = $ftp_path ");


        ftp_close($conn_id);  // close the FTP stream
    }


    public static function ftp_mkdir($host,$user,$pass,$dirname) {

        $conn_id = ftp_connect($host);
        $login_result = ftp_login($conn_id, $user, $pass);
        if ((!$conn_id) || (!$login_result)) {
              //insert error text here;
        die;
        }
        ftp_mkdir($conn_id, $dirname);
        ftp_chmod($conn_id, 757, $dirname); // read & write for everyone
        ftp_quit($conn_id);
    }

    /*
     * Retrun Filename compoent - no path no extension
     */
    public static function filenameOnly($pathname)
    {

        if (is_array($pathname))
        {
            // $pathname is an Array
            $result = array();
            foreach ($pathname as $key => $single_pathname)
                $result[$key] = self::filenameOnly($single_pathname);

        }
        else
        {
            // sinle Filename
            $path_parts = pathinfo($pathname);

            $result = $path_parts['basename'];
            if (array_key_exists('extension', $path_parts))
                $result = str_replace(".".$path_parts['extension'], "", $path_parts['basename']);

        }

        return $result;
    }

    
    
    public static function LS($pattern,$options = "-1",$basenameAsKey = false)
    {
        
        if (is_null($options) ) $options = "-1";
        
        $cmd = "ls $options {$pattern}";
        $result = array();
        
        exec($cmd,$result);
        
        if (!$basenameAsKey) return $result;
        
        $newResult = array();
        foreach ($result as $key => $value) 
        {
            $newResult[basename($value)] = $value;
        }
        
        unset($result);
        
        return $newResult;
    }

    public static function LSfolders($pattern = "*",$options = "-1d",$basenameAsKey = true)
    {
        
        if (is_null($options) ) $options = "-1";
        
        $pattern .= '/';
        
        $cmd = "ls $options {$pattern}";
        $result = array();
        
        exec($cmd,$result);
        
        if (!$basenameAsKey) return $result;
        
        $newResult = array();
        foreach ($result as $key => $value) 
        {
            $newResult[basename($value)] = $value;
        }
        
        unset($result);
        
        return $newResult;
    }
    
    
    
    
    
    public static function Head($filename,$lines)
    {
        $cmd = "head -n {$lines} {$filename}";
        $result = array();
        
        exec($cmd,$result);
        
        return $result;
        
        
    }
    
    
    /**
     *
     * @param type $src  Array of pathnames
     * 
     * @return array Key = Basename of filename  value = filename
     */
    public static function Filelist2BasenamePath($src)
    {
        
        $result = array();
        foreach ($src as $path) 
        {
            $result[basename($path)] = $path;            
        }
        
        return $result;;
        
    }
    
    /**
     * Take in Linux Command line and expect output of command line to be Filepath list
     * 
     * @param type $src
     * @return type 
     */
    public static function Commandline2BasenamePath($src)
    {
        $result = array();
        exec($src,$result);
        return self::Filelist2BasenamePath($result);
    }
    
    
}
?>