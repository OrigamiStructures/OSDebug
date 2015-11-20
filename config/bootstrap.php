<?php

/* 
 * Copyright 2015 Origami Structures
 */

/**
 * Define the constants below when using this plug-in in a non-cakephp environment
 * 
 * The DS constant should be the platform's directory separator, normally returned by the php
 * constant DIRECTORY_SEPARATOR
 * 
 * The ROOT constant is the path to the folder CONTAINING the plugins folder in which 
 * OSDebug is contained.
 * 
 * Alternatively, you can include the single OSDebug.php file in the src/Lib directory and
 * not use this bootstrap at all.
 *  
 */
if(!defined('DS')){
    define('DS', DIRECTORY_SEPARATOR);
}
if(!defined('ROOT')){
    $root = explode(DS, $_SERVER['REQUEST_URI']);
    define('ROOT', $_SERVER['DOCUMENT_ROOT'].DS.$root[1]);
}
if(class_exists('Cake\Core\App')){
    require ROOT.DS.'plugins'.DS.'OSDebug'.DS.'src'.DS.'Lib'.DS.'OSDebug.php';
} else {
    require ROOT.DS.'plugins'.DS.'OSDebug'.DS.'src'.DS.'Lib'.DS.'OSDebug_simple.php';
}