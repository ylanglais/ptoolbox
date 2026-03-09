<?php
require_once("lib/csv.php");
require_once("lib/cachecsv.php");

require_once("rcu/src.php");
require_once("rcu/env.php");

class src_ws {
    private $_class;
    private $_endpoint;
    
	function validate($conf) {
		$errs = [];
		if (!property_exists($conf, "params")) 
			return "params parameter is missing";

		if (!property_exists($conf->params, "file")) 
			array_push($errs, "parameter file is missing");

		if ($conf->type == "ref") 
			if (!property_exists($conf, refkey)) array_push($errs, "refkey parameter is missing");

		return $errs == [] ? true : $errs;
	}
        
        function _send(){
            
            if (!file_exists("classes/".$this->_class.".php")) {
                    _err("no classes/".$this->_class.".php");
                    return;
                }

                include("classes/".$this->_class.".php");
                                
            $class_name = $this->_class;
            $ws = new $class_name(); 
            $rest = $ws->{$this->_endpoint}();

            return $rest;
        }
        
	function __construct($conf) {
            $this->type = $conf->type;
            $this->key = $conf->key;
            $this->_class = $conf->name;
            $this->_endpoint = $conf->params->endpoint;

            $this->data = $this->_send();

            $this->current = -1;
	}
        
	function nlines() {
            
            if(is_array($this->data))
                return count($this->data);
            
            return false;
	}
        
	function next() {
            if($this->current < $this->nlines() - 1){
                $this->current++;
                return true;
            }
	
            return false;
	}
        
	function srcname() {
		return $this->_class;
	}
        
	function value($field) {
            
            if ($this->type == "main"){
                if(is_object($this->data[$this->current]))
                    return $this->data[$this->current]->{$field};
                else if(is_array($this->data[$this->current]))
                    return $this->data[$this->current][$field];
            }
            
            return false;
	}
        
	function key_value() {
            if ($this->type == "main"){
                if(is_object($this->data[$this->current]))
                    return $this->data[$this->current]->{$this->key};
                else if(is_array($this->data[$this->current]))
                    return $this->data[$this->current][$this->key];
            }
            
            return false;
	}
        
	function ref($kval, $field) {
		return false;
	}      
        
}
