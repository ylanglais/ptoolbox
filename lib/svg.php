<?php

require_once("lib/date_util.php");
require_once("lib/locl.php");
require_once("lib/style.php");

function convert_none($z) {
	return $z;
}
function stamp_to_unix($z) {
	return d2e($z);
}
function unix_to_stamp($z) {
	return e2d($z);
}
function unix_to_date($z) {
	return unix_to_stamp($z);
	#return 	date('Y/m/d', $z);
}
function unix_to_time($z) {
	return 	date('H:i:s', $z);
}

class svg {
	public $height;
	public $width;
	public $scl;
	private $conf;
	
	function init($class) {
		#$outs = "<div class='$class' width='".$this->width."px' height='".$this->height."px' >\n";
		#$outs .= "<svg width='".$this->width."px' height='".$this->height."px' >\n";
		$pc = "100%";
		if ($class == "pie" && $this->height > 200) $pc = "75%";
		$hh = $this->height . "px";
		$outs  = "<div class='$class' width='100%' height='100%' style='min-height:$hh;text-align:center;vertical-align:middle'>\n";
		$outs .= "<svg width='$pc'    height='$pc' viewBox='0 0 $this->width $this->height' preserveAspectRatio='xMidYMid meet' style='height: auto;'>\n";
		return $outs;
	}

	function fini() {
		return "</svg>\n</div>\n";
	}

	function reduce($x, $datatype = null) {
		$k =             1000;
		$M =          1000000;
		$G =       1000000000;
		$T =    1000000000000;
		$P = 1000000000000000;
		$m =                 .001;
		$u = 		   	     .000001;
		$n = 			     .000000001;
		$p = 			     .000000000001;
		$f = 			     .000000000000001;

		if ($x >= 0) { 
			$s = 1; 
		} else {
			$x = -$x;
			$x = -1;
		}

		if (!is_numeric($x)) {
			if ($datatype == 'datetime' || $datatype == 'date') {
				if (substr($x, 10, 9) == ' 00:00:00') {
					return substr($x, 0, 10);
				#} else if (substr($x, 13, 6) == ':00:00') {
					return substr($x, 0, 13);
				} else if (substr($x, 16, 3) == ':00') {
					return substr($x, 0, 16);
				}
				return $x;
			} else if ($datatype == 'time' && substr($x, 5, 3) == ':00') {
				return substr($x, 0, 5);
			}
			return $x;
		}

		if ($x >= $P) {
			$x = "". ($s * $x / $P) . "P";
		} else if ($x >= $T) {
			$x = "". ($s * $x / $T) . "T";
		} else if ($x >= $G) {
			$x = "". ($s * $x / $G) . "G";
		} else if ($x >= $M) {
			$x = "". ($s * $x / $M) . "M";
		} else if ($x >= $k) {
			$x = "". ($s * $x / $k) . "k";
		} else if ($x < 1 && $x > 0) {
			if ($x < $p) {
				$x = "" . ($s * $x / $f) . "f";
			} else if ($x < $n) {
				$x = "" . ($s * $x / $p) . "p";
			} else if ($x < $u) {
				$x = "" . ($s * $x / $n) . "n";
			} else if ($x < $m) {
				$x = "" . ($s * $x / $u) . "µ";
			} else if ($x < 1) {
				$x = "" . ($s * $x / $m) . "m";
			}
		}
		return $x;
	}

	function noquo($str) {
		return str_replace("'", "", $str);
	}
	
	function __construct($width = 200, $height = 0, $conf = null) {
		if ($height == 0) {
			$height = $width;
		}
		$this->conf   = $conf;
		$this->tick   = 7;
		$this->margin = 50;
		$this->height = $height;
		$this->width  = $width;
		$this->size   = min($width, $height);
		#$this->xscale = $this->size / 2;
		#$this->yscale = $this->size / 2;
		$this->xscale = $this->width  / 2;
		$this->yscale = $this->height / 2;
		$this->Ox     = $width  / 2;
		$this->Oy     = $height / 2;
		$this->size   = min($width, $height);
		$this->scale  = $this->size / 2;
		$this->colors = ['#8eb021', '#ffd351', '#3b7fc4', '#d04437', '#FFF400', '#654982', '#f691b2', '#999999', '#815b3a', '#f79232', '#59afe1', '#f15c75', '#00FF26', '#0500FF', '#FF0900', '#E8FF00', '#00FFD3', '#9400FF', '#FF04', '#FFC800', '#B5FF00', '#2A7C9C', '#2C2A9C', '#722A9C', '#9C2A9C', '#9C2A63', '#9C2A2A', '#9C772A', '#979C2A', '#649C2A'];

		if (file_exists("conf/svg.php")) {
			include("conf/svg.php");
			if (isset($svg_colors)) $this->colors = $svg_colors;
		}

		$this->ncolors = count($this->colors);

		$r = false;

		if (is_array($conf) && array_key_exists("locale", $conf)) 
			$this->locl = new locl($conf["locale"]);
		else 
			$this->locl = new locl();
	} 

