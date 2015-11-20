<?php

/* 
 * PHP Debugger function
 * Copyright 2015 Origami Structures
 */

//namespace OSDebug\App\Lib;

if(!function_exists('osd')){
    function osd($param = NULL) {
        var_dump("Hey, I'm in osd!!");
        var_dump(__METHOD__);
    }
}
