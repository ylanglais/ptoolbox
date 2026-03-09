<?php

require_once("lib/dbg_tools.php");
require_once("rcu/src.php");
require_once("rcu/dst.php");
require_once("rcu/ilog.php");
require_once("lib/util.php");

class map {
        private static $_map_expetion =  '/^map_link\(.*|^map_vlink\(.*|^map_to_ref\(.*|^map_vref\(.*|^map_dst\(.*/';
	static function conf_valid($conf) {
		$errs = [];
		$greq = [ "engversion", "flux", "sources", "dest", "map" ];
		if (is_string($conf)) $conf = json_decode($conf);

		if ($conf === false || $conf === null || !is_object($conf)) return [ "conf is not an object" ];

		foreach ($greq as $r) if (!property_exists($conf, $r)) array_push($errs, "missing $r property");

		if (!is_array($conf->sources)) array_push($errs, "sources is not an array");
		else {
			$has_main = false;
			$i = 0;
			foreach ($conf->sources as $s) {
				if (property_exists($s, "name")) $name = $s->name;
				else                             $name = $i;

				if (($r = src::validate($s)) !== true) array_push($errs, ["source $name" => $r ]);
				if ($s->type == "main") {
					if ($has_main !== false) {
						array_push($errs, "more than one main ($has_main and $name)");
					} else $has_name = $name;
				}
				$i++;
			}
		}
		if (property_exists($conf, "dest") && ($r = dst::validate($conf))          !== true) array_push($errs, ["dest" => $r ]);
		if (property_exists($conf, "map" ) && ($r = map::map_validate($conf->map)) !== true) array_push($errs, [ "map" => $r ]);
		return $errs == [] ? true : $errs;
	}
	function __construct($conf) {
		global $argv;

                if ($this->conf_valid($conf) !== true) {
			_err("invalid configuration");
			return;
		}
		$this->src = [];
		$this->flux = $conf->flux;
		foreach ($conf->sources as $src) {
			$this->src[$src->name] = new src($src);
			if ($src->type == "main") $this->main = $src->name;
		}
		$this->dst = new dst($conf);
		$this->map = $conf->map;
                $this->fragments = $conf->fragments;
		$this->log = new ilog(false, $this->flux, getmypid(), $this->src[$this->main]->srcname(), $argv[0]);
	}
	function process($progress_cb = null) {
		$nlines = $this->src[$this->main]->nlines();
			
		$this->log->set([ "nb_in" => ($nlines < 0 ? 0 : $nlines), "status" => "En cours"]);

		$lok = 0;
		$rej = 0;
		$iline = 0;

		while ($this->src[$this->main]->next()) {
			$iline++;
			$this->process_line();
			if (($e = $this->dst->line_validate()) !== true) {
				$rej++;
				$this->log->reject($this->src[$this->main]->srcname(), $this->src[$this->main]->key_value(), "Line $iline, error: $e");
			} else if ($this->dst->mode() == "line") {
				$lok++;
			}
			if ($progress_cb !== null) $progress_cb($iline, $nlines);
		}
		if (($e = $this->dst->flux_validate()) !== true) {
			$status = "rejeté";
			if ($this->dst->mode != "line") {
				$this->log->reject($this->src[$this->main]->srcname(), "all", "flux rejeté: $e");
			}
		} else $status = "Terminé";

		$this->log->set([ "nb_charge" => $lok, "nb_rejet" => $rej, "status" => $status, "date_fin" => $this->log->dbnow()]);
	}
	function process_line() {
		_dbg(__FUNCTION__);
		foreach ($this->map as $f) {
			$f = (array) $f;
			#
			# Check destination is set:
			if (!array_key_exists("dest", $f)) {
				_warn("no destination property, skipping");
				continue;
			}
			#
			# Check direct reference to main source:
			if (array_key_exists("main", $f)) {
				$this->dst->set($f["dest"], $this->src[$this->main]->value($f["main"]));
				continue;
			} 
			#
			# Check if we have a type descriptor for map_value:
			$type = false;
			if (array_key_exists("dest_type", $f)) {
				$type = $f["dest_type"];
				unset($f["dest_type"]);
			}
			#
			# Check for other keywords:	
			foreach ($f as $k => $v) {                            
                                preg_match(self::$_map_expetion, $v, $m);                                                                
				if (in_array($k, ["dest", "main", "dest_type"]) && count($m) == 0 ) continue;
				#
				# Check reference to secondary source:
                                if ($k == 'map_value' || count($m) == 1) $this->dst->set($f["dest"], $this->map_parse($v), $type);
				else                 _warn("unknown keyword \"$k\"");
				
			}
		}
		return true;
	}
	static function map_validate($conf) {
		$errs = [];
		$i = 0;
		if (!is_array($conf)) return "map is not an array";
		foreach ($conf as $e) {
			$n = $i;
                        
			if (!property_exists($e, "dest")) array_push($errs, "mapping component $n missing dest");
			else $n .= ": $e->dest";

                        preg_match(self::$_map_expetion, $e->dest, $m);

                        if ((!(property_exists($e, "main") || property_exists($e, "map_value")) ) && count($m) ==0)
				array_push($errs, "$n has no \"main\" nor \"value\" and \"link\" data");

			if (property_exists($e, "value") && ($r = map::value_validate($e->value)) != true) 
				array_push($errs, [ "$n value" => $r ]);
		}
		return $errs == [] ? true : $errs;
	}
	function map_value_validate() {
		# TODO:
		return true;
	}	

