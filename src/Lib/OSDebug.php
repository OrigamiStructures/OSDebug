<?php

/* 
 * PHP Debugger function
 * for cakePHP systems
 * Copyright 2015 Origami Structures
 */

if (!function_exists('osd')) {
    function osd($var = NULL, $label = NULL, $stacktrace = FALSE) {
        echo OSDebug::osd($var, $label, $stacktrace);
    }
}

if (!function_exists('osdLog')) {
    function osdLog($var, $title, $stacktrace = FALSE, $message = FALSE) {
        echo OSDebug::osLog($var, $title, $stacktrace = FALSE, $message = FALSE);
    }
}

class OSDebug{
    public static function osd($var = NULL, $label = NULL, $stacktrace = FALSE) {
        return "Hey, I'm osDebug";
    }
    
    public static function osLog($var, $title, $stacktrace = FALSE, $message = FALSE) {
        return "Hey, I'm osLog";
    }
}
