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
    function osdLog($var, $title, $stacktrace = FALSE, $message = FALSE) {
        return OSDebug::osLog($var, $title, $stacktrace, $message);
    }
}

if (!function_exists('osdTime')) {
	function osdTime() {
		return new OSDTImer();
	}
}

if (!function_exists('whois')) {
	function whois($obj, $label = null) {
        $osdebug = new OSDebug;
        echo $osdebug->whois($obj, $label);
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
	
    public function osd($var, $label = NULL) {
        if (!Configure::read('debug')) {
			return;
		}
		//set variables
		$ggr = Debugger::trace(['start' => 2]);
		$line = preg_split('/[\r*|\n*]/', $ggr);
		$traceKey = uniqid();
        $location = $line[0];
        
        $trace_link = "onclick=\"document.getElementById('$traceKey')";
        $trace_link .= ".style.display = (document.getElementById('$traceKey').style.display == ";
        $trace_link .= "'none' ? '' : 'none');\"";
        
        $line_style = "\"font-size:70%; font-style:normal; margin-left:1em; "
				. "cursor:pointer; text-decoration:underline;\"";
                
		$label = is_null($label) ? '' : "<p>$label</p>";

		echo $this->debugDiv('open');
		echo "<h6 class=\"cake-debug\"><span style=\"font-size: 125%;\">$label</span>"
					. "<span $trace_link style=$line_style>"
					. "<strong>$location</strong></span></h6>";
		echo "<pre id=\"$traceKey\" "
					. "style=\"display:none; font-size: .75em; "
					. "line-height: 1; margin-bottom: 1em; \">$ggr</pre>";
        self::debug($var);
		echo $this->debugDiv('close');
    }
		
	private function debugDiv($mode = 'open') {
		if (strtoupper($mode) === 'OPEN') {
			return "<div style=\"margin-left:1em; padding:.5em; border:thin gray solid; width:75%; background-color: #ffa50080; \" class=\"cake-debug-output cake-debug\">";
		} else {
			return "</div>";
		}
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
		if($stacktrace) {
			ob_start();
			echo chr(13).chr(13) . Debugger::trace(['start' => 2]);
			$trace = ob_get_contents();
			ob_end_clean();
		} else {
			$trace = '';
		}

		$val = chr(13).chr(13) . self::_format($var) . chr(13).chr(13);
		if ($title){
			$title = chr(13).chr(13) . $title;
		}
		if ($message) {
			$message = chr(13).chr(13) . $message;
		}
 		return Log::write('debug', $title . $message . $trace . $val, ['config' => 'osd']);
    }
	
	public function sql($query, $label, $trace) {
        if (!Configure::read('debug')) {
			return;
		}
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
	
	public function whois($obj, $label = null) {
                
		list($namespace, $class) = namespaceSplit(get_class($obj));
		$hash = spl_object_hash($obj);
		$var = "$class - $hash ($namespace)";
		
		$this->osd($var, $label);

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
		
		if ($object && $data instanceof Query) {
			return sql($data);
		}

        if ($object && $data instanceof JsonSerializable) {
            return json_encode($data);
        }

        return print_r($data, true);
    }

}

/**
 * Timer class
 * 
 * Simplifies the process of testing time-to-process.
 * Can handle multiple named timers and calculate between 
 * mixes start/stops of named timers. 
 */

class OSDTImer {
	protected $start = [];
	protected $end = [];
	
	public function start($index = 0) {
		$this->start[$index] = microtime(TRUE);
		return $this->start[$index];
	}
	
	public function end($index = 0) {
		$this->end[$index] = microtime(TRUE);
		return $this->end[$index];
	}
	
	/**
	 * Output the time interval
	 * 
	 * Providing no index will calc the default (zeroth) interval 
	 * Providing an index will calc that recorded timer 
	 * Providing a second (alt_end) index will calculate the time 
	 *	between the start of the first and the end of the second.
	 * 
	 * @param string $index
	 * @param string $alt_end
	 * @return string
	 */
	public function result($index = 0, $alt_end = FALSE) {
        if (!Configure::read('debug')) {
			return;
		}
		$duration = $this->interval($index, $alt_end);
		if ($duration < 1) {
			$duration = ($duration * 1000) . ' miliseconds';
		} else {
			$duration .= ' seconds';
		}
		return "Timer #$index = $duration";		
	}
	
	/**
	 * Get the interval between to time markers as a float
	 * 
	 * @param string $index
	 * @param string $alt_end
	 * @return float
	 */
	public function interval($index = 0, $alt_end = FALSE) {
		if ($alt_end !== FALSE) {
			$this->validateEnd($alt_end);
			$concat = $index . '->' . $alt_end;
			if (isset($this->start[$index])) {
				$this->start[$concat] = $this->start[$index];
				$this->end[$concat] = $this->end[$alt_end];
			}
			$index = $concat;
		}
		if (isset($this->start[$index])){
			$this->validateEnd($index);
		} else {
			return "The index '$index' is unused. No timer result available";
		}
		return $this->end[$index] - $this->start[$index];
	}
	
	/**
	 * Set an end value if it's not already
	 * 
	 * Insures result() before end() yields a duration 
	 * while eliminating fussy insistance of end() 
	 * for simple cases of on-the-fly checking.
	 * 
	 * @param string $index
	 */
	protected function validateEnd($index) {
		if (!isset($this->end[$index])) {
			$this->end($index);
		}
	}
	
	/**
	 * clear all the timers
	 */
	public function reset() {
		$this->start = $this->end = [];
	}
	
	public function hasIndex($index) {
		return in_array($index, array_keys($this->start));
	}
	
	/**
	 * Provides a comprensive debug report
	 * 
	 * Can now substitute for multiple result() calls by 
	 * showing start, end, duration for every key that has 
	 * a recorded end. Shows all know data in any case. 
	 * 
	 * @return array
	 */
	public function __debugInfo() {
		$keys = array_keys($this->start);
		foreach ($keys as $key) {
			$end = isset($this->end[$key]) ? $this->end[$key] : FALSE;
			$output[$key] = [
				'start' => $this->start[$key],
				'end' => ($end !== FALSE ? $end : '*'), 
				'duration' => ($end !== FALSE ? $end - $this->start[$key] : '*'),
			];
		}
		return $output;		
	}
	
}