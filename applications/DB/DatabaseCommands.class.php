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
     
    public static function CommandActionQueue(CommandAction $cmd) 
    {
        
        
        // check to see if we already have it.
        $w = array();
        $w["queueid"]  = self::QueueID();
        $w["objectid"] = $cmd->ID();
        
        $count = DBO::Count(self::ActionsTableName(), $w);
        if ($count instanceof ErrorMessage)  
            return ErrorMessage::Stacked (__METHOD__,__LINE__,"Failed to count Command Actions \n w = ".print_r($w,true), true,$count);
        

        // the serialize data can become large so we are going to save it a s a file and then the size won't matter
        // use the  $cmd->ID() as the id / description
        
        
        if ($count > 0)
        {
            
            ErrorMessage::Marker("Updated the Command Action ");
            
            $uv = array();
            
            //$uv['data']           = $data;   //we were storing the data in the database now stor it on the filesystem 
            $uv['status']         = $cmd->Status();
            $uv['execution_flag'] = $cmd->ExecutionFlag();
            
            $updateCount = DBO::SetArray( self::ActionsTableName(),$uv, "objectid  = ".util::dbq($cmd->ID(),true) );
            
            if ($updateCount instanceof ErrorMessage)  
                return ErrorMessage::Stacked (__METHOD__,__LINE__,"Failed to Update Command \n $uv = ".print_r($uv,true)."\n updateCount = ".print_r($updateCount,true), true,$updateCount);
            
            
            DatabaseFile::RemoveFile($cmd->ID()); // remove this file from DB as we are going to update it
            
            $insertObject = DatabaseFile::InsertObject($cmd, $cmd->ID());
            if ($insertObject instanceof ErrorMessage)  
                return ErrorMessage::Stacked (__METHOD__,__LINE__,"Failed to insert Command Object ", true,$updateCount);
            
            
            
            return  $cmd->ID(); // will hold the row id of the object that was just updated.

        }
        
        
        ErrorMessage::Marker("CReated  the Command Action ");
        
        // Queueed Action Does not exists so Insert it
        
        $ins = array();
        $ins['queueid']        = self::QueueID();
        $ins['objectid']       = $cmd->ID();
        $ins['status']         = $cmd->Status();
        $ins['execution_flag'] = $cmd->ExecutionFlag();

        $insert_result = DBO::InsertArray(self::ActionsTableName(), $ins);
        if ($insert_result instanceof ErrorMessage)  
            return ErrorMessage::Stacked (__METHOD__,__LINE__,"Failed to Insert Command \n $ins = ".print_r($ins,true)."\n insert_result = ".print_r($insert_result,true), true,$insert_result);
        
        if (!is_numeric($insert_result))  
            return ErrorMessage::Stacked (__METHOD__,__LINE__,"Failed to Insert Command insert_result is not numeric \n insert_result = ".print_r($insert_result,true), true,$insert_result);
        
        $insertObject = DatabaseFile::InsertObject($cmd, $cmd->ID());
            if ($insertObject instanceof ErrorMessage)  
                return ErrorMessage::Stacked (__METHOD__,__LINE__,"Failed to insert Command Object ", true,$updateCount);

        
        
        return $cmd->ID();
        
        
    }
        
    
    public static function CommandActionValue($src,$valueName) 
    {
        
        if (is_null($src) || is_null($valueName))
            return new ErrorMessage(__METHOD__,__LINE__,"src or value passed as null  src = [{$src}]  valueName = [{$valueName}]  \n");
            
        $qn = array();
        $qn['objectid'] = ($src instanceof CommandAction) ? $src->ID() : $src;
        $qn['queueid']  = self::QueueID();
        $qn[$valueName]  = null;
        
        $result = DBO::QueryArray(self::ActionsTableName(), $qn, 'objectid');
        if ($result instanceof ErrorMessage)  
            return ErrorMessage::Stacked (__METHOD__,__LINE__,"QueryArray Failed \n qn = ".print_r($qn,true), true,$result);
        
        return array_util::Value(util::first_element($result), $valueName, null);
        
    }
    
    
    
    public static function CommandActionStatus($src) 
    {
        return self::CommandActionValue($src,'status');
    }
    
    
    
    public static function CommandActionRead($commandID) 
    {
        
        if (is_null($commandID) ) 
            return new ErrorMessage(__METHOD__,__LINE__,"commandID passed as NULL");
        
        $commandID = str_replace("_", ".", $commandID);
        
        try {
            
            $object = DatabaseFile::ReadObject($commandID);
            if ($object instanceof ErrorMessage) 
                return new ErrorMessage(__METHOD__,__LINE__,"Failed to read data for command ID {$commandID}".print_r($object,true));
            
            $object instanceof CommandAction;

            
        } catch (Exception $exc) {
            return new ErrorMessage(__METHOD__,__LINE__,"Failed to read data for command ID {$commandID} execption = ".$exc->getMessage());
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
        
        
        foreach (self::CommandActionListIDs() as $object_id)  
            DatabaseFile::RemoveFile($object_id);            // remove all files associated with Command Actions

        
        // remove the command actions 
        $delete = DBO::Delete(self::ActionsTableName(), "queueid = ".util::dbq(self::QueueID()) );
        if ($delete instanceof ErrorMessage)  
            return ErrorMessage::Stacked (__METHOD__,__LINE__,"Failed to Remove All Commands \n queueid = ".util::dbq(self::QueueID()), true,$delete);
        
        return  $delete;
    }
    
    
    public static function CommandActionRemove($commandID) 
    {
        
        $commandID = ($src instanceof CommandAction) ? $id->ID() : $commandID;

        $where = " queueid = ".util::dbq(self::QueueID())." and objectid = ".util::dbq($commandID);
        
        $num_removed = DBO::Delete(self::ActionsTableName(),$where );
        
        if ($num_removed instanceof ErrorMessage)  
            return ErrorMessage::Stacked (__METHOD__,__LINE__,"Failed to Remove  Command  commandID = $commandID \n where = [{$where}] \n", true,$num_removed);
                
        
        return  $num_removed;
    }
    
    
    public static function CommandActionListIDs() 
    {
        
        $qn = array();
        $qn['objectid'] = null;
        $qn['queueid'] = self::QueueID();

        
        $result = DBO::QueryArray(self::ActionsTableName(), $qn, 'objectid');
        if ($result instanceof ErrorMessage)  
            return ErrorMessage::Stacked (__METHOD__,__LINE__,"Failed to Get List of Commands \n qn = ".print_r($qn,true), true,$result);

        
        return array_keys($result);
        
    }
    
    
    public static function CommandActionCount() 
    {
        $result = self::CommandActionListIDs();
        
        if ($result instanceof ErrorMessage)  
            return ErrorMessage::Stacked (__METHOD__,__LINE__,"Failed to Get List for Counting of Commands \n", true,$result);

        return count($result);
        
        
    }
 
    
    public static function CommandActionExecutionFlag($id = null) 
    {
        
        $qid = configuration::CommandQueueID();
        
        if (!is_null($id)) 
            $q = "select objectid,execution_flag from ".self::ActionsTableName()." where queueid='{$qid}' and objectid = '{$id}'"; 
        else
            $q = "select objectid,execution_flag from ".self::ActionsTableName()." where queueid='{$qid}'";    
        
            
        $result = DBO::Query($q);        
        if ($result instanceof ErrorMessage)  
            return ErrorMessage::Stacked (__METHOD__,__LINE__,"Failed to Get Execution flag for command {$id}\n using sql = [$q]", true,$result);
        
        
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
