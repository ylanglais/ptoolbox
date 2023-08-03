<?php
require_once("lib/query.php");
require_once("lib/store.php");
/**
 * Static class to save / restore css parts from param.style table
 */
class style {
	private static $data = false;

	/**
     * Reload method is intended to flush cache of style data
	 */ 
	public  static function reload() {
		self::$data = false;
		$q = new query("select * from param.style");
		while ($o = $q->obj()) {
			$s[$o->key] = (object) [];
			$s[$o->key]->type  = $o->type; 	
			$s[$o->key]->value = $o->value; 	
		}		
		store::put("_s_t_y_l_e_", $s);
		self::$data = $s; 
	}

	/**
     * load style from cache (Store) or database
	 */
	private static function _load() {
		if (($s = store::get("_s_t_y_l_e_")) === false) {
			self::reload();
		} else {
			self::$data = $s;
		}
	}		

	/**
     * check if style cache has a key
	 * @param key 	the key to check
	 * @return 		false if key unknown, else true
	 */
	static public function has($key) {
		if (self::$data === false) self::_load();
		if (!is_array(self::$data) || array_key_exists($key, self::$data) === false) {
			return false;
		}
		return true;
	}
	/**
     * retrieve the value of a given key
	 * @param key 	the key to look for
	 * @return 		false if key is unknown, else the value of the key is returned (use has 1st w/ booleans).
	 */
	static public function value($key) {
		if (self::$data === false) self::_load();
		if (!is_array(self::$data) || array_key_exists($key, self::$data) === false) {
			return false;
		}
		if (self::$data[$key]->type == "color" && substr(($v = self::$data[$key]->value),0, 6) == "color(") {
			return self::value(substr($v, 6, -1));
		}
		return self::$data[$key]->value;
	}
	/**
     * retrieve the (css) type of a given key
	 * @param key 	the key to look for
	 * @return 		false if key is unknown, else the value of the key is returned (use has 1st w/ booleans).
	 */
	static public function type($key) {
		if (self::$data === false) self::_load();
		if (!is_array(self::$data) || array_key_exists($key, self::$data) === false) {
			return false;
		}
		return self::$data[$key]->type;
	}
	/**
     *  Generator for all colors
	 *  @return      colorname => color_value pairs one by one
	 * 
	 * 	@example 
	 *  # Color evaluations:
	 *  foreach (style::colors() as $k => $v) $$k = $v;
     */
	static public function colors() {
		if (self::$data === false) self::_load();
		$a = [];
		foreach (self::$data as $k => $d) {
			if ($d->type == "color") {
				yield $k => self::value($k);
				#dbg("$k => " . self::value($k));
			}
		}		
	}	
}

?>
