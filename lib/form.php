<?php 

require_once("lib/args.php");
require_once("lib/stats.php");

class form {
	function __construct($spec) {
		$this->params = [];
		$this->param_groups = [];

		$props = [ "name", "fname", "param_groups", "params", "actions" ];

		foreach ($props as $v) if (key_exists($v, $spec)) $this->$v = $spec[$v];
		if ($this->param_groups == []) $this->param_groups = [ $this->params ];
		$this->init_vars();
	}

	function init_vars() {
		$vars = (object)[];	
		$a = new args();
		foreach ($this->param_groups as $params) {
			foreach ($params as $n => $p) {
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
		}
		$this->action = $a->val("action");
		$this->vars = $vars;
	}
	function sql($dbs_or_sql, $sql = false) {
		if ($sql === false) {
			$dbs = "defaut";
			$sql = $dbs_or_sql;
		} else {
			$dbs = $dbs_or_sql;
		}
		$db = new db($dbs);
		$q  = new query($db, $sql);
		return $q->all();
	}
	function data() {
		return json_encode($this->vars);
	}

	function get($var) {
		if (property_exists($this->vars, $var)) return $this->vars->$var;
		return false;
	}

	function draw_params($params) {
		$str = "<table>";
		foreach ($params as $n => $p) {
			$str .= "\t<tr><th><label for='$n'>". $p["label"]."</label>";
			if ($p["type"] == "date") {
				$str .= "<td><input id='$n' name='$n' onclick='date_cal_open(this);' size='16' pattern='[0-3][0-9]/[0-1][0-9]/20[0-9][0-9]' placeholder='jj/mm/aaaa' value='"
					   . date_db_to_human($this->vars->$n) . "'/></td></tr>\n";
			} else if ($p["type"] == "string" || $p["type"] == "int" || $p["type"] == "integer") {
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
		$str.="</table>";
		return $str;
	}
	
	function draw() {
		$str = "<div id='form' >\n<table class='form'><tr>";
		foreach ($this->param_groups as $i => $params) {
			$str .= "<td>" .$this->draw_params($params)  . "</td>";
		}
		$i++;
		$str .= "\t</tr><tr class='but'><td class='submit' colspan='$i'>";
		foreach ($this->actions as $a) {
			$txt = "";
			$act = "form_load";
			if (is_array($a)) $a = (object) $a;
			if (is_object($a)) {
				if (property_exists($a, "text")) { $txt = $a->text; }
				if (property_exists($a, "type") && $a->type == "download") $act = "form_download";
	 		} else if (is_string($a)) {
				$txt = $a;
			}
			$str .= "<input type='hidden' name='form' value='true'/>"; #<input type='hidden' name='vars' value='".json_encode($this)."'/>";
			$str .= "<button onclick=\"$act('form_result', '".base64_encode(json_encode($this))."', '$txt')\">$txt</button>";
		}
		$str .= "</td></tr>\n</table>\n</div>\n<div id='form_result' class='formresult'></div>\n";
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
	$st = hrtime(true);
	include("forms/$fname");
	$et = hrtime(true);
	stats_update($fname, (($et - $st) / 1e9));
}

	
?>
