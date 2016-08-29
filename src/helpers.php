<?php

if ( ! function_exists('saltedHash')) {
    function saltedHash($string)
    {
        return sha1(strtolower(Config::get('core.client.abbrev')).$string);
    }
}
