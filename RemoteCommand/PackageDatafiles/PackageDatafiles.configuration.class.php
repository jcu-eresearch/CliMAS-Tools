<?php
class PackageDatafilesConfiguration {

    /*
     * Where we can store zip files for serving to outside world
     *
     */
    public static function BioclimOutboundFolder()
    {
        return "/www/eresearch/TDH-Tools/source/Outbound/";
    }

    public static $MAXENT = "/home/jc166922/TDH/maxent_model/maxent.jar";
    public static $TRAINCLIMATE = "/home/jc165798/Climate/PCMDI/01.Oz.5km.61.90/mxe/1975";
    public static $PROJECTCLIMATE_ASC = "/home/jc165798/Climate/CIAS/Australia/5km/bioclim_asc";

    public static $SPECIES_DATA_FOLDER = "/home/jc166922/TDH/maxent_model";

    public static $SPECIES_DATA_OUTPUT_SUBFOLDER = "output";
    public static $SPECIES_DATA_SCRIPT_SUBFOLDER = "script";

    public static $SPECIES_DATA_OCCURANCE_FILE_NAME = "occur.csv";





}

?>