	function colors($colors) {
		$this->colors  = $colors;
		$this->ncolors = count($this->colors);
	}
	function color($i) {
		return $this->colors[$i % $this->ncolors];
	}

	function title($x, $y, $title) {
		#
		# Draw title if required: 
			return "<text class='graph title' style='fill: ".style::value("svg_graph_title")."' x='$x' y='$y' text-anchor='middle' alignment-baseline='middle'>$title</text>\n";
	}

	function graph($values, $conf = null) {
		if ($conf != null) $this->conf = $conf;

		$outs = "";
		$outs .= $this->init('graph');

		$c = 0;
		$cmax = 0;
		$max = 0;
		$xlbls = [];
		foreach ($values as $series) {
			$c = 0;
			foreach ($series as $k => $v) {
				$c++;
				if ($v > $max) $max = $v;
				if (!in_array($k, $xlbls)) {
					array_push($xlbls, $k);
				}
			}
			if ($c > $cmax) $cmax = $c;
		}
		#ksort($xlbls);

		$margin = $this->margin;
		$tick   = $this->tick;

		$x0 = $margin;
		$x1 = $this->width  - $margin;

		$y0 = $this->height - $margin; 
		$y1 = $margin;

		if ($cmax < 2) return $this->empty_graph();
		
		#
		# Compute scales :
		$xscale = (($x1 - $x0)) / ($cmax - 1);
		if ($max != 0.) 
			$yscale = (($y1 - $y0) + 5) / $max;
			#$yscale = (($y1 - $y0) + $margin) / $max;
		else $yscale = 0.;
		#$yscale = (($y1 - $y0) + $margin) / $max;


		$outs .= $this->title(($x1 - $x0) / 2 + $margin, $margin / 2, $this->param("title"));

		#
		# Draw borders: 
		$outs .= $this->borders();

		#
		# Draw xunits if required:
		if ($this->param('xunits')) {
			$outs .= "<text class='graph units xunits' style='fill:".style::value("svg_graph_units_xunit")."' x='$x1'  y='". ($y0 + $margin/2) ."' text-anchor='middle'>".$this->param('xunits')."</text>\n";
		}	

		#
		# Draw ylabel if required:
		if ($this->param('yunits')) {
			$outs .= "<text class='graph units yunits' style='fill:".style::value("svg_graph_units_yunit")."'x='$x0'  y='". ($y1 - $margin/2) ."' text-anchor='middle'>".$this->param('yunits')."</text>\n";
		}

		#
		# max 5 x-labels:
		$lblsteps = max(1, round(($cmax) / 4.) - 1);
	
		$ticksteps = max(1, $lblsteps / 2.);

		# 
		# Draw x-tick:
		for ($i = 0; $i < $cmax; $i++) {
			$x = $i * $xscale + $x0;
			if (!($i % $ticksteps)) {
				$outs .= "<line class='graph ticks xticks' x1='$x' y1='". ($y0 + $tick/3)."' x2='$x' y2='".($y0 - $tick/3)."' style='stroke:".style::value("svg_graph_ticks_xticks")."; stroke-width: 1px;'/>\n";
			}
		#	_err("$i vs cmax: $cmax\n");
			if (!($i % $lblsteps)) {
				#$outs .= "<text x='$x'  y='". ($y0 + $tick) ."' font-size='70%' text-anchor='middle' alignment-baseline='before-edge'>". $xlbls[$i]."</text>\n";
				$outs .= "<line class='graph ticks xticks' x1='$x' y1='". ($y0 + $tick/2)."' x2='$x' y2='".($y0 - $tick/2)."' style='stroke:".style::value("svg_graph_ticks_xticks")."; stroke-width:1;'/>\n";
				$outs .= "<text class='graph label xlabel' style='fill:".style::value('svg_graph_label_xlabel')."' x='$x'  y='". ($y0 + 2*$tick) ."' text-anchor='middle' dominant-baseline='middle'>". $xlbls[$i]."</text>\n";
				if ($this->param('xgrid')) {
					$outs .= "<line class='graph grid xgrid' style='stroke:".style::value('svg_graph_grid_xgrid')."; stroke-width: .5px;'  x1='$x' y1='$y0' x2='$x' y2='$y1'/>\n";
				} 
			}
		}

		$p = 0;
		$m = $max;
		while ($m / 10 > 10) {
			$p++;
			$m /= 10;
		}

		$i0 = max(1, round($max / 5., - $p));
		
		#
		# Draw y-tick:	
		for ($i = $i0; $i <= $max; $i += $i0) {
			$y = $i * $yscale + $y0;
			if ($this->param('ygrid')) {
				$outs .= "<line class='graph grid ygrid' x1='$x0' y1='$y' x2='$x1' y2='$y' style='stroke:".style::value("svg_graph_grid_ygrid")."; stroke-width:1;'/>\n";
			}
			$outs .= "<line class='graph ticks yticks' x1='".($x0 - $tick/2)."' y1='$y' x2='".($x0 + $tick/2)."' y2='$y' style='stroke:".style::value("svg_graph_ticks_yticks")."; stroke-width:1;'/>\n";

			if (!$this->param('ynoreduce')) 
				$ri = $this->reduce($i);
			else 
				$ri = $i;
			$outs .= "<text class='graph label ylabel' style='fill:".style::value("svg_graph_label_ylabel")."' x='". ($x0 - $tick) . "' y='$y' alignment-baseline='middle' text-anchor='end' >$ri</text>\n";
		}
		if ($i * $yscale + $y0 > $y1) {
			$y = $i * $yscale + $y0;
			if ($this->param('ygrid')) {
				$outs .= "<line class='graph grid ygrid' x1='$x0' y1='$y' x2='$x1' y2='$y' style='stroke:".style::value('svg_graph_grid_ygrid')."; stroke-width:1;'/>\n";
			}
			$outs .= "<line class='graph ticks yticks' x1='".($x0 - $tick/2)."' y1='$y' x2='".($x0 + $tick/2)."' y2='$y' style='stroke:".style::value('svg_graph_ticks_yticks')."; stroke-width:1;'/>\n";

			if (!$this->param('ynoreduce'))
				$ri = $this->reduce($i);
			else
				$ri = $i;
			$outs .= "<text class='graph label ylabel' style='fill:".style::value('svg_graph_label_ylabel')."' x='". ($x0 - $tick) . "' y='$y' alignment-baseline='middle' text-anchor='end' >$ri</text>\n";
		}

		$s = 0;
		foreach ($values as $serie) {
			$n     =  0;
			$line = "<polyline points='";

			foreach ($serie as $k => $v) {
				$x = $n * $xscale + $x0;
				$y = $v * $yscale + $y0;
				$line .= "$x,$y ";
				$n++;
			}
			$line .= "' style='fill:none;stroke:". $this->color($s) .";stroke-width:2'/>";
			$outs .= "$line\n";
			$s++;
		}	
		
		#
		# Legend:
		$i = 0;
		foreach (array_keys($values) as $t) {
			$x0 = $this->Ox + 7 * $this->xscale / 16;
			$x1 = $x0 + 20;  
			$y  = $margin + 10 * ($i + 1);
			$outs .= "<line class='graph legend' x1='$x0' y1='$y' x2='$x1' y2='$y' style='stroke:". $this->color($i).";stroke-width:2px;'/>\n";
			$x  = $x0 + 30;  
			$outs .= "<text class='graph legend' style='fill:".style::value("svg_graph_legend")."' x='$x' y='$y' text-anchor='start' >$t</text>\n";
			$i++;
		}
		
		$outs .= $this->fini();
		return $outs;
	}

