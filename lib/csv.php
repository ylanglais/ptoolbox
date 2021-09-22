<?php

require_once("lib/dbg_tools.php");

/**
 * class to store/retrieve data in csv format
 */
class csv {
	
	/** 
	 * Construct CSV from whether a file name, or a header:
	 * @param 	$file_or_header   must containe either a filename if any or a header ([ list of fields ])
	 * @param 	$sep              separator by defaut  ";"
	 * @param   $encl			  encloser	by default "'"
	 */ 
	function __construct($file_or_header, $sep = ";", $encl = '"') {
		$this->nlines  = 0;
		$this->nfields = 0;
		$this->hdr     = false;
		$this->flds    = false;
		$this->fnums   = false;
		$this->data    = [];

		if (is_array($file_or_header)) 
			$this->fields_set($file_or_header);
		else 
			$this->read($file_or_header, $sep, $encl);

		 #print_r($this->data);
	}

	/**
	 * Set/get header/fields
	 * @param $hdr	an array of column names
	 * @return $hrd 	the actual arrey of column names
	 */	
	private function fields_set(array $hdr = []) :array {
		if ($hdr == []) return $this->$hdr;
		$this->hdr   = $hdr;
		$this->flds  = [];
		$this->fnums = [];
		foreach ($this->hdr as $fld) {
			$this->fnums[$this->nfields] = $fld;
			$this->flds[$fld]            = $this->nfields++;
			#print(">>> $this->nfields: " . $this->fnums[$this->nfields] . "\n");
		}
		return $hdr;
	}

	/**
	 * Return array of field positions
	 */
	function field_positions(): array {
		return $this->flds;
	}

	/**
	 *  Return array of field list: 
	 */
	function field_list(): array {
		return $this->hdr;
	}

	/**
	 * return field/column count
	 */
	function nfields(): int {
		return $this->nfields;
	}

	/**
	 * return number of lines
	 */
	function nlines(): int {
		return $this->nlines;
	}

	/**
     * return data as an array of objects
	 */
	function data(): array {
		return $this->data;
	}

	/** 
	 * Check if csv has a column named $k
	 * @param $k	the column name to check
	 * @return false if no column is named $k, else return true
	 */
	function has_key(string $k): boolean {
		if (!key_exists($k, $this->flds)) return false;	
		return true;
	}
		
	/**
	 * Retrieve object at line $line or value of field $field (index OR name) of line $line:
	 * @param $line the line number (starting at 0)
	 * @param $field	may be a field number if int or a field name if string or unset/empty to get the whole line as an object 
	 * @return null	if empty or field num out of bounds or field is not a proper column name, the whole line as an object if $field is empty, the requested field if found
	 */
	function get(int $line, boolean|int|string $field = false): null|string|object {
		if ($this->nlines == 0) {
			err("empty file");
			return NULL;
		}
		if ($line < 0 || $line >= $this->nlines) {
			err("invalid line number ($line asked but from 0 to $this->nlines)");
			return NULL;
		}

		if ($field === false) return  $this->data[$line];

		if (is_int($field)) {
			if ($field < 0 || $field > $this->nfields) {
				err("invalid field number ($field asked but from 0 to $this->nfields)");
				return NULL;
			}
			return $this->data[$line]->{$this->fnums[$field]};
		}

		if (!$this->hdr || !$this->flds) {
			err("no field definition");
			return NULL;
		}
		if (!key_exists($field, $this->flds)) {
			err("$field is not a known field");
			return NULL;
		}
		return $this->data[$line]->$field;
	}

	function update($line, $data) {
		if ($this->nlines == 0) {
			err("empty file");
			return NULL;
		}
		if ($line < 0 || $line >= $this->nlines) {
			err("invalud line number ($line asked but from 0 to $this->nlines)");
			return NULL;
		}

		if (!$this->hdr || !$this->flds) {
			err("no field definition");
			return NULL;
		}
		
		foreach ($data as $k => $v) {
			if (!(key_exists($k, $this->flds))) {
				warn("$field is not a known field");
			} else {
				$this->data[$line][$this->$flds[$k]] = $v;
			}
		}
	}
	
