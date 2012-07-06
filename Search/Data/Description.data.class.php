<?php
/**
 * 
 *        
 * 
 *   
 */
class Description extends Data {
    
    
    public function __construct() {
        parent::__construct();
        $this->DataName(__CLASS__);

        $this->Filename();
        $this->Description();
        $this->Source();
        $this->MoreInformation();
        $this->URI();

    }
    
    public function __destruct() {

        parent::__destruct();
    }

    /**
     * Called with (null) return Filename<br>
     * Called with ($arg)    set Filename<br>
     *
     * @return string Filename
     *
     */
    public function Filename() {
        if (func_num_args() == 0)
        return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }

    /**
     * Called with (null) return Description<br>
     * Called with ($arg)    set Description<br>
     *
     * @return string Description
     *
     */
    public function Description() {
        if (func_num_args() == 0) return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }

    /**
     * Called with (null) return Source (string)<br>
     * Called with ($arg)    set value of property to $arg<br>
     *
     * @return string Source
     *
     */
    public function Source() {
        if (func_num_args() == 0) return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }


    /**
     * Called with (null) return MoreInformation<br>
     * Called with ($arg)    set MoreInformation<br>
     *
     * @return string
     *
     */
    public function MoreInformation() {
        if (func_num_args() == 0) return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }

    /**
     * Intened to be used to store ther URI / URL to connect toanother data source
     *
     * Called with (null) return URI<br>
     * Called with ($arg)    set URI<br>
     *
     * @return string URI
     *
     */
    public function URI() {
        if (func_num_args() == 0) return $this->getProperty();
        return $this->setProperty(func_get_arg(0));
    }

}
?>