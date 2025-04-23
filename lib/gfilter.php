<?php

class gfilter {
	function __construct($prov) {
		if (!is_object($prov)) $prov = new prov($prov);
		$this->prov = $prov;
		$flds = $prov->fields();

		$this->fields = [];
		foreach ($flds as $f) {
			$this->fields[$f] = $this->prov->datatype($f);
		}
		
	}

	function add($field, $value) {
	}


	function 

}

?>
