<?php

/* 
 * PHP Debugger function
 * for cakePHP systems
 * Copyright 2015 Origami Structures
 */

if (!function_exists('osd')) {
    function osd($var, $label = NULL, $stacktrace = TRUE) {
        $osdebug = new OSDebug;
        echo $osdebug->osd($var, $label, $stacktrace);
    }
}

/**
 *  This will log to whatever log is configure to hand level 'debug'
 */
if (!function_exists('osdLog')) {
    function osdLog($var, $title, $stacktrace = TRUE, $message = FALSE) {
        echo OSDebug::osLog($var, $title, $stacktrace, $message);
    }
}

if (!function_exists('sql')) {
    function sql($query, $label = NULL, $stacktrace = FALSE) {
        $osdebug = new OSDebug;
        echo $osdebug->sql($query, $label, $stacktrace);
    }
}

use Cake\Error\Debugger;
use Cake\Core\Configure;
use Cake\View\ViewBlock;
use Cake\Log\Log;

class OSDebug{
    
    public $view_block = "";
    
    public function __construct() {
        $this->view_block = new ViewBlock;
    }
    
    public function osd($var, $label = NULL, $stacktrace = TRUE) {
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
        
        $debug_link = ''; //"<a $debug_link class=\"showDebug\">  Show  </a>";
		$debug_button = ''; //"<button style=\"font-size:50%; padding:0.25rem;\">$debug_button</button>"

		echo "<div class=\"cake-debug-output cake-debug\">";
		if ($label) {
			echo "<h3 class=\"cake-debug\">{$debug_button}$label"
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

	/**
	 * Debug to a log
	 * 
	 * Will go to which ever log handles level = debug
	 * 
	 * @param type $var
	 * @param string $title
	 * @param type $stacktrace unused
	 * @param string $message
	 * @return type
	 */
    public static function osLog($var, $title = FALSE, $stacktrace = FALSE, $message = FALSE) {
		$val = chr(13).chr(13) . self::_format($var) . chr(13).chr(13);
		if ($title){
			$title = chr(13).chr(13) . $title;
		}
		if ($message) {
			$message = chr(13).chr(13) . $message;
		}
 		return Log::write('debug', $title . $message . $val, ['config' => 'osd']);
    }
	
	public function sql($query, $label, $trace) {
		$sql = \Cake\Utility\Text::wordWrap($query->sql(), 80);
		$values = $query->valueBinder()->bindings();
		$sql = $this->popuateSql($sql, $values);
		
		$values = ['sql' => $sql, 'bindings' => $values];
		$this->osd($values, $label, $trace);
	}
	
	public function popuateSql($sql, $values) {
		preg_match_all('/(:c\d+)/', $sql, $match);
		foreach ($match[0] as $identifier) {
			$sql = str_replace($identifier, $values[$identifier]['value'], $sql);
		}
		return $sql;
	}

	/**
     * Converts to string the provided data so it can be logged. The context
     * can optionally be used by log engines to interpolate variables
     * or add additional info to the logged message.
     *
     * @param mixed $data The data to be converted to string and logged.
     * @param array $context Additional logging information for the message.
     * @return string
     */
    public static function _format($data, array $context = [])
    {
        if (is_string($data)) {
            return $data;
        }

        $object = is_object($data);

        if ($object && method_exists($data, '__toString')) {
            return (string)$data;
        }

        if ($object && $data instanceof JsonSerializable) {
            return json_encode($data);
        }

        return print_r($data, true);
    }

}
