<?php
/**
 * find handlers for Data objects
 *      displayers for Data objects
 */
class DataFactory {


    /**
     *
     * @param type $dataName 
     */
    public static function DataFor($dataName)
    {

        

    }

    /**
     *
     * @param Data $data .. subclass of aData
     * @return string
     */
    public static function DisplayFor(Data $data)
    {

        $className = get_class($data);

        switch ($className) {
            case "SpatialDescriptionData":

                break;

            default:
                break;
        }


        return "";

    }
   
}
?>