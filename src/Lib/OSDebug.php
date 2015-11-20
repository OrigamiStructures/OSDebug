<?php

/* 
 * PHP Debugger function
 * Copyright 2015 Origami Structures
 */

//namespace OSDebug\App\Lib;

if(!function_exists('osd')){
    function osd($param = NULL) {
        if(class_exists('Cake\Core\App')){
            var_dump("Hey, I'm in CAKE");
        }
//        $classes = get_declared_classes();
//        foreach ($classes as $key => $class) {
//            echo "<p>$class</p>";
//        }
//        var_dump(get_declared_classes());
        var_dump("Hey, I'm in osd!!");
        var_dump(__METHOD__);
    }
}
