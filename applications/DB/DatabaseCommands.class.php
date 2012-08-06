<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DatabaseCommands
 *
 * 
 * Read and write commands to database
 * 
 */
class DatabaseCommands extends Object
{
    
    // $this->ActionsTableName('command_action');
    

    public static function QueueID() 
    {
        return configuration::CommandQueueID();
    }

    public static function ActionsTableName() 
    {
        return   "command_action";

    }
     

    /**
     * Add new Action to queue or update the current action
     * 
     * @param CommandAction $cmd
     * @return null 
     */
    public static function CommandActionQueue(CommandAction $cmd) 
    {
        
        //file_put_contents('/tmp/afakes.txt', print_r($cmd,true)."\n",FILE_APPEND);
        
        $data = base64_encode(serialize($cmd));
        
        
        // check to see if we already have it.
        $w = array();
        $w["queueid"]  = self::QueueID();
        $w["objectid"] = $cmd->ID();
        
        $count = DBO::Count(self::ActionsTableName(), $w);
        
        if (is_null($count)) 
        {
            DBO::LogError(__METHOD__."(".__LINE__.")","Failed to count Command Actions \n w = ".print_r($w,true)."\n" );
            return null;
        }
        
        DBO::LogError(__METHOD__."(".__LINE__.")"," counting CDCMa ction count = ".  $count);
        
        
        if ($count > 0)
        {
            
            $uv = array();
            $uv['data']           = $data;
            $uv['status']         = $cmd->Status();
            $uv['execution_flag'] = $cmd->ExecutionFlag();
            
            $updateCount = DBO::SetArray( self::ActionsTableName(),$uv, "objectid  = ".util::dbq($cmd->ID()) );
            
            if ($updateCount != 1 )
            {
                DBO::LogError(__METHOD__."(".__LINE__.")","Failed to Update Command \n $uv = ".print_r($uv,true)."\n updateCount = ".print_r($updateCount,true)."\n" );
                return null;
            }
            
            
            return  $cmd->ID(); // will hold the row id of the object that was just updated.

        }
        
        
        // Queueed Action Does not exists so Insert it
        
        $ins = array();
        $ins['queueid']        = self::QueueID();
        $ins['objectid']       = $cmd->ID();
        $ins['data']           = $data;
        $ins['status']         = $cmd->Status();
        $ins['execution_flag'] = $cmd->ExecutionFlag();

        $insert_result = DBO::InsertArray(self::ActionsTableName(), $ins);
        
        if (is_null($insert_result) || !is_numeric($insert_result))
        {
            DBO::LogError(__METHOD__."(".__LINE__.")","Failed to Insert Command \n $ins = ".print_r($ins,true)."\n insert_result = ".print_r($insert_result,true)."\n" );
            return null;
        }
        
        return $insert_result;
        
    }

    
    public static function CommandActionValue($src,$valueName) 
    {
        
        if (is_null($src) || is_null($valueName))
        {
            DBO::LogError(__METHOD__."(".__LINE__.")","src or value passed as null  src = [{$src}]  valueName = [{$valueName}]  \n" );
            
            return null;
        }
        
        $qn = array();
        $qn['objectid'] = ($src instanceof CommandAction) ? $src->ID() : $src;
        $qn['queueid']  = self::QueueID();
        $qn[$valueName]  = null;
        
        $result = DBO::QueryArray(self::ActionsTableName(), $qn, 'objectid');
        
        if (is_null($result))
        {
            DBO::LogError(__METHOD__."(".__LINE__.")","QueryArray Failed \n qn = ".print_r($qn,true)."\n");
            return null;
        }
        
        return array_util::Value(util::first_element($result), $valueName, null);
        
    }
    
    
    
    public static function CommandActionStatus($src) 
    {
        return self::CommandActionValue($src,'status');
    }
    
    
    
    public static function CommandActionRead($commandID) 
    {
        
        if (is_null($commandID)) 
        {
            echo "commandID is null\n";
            return null;
        }
        
        try {
            
            $data = self::CommandActionValue($commandID,'data');
            
            if (is_null($data)) return null;

            $object = unserialize(base64_decode($data));
            $object instanceof CommandAction;
            
            
        } catch (Exception $exc) {
             return $exc;
        }
        
        return  $object;        
        
    }
    
    
    
    /**
     * Pass true into this function to really do it.
     * 
     * @param type $really
     * @return null 
     */
    public static function CommandActionRemoveAll($really = false) 
    {
        if (!$really) return;
        
        $delete = DBO::Delete(self::ActionsTableName(), "queueid = ".util::dbq(self::QueueID()) );
        
        if (is_null($delete) )
        {
            DBO::LogError(__METHOD__."(".__LINE__.")","Failed to Remove All Commands \n queueid = ".util::dbq(self::QueueID())."\n" );
            return null;
        }
        
        return  $delete;
    }
    
    
    public static function CommandActionRemove($commandID) 
    {
        
        $commandID = ($src instanceof CommandAction) ? $id->ID() : $commandID;

        $where = " queueid = ".util::dbq(self::QueueID())." and objectid = ".util::dbq($commandID);
        
        $num_removed = DBO::Delete(self::ActionsTableName(),$where );
        if (is_null($num_removed) )
        {
            DBO::LogError(__METHOD__."(".__LINE__.")","Failed to Remove  Command  commandID = $commandID \n where = [{$where}] \n" );
            return null;
        }
        
        
        return  $num_removed;
    }
    
    
    public static function CommandActionListIDs() 
    {
        
        $qn = array();
        $qn['objectid'] = null;
        $qn['queueid'] = self::QueueID();

        
        $result = DBO::QueryArray(self::ActionsTableName(), $qn, 'objectid');
        
        if (is_null($result) )
        {
            DBO::LogError(__METHOD__."(".__LINE__.")","Failed to Get List of Commands \n qn = ".print_r($qn,true)." \n" );
            return null;
        }
        
        return array_keys($result);
        
    }
    
    
    public static function CommandActionCount() 
    {
        $list = self::CommandActionListIDs();
        
        if (is_null($list) )
        {
            DBO::LogError(__METHOD__."(".__LINE__.")","Failed to Get List for Counting of Commands \n" );
            return null;
        }

        return count($list);
        
        
    }
 
    
    public static function CommandActionExecutionFlag($id = null) 
    {
        
        
        $qid = configuration::CommandQueueID();
        
        if (!is_null($id)) 
            $q = "select objectid,execution_flag from ".self::ActionsTableName()." where queueid='{$qid}' and objectid = '{$id}'"; 
        else
            $q = "select objectid,execution_flag from ".self::ActionsTableName()." where queueid='{$qid}'";    
        
            
        $result = DBO::Query($q);
        
        $efresult = null;
        
        if (!is_null($id))
        {
            $first_row = util::first_element($result);
            $efresult = $first_row['execution_flag'];
        }
        else
        {
            $efresult = array();
            foreach ($result as $row) 
                $efresult[$row['objectid']] = $row['execution_flag'];
            
        }
        
        unset($result);
        
        return $efresult;
    }    
    
    
}

?>
