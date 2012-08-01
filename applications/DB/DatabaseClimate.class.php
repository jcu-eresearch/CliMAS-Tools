<?php
/**
 * Description of DatabaseClimate
 *
 * 
 */
class DatabaseClimate {
    
    
    public static   function getBioclimID($bioclim) 
    {
        return DBO::GetSingleRowValue("select id from bioclim where dataname = ".util::dbq($bioclim),'id');
    }
    
    
    public static   function getScenarioID($scenario) 
    {
        return DBO::GetSingleRowValue("select id from scenarios where dataname = ".util::dbq($scenario),'id');
    }

    public static  function getModelID($model) 
    {
        return DBO::GetSingleRowValue("select id from models where dataname = ".util::dbq($model),'id');
    }

    public static  function getTimeID($time) 
    {
        return DBO::GetSingleRowValue("select id from times where dataname = ".util::dbq($time),'id');
    }
    
    public static  function GetBioclims() 
    {
        $results = matrix::Column(DBO::Unique('bioclim', 'dataname'),'dataname');
        return $results;
    }
    
    
    public static  function GetScenarios() 
    {
        $results = matrix::Column(DBO::Unique('scenarios', 'dataname'),'dataname');
        return $results;
    }

    public static  function GetModels() 
    {
        $results= matrix::Column(DBO::Unique('models', 'dataname'),'dataname');
        return $results;
    }

    public static  function GetTimes() 
    {
        $results = matrix::Column(DBO::Unique('times', 'dataname'),'dataname');
        return $results;

    }

    public static  function GetBioclimDescriptions() 
    {
        return Descriptions::fromTable("bioclim");
    }
    
    
    public static  function GetScenarioDescriptions() 
    {
        return Descriptions::fromTable("scenarios");
    }

    public static  function GetModelsDescriptions() 
    {
        return Descriptions::fromTable("models");
    }

    public static  function GetTimesDescriptions() 
    {
        return Descriptions::fromTable("times");
    }
    
    
}

?>
