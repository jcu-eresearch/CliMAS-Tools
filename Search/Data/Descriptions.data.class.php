<?php
/**
 * 
 *  A List of single Description Objects
 *   
 */
class Descriptions extends Data {
    
    public static function create(array $src,$keyIsDescriptive = false)
    {
        $D = new Descriptions();
        $D->keyIsDescriptive($keyIsDescriptive);
        $D->FromArray($src);
        return $D;
    }

    public static function fromFile($filename, $delim = ",", $stringQuote = '"')
    {
        
        if (!file_exists($filename)) return null;

        $file = file($filename);


        $header = str_getcsv($file[0], $delim, $stringQuote);

        // get indexs for each column nam
        $indexName = array_util::GetKeyForValueContain($header,"Name");
        $indexDesc = array_util::GetKeyForValueContain($header,"Description");
        $indexMoreInfo = array_util::GetKeyForValueContain($header,"MoreInformation");
        $indexURI = array_util::GetKeyForValueContain($header,"URI");

        $D = new Descriptions();
        $D->keyIsDescriptive(false);

        for ($index = 1; $index < count($file); $index++)
        {
            if (trim($file[$index]) == "") continue;

            $line = str_getcsv($file[$index], ",", '"');
            $desc = new Description();
            $desc->DataName(array_util::Value($line, $indexName));
            $desc->Description(array_util::Value($line, $indexDesc));
            $desc->MoreInformation(array_util::Value($line, $indexMoreInfo));
            $desc->URI(array_util::Value($line, $indexURI));

            $D->Add($desc);

        }
        
        
        return $D;
    }




    private $descriptions = array();

    public function __construct() {
        parent::__construct();
        $this->DataName(__CLASS__);
        $this->keyIsDescriptive(false);
    }
    
    public function __destruct() {

        parent::__destruct();
    }

    public function Descriptions() {
        return $this->descriptions;
    }


    public function FromArray($src)
    {
        if (is_null($src)) return null;
        if (!is_array($src)) return null;

        foreach ($src as $key => $value)
        {
            $d = new Description();
            $d->DataName(null);
            if ($this->keyIsDescriptive())  $d->Name($key); //only set the name to the key  if it means something
            $d->Description($value);
            $this->Add($d);
        }

    }



    public function count()
    {
        return count($this->descriptions);
    }


    public function Add(Description $value)
    {
        $this->descriptions[$value->ID()] = $value;
        return $value->DataName();
    }

    public function Remove($key)
    {
        if (!$this->has($key)) return false;

        unset($this->descriptions[$key]);
        return true;
    }

    public function Get($key,$null_value = null)
    {
        if (!$this->has($key)) return $null_value;
        return $this->descriptions[$key];
    }

    public function asSimpleArray($valuePropertyName = "Description")
    {
        $result = array();
        foreach ($this->descriptions as $key => $desc)
        {
            $desc instanceof Description;
            $result[$desc->DataName()] = $desc->getPropertyByName($valuePropertyName);
        }
        
        return $result;
    }


    public function has($key)
    {
        return (array_key_exists($key, $this->descriptions));
    }

    public static function isA($src)
    {
        return $src instanceof Descriptions;
    }
 
    public static function cast($src)
    {
        $result = $src;
        $result instanceof Descriptions;
        return $result;
    }

    
    
    public function keyIsDescriptive() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }





}

?>
