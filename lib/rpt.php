<?php
require_once("lib/svg.php");
require_once("lib/dbg_tools.php");
require_once("lib/db.php");
require_once("lib/query.php");
require_once("lib/locl.php");
require_once("lib/stats.php");
require_once("lib/scrm.php");


class rpt {
	private $icons = [
		"duplicate" => "ic-doublons.png",
		"email"     => "ic-email.png",
		"postal"    => "ic-enveloppe.png",
		"phone"     => "ic-tel.png",
		"increase"  => "ic-level.png"
	];

    function __construct($o, $vars = []) {
        $this->fallback = 'fr_FR.UTF-8';
        $this->locale   = 'fr_FR.UTF-8';
		$this->lang     = '';
		$this->nsec     = 0;
		$this->nsub     = 0;
		$this->dbs		= [];
		$this->sqldbg   = false;

		$this->vars     = [];
		$this->form     = [];

        $this->colors   = [ "#0972e3",  "#055f8d", "#2a82ac", "#3399cc", "#25bbdb", "#89afe3", "#c5e4f2", "#89d9e3", "#d4f2f7" ];

		if (file_exists("conf/rpt.php")) {
			include("conf/rpt.php");
			foreach ([ "locale", "lang", "colors", "sqldbg" ] as $k) {
				if (isset(${"rpt_$k"})) $this->$k = ${"rpt_$k"};
			}
		}
        if (is_object($o) && property_exists($o, "report") && is_object($o->report)) {
            if (property_exists($o, "rpt_name")) { 
				$this->rpt_name = $o->rpt_name;
			} else $this->rpt_name = false;
            if (property_exists($o->report, "locale")) { 
                try {
                    setlocale(LC_ALL, $o->report->locale);
                    $this->locale = $o->report->locale;
                } catch(\Exception $e) {
                    $this->locale = $this->fallback;
                    _err("invalid locale (".$o->report->locale."), using fallback ($this->fallback)\n");
                }
            }       
			if (property_exists($o->report, "lang")) { 
				if (file_exists("lang/".$o->report->lang)) {
					$this->lang = $o->report->lang;
				}
			}
        }

		$this->locl = new locl($this->locale);
        $this->ncolors  = count($this->colors);

		foreach ($vars as $k => $v) {
			$this->vars[$k] = $v;			
		}
    }
	function rpt_name($o) {
		$this->rpt_name = $o;
	}
	function rpt_dbs($o) {
		if (is_object($o) && property_exists($o, "dbs")) {
			$this->rpt_db($o);
		}
		if (!is_array($o)) {
			warn("rpt_dbs param is not an array (".json_encode($o).")");
			return;
		}
		foreach ($o as $d) $this->rpt_db($d);
	}
	function rpt_db($o) {
		$dbopts = null;
		$name = "default";
		if (property_exists($o, "name")) { $name = $o->name;} 
		if (property_exists($o, "dbs")) {
			if (property_exists($o, "dbuser") && property_exists($o, "dbpass")) {
				if (property_exists($o, "dbopts")) {
					$this->dbs[$name] = new db($o->dbs, $o->dbuser, $o->dbpass, $o->dbopts); 
				} else { 
					$this->dbs[$name]  = new db($o->dbs, $o->dbuser, $o->dbpass); 
				}
			} else {
				$this->dbs[$name] = new db($o->dbs);
			}
		} else {
			$this->dbs[$name] = new db();
		}
	}
	function rpt_form($o) {
		$str = "<div id='form_div'><table class='form'>";
		$this->form = $o;
		foreach ($o as $i) foreach ($i as $k => $data) {
			$str .= "\t<tr><th><label for='$k'>". $data->label."</label>";
			$v = false;
			$v_isset = false;	
			if ($this->var_isset($k)) {
				$v = $this->var_get($k);
				$v_isset = true;	
			} else if (property_exists($data, "default")) {
				$d = $data->default;
				if ($data->type == "date") {
					if (preg_match("/^[a-z]*$/", $d)) {
					switch ($d) {
						case "s12m":
						case "sliding_12_month":
							$v = date_prev_year(som());
							$v_isset = true;	
							break;
						case "eopm":
						case "end_of_previous_month":
							$v = date_prev(som());
							$v_isset = true;	
							break;
						case "som":
						case "start_of_month":
							$v = som();
							$v_isset = true;	
							break;
						case "soy":
						case "start_of_year":
							$v = soy();
							$v_isset = true;	
							break;
						case "eom":
						case "end_of_month":
							$v = eom();
							$v_isset = true;	
							break;
						case "eoy":
						case "end_of_year":
							$v = eoy();
							$v_isset = true;	
							break;
						case "today":
							$v = today();
							$v_isset = true;	
							break;
						}
					} else if (preg_match("/^([a-z0-9_]*)\(([^)]*)\)$/", $d, $m)) {
						if ($m[1] == "sql") {
							$s = $this->_rpt_sql($m[2]);
							if (is_array($s) && $s != []) {
								$v = $s[0];
								$v_isset = true;	
							}
						} else if ($m[1] == 'rpt_var') {
							$v = $this->var_get(substr($d, 8, -1));
							$v_isset = true;	
						} else {
							err("default value not recognized ($d)");
						}
					} else if (preg_match("/([0-3][0-9])[\/-]([0-1][0-9])[\/-]([0-9]{4})/", $d, $m)) {
						$v = "$m[1]-$m[2]-$m[3]";
						$v_isset = true;	
					}
				} else if (preg_match("/^([a-z0-9_]*)\(([^)]*)\)$/", $d, $m)) {
					if ($m[1] == "sql") {
						$s = $this->_rpt_sql($m[2]);
						if (is_array($s) && $s != []) {
							$v = $s[0];
							$v_isset = true;	
						}
					} else if ($m[1] == 'rpt_var') {
						$v = $this->var_get(substr($d, 6, -1));
						$v_isset = true;	
					}
				} else $v = $d;
				$this->var_set($k, $v);
			} else {
				$this->var_set($k, false);
			}
			if ($data->type == "date") {
				$str .= "<td><input id='$k' name='$k' onclick='date_cal_open(this);' size='16' pattern='[0-3][0-9]/[0-1][0-9]/20[0-9][0-9]' placeholder='jj/mm/aaaa' value='";
				if ($v_isset) $str .= date_to_human($v);
				$str .= "'/></td></tr>\n";
			} else if ($data->type == "string") {
				$str .= "<td><input id='$k' name='$k' value='" . $v . "'/></td></tr>\n";
				if ($v_isset) $str .= $v;
				$str .=  "'/></td></tr>\n";
			} else if ($data->type == "list" || $data->type == 'mlist') {
				$m = ""; 
				if ($data->type == 'mlist') $m = "multiple";
				$str .= "<td><select name='$k' id='$k' $m>";
				if (is_string($data->values) && substr($data->values, 0, 4) == 'sql(') {
					$data->values = $this->_rpt_sql(substr($data->values, 5, -1));
				}
				foreach ($data->values as $val) {
					if (is_array($v) && in_array($v, $val)) $sel = "selected";
					else if (is_string($v) && $val = $v)    $sel = "selected";
					else                                    $sel = "";
					$str .= "<option value='$val' $sel>$val</option>";
				}
				$str .= "</select></td></tr>";
			}
		}
		$str.="<tr><td colspan='2'><input type='button' onclick='rpt_reload()' value='Appliquer'/></td></table></div>";
		return $str;
	}	
	function colors_get() {
		return $this->colors;
	}
	function colors_set($colorset) {
		$this->colors = $colorset;
        $this->ncolors  = count($this->colors);
	}
    function is_date($value) {
        if (!$value) {
            return false;
        }
        try {
            new \DateTime($value);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
	function get_image($filename) {
		if (!file_exists($filename)) {
			_err("cannot open \"$filename\"");
			return "";
		} 
		return 'data: '.mime_content_type($filename).';base64,'. base64_encode(file_get_contents($filename));
	}
	function var_parse($str, $var) {
		if (substr($str, 0, 4) == "sql(") {
			$all = $this->_rpt_sql($str);
			if ($all == false) return false;
			if (is_array($all)) $all = $all[0];
			if (!is_object($all) || !property_exists($all, $var)) return false;
			return $all->$var;
		}
		if (substr($str, 0, 4) == "rpt_var(") {
			return $this->var_get(substr($str, 6, -1));
		}
		return $str;
	}
	function count_parse($str) {
		return $this->var_parse($str, "count");
	}
	function table_parse($str, $hdr = false, $lnum = false) {
		if (substr($str, 0, 4) == "sql(") {
			$all = $this->_rpt_sql($str);

			if ($all == false) return [];

			$arr = [];
			$hdd = [];

			$r = 1;
			if ($hdr && $lnum) array_push($hdd, "#");

			foreach ($all as $row) {
				$row = (array) $row;
				$arl = [];
				if ($lnum) array_push($arl, $r);
				foreach ($row as $k => $v) {
					if ($hdr && $r == 1) array_push($hdd, $k);	
					array_push($arl, $v);
				}	
				if ($hdr && $r == 1) array_push($arr, $hdd);
				array_push($arr, $arl);
				$r++;
			}
			return $arr;
		}
		return [];
	}
	function range_parse($str) {
		if (substr($str, 0, 4) == "sql(") {
			$all = $this->_rpt_sql($str);
			$arr = [];
			foreach ($all as $row) {
				foreach ($row as $k => $v) {
					$arr[$k] = $v;
				}	
			}
			return $arr;
		}
		return [];
	}
	function data_parse($str, $data = 'month', $count = 'count') {
		if (substr($str, 0, 4) == "sql(") {
			$all = $this->_rpt_sql($str);
			$arr = [];
			if ($all !== false) foreach ($all as $row) $arr[$row->$data] = $row->$count;
			return $arr;
		}
		if (substr($data, 0, 4) == "rpt_var(") {
			return $this->var_get(substr($str, 6, -1));
		}
		return $str;
	}
	function series_parse($str, $series = 'series', $x = 'date', $y = 'count') {
		if (substr($str, 0, 4) == "sql(") {
			$all = $this->_rpt_sql($str);
			$ser = [];
			foreach ($all as $r) {
				if (!array_key_exists($r->$series, $ser)) $ser[$r->$series] = [];
				$ser[$r->$series][$r->$x] = $r->$y;
			}
			return $ser;
		}
		if (substr($data, 0, 4) == "rpt_var(") {
			return $this->var_get(substr($str, 6, -1));
		}
		return $str;
	} 
    function parse($o) {
		$div = false;
		if (is_null($o) || $o === false) return;
		if (is_string($o)) $o = json_decode($o);
		if (!is_object($o) && !is_array($o)) {
			_err("invalid object: " . print_r($o, TRUE));
			return $str;
		}
        if (property_exists($o, "report")) {
		 	$str = "<div id='report' class='report'>";
			$div = true;
		} else $str = "";
	
        foreach ($o as $k => $v) {
            if (substr($k, 0, 4) == "rpt_") {
                if (!method_exists($this, $k)) {
                    _err("Rpt has no method $k, skipping block");
                } else {
                    $str .= $this->$k($v);
                }
            }
        }
		if ($div == true) {	
			$str .= "<input type='hidden' id='rpt_name' value='". $this->rpt_name ."'/></div>";
			$str .= "<input type='hidden' id='rpt_vars' value='". json_encode($this->vars)."'/></div>";
			if ($this->form != []) $str .= "<input type='hidden' id='rpt_form' value='". json_encode($this->form)."'/></div>";
		}
        return $str;
    }
	function rpt_vars($o) {
		foreach ($o as $oo) $this->rpt_var($oo);
	}
	function rpt_var($o) {
		$this->var_set($o->var, $this->var_parse($o->val, $o->var));
	}
	function _rpt_sql($str) {
		if (substr($str, 0, 4) == 'sql(') {
			$str = substr($str, 4, -1);
		}
		if (preg_match("/^[ 	]*([^, 	]*)[ 	]*,[ 	]*([Ss][Ee][Ll][Ee][Cc][Tt].*)$/", $str, $m)) {
			$dbn = $m[1];
			$str = $m[2];
		} else {
			$dbn = "default";
		}
		if (strstr($str, "rpt_var(")) {
			$str = $this->rpt_var_replace($str);
		}
		if ($this->sqldbg) dbg($str);
		$q   = new query($str, $this->dbs[$dbn]);
		$all = $q->all();
		if ($all == [])       return false;
		return $all;
	}
	function var_set($k, $v) {
		$this->vars[$k] = $v;
	}
	function var_isset($k) {
		if (array_key_exists($k, $this->vars)) {
			return true;
		}
		return false;
	}
	function var_get($k) {
		if (array_key_exists($k, $this->vars)) return $this->vars[$k];
		return false;
	}
	function rpt_var_replace($s) {
		while (preg_match('/rpt_var\(([A-Za-z_][A-Za-z_0-9]*)\)/', $s, $m)) {
			if (array_key_exists($m[1], $this->vars)) {
				$v = $this->vars[$m[1]];
				$s = str_replace($m[0], $v, $s);
			} else {
				warn("$m[1] is not a defined variable");
				exit;
			} 
		}
		return $s;
	}
    function rpt_pages($o) {
        $str = "";
        foreach ($o as $p) {    
            $str .="\n<div class='page-break'>\n" . $this->parse($p) . "</div>\n";
        }
        return $str;
    }
    function rpt_header($o) {
		if (property_exists($o, "title")) {
			$t = $this->rpt_var_replace($o->title);
			$str = "<h1>$t</h1>";
		}
        return $str;
    }
    function rpt_columns($o) {
        $src = "<table class='spacing'><tr class='spacing' >\n";
        foreach ($o as $r) {
			$colspan = "";
			$rowspan = "";
			if (property_exists($r, "colspan")) {
				$colspan=" colspan='" . $r->colspan . "'";
				unset($r->colspan);
			}
			if (property_exists($r, "rowspan")) {
				$colspan=" rowspan='" . $r->rowspan . "'";
				unset($r->rowspan);
			}
            $src .= "\t<td class='spacing' $colspan$rowspan>\n" . $this->parse($r) . "\t</td>\n";  
        }
        $src .= "</tr></table>\n";    
        return $src;
    }
    function rpt_rows($o) {
        $src = "<table class='spacing'>\n";
        foreach ($o as $r) {
			$colspan = "";
			$rowspan = "";
			if (property_exists($r, "colspan")) {
				$colspan=" colspan='" . $r->colspan . "'";
				unset($r->colspan);
			}
			if (property_exists($r, "rowspan")) {
				$colspan=" rowspan='" . $r->rowspan . "'";
				unset($r->rowspan);
			}
            $src .= "\t<tr class='spacing'><td class='spacing' $colspan$rowspan>" . $this->parse($r) . "</td></tr>\n"; 
        }
        $src .= "</table>\n"; 
        return $src;
    }
	function secid() {
		return $this->nsec++;
	}
    function rpt_sections($o) {
        $str = "<div id='rpt_body'>";
        foreach ($o as $p) {
			$id = "s". $this->secid();
			$class = "class='section'";
			if (property_exists($p, "bgcolor") && $p->bgcolor == true) {
				$class ="class='section bg'";
			}
			if (property_exists($p, "title")) {
				$t = $this->rpt_var_replace($p->title);
				$str .= "\n<div class='st'><h1 onclick='e=document.getElementById(\"$id\");if (e.style.display ===\"none\") e.style.display=\"block\"; else e.style.display=\"none\";'>$t</h1>\n";
			}
			$str .= "<div $class id='$id'>\n";
            $str .= $this->parse($p);
			$str .= "</div></div>\n";
        }
        return $str . "</div>";
    }
    function rpt_subsections($o) {
        $str = "";
        foreach ($o as $p) {
			$class = "class='subsection'";
			if (property_exists($p, "bgcolor") && $p->bgcolor == true) 
				$class ="class='subsection bg'";
			$str .= "<div $class>\n";
			if (property_exists($p, "align"))
				$style = "style='text-align: $p->align'";
			else 
				$style = "";
			if (property_exists($p, "title")) {
				$t = $this->rpt_var_replace($p->title);
            	$str .= "\n<h2 $style>$t</h2>\n";
			}
			if (property_exists($p, "subtitle")) {
				$t = $this->rpt_var_replace($p->subtitle);
            	$str .= "\n<h3 $style>$t</h3>\n";
			}
            $str .= $this->parse($p);
			$str .= "</div>\n";
        }
        return $str;
    }
    function rpt_array($o, $hdr = true, $tailer = false, $nolocl = false, $lnum = false) {
        if (!is_array($o)) return "";
        $str = "\t<table class='results'>\n";
        $i = 0;
		$n = count($o);
        foreach ($o as $l) {
            $str .= "\t\t<tr>";
            $i++;
			$j = -1;
            foreach ($l as $c) {
				$j++;
				$fmt = $this->locl->type_detect($c);
				$cla = "";
				switch ($fmt) {
				case "numeric":
					if ($j == 0 && $lnum) {
						$cla = " class='num'";
						 break;
					}
				case "float":	
				case "percentage":	
					$cla = " class='number'";
					break;
				case "date": 
					$cla=" class='center'";
				}
				if ($i == 1) {
					if  ($hdr) { 
						if (!$nolocl) $str .= "<th>" . $this->locl->format($c) . "</th>";
						else $str .= "<th>$c</th>";
					} else {
						if (!$nolocl) $str .= "<td>" . $this->locl->format($c) . "</td>";
						else $str .= "<td$cla>$c</td>";
					}
                } else {
					if ($i == $n && $tailer === true) {
						if (!$nolocl) $str .= "<th>" . $this->locl->format($c) . "</th>";
						else $str .= "<th>$c</th>";
					} else {
						if (!$nolocl) $str .= "<td$cla>" . $this->locl->format($c) . "</td>";
						else $str .= "<td$cla>$c</td>";
					}
                }
            }
            $str .= "</tr>\n";
        }
		$str .= "\t</table>\n";
        return $str;
    }
	function rpt_table($o, $hdr = true, $tailer = false) {
        if (is_array($o))   return $this->rpt_array($o, $hdr, $tailer);
		if (!is_object($o)) return "";
		$str = "";
		$nolocl = false;
		if (property_exists($o, "header")) $hdr = $o->header;
		if (property_exists($o, "hdr"))    $hdr = $o->hdr;
		if (property_exists($o, "nolocl")) $nolocl = $o->nolocl;
		if (property_exists($o, "title")) {
			$t = $this->rpt_var_replace($o->title);
			$str .= "<h2 style='text-align: center'>$t</h2>";
		}
		if (property_exists($o, "data")) {
			if (is_array($o->data)) $str .= $this->rpt_array($o->data, $hdr, $tailer);
			else if (substr($o->data, 0, 4) == "sql(") {
				$lnum = false;
				if (property_exists($o, "linenumbers") && $o->linenumbers === true) $lnum = true;
				if (property_exists($o, "lnum")        && $o->lnum        === true) $lnum = true;
				$str .= $this->rpt_array($this->table_parse($o->data, $hdr, $lnum), $hdr, $tailer, $nolocl, $lnum);
			}
		}
		return $str;
	}

    function rpt_table_noheader($o, $tailer = false) {
        return $this->rpt_table($o, false, $tailer);
    }

	function rpt_icon($o) {
		if (!key_exists($o, $this->icons)) return "";
		return "<img src='" . $this->get_image("images/".$this->icons[$o]) . "' height='60px'/>"; 
	}
	function rpt_iconbox($o) {
		if (!property_exists($o, "icon") || !property_exists($o, "value")) return "";

		$str = "<table class='spacing'><tr class='spacing'>";	

		if (property_exists($o, "text") && property_exists($o, "value"))  $rspan="rowspan='2'";
		else $rspan = '';

		if (property_exists($o, "icon") && key_exists($o->icon, $this->icons)) {
			$str .= "<td $rspan class='spacing'><img src='" . $this->get_image("images/".$this->icons[$o->icon]) . "' height='60px'/></td>";
		}
		if (property_exists($o, "text")) {
			$str .= "<td class='h2'>$o->text</td></tr><tr>";
		} 
		if (property_exists($o, "color")) {
			$style = "style='color: $o->color'";
		} else $style = '';
		$str .= "<td class='ligne2' $style>" . $this->locl->format($o->value) ."</td></tr></table>\n";
		
		return $str;
	}
    function rpt_lcpblock($o) {
		if (!property_exists($o, "pcent")) $o->pcent = '';
		else if (substr($o->pcent, -1) != '%') $o->pcent .= "%";
		if (property_exists($o, "color")) { $c = " $o->color"; } else $c = "";

        $str  = "\t<table class='$c w47'>\n";
        $str .= "\t\t<tr><td class='ligne1' colspan='2'>".$this->locl->format($o->label)."</td></tr>\n";

		$count = $this->count_parse($o->count);

		if (property_exists($o, "sublabel")) {
			$str .= "\t\t<tr><td class='ligne4' colspan='2'>$o->sublabel</td></tr>\n";
		}
		$str .= "\t\t<tr><td class='ligne2' width='50%'>".$this->locl->format($count)."</td><td class='ligne2 lbw' width='50%'>" . $this->locl->format($o->pcent)."</td></tr>\n";
        $str .= "\t</table>\n";

		if (property_exists($o, "var")) $this->var_set($o->var, $count); 


        return $str;    
    }
	function rpt_lcpblock_array($o) {
		$total = false;
		if (!is_array($o)) {
			_err("badly formed rpt_lcpblock_array => no data");
			return "";
		}

		$str = "<table class='lcpblock_array'>\n";
		foreach ($o as $a) {
			if (count($a) == 1) {
				$str .= "\t<tr><td colspan='2'><h2 style='text-align: center'>". $this->locl->format($a[0])."</h2></td></tr>\n";
			} else if (count($a) == 3) {
				$str .= "\t<tr><td colspan='2' class='bluebold'>". $this->locl->format($a[0]) . "</td></tr>\n";
				$str .= "\t<tr><td class='ligne2'>" . $this->locl->format($a[1]) . "</td><td class='lbb ligne2'>" . $this->locl->format($a[2]) . "</td></tr>\n";
			} else {
				_err("invalid lcpblock_array block description");
			}
		}
		$str .= "</table>\n";
		return $str;
	}
    function rpt_blocks($o) {
		$str = "";
		$total = 0;

		if (is_object($o)) {
			if (property_exists($o, "base100")) {
				$total = $this->count_parse($o->base100);
			}
			if (property_exists($o, "label")) {
				$str .= "<h2>$o->label</h2>";	
			}
			$data = $o->data;
		} else $data = $o;

        $i = 0;
		
        $str .= "<table class='analyse_results'><tr>";
        foreach ($data as $b) {
			$col  = $this->colors[$i % $this->ncolors];
			$i++;

			if (property_exists($b, "count")) {
				$count = $this->count_parse($b->count);
			} else if (property_exists($b, "value")) {
				$count = $b->value;
			} else $count = 0;

			if (!property_exists($b, "pcent")) {
				if ($total == 0) {
					$pcent = "";
				} else $pcent = ($count / $total * 100) . "%"; 
			} else {
				$pcent = $b->pcent;
				if (substr($pcent, -1) != '%') $pcent .= "%";
			} 

            $str .= "<td>\n\t<table class='bg_results' style='background-color: $col;'>\n";
            $str .= "\t\t<tr><td class='ligne1'>" . $b->label                . "</td></tr>\n";
            $str .= "\t\t<tr><td class='ligne2'>" . $this->locl->format($count) . "</td></tr>\n";
            if ($pcent != "") $str .= "\t\t<tr><td class='ligne3'>" . $this->locl->format($pcent)    . "</td></tr>\n";
			else $str .= "\t\t<tr><td class='ligne3'>&nbsp;</td></tr>\n";
			if (property_exists($b, "subtitle")) {
				$t = $this->rpt_var_replace($b->subtitle);
				$subtitle = $t;
			} else $subtitle = "";

			$str .= "\t\t<tr><td class='ligne4'>$subtitle</td></tr>\n";
			
            $str .= "\t</table>\n</td>";
        }
        $str .= "</tr></table>\n";
        return $str;
    }
	function rpt_async($o) {
		if (!property_exists($o, "rpt_element") || !property_exists($o, "id")) {
			err("bad async report element");
			return "";
		}
		$e = $this->rpt_var_replace(json_encode($o->rpt_element));
		$token = scrm_do($e);
		return "<div id='$o->id'><img width='50px' src='images/wait.gif' onload='ctrl(\"rpt\",  { \"token\": \"$token\"}, \"$o->id\", false)'/></div>\n";
	}

	function rpt_graph($o) {
		$str = "";
		if (property_exists($o, "title")) {
			$t = $this->rpt_var_replace($o->title);
            $str .= "<h2 style='text-align: center'>$t</h2>\n";
		}
		if (property_exists($o, "subtitle")) {
			$t = $this->rpt_var_replace($o->subtitle);
            $str .= "<h4 style='text-align: center'>$t</h4>\n";
		}
		if (!property_exists($o, "data")) {
			_err("badly formed rpt_graph block: no data");
			return "";
		}
			
		$w = 500;
		$h = 250;
		if (property_exists($o, "width"))  $w = $o->width;
		if (property_exists($o, "height")) $h = $o->height;
		$opts = [];
		foreach ([ "xunits" => null, "yunits" => null, "xgrid" => true, "ygrid" => true, "xdatatype" => null, "ydatatype" =>null, "borders" => false  ] as $p => $v) {
			if (property_exists($o, $p)) $opts[$p] = $o->$p;
			else if ($v != null) $opts[$p] = $v;
		}

		# old defaut settings for backword compatibility:
		$x = "month"; $y = "count";
		if (property_exists($o, "x")) $x = $o->x;
		if (property_exists($o, "y")) $y = $o->y;

	
		$data = [];
		if (property_exists($o, "series")) {
			$data = $this->series_parse($o->data, $o->series, $x, $y);
		
		} else if (property_exists($o, "data") && is_array($o->data)) { 
			foreach ($o->data as $series) {
				$data[$series->name] = $this->data_parse($series->value, $x, $y);
			}
		} else {
			_err("badly formed rpt_graph : invalid data");
			return "";
		}

		$svg = new svg($w, $h);
		$svg->colors($this->colors); 
		if (property_exists($o, "xy") && $o->xy === true)
			$str .= $svg->graph_xy($data, $opts);
		else
			$str .= $svg->graph($data, $opts);

		return $str;
	}
	function rpt_evolution($o) {
	}
	function rpt_bars($o) {
		if (!property_exists($o, "data")) {
			_err("badly formed rpt_bars block: no data");
			return "";
		}
		$data = $this->range_parse($o->data);
		$w = 500;
		$h = 250;
		if (property_exists($o, "width"))  $w = $o->width;
		if (property_exists($o, "height")) $h = $o->height;
		$opts = [];
		foreach (["xgrid" => true, "ygrid" => true, "title" => null, "barmargin" => 15, "borders" => false] as $p => $v) {
			if (property_exists($o, $p)) $opts[$p] = $o->$p;
			else if ($v != null) $opts[$p] = $v;
		}
		$svg = new svg($w, $h);
		$svg->colors($this->colors); 
		$str = $svg->bars($data, $opts);
		return $str;
	}
    function rpt_pie($o) {
		if (!property_exists($o, "labels")) {
			_err("badly formed rpt_pie block: no labels");
			return "";
		} 
		if (!property_exists($o, "values")) {
			_err("badly formed rpt_pie block: no values");
			return "";
		} 
		$w = $h = 200;
		if (property_exists($o, "width"))  $w = $o->width;
		if (property_exists($o, "height")) $h = $o->height;

		$pcent = [];
		$lbl   = [];
		$total = 0;

		foreach ($o->values as $n => $v) {
			$total +=  $v; 
		}

		arsort($o->values);
		
		foreach($o->values as $n => $v) {
			$p = $v / $total;
			array_push($pcent, $p);
			array_push($lbl, $o->labels[$n]);
		}

		$svg = new svg($w, $h);
		$svg->colors($this->colors); 
		return $svg->pie($pcent, $lbl);
		#return "<div class='pie'>" .  $svg->pie($pcent, $lbl) . "</div>\n";
    }
    function rpt_breakdown($o) {
		$str   = "";
		$lbl   = $dat = [];
		$total = 0;

		$tlr = false;

		$nolimit = false;
		$max_totpc  = 95; #95% 
		$max_parts  =  8 ;
		$min_partpc =  2; # (if not the last item

        if (!property_exists($o, "labels")) {
			if (!property_exists($o, "data")) {
                _err("badly formed rpt_breakdown block ignored (no label and no data)");
				return "";
			} else {
				if (substr($o->data, 0, 4) == 'sql(') {
					$all = $this->_rpt_sql($o->data);
					
					foreach ($all as $row) {
						if ($row->count != 0) {
							array_push($lbl, $row->label);
							array_push($dat, $row->count);
							$total += $row->count;
						}
					}
				}
			}
		} else if (!property_exists($o, "data")) {
			_err("badly formed rpt_breakdown block ignored (no data)");
			return "";
		} else {
			if (!is_array($o->labels)) {
				_error("badly formed rpt_breakdown block ignored (label isn't an array " . print_r($o, FALSE));
				return "";
			}
			$n = count($o->labels);
			for ($i = 0; $i < $n; $i++) {
				if ($o->data[$i] != 0) {
					array_push($lbl, $o->labels[$i]);
					array_push($dat, $o->data[$i]);
					$total += $o->data[$i];
				}
			}
		}

	 	if (property_exists($o, "nolimit") && $o->nolimit == true) $nolimit = true;
		else {
	 		if (property_exists($o, "max_totpc" ) && $o->max_totpc  > 0 && $o->max_totpc  <= 100) $max_totpc  = $o->max_totpc;
	 		if (property_exists($o, "min_partpc") && $o->min_partpc > 0 && $o->min_partpc <  100) $min_partpc = $o->min_partpc;
	 		if (property_exists($o, "max_parts")  && $o->max_parts  > 0 && $o->max_parts  <   20) $max_parts  = $o->max_parts;
			
		}

        $l = $d = [];
		
		$a = [];
		$t = 0;
		$c = 0;

		if (property_exists($o, "header")) {
			$hdr = true;
			$a[0] = [ $o->header, "Nb", " % " ];
		} else $hdr = false;
	
		$n = count($dat);
        for ($i = 1; $i <= $n; $i++) {
			$p = $dat[$i-1] / $total * 100;
			# limit to total = 95% or 8 parts or < 2% (if not the last item):
			if ($nolimit === false && ($t >= $max_totpc || $i > $max_parts || $p < $min_partpc) && $i < $n - 2) {
				array_push($l, "Autres");
				array_push($d, $total - $c);
				$a[$i] = [ "Autres", $total - $c, (100. - $t) . "%" ];
				break;
			}
			array_push($l, $lbl[$i-1]);
			array_push($d, $dat[$i-1]);
			$t += $p;
			$c += $dat[$i-1];
            $a[$i] = [ $lbl[$i-1], $dat[$i-1], ($dat[$i-1] / $total * 100) . "%"];   
        }
		if (!property_exists($o, "total") || $o->total !== false) {
			$tlr = true;
			$a[$i+1] = [ "Total", $total, "100%"];
		}

		$bg = "";
		if (property_exists($o, "bgcolor") && $o->bgcolor == true) {
			$bg = "bg";
		} else {
			$bg = "white";
		}

		$str .= "<table class='spacing $bg'>\n\t<tr class='spacing'>\n";
        if (property_exists($o, "title")) {
			$t = $this->rpt_var_replace($o->title);
			$span = "colspan='2'";
			if (!property_exists($o, "orient") || $o->orient == "vertical") $span="";
            $str .= "\t\t<td class='spacing' $span><h2 style='text-align: center'>$t</h2></td>\n</tr>\n\t<tr class='spacing'>\n";
        }
		$str .= "<td class='spacing'>\n";
        $str .= $this->rpt_pie((object) [ "labels" => $l, "values" => $d ]);
		$str .= "</td>\n";
		if (!property_exists($o, "orient") || $o->orient == "vertical") {
			$str .= "</tr><tr class='spacing'>\n";
		}	
		$str .= "<td class='spacing'>\n";
		$str .= $this->rpt_table($a, $hdr, $tlr);
		$str .= "</td></tr></table>\n";

        return $str;
    }
}
function rpt_ctrl() {
	$rpt_name = false;
	$data     = false;
	
	$a = new args();

	if ($a->has("token")) {
		$data = scrm_un($a->val("token"));
	} else if ($a->has("rpt_name") && $a->val("rpt_name") !== false) {
		$rpt_name = $a->val("rpt_name");
		$j = file_get_contents("reports/$rpt_name");
		$data = json_decode($j);
		if ($data === false) {
			err("invalid json from $rpt_name");
			return;
		}
		$data->rpt_name = $rpt_name;
	} else if ($a->has("rpt_element")) {
		$data = json_decode($a->val("rpt_element"));
	} else {
		err("No rpt specification found");
		print("No report specified");
		return;
	}
	$vars = [];
	if ($a->has("rpt_vars")) { $vars = $a->val("rpt_vars"); }
	$r = new rpt($data, $vars);
	$st = hrtime(true);
	$h = $r->parse($data);
	if ($rpt_name) {
		$et = hrtime(true);
		stats_update($rpt_name, (($et - $st) / 1e9));
	}
	print($h."\n");
}

?>
