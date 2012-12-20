<?php
/**
 * database configuration and default values
 *
 * TODO:: Needs to come from Config file that is specific to Hostname
 *
 */
class ToolsDataConfiguration {

    public static function Species_DB_Server()   { return "localhost"; }
    public static function Species_DB_Port()     { return "5432"; }
    public static function Species_DB_Username() { return "climas"; }
    public static function Species_DB_Password() { return "asdf"; }
    public static function Species_DB_Database() { return "climas_production"; }

}

?>
