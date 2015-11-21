<?php

/* 
 * PHP Debugger function
 * for cakePHP systems
 * Copyright 2015 Origami Structures
 */

if (!function_exists('osd')) {
    function osd($var = NULL, $label = NULL, $stacktrace = FALSE) {
        $osdebug = new OSDebug;
        echo $osdebug->osd($var, $label, $stacktrace);
    }
}

if (!function_exists('osdLog')) {
    function osdLog($var, $title, $stacktrace = FALSE, $message = FALSE) {
        echo OSDebug::osLog($var, $title, $stacktrace = FALSE, $message = FALSE);
    }
}

use Cake\Error\Debugger;
use Cake\Core\Configure;
use Cake\View\ViewBlock;

class OSDebug{
    
    public $view_block = "";
    
    public function __construct() {
        $this->view_block = new ViewBlock;
    }
    
    public function osd($var = NULL, $label = NULL, $stacktrace = FALSE) {
		//set variables
		$ggr = Debugger::trace();
		$line = preg_split('/[\r*|\n*]/', $ggr);
		$traceKey = sha1($line[2]);
        $debKey = uniqid();
        $location = preg_replace("/^([\w]*\\\\)+/", "", $line[2]);
        
        $trace_link = "onclick=\"document.getElementById('$traceKey')";
        $trace_link .= ".style.display = (document.getElementById('$traceKey').style.display == ";
        $trace_link .= "'none' ? '' : 'none');\"";
        
        $debug_link = "onclick=\"document.getElementById('$debKey')";
        $debug_link .= ".style.display = (document.getElementById('$debKey').style.display == ";
        $debug_link .= "'none' ? '' : 'none');\"";
        
        $line_style = "\"font-size:70%; font-style:italic; margin-left:1em;";
        $line_style .= $stacktrace ? " cursor:pointer; text-decoration:underline;\"" : "\"";
        
        $button_style = "\"font-size:";
        
        $debug_button = "<a $debug_link class=\"showDebug\">  Show  </a>";

		echo "<div class=\"cake-debug-output\">";
		if ($label) {
			echo "<h3 class=\"cake-debug\"><button style=\"font-size:50%; padding:0.25rem;\">$debug_button</button>$label"
                    . "<span $trace_link style=$line_style><strong>$location</strong></span></h3>";
		} else {
            echo "<h3 class=\"cake-debug\"><span $trace_link style=$line_style><strong>$location</strong></span></h3>";
        }
		if ($stacktrace) {
			echo "<pre id=\"$traceKey\" style=\"display:none;\">$ggr</pre>";
		}
        self::debug($var);
		echo"</div>";
    }
    
    /**
     * Prints out debug information about given variable.
     *
     * Only runs if debug level is greater than zero.
     *
     * @param mixed $var Variable to show debug information for.
     * @param bool|null $showHtml If set to true, the method prints the debug data in a browser-friendly way.
     * @param bool $showFrom If set to true, the method prints from where the function was called.
     * @return void
     * @link http://book.cakephp.org/3.0/en/development/debugging.html#basic-debugging
     * @link http://book.cakephp.org/3.0/en/core-libraries/global-constants-and-functions.html#debug
     */
    public static function debug($var, $showHtml = null, $showFrom = true) {
        if (!Configure::read('debug')) {
            return;
        }

        $file = '';
        $line = '';
        $lineInfo = '';
        if ($showFrom) {
            $trace = Debugger::trace(['start' => 1, 'depth' => 2, 'format' => 'array']);
            $search = [ROOT];
            if (defined('CAKE_CORE_INCLUDE_PATH')) {
                array_unshift($search, CAKE_CORE_INCLUDE_PATH);
            }
            $file = str_replace($search, '', $trace[0]['file']);
            $line = $trace[0]['line'];
        }
        $html = <<<HTML
<pre class="cake-debug">
%s
</pre>
</div>
HTML;
        $text = <<<TEXT
%s
########## DEBUG ##########
%s
###########################

TEXT;
        $template = $html;
        if (PHP_SAPI === 'cli' || $showHtml === false) {
            $template = $text;
            if ($showFrom) {
                $lineInfo = sprintf('%s (line %s)', $file, $line);
            }
        }
        if ($showHtml === null && $template !== $text) {
            $showHtml = true;
        }
        $var = Debugger::exportVar($var, 25);
        if ($showHtml) {
            $template = $html;
            $var = h($var);
            if ($showFrom) {
                $lineInfo = sprintf('<span><strong>%s</strong> (line <strong>%s</strong>)</span>', $file, $line);
            }
        }
        printf($template, $var);
	}

    public static function osLog($var, $title, $stacktrace = FALSE, $message = FALSE) {
        return "Hey, I'm osLog";
    }
}
