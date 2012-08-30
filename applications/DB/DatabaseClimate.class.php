<?php
/**
 * Description of DatabaseClimate
 *
 * 
 */
class DatabaseClimate {
    
    
    public static   function getBioclimID($bioclim) 
    {
        $result = DBO::GetSingleRowValue("select id from bioclim where dataname = ".util::dbq($bioclim,true),'id');
        if ($result instanceof ErrorMessage) return ErrorMessage::Stacked (__METHOD__,__LINE__,"", true,$result);
        return $result;
    }
    
    
    public static   function getScenarioID($scenario) 
    {
        $result = DBO::GetSingleRowValue("select id from scenarios where dataname = ".util::dbq($scenario,true),'id');
        if ($result instanceof ErrorMessage) return ErrorMessage::Stacked (__METHOD__,__LINE__,"", true,$result);
        return $result;
    }

    public static  function getModelID($model) 
    {
        $result = DBO::GetSingleRowValue("select id from models where dataname = ".util::dbq($model,true),'id');
        if ($result instanceof ErrorMessage) return ErrorMessage::Stacked (__METHOD__,__LINE__,"", true,$result);
        return $result;
    }

    public static  function getTimeID($time) 
    {
        $result = DBO::GetSingleRowValue("select id from times where dataname = ".util::dbq($time,true),'id');
        if ($result instanceof ErrorMessage) return ErrorMessage::Stacked (__METHOD__,__LINE__,"", true,$result);
        return $result;
    }
    
    
    private static function getDataname($table)
    {
        
        $r = DBO::Unique($table, 'dataname');
        if ($r instanceof ErrorMessage) return ErrorMessage::Stacked (__METHOD__,__LINE__,"can't get dataname for {$table}", true,$r);
        
        $result = matrix::Column($r,'dataname');
        if (is_null($result)) return new ErrorMessage(__METHOD__,__LINE__,"Can't get values as a column array");
        
        sort($result);
        
        return $result;
    }
    
    public static  function GetBioclims() 
    {
        return self::getDataname('bioclim');
    }
    
    
    public static  function GetScenarios() 
    {
        $result = self::getDataname('scenarios');
        if ($result instanceof ErrorMessage) 
            return ErrorMessage::Stacked (__METHOD__,__LINE__,"Can't get Data name from Scenarios", true,$result);

        unset($result['ALL']);
        unset($result['CURRENT']);
        
        
        return $result;
    }

    public static  function GetModels() 
    {
        $result = self::getDataname('models');
        if ($result instanceof ErrorMessage) 
            return ErrorMessage::Stacked (__METHOD__,__LINE__,"Can't get Data name from Models", true,$result);
            
        unset($result['ALL']);
        unset($result['CURRENT']);
        
        return $result;
    }

    public static  function GetTimes() 
    {
        $result = self::getDataname('times');
        if ($result instanceof ErrorMessage) 
            return ErrorMessage::Stacked (__METHOD__,__LINE__,"Can't get Data name from times", true,$result);
            
        unset($result['1990']);
        unset($result['1975']);
        
        return $result;
        
        
        
        return self::getDataname('times');
    }

    
    private  static  function getDataNamesNamed($table,$pattern) 
    {
        
        $r = DBO::Unique($table, 'dataname',"dataname like '{$pattern}'");
        if ($r instanceof ErrorMessage) return ErrorMessage::Stacked (__METHOD__,__LINE__,"Can't get Datanamed from {$table} using pattern {$pattern}", true,$r);

        $result = matrix::Column($r,'dataname');
        if (is_null($result)) return new ErrorMessage(__METHOD__,__LINE__,"Can't get Datanamed from {$table} using pattern {$pattern} as a column array");
        
        unset($result['ALL']);
        
        return $result;        
    }
    
    
    public static  function GetScenariosNamed($pattern) 
    {
        return self::getDataNamesNamed('scenarios',$pattern);
     
    }

    public static  function GetModelsNamed($pattern) 
    {
        return self::getDataNamesNamed('models',$pattern);
    }

    public static  function GetTimesNamed($pattern) 
    {
        return self::getDataNamesNamed('times',$pattern);
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

    
    public static  function GetScenarioDescription($named) 
    {
        $desc = Description::fromTable("scenarios","dataname", "description", "moreinfo", "uri", $named);
        $desc instanceof Description;
        return $desc;
    }

    public static  function GetModelDescription($named) 
    {
        $desc = Description::fromTable("models","dataname", "description", "moreinfo", "uri", $named);
        $desc instanceof Description;
        return $desc;
    }

    public static  function GetTimeDescription($named) 
    {
        $desc = Description::fromTable("times","dataname", "description", "moreinfo", "uri", $named);
        $desc instanceof Description;
        return $desc;
    }
    
    
    
    public static  function GetFutureTimesDescriptions() 
    {
        $descs = self::GetTimesDescriptions() ;
        
        $result = new Descriptions();
        
        foreach ($descs->Descriptions() as $desc) 
        {
            if ($desc->DataName() == "1990") continue;
            if ($desc->DataName() == "1975") continue;
            
            if ($desc->DataName() > 2000)
                $result->Add($desc);
        }
        
        
        return $result;
    }
    
    
    
    public static  function CombinationsSingleLevel($delim="_") 
    {
        
        $result = array();
    
        foreach (self::GetScenarios()  as $scenario) 
        {
            
            foreach (self::GetModels() as $model) 
            {
                foreach (self::GetTimes() as $time) 
                {
                    $combo = $scenario . $delim . $model . $delim . $time;
                    $result[$combo] = $combo;

                }


            }
            
        }
        
        return $result;
        
        
    }
    
    public static  function CombinationsMultiLevel($delim="_") 
    {
        
        $result = array();
    
        foreach (self::GetScenarios()  as $scenario) 
        {
            $result[$scenario] = array();
            foreach (self::GetModels() as $model) 
            {
                $result[$scenario][$model] = array();
                foreach (self::GetTimes() as $time) 
                {
                    $combo = $scenario . $delim . $model . $delim . $time;
                    $result[$scenario][$model][$time] = $combo;
                }
            }
        }
        
        return $result;
        
        
    }
    
    
    
    
    
    
}

?>
