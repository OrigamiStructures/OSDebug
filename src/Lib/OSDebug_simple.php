<?php

/*
 * PHP Debugger function
 * for simple PHP systems
 * Copyright 2015 Origami Structures
 */


if (!function_exists('osd')) {
    function osd($var = NULL, $label = NULL, $stacktrace = FALSE) {
        return OSDebug::osDebug($var, $label, $stacktrace);
    }
}

if (!function_exists('osdLog')) {
    function osdLog($var, $title, $stacktrace = FALSE, $message = FALSE) {
        return OSDebug::osLog($var, $title, $stacktrace = FALSE, $message = FALSE);
    }
}

class OSDebug{
    public static function osDebug($var = NULL, $label = NULL, $stacktrace = FALSE) {
        return "Hey, I'm osDebug";
    }
    
    public static function odLog($var, $title, $stacktrace = FALSE, $message = FALSE) {
        return "Hey, I'm osLog";
    }
}