	/** 
	 * Push object as a new line (if valid)
	 * @param $object	the new line
	 * @return false if no column defnition present else true;
	 */
	function push(object $obj): boolean {
		#
		# Check we have at list header & field list:
		if (!$this->hdr || !$this->flds) {
			err("no field definition");
			return false;
		}

		#
		# create a csv line array:
		foreach ($this->hdr as $f) {
			if (!key_exists($f, $obj))
				warn("invalid field \"$f\"");
			else
				$dat[$f] = $obj->$f;
		}

		#
		# Push the new line array at end of file:
		array_push($this->data, $dat);
		return true;
	}
	/**
	 * write csv as a file named $file
	 * @param $file	the destination filename
	 * @param $header	boolean to write header or not (default true)
	 * @param $sep as separator (default ;)
	 * @param $encl	encloser (default none)
	 * @return true if ok or false if an error occured
	 */
	function write(string $file, boolean $header = true, string $sep = ";", string $encl = ''): boolean {
		#
		# Check we have at list header & field list:
		if (!$this->hdr || !$this->flds) {
			err("no field definition");
			return false;
		}

		#
		# Open output file:
		if (($h = fopen($file, "w")) === false) {
			err("file '$file' cannot be opened");
			return false;
		}

		$i = 0;
		foreach ($this->hdr as $f) {
			if ($i++ > 0) fprintf($h, "$sep");

			$f = "$encl$f$encl";
			fprintf($h, "%s", $f);
		}
		fprintf($h, "\n");

		foreach($this->data as $l) {
			$i = 0;
			foreach ($l as $k => $v) {
				if ($i++ > 0) fprintf($h, "$sep");
				$v = "$encl$v$encl";
				fprintf($h, "%s", $v);
			}
			fprintf($h,"\n");
		} 
		
		#
		# close file:
		fclose($h);
		return true;
	}
	/**
	 * read csv as a file named $file. 1st line is treated as a header 
	 * @param $file	filename to be read
	 * @param $sep as separator (default ;)
	 * @param $encl	encloser (default none)
	 * @return true if ok or false if an error occured
	 */
	function read(string $file, string $sep = ";", string $encl = ''): boolean  {
		# TODO: allow 1st line not to be a header if header already given.
		if (!file_exists($file)) {
			err("file '$file' does not exist");
			return false;
		}
		if (($h = fopen($file, "r")) === false) {
			err("file '$file' cannot be opened");
			return false;
		}

		#
		# Read header:
		if (($hdr = fgetcsv($h, 0, $sep, $encl)) === false) {
			err("cannot read '$file' header");
			fclose($h);
			return false;
		}
		#
		#	
		$this->fields_set($hdr);
		
		#
		# Read data:
		$this->data  = [];
		while (!feof($h)) {
			$dat = fgetcsv($h, 0, $sep, $encl);
			if ($dat == "") continue;
			$d = new stdclass();
			for ($i = 0; $i < $this->nfields; $i++)
				$d->{$this->hdr[$i]} = $dat[$i];

			array_push($this->data, $d); 
			$this->nlines++;
		}
		fclose($h);
		return true;
	}
	/** 
	 * obsolete
	 */
	function update_hdr($field){
		if ($this->nlines == 0) {
			err("empty file");
			return NULL;
		}

		if (!$this->hdr || !$this->flds) {
			err("no field definition");
			return NULL;
		}
		
		foreach ($field as $k => $v) {
			$this->hdr[$k] = $v;
		}
	}

};

/** test function */
function csv_test() {
	$str = "A;B;C;D\n1;2;3;4\na;b;c;d\n";
	$in = "test-csv.in";
	$ou = "test-csv.out";

	file_put_contents($in, $str);
	$cin = new CSV($in);
	$cou = new CSV(["A", "B", "C", "D"]);
	$cou->push($cin->get(0));
	$cou->push($cin->get(1));
	$cou->write($ou);
	print_r($cou->data());
}

?>
