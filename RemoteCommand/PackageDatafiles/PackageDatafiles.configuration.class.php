<?php
class PackageDatafilesConfiguration {



    //public static $MAXENT = "/home/jc166922/TDH/maxent_model/maxent.jar";
    //public static $TRAINCLIMATE = "/home/jc165798/Climate/PCMDI/01.Oz.5km.61.90/mxe/1975";

    public static function PROJECTCLIMATE_ASC()
    {
        $hostname = trim(exec("hostname --fqdn"));
        if (stripos( $hostname, "afakes-eresearch") !== FALSE) return "";  // #NA
        if (stripos( $hostname, "default.domain")   !== FALSE) return          "/home/jc165798/Climate/CIAS/Australia/5km/bioclim_asc";
        if (stripos( $hostname, "spatialecology") !== FALSE) return "/home/ctbccr/bioclimdata/data/source/bioclim_asc";

        return null;
    }


    //public static $SPECIES_DATA_FOLDER = "/home/jc166922/TDH/maxent_model";





}

?>
