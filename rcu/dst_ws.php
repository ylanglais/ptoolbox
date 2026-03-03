<?php
require_once("lib/dbg_tools.php");

class dst_ws {
    private $mode     = "line";
    private $buf      = [];
	private $last_err = null;
    private $_params  = [];
    private $_conf;
    
    static function validate($conf) {
            return true;
    }
    function __construct($conf) {
        $this->_conf = $conf->dest->params;
        
        if (!file_exists("classes/".$this->_conf->class.".php")) {
            _err("no classes/".$this->_conf->class.".php");
            return;
        }
        
        include("classes/".$this->_conf->class.".php");
        
        $reflection = new ReflectionMethod($this->_conf->class,$conf->dest->params->endpoint);
        $liste_param = $reflection->getParameters();
        
        foreach($liste_param as $param){
            if(array_search($param->getName(), array_column($conf->map,'dest')) === false && !$param->isOptional()){
                _err("required field [".$param->getName()."] since not present in source");
            }else{
                $this->_params[$param->getName()] = ($param->isOptional())? 'true':'false';
            }
        }
    }
    
    function mode() {
            return $this->mode;	
    }
    
    function set($field, $value) {
        if(array_key_exists( $field , $this->_params)){
            $this->buf[$field] = $value;
            
        }else{
            _warn("ignore field $field since not present in destination WS");
        }

        return true;        
    }
   	function last_error() {
		return null;
	} 
    function line_validate() {
        $class_name = $this->_conf->class;
        $dn = new $class_name($this->_conf);   
		$this->last_err = null;
        $rest = call_user_func_array(array($dn, $this->_conf->endpoint), $this->buf);
        if(isset($rest->error)) {
			$this->last_err = $rest->error;
            _err($rest->error);
			return false;
		}
        
        return true;
    }
    function flux_validate() {
        return true;
    }


}
