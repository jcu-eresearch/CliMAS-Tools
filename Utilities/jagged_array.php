<?php
class jagged_array 
{
    
    public static function Median($src,$null_value = null)
    {
        $result = array();
        foreach ($src as $row_id => $row) 
        {
            $result[$row_id] = stats::median($row);
        }
        return $result;
    }

    public static function Count($src)
    {
        $result = array();
        foreach ($src as $row_id => $row) 
        {
            $result[$row_id] = count($row);
        }
        return $result;
    }
    
    
}
?>