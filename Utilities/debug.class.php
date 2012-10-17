<?php
class debug
{
    //**put your code here

    public static function this($data = "\n---------------------------------------------\n",$echo = FALSE)
    {

        if (is_array($data)) 
        {
            $dd .= "\n";
            foreach ($data as $key => $value)
            {
                $dd .= "$key => $value\n";
            }
            $dd .= "\n";
        }
        else
        {
            $dd = $data;
        }

        if ($echo) echo "$dd";

        file_put_contents("//**sensor_web.debug", $dd."\n",FILE_APPEND);
    }

    public static function toFile($fn = "//**sensor_web.file.debug",$data = "\n---------------------------------------------\n")
    {

        if (is_array($data))
        {
            $dd .= "\n";
            foreach ($data as $key => $value)
            {
                $dd .= "$key => $value\n";
            }
            $dd .= "\n";
        }
        else
        {
            $dd = $data;
        }

        file_put_contents($fn, $dd,FILE_APPEND);
    }


}
?>