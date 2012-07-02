<?php
class documentIt {


    /*
    * @method now
    * @param $filename
    * @return mixed
    */
    public static function now($filename)
    {
        $D =  new documentIt($filename);
        $D->doWork();

    }

   /*
    * @method __construct
    * @param $filename
    * @return NULL
    */
    public function  __construct($filename)
    {
        global $CM;

        $this->CM($CM);
        $this->Filename($filename);
    }

    private $filename;

    /*
    * @property Filename
    * @return mixed
    */
    public function Filename()
    {
        if (func_num_args() == 0 ) return $this->filename;
        $this->filename = func_get_arg(0);
    }



    private $cm;

    /*
    * @property CM
    * @return mixed
    */
    public function CM()
    {
        if (func_num_args() == 0 ) return $this->cm;
        $this->cm = func_get_arg(0);
    }



    /*
    * @method doWork
    * @return mixed
    */
    public function doWork()
    {

        if (!file_exists($this->Filename()))
        {
            echo "File does not exist: ".$this->Filename()."\n";
            return;
        }

        // copy file to a backup
         $backupFilename = $this->Filename().".backup";

        if (file_exists($backupFilename)) unlink($backupFilename);
        // copy($this->Filename(),$backupFilename);

        $newVersionText = $this->doGenerateComments();
        $newFilename = $this->Filename().".new";
        if (file_exists($newFilename)) unlink($newFilename);


        file_put_contents($this->Filename(), $newVersionText);

    }


    /*
    * @method doGenerateComments
    * @return mixed
    */
    public function doGenerateComments()
    {

        $tab = "    ";

        $code = file($this->Filename());

        $lineNumbers = $this->getMethodLineNumbers($code);

        $replace = array();
        foreach ($lineNumbers as $methodName => $lineNumber)
        {

            $originalLine = $code[$lineNumber];
            $previousLine = $code[$lineNumber - 1];

            $isProperty = false;
            if (util::contains(strtolower($previousLine) , "private $".strtolower($methodName).";")) $isProperty = true;

            $commentType = "@method";
            if ($isProperty)  $commentType = "@property";

            $comment = "\n$tab/*";
            $comment .= "\n$tab* $commentType $methodName ";

            // find bracket - start of parameters

                    $bracketStart = strpos($originalLine, '(');
            $parameterStr = substr($originalLine,$bracketStart);
            $parameterStr = str_replace("(", "", $parameterStr);
            $parameterStr = trim(str_replace(")", "", $parameterStr));
            $parmArray = explode(",",$parameterStr);

            foreach ($parmArray as $singleParmNameStr)
                if (trim($singleParmNameStr) != "" )
                    $comment .= "\n$tab* @param ".trim($singleParmNameStr)." ";


            $comment .= "\n$tab* @return mixed";
            $comment .= "\n$tab*/";

            $comment .= "\n".$originalLine;

            $replaceKey = $originalLine;
            $replace[$replaceKey] = $comment;


        }

        $newCode = file_get_contents($this->Filename());

        foreach ($replace as $find => $replaceWith)
        {
            $newCode = str_replace($find, $replaceWith, $newCode);
        }

        return $newCode;


    }


    /*
    * @method getMethodLineNumbers
    * @param $code
    * @return mixed
    */
    private function getMethodLineNumbers($code)
    {

        $classNames = $this->CM()->classNames($this->Filename());

        $lineNumbers = array();
        foreach ($classNames as $className)
        {
            $methods = get_class_methods($className);

            if (is_array($methods))
            {
                foreach ($methods as $methodName)
                {
                    $lookFor = " function $methodName(";
                    $lineNumbers[$methodName] = array_util::GetKeyForValueContain($code, $lookFor);
                }
            }

        }

        // $lineNumbers holds the MethodName and the line it appears on

                return $lineNumbers;

    }


}
?>