	function map_parse($str) {
		$re = '/(map_[a-z_]*)\(((?>[^()]|(?R))*)\)/'; #ok
                
		while (preg_match($re, $str, $m)) {
			$all = $m[0];
			$fct = $m[1];
			$prm = $m[2];                         
			if ($fct == "map_src" || $m[1] == "map_source") {
				$r = $this->map_src($prm);
			} elseif ($fct == "map_ref") {
				$r = $this->map_ref($this->args_parse($prm));
                        } elseif ($fct == "map_dst") {
				$r = $this->map_dst($this->args_parse($prm));
                        } elseif ($fct == "map_genuuid") {
				$r = gen_uuid();
                        } elseif ($fct == "map_link") {
				$r = $this->map_link($this->args_parse($prm));
                        } elseif ($fct == "map_vlink") {
				$r = $this->map_vlink($this->args_parse($prm));			
                        } elseif ($fct == "map_to_ref") {
				$r = $this->map_to_ref($this->args_parse($prm));
                        } elseif ($fct == "map_vref") {
				$r = $this->map_vref($this->args_parse($prm));        
                        } else if (method_exists($this, $fct)) {
				$r = $this->{$fct}($this->args_parse($prm));
			} else {
				_warn("bad function $fct"); 
			}
			$str =  str_replace($all, $r, $str);
		}
		return $str;
	}
	function map_src($fld) {
		return $this->src[$this->main]->value($fld);
	}
        
        function map_dst($items) {
            $c = count($items);
            $mode ="";
            if ($c !== 2 && $c !== 3) {
                _err("invalid arg count ($c) args instead of 3");
                return false;
            }
            if(strstr($items[1],"&")){
                parse_str($items[1], $output);
                foreach($output as $k => $v)
                $values[$k] = $this->map_src($v);
            }else $values = $this->map_src($items[1]);
            if(isset($items[2]))
                $mode = $items[2];
            return $this->dst->map_dst($items[0],$values,$mode);
	}
        
        function map_vlink($items) {
            // 0 - table link
            // 1 - data from file to table link
            // 2 - table ref
            // 3 - data from file to table ref
            // type 
            if (($c = count($items)) < 4) {
                _err("invalid arg count ($c args instead of 4 or 5)");
                return false;
            }
            
            if(isset($items[4])) $dst_items = array($items[2],$items[3],$items[4]);
            else $dst_items = array($items[2],$items[3]);      
            $this->map_dst($dst_items);
            if(strstr($items[3],"&")){                
                $items[3] = $this->get_ref_name($items[3],$items[2]);
            }
            return $this->dst->map_link($this->map_src($items[3]),$items[2]);       
        }
        
        function map_to_ref($items){
            if (($c = count($items)) != 3) {
		_err("invalid arg count ($c args instead of 2");
		return false;
            }
            $this->dst->map_to_ref($items[0],$items[1],$this->map_src($items[2]));            
        }
        
        function map_vref($items){
            if (($c = count($items)) != 5) {
				_err("invalid arg count ($c args instead of 5");
				return false;
            }
            $this->dst->map_vref($items[0],$items[1],$this->map_src($items[1]),$items[2],$this->map_src($items[3]),$items[4]);            
        }
        
        function get_ref_name($str,$src){
            $tabs = array();                
            parse_str($str, $output);
            foreach($output as $k => $v)
            $tabs[$k] = $v;

            foreach($this->fragments as $fragment){
                foreach($fragment as $k => $v){
                    if($k == "table" && $src == $v){
                        $ref = $fragment->ref;
                    }
                }
            }
            return $tabs[$ref];
        }
        
