<?php

/**
 * Description of classManage
 *
 * @author jc166922
 */
class classManager {
    //put your code here


    /**
    * @name __construct
    * @param $rootFolder - top level folder where application is installed
    */

    /*
    * @method __construct
    * @param $rootFolder
    * @return mixed
    */
    public function __construct($rootFolder)
    {
        $this->ApplicationRootFolder($rootFolder);
        $this->ConfigLoad();
    }


    private $config;

    /*
    * @property Config
    * @return mixed
    */
    public function Config()
    {
        if (func_num_args() == 0 ) return $this->config;
        $this->config = func_get_arg(0);
    }

    private function ConfigLoad()
    {
        $configFilename = $this->ApplicationRootFolder()."/".$this->Hostname().".config";

        if (file_exists($configFilename))
            $this->Config(util::loadMatrix($configFilename,",","key"));
        else
            $this->Config(array());

    }



    /*
    * @method ConfigRow
    * @param $rowID
    * @return mixed
    */
    public function ConfigRow($rowID)
    {
        if (!array_key_exists($rowID, $this->Config()) ) return null;
        $ca = $this->Config();
        return $ca[$rowID];
    }


    /*
    * @method ConfigValue
    * @param $rowID
    * @param $defaultValue = null
    * @return mixed
    */
    public function ConfigValue($rowID,$defaultValue = null)
    {
        $row = $this->ConfigRow($rowID);
        if (is_null($row)) return $defaultValue;
        return $row['value'];
    }



    private $hostname = null;

    /*
    * @method Hostname
    * @return mixed
    */
    public function Hostname()
    {
        if ($this->hostname == null ) $this->hostname = util::hostname();
        return $this->hostname;
    }


    private $applicationRootFolder;

    /*
    * @property ApplicationRootFolder
    * @return mixed
    */
    public function ApplicationRootFolder()
    {
        if (func_num_args() == 0 ) return $this->applicationRootFolder;
        $this->applicationRootFolder = func_get_arg(0);
    }

    private $applicationFiles = NULL;

    /*
    * @method ApplicationFiles
    * @return mixed
    */
    public function ApplicationFiles()
    {
        if ($this->applicationFiles == NULL)
            $this->applicationFiles = file::file_tree($this->ApplicationRootFolder());

        return $this->applicationFiles;
    }


    // bulk load any classes available under the main folder

    /*
    * @method loadTree
    * @param $folder
    * @return mixed
    */
    public function loadTree($folder)
    {
        $filter = ".class.php";
        $files = file::file_tree_filtered($this->ApplicationRootFolder()."/".$folder,"/",$filter);

        $files = file::arrayFilterOut($files, "~");
        $files = file::arrayFilterOut($files, ".new");
        $files = file::arrayFilterOut($files, ".backup");


        // load classes found
        foreach ($files as $key => $value)
        {

            include_once $value;
        }

    }


    /*
    * @method classInclude
    * @param $className
    * @return mixed
    */
    public function classInclude($className)
    {
        
        $files = file::file_tree_filtered($this->ApplicationRootFolder,"/",$filter);

        // load classes found
        foreach ($files as $key => $value)
        {
            $thisFile = file_get_contents($value);

            $classPos = strpos($thisFile,"class") + 5;
            $bracePos = strpos($thisFile,"{",$classPos) ;
            $classNameLen = $bracePos - $classPos;

            $fileClassName = substr($thisFile, $classPos, $classNameLen);

            
            // include_once $value;
        }



    }





    /*
    * @method loadClass
    * @param $_className
    * @return mixed
    */
    public function loadClass($_className)
    {


        if (file_exists($_className)) // see if the class name they passed ins a full path that exists
        {
            $classFilename = $_className; // yes it is
        }
        else
        {
            // relative path
            $classFilename = $this->ApplicationRootFolder()."/".$_className.".class.php";

            if (!file_exists($classFilename)) return false; // the class does not exist

        }

        include_once $classFilename;



        return true;
    }



    /*
    * @method classNames
    * @param $_className
    * @return mixed
    */
    public function classNames($_className)
    {

        // see if the class name they passed ins a full path that exists
        if (file_exists($_className))
            $classFilename = $_className; // yes it is
        else
        {
            // relative path
            $classFilename = $this->ApplicationRootFolder()."/".$_className.".class.php";
            if (!file_exists($classFilename)) return false; // the class does not exist

        }

        $classCodeArray = file($classFilename);

        $result = array();
        foreach ($classCodeArray as $line)
        {
            if (util::contains(strtolower($line), 'class'))
            {
                $extractClassName = util::midStr($line,'class ',' ');
                $result[$extractClassName] = $extractClassName ;  // new for this class
            }
        }


        return $result;
    }





    /*
    * @method loadFolder
    * @param $folder
    * @return mixed
    */
    public function loadFolder($folder)
    {

        $files = file::folder_files($this->ApplicationRootFolder()."/".$folder);
        $files = file::arrayFilter($files, ".class.php");

        $files = file::arrayFilterOut($files, "~");
        $files = file::arrayFilterOut($files, ".new");
        $files = file::arrayFilterOut($files, ".backup");


        foreach ($files as $classFilename)
        {
            if (file_exists($classFilename) == FALSE) return false;
            include_once $classFilename;
        }

    }




    /*
    * @method loadNew
    * @param $_className
    * @param $parms
    * @return mixed
    */
    public function loadNew($_className, $parms)
    {

        // get fro Last Slash
        $simpleClassName = util::fromLastSlash($_className,"/");

        $classFilename = $this->ApplicationRootFolder()."/".$_className.".class.php";

        if (file_exists($classFilename))
        {
            include_once $classFilename;
            $obj = new $simpleClassName($parms);
            return $obj;
        }
        else
        {
            return false;
        }

    }


    /*
    * @method loadedClasses
    * @return mixed
    */
    public function loadedClasses()
    {
        return get_declared_classes();
    }


    /*
    * @method loadedSensors
    * @return mixed
    */
    public function loadedSensors()
    {

        $classFiles = get_included_files() ;
        $sensorPackageFiles = array_util::ElementsThatContain($classFiles, "sensorPackage");

        debug::this($sensorPackageFiles);


        $sensorNamesList = array();

        // load classes found
        foreach ($sensorPackageFiles as $sensorPackageFile)
        {


            $thisFile = file_get_contents($sensorPackageFile);

            $extendsPos = strpos($thisFile,"extends");

            if ($extendsPos > 0)
            {

                $extendsPos = $extendsPos + 7;

                $bracePos = strpos($thisFile,"{",$extendsPos) ;
                $extendsNameLen = $bracePos - $extendsPos;

                $extendsName = trim(substr($thisFile, $extendsPos, $extendsNameLen));


                if ( $extendsName == "sensorPackage")
                {
                    //debug::this("---- ".$sensorPackageFile." = ".strlen($sensorPackageFile)."\n");
                    //debug::this("EXTEND NAME [".$extendsName."] \n");

                    $classPos = strpos($thisFile,"class") + 5;
                    $bracePos = strpos($thisFile,"extends",$classPos) ;
                    $classNameLen = $bracePos - $classPos;

                    $fileClassName = trim(substr($thisFile, $classPos, $classNameLen));

                    $sensorNamesList[$fileClassName] = $sensorPackageFile;

                }



            }
            // echo " class name in Load $value is $fileClassName <br>\n";
            // include_once $value;
        }



        return $sensorNamesList;

    }



}
?>