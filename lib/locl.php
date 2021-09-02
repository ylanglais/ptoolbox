<?php

/**
 * locl is a quick ack to format data (not dates yet) according to locale 
 */
class locl {
	/**
     * Constructor can handle a specific locale, default is set to fr_FR.UTF-8
     */
	function __construct($locale = false) {
		$r = false;
		if ($locale !== false) {
        	$r = setlocale(LC_ALL, $locale);
		}
		if ($r === false) {
			setlocale(LC_ALL, "fr_FR.UTF-8");
		}
		$this->lc = (object) localeconv();
		setlocale(LC_ALL, "C");
	}

	/**
	 * Generic formatter to transform a value according to the locale
     * if $val is :
     * - a float, it is formatted according to the locale decimal point and the thousand separator w/ n
     * - a numeric, it is formatted as an integer value w/ thousand separator
     * - a %age, it is formated as wether a float or an int + % apended 
	 * - no transformation in any other case. 
	 */
	function format($val) {
		if (is_float($val)) {
			$v = number_format($val, 1, $this->lc->decimal_point, $this->lc->thousands_sep);   
			if (substr($v, -2) == $this->lc->decimal_point . "0") $v = substr($v, 0, -2);
			return $v;  
		} else if (is_numeric($val)) {
			return number_format($val, 0, $this->lc->decimal_point, $this->lc->thousands_sep);
		} else if (substr($val, -1) == "%") {
			return $this->format((float) substr($val, 0, -1)) . "%"; 
		}
		/*** 
		else if (is_date($v)) {
			$dt = new DateTime($v);
			return 
		}
		***/
		return $val;    
	}
	/**
	 * Generic formatter to transform a value according to the locale as a currency (w/o ccy sign) with respect to number of decimals required
	 */
	function ccy($val, $digits = 2) {
		if (is_float($val)) {
			$v = number_format($val, $digits, $this->lc->decimal_point, $this->lc->thousands_sep);   
			return $v;
		}
		return $val;  
	}
}


?>