	function xy_minmax($series, $convx, $convy) {
		$sd   = new StdClass();
		$sd->xmin = $sd->xmax = $sd->ymin = $sd->ymax = false;
		$sd->count = 0;
		foreach ($series as $xx => $yy) {
			$x = $convx($xx);
			$y = $convy($yy);
			$sd->count++;
			if ($sd->xmin === false) {
				$sd->xmin = $sd->xmax = $x;
				$sd->ymin = $sd->ymax = $y;
			} else {
				if ($x < $sd->xmin) $sd->xmin = $x;
				if ($x > $sd->xmax) $sd->xmax = $x;
				if ($y < $sd->ymin) $sd->ymin = $y;
				if ($y > $sd->ymax) $sd->ymax = $y;
			}
		} 
		return $sd;
	}

	function param($key) {
		if ($this->conf == null)    return false;
		if ($this->conf == null || !is_array($this->conf)) return false;
#	_err("key = ". print_r($key, true));
		if (!array_key_exists($key, $this->conf)) return false;
		return $this->conf[$key]; 
	}

	function borders() {
		$outs = "";
		if ($this->param('noborder') == false) {
			$x0 = $this->margin;
			$x1 = $this->width  - $this->margin;
			$y0 = $this->height - $this->margin; 
			$y1 = $this->margin;
			$outs .= "<line class='graph border bottom' style='stroke:".style::value("svg_graph_border_bottom")."' x1='$x0' y1='$y0' x2='$x1' y2='$y0' />\n";
			$outs .= "<line class='graph border left'   style='stroke:".style::value("svg_graph_border_left")."'   x1='$x0' y1='$y0' x2='$x0' y2='$y1' />\n";
			$outs .= "<line class='graph border top'    style='stroke:".style::value("svg_graph_border_top")."'    x1='$x0' y1='$y1' x2='$x1' y2='$y1' />\n";
			$outs .= "<line class='graph border right'  style='stroke:".style::value("svg_graph_border_right")."'  x1='$x1' y1='$y0' x2='$x1' y2='$y1' />\n";
		}
		return $outs;
	}

	function empty_graph() {
		$outs = "";
		#
		# Prepare div and svg:
		$outs .= $this->init('empty');
		$outs .= $this->borders(null);	
		$outs .= "<text class='graph empty' style='fill: ".style::value("svg_graph_empty")."' x='$this->Ox' y='$this->Oy' alignment-baseline='middle'>- No data -</text>\n";
		$outs .= "";
		$outs .= $this->fini();
		return $outs;
	}

