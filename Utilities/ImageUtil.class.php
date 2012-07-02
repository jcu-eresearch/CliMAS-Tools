<?php
include_once 'includes.php';

class ImageUtil {
    

    public function Animate($filenames, $output_path = null)
    {
        
        if (is_null($output_path)) $output_path = file::random_filename().".gif";
        
        $cmd  = "convert -delay 50 -dispose Background " . "'".join("' '",$filenames)."'"." '{$outputFilename}'" ;
        exec($cmd);
        
        if (!file_exists($output_path)) return null;
        return $output_path;
        
    }
    
}
?>
