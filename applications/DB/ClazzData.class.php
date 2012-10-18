<?php

/**
 *
 * Connect, disconnect and data flow to and from Class info bucket
 *
 */
class ClazzData extends Object {

    /**
     *
     * For any class, return it's common name
     *
     * @param type $pattern
     * @return type
     */
    public static function clazzCommonName($clazz = "")
    {
        // bail if we didn't get a class
        if ($clazz == '') return '';

        // search in lower case
        $clazz = strtolower($clazz);

        // too lazy to look up how to make this static
        $known_names = array(
            'mammalia' => 'mammals',
            'aves' => 'birds',
            'reptilia' => 'reptiles',
            'amphibia' => 'amphibians'
        );

        // if the class we were asked about is in our list of
        // kown names, return the corresponding name
        if (array_key_exists($clazz, $known_names)) {
            return $known_names[$clazz];
        }

        // if we got here, we don't have anything left to try.
        return "";
    }



}

?>