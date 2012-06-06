<?php

/**
 * INTERFACE: iOutput
 *        
 */
interface iOutput {


    public function OutputName();

    /** 
     * Where is the data for the content (& styles) comning from
     */
    public function Source();

    /**
     * Can store CSS or other syle information that maybe need before the Result can be displayed
     */
    public function Head();

    /**
     * What is to be to be displayed
     *
     */
    public function Content();


    /**
     * Title tag
     *
     */
    public function Title();


    /**
     * PreoProcess
     * - any procxessing that has to be done beforew thoputput is available
     */
    public function PreProcess();


    /**
     * Number of seconds to refresh page
     *  
     */
    public function Refresh();

    /**
     * Used to hold IDs that can be passed to and from the action and
     *
     * @return type
     */
    public function IDs();

}

class Output extends Object implements iOutput{

    public function __construct() {
        parent::__construct();        
        
    }
    
    public function __destruct() {
        parent::__destruct();
        
    }


    /**
     * Where is the data for the content (& styles) coming from
     *
     * @param mixed $arg1 data source
     *
     */
    public function Source() {
        if (func_num_args() == 0)
        {
            $result = $this->getProperty();
            $result instanceof Object;
            return $result;
        }

        $result = $this->setProperty(func_get_arg(0));
        $result instanceof  Object;

        return $result;
    }




    /**
     * Can store CSS or other syle information that maybe need before the Result can be displayed
     */
    public function Head()
    {
        throw new Exception("{$this->Name()} Style has not been implemented");
    }
    
    /**
     * What is to be to be displayed
     *
     */
    public function Content()
    {
        throw new Exception("{$this->Name()} Content has not been imnplemented");
    } 

    /**
     * Title Tag
     *
     */
    public function Title()
    {
        throw new Exception("{$this->Name()} Title has not been imnplemented");
    }


    /**
     * WIll be left blank here
     * - a subclass can override this for things to be done
     */
    public function PreProcess()
    {


    }

    public function OutputName() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }

    public function Refresh() 
    {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }

    /**
     * Used to hold IDs that can be passed to and from the action and
     *
     * @return type
     */
    public function IDs()
    {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }


    
}

?>
