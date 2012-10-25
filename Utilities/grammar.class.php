<?php
class grammar {

    /* given a noun, return either "a noun" or "an noun" */
    public static function IndefiniteArticle($noun) {
        if (in_array($noun[0], array('a','e','i','o','u'))) {
            return "an " . $noun;
        } else {
            return "a " . $noun;
        }
    }

}
?>