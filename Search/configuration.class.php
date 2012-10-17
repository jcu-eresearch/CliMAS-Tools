<?php
// 
class configuration {

    
    public static function DefaultMapableActionClassname() { return "ContextLayerAustralianStates"; }
    
    public static function MapableBackgroundLayers() { return "ContextLayerMapableBackgroundLayers"; }
    
    
    
    public static function ApplicationName() 
    { 
        global $conf;
        return $conf[Parameter::$APPLICATION_NAME]; 
    }


    public static function ApplicationFolder()
    {
        global $conf;
        return $conf[Parameter::$APPLICATION_FOLDER]; 
    }

    public static function ApplicationFolderWeb()
    {
        global $conf;
        return $conf[Parameter::$APPLICATION_FOLDER_WEB]; 
    }


    public static function UtilityClasses() 
    {
        global $conf;
        return $conf[Parameter::$UTILITIES_CLASSES]; 
    }


    /**
     * Path to Downloads folder accessable from the web
     * @return string|null Filepath
     */
    public static function WebDownloadFolder()
    {
        global $conf;
        return $conf[Parameter::$DOWNLOAD_FOLDER_WEB]; 
    }

    /**
     * Filesystem  buddy to WebDownloadFolder
     * @return string|null  Filepath
     */
    public static function FilesDownloadFolder()
    {
        global $conf;
        return $conf[Parameter::$DOWNLOAD_FOLDER_REAL]; 
    }


    public static function ResourcesFolder()
    {
        global $conf;
        return $conf[Parameter::$RESOURCES_FOLDER]; 
    }

    
    public static function Descriptions_ClimateModels() {
        global $conf;
        return $conf[Parameter::$Descriptions_ClimateModels]; 
    }

    public static function Descriptions_EmissionScenarios() {
        global $conf;
        return $conf[Parameter::$Descriptions_EmissionScenarios]; 
    }

    public static function Descriptions_Years() {
        global $conf;
        return $conf[Parameter::$Descriptions_Years]; 
    }

    // web paath to ICONS
    public static function IconSource() {
        global $conf;
        return $conf[Parameter::$ICONS_FOLDER]; 
    }


    public static function SourceDataFolder() {
        global $conf;
        
        $df = $conf[Parameter::$SOURCE_DATA_FOLDER];
        return $df; 
    }

    public static function ContextSpatialLayersFolder()
    {
        return self::SourceDataFolder() . "context" . self::osPathDelimiter();
    }
    
    public static function osPathDelimiter()      { 
        global $conf;
        return $conf[Parameter::$PathDelimiter]; 
    }
    
    public static function osExtensionDelimiter() { 
        global $conf;
        return $conf[Parameter::$ExtensionDelimiter]; 
    }

    public static function CommandQueueID() { 
        global $conf;
        return $conf[Parameter::$COMMAND_QUEUE_ID]; 
    }
    
    public static function CommandQueueLog() { 
        global $conf;
        return $conf[Parameter::$COMMAND_QUEUE_LOG]; 
    }
    
    public static function CommandQueueFolder() { 
        global $conf;
        return $conf[Parameter::$COMMAND_QUEUE_FOLDER]; 
    }
    
    public static function CommandScriptsFolder() { 
        global $conf;
        return $conf[Parameter::$COMMAND_SCRIPTS_FOLDER]; 
    }
    
    public static function CommandScriptsExecutor() { 
        global $conf;
        return $conf[Parameter::$COMMAND_SCRIPTS_EXE]; 
    }
    
    public static function CommandExtension() { 
        global $conf;
        return $conf[Parameter::$COMMAND_EXTENSION]; 
    }
    
    public static function CommandScriptsPrefix() { 
        global $conf;
        return $conf[Parameter::$COMMAND_SCRIPTS_PREFIX]; 
    }

    public static function CommandScriptsSuffix() { 
        global $conf;
        return $conf[Parameter::$COMMAND_SCRIPTS_SUFFIX]; 
    }

    public static function MaxentJar() { 
        global $conf;
        return $conf[Parameter::$MaxentJar]; 
    }

    public static function Maxent_Taining_Data_folder() { 
        global $conf;
        return $conf[Parameter::$Maxent_Taining_Data_folder]; 
    }
    
    public static function Maxent_Future_Projection_Data_folder() { 
        global $conf;
        return $conf[Parameter::$Maxent_Future_Projection_Data_folder]; 
    }
    
    public static function Maxent_Species_Data_folder() { 
        global $conf;
        return $conf[Parameter::$Maxent_Species_Data_folder]; 
    }
    
    public static function Maxent_Species_Data_Output_Subfolder() { 
        global $conf;
        return $conf[Parameter::$Maxent_Species_Data_Output_Subfolder]; 
    }
    
    public static function Maxent_Species_Data_Occurance_Filename() { 
        global $conf;
        return $conf[Parameter::$Maxent_Species_Data_Occurance_Filename]; 
    }

    public static function TempFolder() { 
        global $conf;
        return $conf[Parameter::$TempFolder]; 
    }
    
    
}
?>