	function reformat($datatype, $format, $x) {
		if (!$format) {
			return $x;
		}
		if ($datatype == 'datetime' || $datatype == 'date' || $datatype == 'time') {
			return date($format, strtotime($x));
		}
		return sprintf($format, $x);
	}

	function ticksize($min, $max, $datatype) {
		$d = ($max - $min);
		if ($datatype == 'date' || $datatype = 'datetime' || $datatype == 'time') {
			$t = [        1,       60,     60,    24,      30,     12 ];
			$u = [ 'second', 'minute', 'hour', 'day', 'month', 'year' ];
			$i = 0;
			foreach ($t as $a) {
				if ($d > 5 * $a) {
					$d /= $a;
					$i++;
				} else {
					break;
				}
			}	
			if ($d <= 5 * $a) $i--;
			$unit = $u[min($i, count($t))];
			$n    = max(round($d / 5), 1);
			
	#_err("ticksize / datetime / + $n $unit");
			return " + $n $unit";
			
		} 
		if ($d > 1) {
			$c = 1;
			while ($d > 10) {
				$c *= 10; $d /= 10;
			}
			if ($d <= 2.5) return $c / 2;
			if ($d <= 5)   return $c;
		} else {
			$c = 1;
			while ($d < .1) {
				$c /= 10; $d *= 10;
			}		
		}
		if ($d <= 2.5) return $c / 2;
		if ($d <= 5)   return $c;
		return 2 * $c;
	}

	function xticks_draw($xmin, $xmax, $xscale, $xtick, $x0, $y0, $y1, $yy0, $tick) {
		$outs = "";
		for ($x = $xmin; $x <= $xmax; $x += $xstick) {
			$xx = ($x - $xmin) * $xscale + $x0;
			$outs .= "<line class='graph stick xstick' x1='$xx' y1='". ($yy0 + $tick/4)."' x2='$xx' y2='".($yy0 - $tick/4)."'/>\n";
		}
	
		for ($x = $xmin; $x <= $xmax; $x += $xtick) {
			$xx = ($x - $xmin) * $xscale + $x0;
			if ($this->param('xgrid')) {
				$outs .= "<line class='graph grid xgrid' style='stroke:".style::value('svg_graph_grid_xgrid')."; stroke-width: .5px;'  x1='$xx' y1='$y0' x2='$xx' y2='$y1'/>\n";
			} 
			$outs .= "<line class='graph tick xtick' style='stroke:".style::value("svg_graph_ticks_xticks").";' x1='$xx' y1='". ($yy0 + $tick/2)."' x2='$xx' y2='".($yy0 - $tick/2)."'/>\n";
			
			$X = $x;
			#$X = $rconvx($x);
			if ($this->param('xlabelformat')) {
				$X = $this->reformat($this->param('xdatatype'), $this->param('xlabelformat'), $X);
			} else  if (!$this->param('xnoreduce')) {
				$X = $this->reduce($X, $this->param('xdatatype'));
			}
			$outs .= "<text class='graph label xlabel' style='fill:".style::value('svg_graph_label_xlabel')."' x='$xx'  y='". ($yy0 + 2*$tick) ."' text-anchor='middle'>$X</text>\n";
		}
		return $outs;
	}


	function xticks_datatime_draw($xmin, $xmax, $xscale, $xtick, $x0, $y0, $y1, $yy0, $tick, $convx, $rconvx) {
		$outs = "";
		#_err("xmin = $xmin, xmax = $xmax");
		for ($x = $xmin; $x <= $xmax; $x = $convx(datetime_shift($xtick, $rconvx($x)))) {
			#_err("$x -> ".$rconvx($x));
			$xx = ($x - $xmin) * $xscale + $x0;
			$outs .= "<line class='graph stick xstick' style='stroke:".style::value("svg_graph_ticks_xticks").";' x1='$xx' y1='". ($yy0 + $tick/4)."' x2='$xx' y2='".($yy0 - $tick/4)."'/>\n";
		}
	
		for ($x = $xmin; $x <= $xmax; $x = $convx(datetime_shift($xtick, $rconvx($x)))) {
			$xx = ($x - $xmin) * $xscale + $x0;
			if ($this->param('xgrid')) {
				$outs .= "<line class='graph grid xgrid' style='stroke:".style::value('svg_graph_grid_xgrid')."; stroke-width: .5px;'  x1='$xx' y1='$y0' x2='$xx' y2='$y1'/>\n";
			} 
			$outs .= "<line class='graph tick xtick' style='stroke:".style::value("svg_graph_ticks_xticks")."' x1='$xx' y1='". ($yy0 + $tick/2)."' x2='$xx' y2='".($yy0 - $tick/2)."'/>\n";
			
			$X = $rconvx($x);
			if ($this->param('xlabelformat')) {
				$X = $this->reformat($this->param('xdatatype'), $this->param('xlabelformat'), $X);
			} else if (!$this->param('xnoreduce')) {
				$X = $this->reduce($X, $this->param('xdatatype'));
			}
			$outs .= "<text class='graph label xlabel' style='fill: ".style::value("svg_graph_label_xlabel")."' x='$xx'  y='". ($yy0 + 2*$tick) ."' text-anchor='middle'>$X</text>\n";
		}
		return $outs;
	}

