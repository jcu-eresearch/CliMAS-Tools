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

    public static function ALAFullTextSearch() { return  "http://bie.ala.org.au/search?q=";}

    public static function ALAFullTextSearchJSON() { return  "http://bie.ala.org.au/search.json?q=";}


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
    
    
    /**
     * look into this folder for a list of folder's
     * - this set of folders are those that have been computed
     * @return string 
     */
    public static function ModelledSpeciesFolder()
    {
        return "/data/dmf/TDH-Tools/source/species";
    }

    /**
     * Chnage this to the folder that will actaully hold the data accessible on the HPC
     *
     * most likley /home/jc165798/Climate/CIAS/Australia/5km/bioclim_asc/
     *
     * @return string
     */
    public static function BioclimSourceFolder()
    {
        return "/www/eresearch/TDH-Tools/source/bc/";
    }




}

?>
