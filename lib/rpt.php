<?php

require_once("lib/svg.php");
require_once("lib/dbg_tools.php");
require_once("lib/db.php");
require_once("lib/query.php");
require_once("lib/locl.php");

class rpt {
	private $icons = [
		"duplicate" => "ic-doublons.png",
		"email"     => "ic-email.png",
		"postal"    => "ic-enveloppe.png",
		"phone"     => "ic-tel.png",
		"increase"  => "ic-level.png"
	];

    function __construct($o) {
        $this->fallback = 'fr_FR.UTF-8';
        $this->locale   = 'fr_FR.UTF-8';
		$this->lang     = '';
		$this->nsec     = 0;
		$this->nsub     = 0;

		$this->vars     = [];

        $this->colors  = [ "#0972e3",  "#055f8d", "#2a82ac", "#3399cc", "#25bbdb", "#89afe3", "#c5e4f2", "#89d9e3", "#d4f2f7" ];

		if (file_exists("config/rpt.php")) {
			include("conf/rpt.php");
			foreach ([ "locale", "lang", "colors" ] as $k) {
				if (isset("rpt_$k")) $this->$k = ${"rpt_$k"};
			}
		}
	
        if (is_object($o) && property_exists($o, "report") && is_object($o->report)) {
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
    }

	function rpt_db($o) {
		$dbopts = null;
		if (property_exists($o, "dbs")) {
			if (property_exists($o, "dbuser") && property_exists($o, "dbpass")) {
				if (property_exists($o, "dbopts")) {
					$this->odb = new db($o->dbs, $o->dbuser, $o->dbpass, $o->dbopts); 
				} else { 
					$this->odb = new db($o->dbs, $o->dbuser, $o->dbpass); 
				}
			} else {
				$this->odb = new db($o->dbs);
			}
		} else {
			$this->odb = new db();
		}
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
	function count_parse($str) {
		if (substr($str, 0, 4) == "sql(") {
			$q = new query(substr($str, 4, -1), $this->odb);
			$o = $q->obj();
			if (!is_object($o) || !property_exists($o, "count")) return false;
			
			return $o->count;
		}
		if (substr($str, 0, 4) == "var(") {
			return $this->var_get(substr($str, 4, -1));
		}
		return $str;
	}
	function range_parse($str) {
		if (substr($str, 0, 4) == "sql(") {
		
			$q = new query(substr($str, 4, -1), $this->odb);
			$arr = [];
			while ($d = $q->data()) {
				foreach ($d as $k => $v) {
					$arr[$k] = $v;
				}	
			}
			return $arr;
		}
		return [];
	}
	function data_parse($str) {
		if (substr($str, 0, 4) == "sql(") {
			$q = new query(substr($str, 4, -1), $this->odb);
			$arr = [];
			while ($d = $q->obj()) {
				$arr[$d->month] = $d->count;
			}
			return $arr;
		}
		if (substr($data, 0, 4) == "var(") {
			return $this->var_get(substr($str, 4, -1));
		}
		return $str;
	}
    function parse($o) {
        $str = "";
		if (is_null($o) || $o === false) return;
		if (!is_object($o) && !is_array($o)) {
			_err("invalid object: " . print_r($o, TRUE));
			return $str;
		}
        foreach ($o as $k => $v) {
            #print("------------------------\n");
            #print_r($v); print("\n");
            #print("------------------------\n");
            if (substr($k, 0, 4) == "rpt_") {
                #print("--> $k\n");
                if (!method_exists($this, $k)) {
                    _err("Rpt has no method $k, skipping block");
                } else {
                    $str .= $this->$k($v);
                }
            }
        }
        return $str;
    }
	function rpt_var($o) {
		$this->var_set($o->var, $this->count_parse($o->val));
	}
	function var_set($k, $v) {
		$this->vars[$k] = $v;
	}
	function var_get($k) {
		if (array_key_exists($k, $this->vars)) return $this->vars[$k];
		return false;
	}
    function rpt_pages($o) {
        $str = "";
        foreach ($o as $p) {    
            $str .="\n<div class='page-break'>\n" . $this->parse($p) . "</div>\n";
        }
        return $str;
    }
    function rpt_header($o) {
		$str = "";
/******
		if (file_exists("header.html")) $str .= file_get_contents("header.html"); 

		$str .= "<body>\n<style>\n" . file_get_contents("style.css") . "</style>\n";

        if (!is_object($o) || (!property_exists($o, "rpt_logo") && (!property_exists($o, "rpt_headlines") || !is_array($o->rpt_headlines)))) {
			#_warn("badly formed header skipped");
            return $str;
        }
		if (property_exists($o, "logo") && !file_exists($o->logo)) {
			$o->logo = "images/logo.png";
		}
        if (!property_exists($o, "rpt_headlines") || !is_array($o->rpt_headlines)) {
            $str .= "<header id='header'>\n<table class='entete'>\n\t<tr><td><img class='logo' src='". $this->get_image($o->logo) . "'/></td></tr>\n</table>\n</header>\n";
            return $str;
        } 

        $str .= "<header id='header'>";

        $n = count($o->rpt_headlines);
        $str .= "<table class='entete'>\n\t<tr><td rowspan='$n'>";
        if (property_exists($o, "logo")) {
            $str .= "<img class='logo' src='" . $this->get_image($o->logo) . "'/>";
        } 
        $str .= "</td>\n";
        $i = 0;
        foreach ($o->rpt_headlines as $h) {
            if ($i > 0) {
                $str .= "\t<tr>";
            }
            $i++;
            $str .= "<td style='text-align: right;'>$h->label:</td><td>$h->value</td></tr>\n";
        }
        $str .= "</table>\n</header>\n";
******/
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
        $str = "";
        foreach ($o as $p) {
			$id = "s". $this->secid();
			$class = "class='section'";
			if (property_exists($p, "bgcolor") && $p->bgcolor == true) {
				$class ="class='section bg'";
			}
			if (property_exists($p, "title")) {
				$str .= "\n<div class='st'><h1 onclick='e=document.getElementById(\"$id\");if (e.style.display ===\"none\") e.style.display=\"block\"; else e.style.display=\"none\";'>$p->title</h1>\n";
			}
			$str .= "<div $class id='$id'>\n";
            $str .= $this->parse($p);
			$str .= "</div></div>\n";
        }
        return $str;
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
			if (property_exists($p, "title"))
            	$str .= "\n<h2 $style>$p->title</h2>\n";
			if (property_exists($p, "subtitle"))
            	$str .= "\n<h3 $style>$p->subtitle</h3>\n";
            $str .= $this->parse($p);
			$str .= "</div>\n";
        }
        return $str;
    }
    function rpt_table($o, $hdr = true) {
        if (!is_array($o)) return "";
        $str = "\t<table class='results'>\n";
        $i = 0;
		if (array_key_exists("header", $o)) $hdr = $o->header;
		if (array_key_exists("hdr", $o))    $hdr = $o->hdr;
        foreach ($o as $l) {
            $str .= "\t\t<tr>";
            $i++;
            foreach ($l as $c) {
                if ($i == 1) {
					if  ($hdr) 
						$str .= "<th>" . $this->locl->format($c) . "</th>";
					else
						$str .= "<td><b>" . $this->locl->format($c) . "</b></td>";
                } else {
                    $str .= "<td>" . $this->locl->format($c) . "</td>";
                }
            }
            $str .= "</tr>\n";
        }
		$str .= "\t</table>\n";
        return $str;
    }
    function rpt_table_noheader($o) {
        return $this->rpt_table($o, false);
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


		if (substr($o->count, 0, 4) == 'sql(') {
			$q  = new query(substr($o->count, 4, -1), $this->odb);
			$count = $q->obj()->count;
		} else if (substr($o->count, 0, 4) == 'var(')
			$count = $this->var_get(substr($o->count, 4, -1));
		else $count = $o->count;	

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
			if (property_exists($b, "subtitle")) $subtitle = $b->subtitle;
			else $subtitle = "";

			$str .= "\t\t<tr><td class='ligne4'>$subtitle</td></tr>\n";
			
            $str .= "\t</table>\n</td>";
        }
        $str .= "</tr></table>\n";
        return $str;
    }
	function rpt_graph($o) {
		$data = [];
		if (!property_exists($o, "data")) {
			_err("badly formed rpt_graph block: no data");
			return "";
		}
		if (is_array($o->data)) {
			foreach ($o->data as $series) {
				$data[$series->name] = $this->data_parse($series->value);
			}
		/* } else if (is_string($o->data)) {
			$data = $this->data_parse($o->data);
			if (!is_array($data)) {
				return "";
			} */
		} else {
			_err("badly formed rpt_graph : invalid data (array or action strinc)");
			return "";
		}
			
		$w = 500;
		$h = 250;
		if (property_exists($o, "width"))  $w = $o->width;
		if (property_exists($o, "height")) $h = $o->height;
		$opts = [];
		foreach (["xgrid" => true, "ygrid" => true, "title" => null] as $p => $v) {
			if (property_exists($o, $p)) $opts[$p] = $o->$p;
			else if ($v != null) $opts[$p] = $v;
		}

		$svg = new svg($w, $h);
		$svg->colors($this->colors); 
		return $svg->graph($data, $opts);
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
	
        if (!property_exists($o, "labels")) {
			if (!property_exists($o, "data")) {
                _err("badly formed rpt_breakdown block ignored (no label and no data)");
				return "";
			} else {
				if (substr($o->data, 0, 4) == 'sql(') {
					$q = new query(substr($o->data, 4, -1), $this->odb);
					while ($oo = $q->obj()) {
						if ($oo->count != 0) {
							array_push($lbl, $oo->label);
							array_push($dat, $oo->count);
							$total += $oo->count;
						}
					}
				}
			}
		} else if (!property_exists($o, "data")) {
			_err("badly formed rpt_breakdown block ignored (no data)");
			return "";
		} else {
			if (!is_array($o->labels)) {
				_error("badlu formed rpt_breakdown block ignored (label isn't an array " . print_r($o, FALSE));
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

        $l = $d = [];
		
		$a = [];
		$t = 0;
		$c = 0;
	
		$n = count($dat);
        for ($i = 0; $i < $n; $i++) {
			$p = $dat[$i] / $total * 100;
			# limit to total = 95% or 8 parts or < 2% (if not the last item):
			if (($t >= 95 || $i > 8 || $p < 2) && $i < $n - 2) {
				array_push($l, "Autres");
				array_push($d, $total - $c);
				$a[$i] = [ "Autres", $total - $c, (100. - $t) . "%" ];
				break;
			}
			array_push($l, $lbl[$i]);
			array_push($d, $dat[$i]);
			$t += $p;
			$c += $dat[$i];
            $a[$i] = [ $lbl[$i], $dat[$i], ($dat[$i] / $total * 100) . "%"];   
        }

		$bg = "";
		if (property_exists($o, "bgcolor") && $o->bgcolor == true) {
			$bg = "bg";
		} else {
			$bg = "white";
		}

		$str .= "<table class='spacing $bg'>\n\t<tr class='spacing'>\n";
        if (property_exists($o, "title")) {
			$span = "colspan='2'";
			if (!property_exists($o, "orient") || $o->orient == "vertical") $span="";
            $str .= "\t\t<td class='spacing' $span><h2 style='text-align: center'>$o->title</h2></td>\n</tr>\n\t<tr class='spacing'>\n";
        }
		$str .= "<td class='spacing'>\n";
        $str .= $this->rpt_pie((object) [ "labels" => $l, "values" => $d ]);
		$str .= "</td>\n";
		if (!property_exists($o, "orient") || $o->orient == "vertical") {
			$str .= "</tr><tr class='spacing'>\n";
		}	
		$str .= "<td class='spacing'>\n";
		$str .= $this->rpt_table_noheader($a);
		$str .= "</td></tr></table>\n";

        return $str;
    }
}

?>