	function graph_xy($values, $conf = null) {
		if ($conf != null) $this->conf = $conf;
		$outs   = "";
		$series = [];
		$convx  = 'convert_none';
		$rconvx = 'convert_none';
		$convy  = 'convert_none';
		$rconvy = 'convert_none';

		#
		# Check x < y datatype for convertions:
		if ($this->param("xdatatype")) {
			$xdt = $this->param("xdatatype");
			if ($xdt == 'datetime') {
				$convx  = 'stamp_to_unix';
				$rconvx = 'unix_to_stamp';
			} else if ($xdt == 'date') {
				$convx  = 'stamp_to_unix';
				$rconvx = 'unix_to_date';
			} else if ($xdt == 'time') {
				$convx  = 'stamp_to_unix';
				$rconvx = 'unix_to_time';
			}
		}
		if ($this->param("ydatatype")) {
			$ydt = $this->param("ydatatype");
			if ($ydt == 'datetime') {
				$convy  = 'stamp_to_unix';
				$rconvy = 'unix_to_stamp';
			} else if ($ydt == 'date') {
				$convy  = 'stamp_to_unix';
				$rconvy = 'unix_to_date';
			} else if ($ydt == 'time') {
				$convy  = 'stamp_to_unix';
				$rconvy = 'unix_to_time';
			}
		}

		#
		# Analyse x/y data:
		$nodata = true;
		
		#
		# Get min/max x/y from data:	
		$xmin = $xmax = $ymin = $ymax = false;
		foreach ($values as $name => $ser) {
			$series[$name] = (object) [];
			if (count($ser) < 1) continue;
			$series[$name] = $this->xy_minmax($ser, $convx, $convy);
			if ($series[$name]->count > 0) $nodata = false;
			if ($xmin === false) {
				$xmin = $series[$name]->xmin;
				$xmax = $series[$name]->xmax;
				$ymin = $series[$name]->ymin;
				$ymax = $series[$name]->ymax;
			} else {
				if ($series[$name]->xmin < $xmin) $xmin = $series[$name]->xmin;
				if ($series[$name]->xmax > $xmax) $xmax = $series[$name]->xmax;
				if ($series[$name]->ymin < $ymin) $ymin = $series[$name]->ymin;
				if ($series[$name]->ymax > $ymax) $ymax = $series[$name]->ymax;
			}	
		}

		if ($nodata) { return $this->empty_graph(); }

		#
		# Correct ymin if > 0;
		if ($ymin > 0) $ymin = 0;
		#
		# Check if conf contains overload:
		if ($this->param('xmin') !== false) $xmin = $convx($this->param('xmin'));
		if ($this->param('xmax') !== false) $xmax = $convx($this->param('xmax'));
		if ($this->param('ymin') !== false) $ymin = $convy($this->param('ymin'));
		if ($this->param('ymax') !== false) $ymax = $convy($this->param('ymax'));

		if ($xmax <= $xmin) {
			_err("ATTTTTT xmin = $xmin while xmax = $xmax");
			return $this->empty_graph();
		}
		#
		#
		$margin = $this->margin;
		$tick   = $this->tick;

		#
		# Compute drawing area coordinates:
		$x0 = $margin;
		$x1 = $this->width  - $margin;

		$y0 = $this->height - $margin; 
		$y1 = $margin;

		#
		# Compute scales:
		$xscale = ($x1 - $x0) / ($xmax - $xmin);
		$yscale = ($y1 - $y0) / ($ymax - $ymin);

		#
		# Compute xtick: 
		$xtick = round($xmax - $xmin) / 6;
		

		if ($this->param('xtick')) {
			$xtick = $this->param('xtick');
		} 
		if ($xtick < 1) $xtick = 1;
		$xstick = round($xtick / 2.);

		if ($this->param('xstick')) {
			$xstick = $this->param('xstick');
		} 

		#
		# Avoid infinite loops:	
		if ($xtick == 0) {
			$xtick = $xstick = $xmax - $xmin;
		}

		if ($this->param('ytick')) {
			$ytick = $this->param('ytick');
		} else { 	
			#
			# Compute ytick: 
			$p = 0;
			$m = abs($ymax);
			if ($m > 1) {
				while ($m / 10 > 1) {
					$p++;
					$m /= 10;
				}
			} else {
				#
				# This must be tested!!!!:
				while ($m * 10 < 1) {
					$p++;
					$m *= 10;
				}

			}

#_err("ymax = $ymax");

			if (!$this->param('ymax')) {
				# raise ymax:
				$m  = $ymax  / pow(10, $p);
				$m1 = ceil($m)  * pow(10, $p);
				$m2 = floor($m) * pow(10, $p);
				$m3 = ($m2 + ($m1 - $m2) / 2);
				$m5 = ($m2 + ($m1 - $m2) / 5);

				if      ($ymax < $m5) $ymax = $m5; 
				else if ($ymax < $m3) $ymax = $m3; 
				else $ymax = $m1;

				# Recompute yscale:
				$yscale = ($y1 - $y0) / ($ymax - $ymin);
			}

			$ytick  = max(1, round(($ymax - $ymin) / 5., - ($p-1)));
			#_err("p = $p, ytick = $ytick");

		} 
		if ($this->param('ystick')) {
			$ystick = $this->param('ystick');
		}  else {
			$ystick = round($ytick / 2.);
		}

		#
		# Avoid infinite loops:	
		if ($ytick == 0) {
			$ytick = $ystick = $ymax - $ymin;
		}

		#
		# Prepare div and svg:
		$outs .= $this->init("graph_xy");

		#
		# Draw title if required: 
		if ($this->param("title")) $outs .= $this->title(($x1 - $x0) / 2 + $margin, $margin / 2, $this->param("title"));

		#
		# Draw borders: 
		$outs .= $this->borders();
	
		#
		# Draw axis:
		$xx0 = min(max(- $xmin * $xscale + $x0, $x0), $x1);
		$yy0 = max(min(- $ymin * $yscale + $y0, $y0), $y1);
		
		#$outs .= "xx0 = $xx0, yy0 =  $yy0\n";
		
		$outs .= "<line class='graph axis xaxis' style='stroke: ".style::value("svg_graph_axis_xaxis")."' x1='$x0'  y1='$yy0' x2='$x1'  y2='$yy0'/>\n";
		$outs .= "<line class='graph axis yaxis' style='stroke: ".style::value("svg_graph_axis_yaxis")."' x1='$xx0' y1='$y0'  x2='$xx0' y2='$y1'/>\n";
	
		#_err(">>> xmin = $xmin, xmax = $xmax, xtick = $xtick, xstick = $xstick\n");

		$xtick = $this->ticksize($xmin, $xmax, $this->param('xdatatype'));
		if (is_numeric($xtick)) {
			$outs .= $this->xticks_draw($xmin, $xmax, $xscale, $xtick, $x0, $y0, $y1, $yy0, $tick);
		} else {
			$outs .= $this->xticks_datatime_draw($xmin, $xmax, $xscale, $xtick, $x0, $y0, $y1, $yy0, $tick, $convx, $rconvx);
		}

		#
		# Draw xunits if required:
		if ($this->param('xunits')) {
			$outs .= "<text class='graph units xunits' style='fill: ".style::value("svg_graph_units_xunit")."' x='$x1'  y='". ($y0 + $margin/2) ."' text-anchor='middle'>".$this->locl->format($this->param('xunits'))."</text>\n";
		}

/**
		#
		# Draw y-tick:
		#for ($y = $ymin; $y <= $ymax; $y += $ystick) {
		#	$yy = ($y - $ymin) * $yscale + $y0;
		#	$outs .= "<line class='graph stick ystick' style='stroke: ".style::value("svg_graph_ticks_yticks")."' x1='".($xx0 + $tick/4)."' y1='$yy' x2='".($xx0 - $tick/4)."' y2='$yy'/>\n";
		#}
		for ($y = $ymin; $y <= $ymax; $y += $ytick) {
			$yy = ($y - $ymin) * $yscale + $y0;
			#$outs .= "<line class='graph tick ytick' style='stroke: ".style::value("svg_graph_ticks_yticks")."' x1='".($xx0 + $tick/2)."' y1='$yy' x2='".($xx0 - $tick/2)."' y2='$yy'/>\n";
			if ($this->param('ygrid')) {
				$outs .= "<line class='graph grid ygrid' style='stroke:".style::value("svg_graph_grid_ygrid")."; stroke-width: .5px;' x1='$x0' y1='$yy' x2='$x1' y2='$yy'/>\n";
			}
			#$outs .= "<line class='graph tick ytick' style='stroke: ".style::value("svg_graph_ticks_yticks")."' x1='".($xx0 + $tick/2)."' y1='$yy' x2='".($xx0 - $tick/2)."' y2='$yy'/>\n";

			$Y = $rconvy($y);
			if ($this->param('ylabelformat')) {
				$Y = $this->reformat($this->param('ydatatype'), $this->param('ylabelformat'), $Y);
			} else if (!$this->param('ynoreduce')) {
				$Y = $this->reduce($Y, $this->param('ydatatype'));
			}
			$outs .= "<text class='graph label ylabel' style='fill: ".style::value("svg_graph_label_xlabel")."' x='". ($xx0 - $tick) . "' y='$yy' text-anchor='end'>". $this->locl->format($Y). "</text>\n";
		}
*/
		#
		# Draw ylabel if required:
		#
		# Draw y-tick:
		for ($y = $ymin; $y <= $ymax; $y += $ystick) {
			$yy = ($y - $ymin) * $yscale + $y0;
			$outs .= "<line class='graph stick ystick' style='stroke:".style::value("svg_graph_ticks_yticks")."' x1='".($xx0 + $tick/4)."' y1='$yy' x2='".($xx0 - $tick/4)."' y2='$yy'/>\n";
		}
		for ($y = $ymin; $y <= $ymax; $y += $ytick) {
			$yy = ($y - $ymin) * $yscale + $y0;
			if ($this->param('ygrid')) {
				$outs .= "<line class='graph grid ygrid' style='stroke:".style::value("svg_graph_grid_ygrid")."; stroke-width: .5px;'x1='$x0' y1='$yy' x2='$x1' y2='$yy'/>\n";
			}
			$outs .= "<line class='graph tick ytick' style='stroke:".style::value("svg_graph_ticks_yticks")."' x1='".($xx0 + $tick/2)."' y1='$yy' x2='".($xx0 - $tick/2)."' y2='$yy'/>\n";

			$Y = $rconvy($y);
			if ($this->param('ylabelformat')) {
				$Y = $this->reformat($this->param('ydatatype'), $this->param('ylabelformat'), $Y);
			} else if (!$this->param('ynoreduce')) {
				$Y = $this->reduce($Y, $this->param('ydatatype'));
			}
			$outs .= "<text class='graph label ylabel' style='fill: ".style::value("svg_graph_label_ylabel")."' x='". ($xx0 - $tick) . "' y='$yy' alignment-baseline='middle' text-anchor='end'>".$this->locl->format($Y)."</text>\n";
		}

		if ($this->param('yunits')) {
			$outs .= "<text class='graph units yunits' style='fill: ".style::value("svg_graph_units_xunit")."' text-size='70%'  x='$x0'  y='". ($y1 - $margin/2) ."' text-anchor='middle'>".$this->locl->format($this->param('yunits'))."</text>\n";
		}
			
		$s = 0;
		foreach ($values as $serie) {
			$n     =  0;
			$line = "<polyline class='graph data' points='";
			foreach ($serie as $x => $y) {
				$xx = ($convx($x) - $xmin) * $xscale + $x0;
				$yy = ($convy($y) - $ymin) * $yscale + $y0;
				$line .= "$xx,$yy ";
				$n++;
			}
			$line .= "' style='fill:none;stroke:". $this->color($s) .";stroke-width:2'/>";
			$outs .= "$line\n";
			$s++;
		}	
		
		#
		# Legend:
		$i = 0;
		foreach (array_keys($values) as $t) {
			$x0 = 3 / 4 * $this->width;
			$x1 = $x0 + 20;  
			$y  = $margin + 10 * ($i + 1);
			$outs .= "<line class='graph legend' x1='$x0' y1='$y' x2='$x1' y2='$y' style='stroke:". $this->color($i).";stroke-width:2;'/>\n";
			$x  = $x0 + 30;  
			$outs .= "<text class='graph legend' style='fill: ".style::value("svg_graph_legend")."' x='$x' y='$y' text-anchor='start' >" . $this->locl->format($t) ."</text>\n";
			$i++;
		}
		$outs .= $this->fini();
		return $outs;
	}

