<?php

require_once("lib/dbg_tools.php");
/**
 * This class is intended to use a csv as an in ram data cache
 *
 * Example:<pre>
 *	$str = "A;B;C;D\n1;2;3;4\na;b;c;d\n";
 *	$in = "test-csv.in";
 *
 *	file_put_contents($in, $str);
 *
 *	$cr = new cachecsv($in, "A", ["B", "D"]);
 *	foreach ([1, "a", "A"] as $k) {
 *		$v = $cr->get($k, "B");
 *		print(" $k -> ". ($v === false ? "false" : $v) . "\n"); 
 *	}	
 *	$cr = new cachecsv($in, "A", [2, 3]);
 *	foreach ([1, "a", "A"] as $k) {
 *		$v = $cr->get($k, "C");
 *		print(" $k -> ". ($v === false ? "false" : $v) . "\n"); 
 *	}	
 *	$cr = new cachecsv($in, "A");
 *	foreach ([1, "a", "A"] as $k) {
 *		$v = $cr->get($k, "D");
 *		print(" $k -> ". ($v === false ? "false" : $v) . "\n"); 
 *	}	
 *	</pre>
 */
class cachecsv {
	/** 
	 *	@param file  		filename
	 *  @param key   		name of the id column
	 *  @param columns 		list of columns to cache (array of column {numbers, names or empty = all columns}) 
	 *  @param sep  		csv field separator
	 *	@param del  		csv field delimitor;
	 *	@param field_list 	use field names as header instead of reading it from file (1st line treated as data)
	 */
	function __construct($file, $key, $columns = [], $sep = ";", $del = '"', $field_list = []) {
		if (!file_exists($file)) {
			_err("file $file doesn't exist");
			return;
		}
		#
		#
		if (($fh = fopen($file, "r")) === false) {
			_err("file $f iledoesn't exist");
			return;
		}
			
		$this->fl   = [];
		$this->data = [];
		#
		#
		if ($field_list == []) {
			$field_list = fgetcsv($fh, 0, $sep, $del);
		}
		foreach($field_list as $n => $v) {
			$this->fl[trim($v)] = $n;
		}
		#
		# Check if we actually Have the key:
		if (!array_key_exists($key, $this->fl)) {
			print_r($this->fl); print("\n");
			_err("$key is not a colmns of $file");
			$this->fl = $this->data = false;
			return;
		}
		$cols = [];
		$cnam = [];
		foreach ($this->fl as $c => $n) {
			if ($columns == [] || in_array($c, $columns) || in_array($n, $columns)) {
				array_push($cols, $n);
				$cnam[$n] = $c;
			}
		}
	 	if (!is_int($key)) {
			$k = $this->fl[$key];
		} else {
			$k = $key; 
		}
		#
		#
		while (!feof($fh)) {
			$a  = fgetcsv($fh, 0, $sep, $del);
			if ($a === false) break;
			$kv = $a[$k];
			if (array_key_exists($kv, $this->data)) {
				_err("$key is not unique");
				$this->fl = $this->data = false;
			}
			foreach ($cols as $foo => $n) {
				$this->data[$kv][$cnam[$n]] = $a[$n];
			}
		}
		fclose($fh);
	}

	/**
     * 	Retrieve field (or fields) corresponding to id/key within the csv
	 *  @param $kval 	the value of the key searched for
	 * 	@param $field	the name of the requested field or all fields of the line if false
	 *  @return false	if index value not found,<br/>
	 *          field value corresponding to index value<br/>
	 *			field list corresponding to index value       
     */
	function get($kval, $field = false) {
		if ($this->data === false)                         return false;
		if (!array_key_exists($kval, $this->data))         return false;
		if ($field === false)                              return $this->data[$kval];
		if (!array_key_exists($field, $this->data[$kval])) return false;
		return $this->data[$kval][$field];
	}
}
/**
 * a est fonction for cachecsv class
 */ 
function cachecsv_test() {
	$str = "A;B;C;D\n1;2;3;4\na;b;c;d\n";
	$in = "test-csv.in";

	file_put_contents($in, $str);

	$cr = new cachecsv($in, "A", ["B", "D"]);
	foreach ([1, "a", "A"] as $k) {
		$v = $cr->get($k, "B");
		print(" $k -> ". ($v === false ? "false" : $v) . "\n"); 
	}	
	$cr = new cachecsv($in, "A", [2, 3]);
	foreach ([1, "a", "A"] as $k) {
		$v = $cr->get($k, "C");
		print(" $k -> ". ($v === false ? "false" : $v) . "\n"); 
	}	
	$cr = new cachecsv($in, "A");
	foreach ([1, "a", "A"] as $k) {
		$v = $cr->get($k, "D");
		print(" $k -> ". ($v === false ? "false" : $v) . "\n"); 
	}	
}

