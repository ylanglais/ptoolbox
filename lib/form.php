<?php 

require_once("lib/args.php");

class form {
	function __construct($spec) {
		$vars = [ "name", "fname", "params", "actions" ];
		foreach ($vars as $v) {
			if (key_exists($v, $spec)) $this->$v = $spec[$v];
		}
		$this->init_vars();
	}

	function init_vars() {
		$vars = (object)[];	
		$a = new args();
		foreach ($this->params as $n => $p) {
			if (($vars->$n = $a->val($n)) == null) {
				$d = $p["default"];
				if ( $p["type"] == "date" ) {
					if (preg_match("/^[a-z]*$/", $d)) {
						switch ($d) {
						case "s12m":
						case "sliding_12_month":
							$d = date_prev_year(som());
							break;
						case "eopm":
						case "end_of_previous_month":
							$d = date_prev(som());
							break;
						case "som":
						case "start_of_month":
							$d = som();
							break;
						case "soy":
						case "start_of_year":
							$d = soy();
							break;
						case "eom":
						case "end_of_month":
							$d = eom();
							break;
						case "eoy":
						case "end_of_year":
							$d = eoy();
							break;
						case "today":
							$d = today();
							break;
						}
					} else if (preg_match("/([0-3][0-9])[\/-]([0-1][0-9])[\/-]([0-9]{4})/", $d, $m)) {
						$d = "$m[1]-$m[2]-$m[3]";
					}
					$vars->$n = $d;
				} else {
					$vars->$n = $p["default"];
				} 
			} 
		}
		$this->action = $a->val("action");
		return ($this->vars = $vars);
	}

	function sql($string) {
			
	}

	function data() {
		return json_encode($this->vars);
	}

	function get($var) {
		if (property_exists($this->vars, $var)) return $this->vars->$var;
		return false;
	}
	
	function draw() {
		$str = "<div id='form_$this->name' class='form'>\n<table class='form'>\n";
		foreach ($this->params as $n => $p) {
			$str .= "\t<tr><th><label for='$n'>". $p["label"]."</label>";
			if ($p["type"] == "date") {
				$str .= "<td><input id='$n' name='$n' onclick='date_cal_open(this);' size='16' pattern='[0-3][0-9]/[0-1][0-9]/20[0-9][0-9]' placeholder='jj/mm/aaaa' value='"
					   . date_db_to_human($this->vars->$n) . "'/></td></tr>\n";
			} else if ($p["type"] == "string") {
				$str .= "<td><input id='$n' name='$n' value='" . $this->vars->$n. "'/></td></tr>\n";
			} else if ($p["type"] == "list" || $p["type"] == 'mlist') {
				$m = ""; 
				if ($p["type"] == 'mlist') $m = "multiple";
				$str .= "<td><select name='$n' id='$n' $m>";
				foreach ($p["values"] as $v) {
					if ($this->vars->$n == $v) 
						$sel="selected";
					else  
						$sel="";
					$str .= "<option value='$v' $sel>$v</option>";
				}
				$str .= "</select></td></tr>";
			}
		}
		$str .= "\t<tr class='but'><td class='submit' colspan='2'>";
		foreach ($this->actions as $a) {
			$str .= "<input type='hidden' name='form' value='true'/>"; #<input type='hidden' name='vars' value='".json_encode($this)."'/>";
			$str .= "<button onclick=\"form_load('$this->name', '".base64_encode(json_encode($this))."', '$a')\">$a</button>";
		}
		$str .= "</td></tr>\n</table>\n</div>\n<div id='".$this->name."_data'></div>\n";
		return $str;
	}
}

function form_ctrl() {
	$a = new args();
	if (!$a->has("fname")) {
		err("No form specified");
		print("No form   specified");
		return;
	}
	$fname = $a->val("fname");
	if ($a->has("titre")) {
		$titre = $a->val("titre");
	} else {
		$titre = $fname;
	}
	if (!file_exists("forms/$fname")) print("no form named $fname");
	include("forms/$fname");
}

	
?>