	function pie($values, $labels) {
		$outs = "";
		$outs .= $this->init("pie");
		
		$r = $this->scale - 20;
		
		$a0 = - M_PI / 2.;
		$i  = 0;
		$text = "";
		if (count($values) == 1) {
			$text = "<circle r='$r' cx='". ($this->width / 2.) ."' cy='". ($r) ."' fill='". $this->color(0). "'/>";
			$rco = 0.5;
			$xt = $this->width / 2.;
			$yt = $this->height / 2;
			$text .= "<text id='".$this->noquo($labels[$i])."' class='graph pie legend' x='$xt' y='$yt' dominant-baseline='middle' text-anchor='middle' >".$this->locl->format($labels[$i])."</text>\n";
			return $outs. $text . $this->fini();
		}
		foreach ($values as $v) {
			$x0 = cos($a0)  * $r + $this->Ox;
			$y0 = sin($a0)  * $r + $this->Oy;
			$a1 = 2. * M_PI * $v + $a0;
			$x1 = cos($a1)  * $r + $this->Ox;
			$y1 = sin($a1)  * $r + $this->Oy;
			$at = 2. * M_PI * $v / 2  + $a0;
			$rco = 0.5;
			$xt = cos($at)  * $r * $rco + $this->Ox;
			$yt = sin($at)  * $r * $rco + $this->Oy;

			if  ($v > .5) $lf = 1; else $lf = 0;
			$outs .= "<path d='M $x0 $y0 A $r $r 0 $lf 1 $x1 $y1 L $this->Ox $this->Oy 0' fill='" .  $this->color($i) .  "'/>\n";
			#
			# With basic interaction:
			# $outs .= "<path d='M $x0 $y0 A $this->Ox $this->scale 0 $lf 1 $x1 $y1 L $this->Ox $this->Oy 0' fill='" .  $this->color($i) .  "' onmouseover='document.getElementById(\"".$labels[$i]."\").style.visibility = \"visible\"' onmouseout='document.getElementById(\"".$labels[$i]."\").style.visibility = \"hidden\"'/>\n";
			if ($v > .03 ) {
				# $outs .= "<text x='$xt' y='$yt' text-anchor='middle' >".$labels[$i]."</text>\n";
				$text .= "<text id='".$this->noquo($labels[$i])."' class='graph pie legend' x='$xt' y='$yt' dominant-baseline='middle' text-anchor='middle' >".$this->locl->format($labels[$i])."</text>\n";
			}
			$i++;
			$a0 = $a1;
		}	
		$outs .= "$text";
		$outs .= $this->fini();
		return $outs;
	}
	
