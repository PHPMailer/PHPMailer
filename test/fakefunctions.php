<?php

if (!function_exists('idn_to_ascii')) {
    function idn_to_ascii()
    {
        return true;
    }
}

if (!function_exists('mb_convert_encoding')) {
    function mb_convert_encoding()
    {
        return true;
    }
}
