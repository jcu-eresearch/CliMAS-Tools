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

    
    public static function Species_DB_Server()   { return "tdh-tools-2.hpc.jcu.edu.au"; }
    public static function Species_DB_Port()     { return "5432"; }
    public static function Species_DB_Username() { return "ap02"; }
    public static function Species_DB_Password() { return "71a6e5db6b9cfda9f0af062254b5bfbe"; }
    public static function Species_DB_Database() { return "ap02";}
    
    

    
    
    /**
     * look into this folder for a list of folder's
     * - this set of folders are those that have been computed
     * @return string 
     */
    public static function ModelledSpeciesFolder()
    {
        return configuration::Maxent_Species_Data_folder();
    }


}

?>
