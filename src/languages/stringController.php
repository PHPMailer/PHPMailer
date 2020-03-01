<?php
/**
 * Strings Controller
 * @author Juan David Rueda <jdruedaq@gmail.com>
 */

class stringController
{
    /**
     * @return string return language code
     */
    private function langDetect()
    {
        $lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);             // Get Browser Lang
        $enabledLang = ['es', 'en'];                                                     // Enable Languages Array
        $lang = in_array($lang, $enabledLang) ? $lang : 'en';                            // If is enabled lang is assigned else default lang is english

        return $lang;
    }

    /**
     * Include lang strings
     */
    public function langStrings()
    {
        $lang = $this->langDetect();                                                       // Detect Lang
        require_once "lang-{$lang}.php";                                                   // Get Lang PHP File with strings
    }
}