        function map_link($items) {
            if (($c = count($items)) < 4) {
                _err("invalid arg count ($c args instead of 4 or 5)");
                return false;
            }
            if(isset($items[4])) $dst_items = array($items[2],$items[3],$items[4]);
            else $dst_items = array($items[2],$items[3]);                        
            $this->map_dst($dst_items);
            
            return $this->dst->map_link($this->map_src($items[3]));
        }
        
	function map_ref($items) {
		if (($c = count($items)) != 2) {
			_err("invalid arg count ($c args instead of 2");
			return false;
		}
		if (!array_key_exists($items[0], $this->src)) {
			_err("no such data source $items[0]");
			return false;
		}

		$ref = $this->src[$items[0]];
		$mai = $this->src[$this->main];
	
		return $ref->ref($mai->value($ref->key_ref()), $items[1]);
		#return $this->src[$items[0]]->ref($this->src[$this->main]->value(  $this->src[$items[0]]->key_ref()), $items[1]);
	}
	#
	# comparaisons:
	function map_equal($items) {
		if (($c = count($items)) != 2) {
			_err("invalid arg count ($c args instead of 2");
			return false;
		}
		if ($items[0] == $items[1]) return "1";
		return "0";
	}
	function map_greater_or_equal($items) {
		if (($c = count($items)) != 2) {
			_err("invalid arg count ($c args instead of 2");
			return false;
		}
		if ($items[0] >= $items[1]) return "1";
		return "0";
	}
	function map_greater($items) {
		if (($c = count($items)) != 2) {
			_err("invalid arg count ($c args instead of 2");
			return false;
		}
		if ($items[0] > $items[1]) return "1";
		return "0";
	}
	function map_less_or_equal($items) {
		if (($c = count($items)) != 2) {
			_err("invalid arg count ($c args instead of 2");
			return false;
		}
		if ($items[0] <= $items[1]) return "1";
		return "0";
	}
	function map_less($items) {
		if (($c = count($items)) != 2) {
			_err("invalid arg count ($c args instead of 2");
			return false;
		}
		if ($items[0] < $items[1]) return "1";
		return "0";
	}
	function map_if($items) {
		if (($c = count($items)) != 3) {
			_err("invalid if expression ($c args instead of 3)");
			return false;
		}
		if ($items[0] == "1") return $items[1];
		return $items[2];
	}
	function map_and($items) {
		if (($c = count($items)) != 2) {
			_err("invalid if expression ($c args instead of 2)");
			return false;
		}
		return ((bool) $items[0] && (bool) $items[1]) == true ? "1" : "0";
	}
	function map_or($items) {
		if (($c = count($items)) != 2) {
			_err("invalid if expression ($c args instead of 2)");
			return false;
		}
		return ((bool) $items[0] || (bool) $items[1]) == true ? "1" : "0";
	}
	function map_not($items) {
		if (($c = count($items)) != 1) {
			_err("invalid if expression ($c args instead of 1)");
			return false;
		}
		return ($items[0] == "1" ? "0" : "1");
	}
	#
	# String functions:
	function map_concat($items) {
		$s = "";
		foreach ($items as $i) {
			$s .= $i;
		}
		return $s;
	}

	function map_substr($items) {
		$c = count($items);
		if ($c == 0) {
			_err("invalid substr expression ($c args instead of at least 1)");
			return false;
		}
		if ($c == 1) return $items[0];
		if ($c >  2) return substr($items[0], (int) $items[1], (int) $items[2]);
		return substr($items[0], (int) $items[1]);
	}
	#
	# constant:
	function map_const($items) {
		if (($c = count($items)) != 1) {
			_err("invalid if expression ($c args instead of 1)");
			return false;
		}
		return $items[0];
	}
	function args_parse($str) {
		$inq = 0;
		$items = [];
		$ss = "";
		$n = strlen($str);
		for ($i = 0; $i < $n; $i++) {
			if ($ss == "" && ($str[$i] == ' ' || $str[$i] == '\t')) continue;
			if ($ss == "" && $str[$i] == "'") {
				for ($j = $i + 1; $j < $n; $j++) {
					if ($str[$j] == "'" && ($j == 0 || $str[$j - 1] != '\\')) break;
					$ss .= $str[$j]; 
				} 
				if ($str[$j] == "'") {
					$i = $j;
					continue;
				}
				#
				# unballanced "'"=> treat ' as normal caracter:
				$ss = "'";
			}
			if ($str[$i] == ',') {
				array_push($items, trim($ss));
				$ss = "";	
				continue;
			}
			$ss .= $str[$i];
		}
		if ($ss != "") array_push($items, trim($ss));
		return $items;
	}
}
