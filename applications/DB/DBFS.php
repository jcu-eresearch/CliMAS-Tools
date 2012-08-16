<?php

/**
 * CLASS: 
 *        
 * 
 *   
 */
class DBFS extends Object {
    //**put your code here
    
    private $database_connection = null;
    
    public function __construct($db_host = null,$db_database = null,$db_user = null,$db_password = null) { 
        parent::__construct();
        
        $this->Host(    configuration::DBFS_DB_HOST());
        $this->Database(configuration::DBFS_DB_DATABASE());
        $this->User(    configuration::DBFS_DB_USER());
        $this->Password(configuration::DBFS_DB_PASSWORD());

        if (!is_null($db_host))     $this->Host($db_host);
        if (!is_null($db_database)) $this->Database($db_database);
        if (!is_null($db_user))     $this->User($db_user);
        if (!is_null($db_password)) $this->Password($db_password);
        
        $this->database_connection = new database($this->Database(), $this->Host(), $this->User(), $this->Password());
        
    }
    
    public function DB() 
    {    
        return $this->database_connection;
    }
    
    public function __destruct() {    
        parent::__destruct();
        $this->database_connection->disconnect();
        
    }

    private function Host() {
        if (func_num_args() == 0) return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
        
    }

    private function Database() {
        if (func_num_args() == 0) return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
        
    }
    

    private function User() {
        if (func_num_args() == 0) return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
        
    }
    
    private function Password() {
        if (func_num_args() == 0) return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
        
    }
    
    
    

    public static function CreateTable($key = null) 
    {
        // if (is_null($key)) return new ErrorMessage(__METHOD__, null, "API Key for create table not passed");
        
        $DBFS = new DBFS(); $D = $DBFS->DB();
        
        $table = array();
        $table['file_unique_id'] = "varchar(50)";
        $table['mimetype'] = "varchar(100)";
        $table['data'] = "longblob";

        ErrorMessage::Marker($table);
        
        
        
        unset($D);
        
    }
    
    
    
}

?>
