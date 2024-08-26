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
		if ($locale !== false) $r = setlocale(LC_ALL, $locale);
		if ($r === false) {
			$conf = "conf/locl.php";
			if (file_exists($conf)) {
				include($conf);
				$this->locale = $locl_locale;
				$this->lang   = $locl_lang;
				setlocale(LC_ALL, $this->locale);
			} else {
				setlocale(LC_ALL, "fr_FR.UTF-8");
			}	
		}
		$this->lc = (object) localeconv();
		setlocale(LC_ALL, "C");
	}

	function type_detect($val) {
		if (is_float($val))          return "float";
		if (is_numeric($val))        return "numeric";
		if (substr($val, -1) == "%") return "percentage";
		if (is_date($val))			 return "date";
		return "string";
	}

	/**
	 * Generic formatter to transform a value according to the locale
     * @param if $val is :
     * - a float, it is formatted according to the locale decimal point and the thousand separator w/ n
     * - a numeric, it is formatted as an integer value w/ thousand separator
     * - a %age, it is formated as wether a float or an int + % apended 
	 * - no transformation in any other case. 
	 */
	function format($val) {
		$fmt = $this->type_detect($val);
		switch ($fmt) {
		case "float":
			$v = number_format($val, 1, $this->lc->decimal_point, $this->lc->thousands_sep);   
			if (substr($v, -2) == $this->lc->decimal_point . "0") $v = substr($v, 0, -2);
			return $v;  
		case "numeric":
			return number_format($val, 0, $this->lc->decimal_point, $this->lc->thousands_sep);
		case "percentage":
			return $this->format((float) substr($val, 0, -1)) . "%"; 
		case "date":
		case "string":
		default:
			return $val;
		}
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
