<?php

class SearcherNames extends Action {

    public function __construct() {
        parent::__construct();
        $this->ActionName(__CLASS__);
        $this->FinderName("SearcherFinder");

    }


    public function __destruct() {
        parent::__destruct();
    }


    /*
     *
     * @return array ActionClassname Search Actions for Finders
     */
    public function Execute()
    {
        // look into the finders and return only those that have a 
        $result = array_keys(FinderFactory::ActionsNamed("Search")); // just action names

        $this->Result($result);

        return $result;
    }


}

?>
