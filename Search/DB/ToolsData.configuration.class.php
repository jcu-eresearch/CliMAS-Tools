<?php
/**
 * datbasae  configuration and default values
 *
 * TODO:: Needs to com from Config file that is specific to HOstname
 *
 */
class ToolsDataConfiguration {

    public static function Server()  { return "localhost"; }
    public static function Username() { return "jc166922"; }
    public static function Password() { return "Volts100."; }
    public static function Database() {return "TDH-TOOLS";}

    //
    // this is used
    /**
     * This file will hold all the names of the Modelling Layers
     * the filenames in this file are in the form [scenario]_[gcm]_[year]
     *
     * @return string Fuill pathname to file that contains list of modeling filenames
     *
     */
    public static function ClimateModellingSourceFilelistFilename()
    {
        return "/data/dmf/TDH/ClimateModellingFilelist.txt";
    }

    

}

?>
