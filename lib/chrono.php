<?php
require_once("lib/dbg_tools.php");

class chrono {
	function __construct() {
		$this->state   = "stopped";
		$this->start   = false;
		$this->elapsed = 0;
		$this->laps    = [];
	}
	function dmp() {
		$now = hrtime(true);
		print("state:   $this->state\n");
		print("start:   $this->start\n");
		print("elapsed: $this->elapsed\n");
		if ($this->state == "running") {
			print("--> current elsapsed:" . round(($now - $this->start)/1e9, 3)                  ."\n");
			print("--> total   elsapsed:" . round(($this->elapsed + $now - $this->start)/1e9, 3) ."\n");
		}
		foreach ($this->laps as $i => $l)
			print("lap $i: $l\n");
	}
	function start() {
		$this->start = hrtime(true);
		$this->state = "running";
		return true;
	}
	function pause() {
		$now = hrtime(true);
		if ($this->start === false || $this->state != 'running') {
			err("chrono cannot be paused in $this->state");
			return false;	
		}
		$this->state    = "paused";
		$lap = $now - $this->start;
		$this->elapsed += $lap;
		$this->start    = false;
		return round($lap/1e9, 3);
	}
	function resume() {
		return $this->start();
	}
	function stop() {
		$now = hrtime(true);
		if ($this->start === false || $this->state != 'running') {
			err("chrono cannot be stopped in $this->state");
			return false;	
		}
		$this->state = "stopped";
		$this->elapsed += $now - $this->start;
		$this->start = false;
		return round($this->elapsed/1e9, 3);
	}
	function lap() {
		$now = hrtime(true);
		if (!$this->state == "running") return round($this->elapsed/1e9,3);
		array_push($this->laps, ($lap = round(($this->elapsed + $now - $this->start)/1e9, 3)));
		return $lap;
	}
	function elapsed() {
		if ($this->state == "running") {
			$now = hrtime(true);
			return 	round(($this->elapsed + $now - $this->start)/1e9, 3);
		}
		return round($this->elapsed/1e9, 3);
	}	
}