	#
#
	function bars($values, $conf = null) {
		if ($conf != null) $this->conf = $conf;
		$outs = "";

		$outs .= $this->init("bar");

		if ($this->param("title") !== false) $outs .= $this->title($this->width / 2, $this->margin / 2, $this->param("title"));
		$outs .= $this->borders();
		

		$n = count($values);
		$s = 1;

		if ($n <= 0) return $this->empty_graph();

		$ymin = 0;
		$ymax = 0;
		foreach ($values as $k => $v) {
			if ($v > $ymax) $ymax = $v;
			if ($v < $ymin) $ymin = $v;
		}
		
		$margin = $this->margin;
		#
		# Usefull size:
		$x0 = $margin + 1;
		$y1 = $margin + 1;

		$x1 = $this->width  - $margin - 1;
		$y0 = $this->height - $margin - 1;

		if (($ymax - $ymin) == 0)  return $this->empty_graph();

		$yscale = ($y1 - $y0) / ($ymax - $ymin);
		
		if ($this->param("barmargin")) $wbarm = $this->param("barmargin");
		else $wbarm  = 0;

		$wbar   = ($x1 - $x0) / ($n) / $s; 

		if ($wbar <= 0) return $this->empty_graph();

		if ($wbar < $wbar) {
			if ($wbar < 4) $wbarm = 0;
			else $wbarm = $wbar / 4;
		}
		$wbar = $wbar - $wbarm;
		
		$yy0 = max(min(- $ymin * $yscale + $y0, $y0), $y1);
		if ($yy0 != $y0) 
			$outs .= "<line class='graph axis xaxis' x1='$x0'  y1='$yy0' x2='$x1'  y2='$yy0'/>\n";

		$i = -1;
		#$y = $y1;
		foreach ($values as $k => $v) {
			$i++;
			$x = $x0 + $wbarm / 2 + $i * ($wbar + $wbarm);
            $y = ($v - $ymin) * $yscale + $y0;
			$h = -$v * $yscale; 
			#$h = - $v * $yscale;
			if ($h < 0) {
				$h *= -1;
				$y = $yy0;	
			}
			$outs .= "<rect x='$x' y='$y' width='$wbar' height='$h'  style='stroke:none;fill:". $this->color($s)."; stroke-width:1;'/>";
					
			$x += $wbar / 2;
			$y = $y + $h / 2;
			$outs .= "<text class='graph legend' style='fill: ".style::value("svg_graph_legend")."' x='$x' y='$y' text-anchor='middle'  dominant-baseline='middle' >".$this->locl->format($v)."</text>\n";
			
			$y = $y0 + 20;
			$outs .= "<text class='graph label xlabel' style='fill: ".style::value("svg_graph_label_xlabel")."' x='$x' y='$y' text-anchor='middle' >$k</text>\n";
		}

		$outs .= $this->fini();
		return $outs;
	}
}
function test_pie() {
	$s = new svg(400);
	print($s->pie([.25], ['un']));
	$s = new svg(400);
	print($s->pie([.60], ['un']));
	$s = new svg(400);
	print($s->pie([.60, .30], ['un' , 'deux']));
	$s = new svg(400);
	print($s->pie([.60, .30, .10], ['un' , 'deux', 'trois']));
	$s = new svg(400);
	print($s->pie([.50, .20, .20, .09], ['un' , 'deux', 'trois', 'quatre']));
	$s = new svg(400);
	print($s->pie([.60, .20, .10, .09, .01], ['un' , 'deux', 'trois', 'quatre', 'cinq']));
}
function test_graph() {
	$arr = [
		'ouverts' => ['un' => 1, 'deux' => 2, 'trois' => '27', 'quatre' => '7', 'cinq' => '5'], 
		'fermés'  => ['un' => 0, 'deux' => 0, 'trois' => '2',  'quatre' => '3', 'cinq' => '7']
	];

	$s = new svg(400);
	print($s->graph($arr));
}

function test_graph_xy() {
	$arr = [ 
		'serie 1' => [ -3 => 1, -2 => 2, -0.5 => 3  , 0 => 4, 1 => 3, 4 => -1, 9 => 2],
		'serie 2' => [ -2 => 2, -1 => 1,  0   => 1.5, 1 => 2, 3 => 3, 4 => 3,  5 => 2.5, 6 => 2.7]
	];
	$s = new svg(800, 400);
	print($s->graph_xy($arr));
}

function test_bar() {
	$arr = [ "one" => "10", "two" => 3, "three" => -5, "four" => 2 ];
/*
	$arr = ["labels" => [ "one", "two", "three", "four" ],
			"series1" => ["10", 3,  -5,  2 ],
			"series2" => ["11", 3,  -2,  1 ],
			"series3" => ["7",  1,  0,   1 ]
		   ]
*/
	$s = new svg(700, 300, ["barmargin" => 10 ]);
	print($s->bars($arr));
}


#test_graph();
 

?>
