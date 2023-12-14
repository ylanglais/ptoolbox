<?php
require_once("lib/dbg_tools.php");
	
/**
 * Ease access to curl
 */
class curl {
	private $c;
	private $baseurl;
	private $debug;

	/**
	 * Constructor with maximum stuff preconfigured 
	 * @param $baseurl base url of API 
	 * @param $header  header used for API consumation 
	 * @param $prevent proxy usage event though defined in configuration
	 * @param $debug   locally turn on/off debugging supercharging configuration flag
	 */
	function __construct($baseurl, $header = "", $noproxy = false, $debug = false, $opts = []) {
		$this->debug = $debug;
		
		$curl_proxy           = "";
		$curl_ssl_verify_peer = true;
		$curl_ssl_verify_host = false;
		$curl_cookie_file     = "./.cookies";
		$curl_cookie_jar      = "./.cookies";
		$curl_return_transfer = true;
		$curl_follow_location = true;
		$curl_debug           = false;

		if (file_exists("conf/curl.php")) {
			if ($this->debug) {
				_dbg("sourcing conf/curl.php");
			}
			include("conf/curl.php");
		} else if ($this->debug) _dbg("no conf/curl.php file to source");

		if (isset($curl_debug) && $curl_debug) $this->debug = $curl_debug;

		$this->c       = curl_init();
		$this->baseurl = $baseurl;

		if (!$noproxy) {
			if ($this->debug && $curl_proxy != "") _dbg("setting proxy to $curl_proxy");
			curl_setopt($this->c, CURLOPT_PROXY, $curl_proxy);
			if (isset($curl_proxy_auth) && $curl_proxy_auth != '') curl_setopt($this->c, CURLOPT_PROXYUSERPWD, $curl_proxy_auth);
		} else {
			curl_setopt($this->c, CURLOPT_PROXY, ""); 
		}
		curl_setopt($this->c, CURLOPT_SSL_VERIFYPEER, $curl_ssl_verify_peer); 
		curl_setopt($this->c, CURLOPT_SSL_VERIFYHOST, $curl_ssl_verify_host); 
		curl_setopt($this->c, CURLOPT_COOKIEFILE,     $curl_cookie_file);
		curl_setopt($this->c, CURLOPT_COOKIEJAR,      $curl_cookie_jar);
		curl_setopt($this->c, CURLOPT_RETURNTRANSFER, $curl_return_transfer);
		curl_setopt($this->c, CURLOPT_FOLLOWLOCATION, $curl_follow_location);

		if ($opts != []) {
			foreach ($opts as $k => $v) {
				try {
					curl_setopt($this->c, $k, $v); 
				} catch (Exception $e) {
					warn("bad option value pair ($k/$v)");
				}
			}
		}

		if (isset($curl_other_options) && is_array($curl_other_options != [])) 
			foreach ($curl_other_options as $opt => $val) 
				curl_setopt($this->c, $opt, $val);

		if ($header != "") curl_setopt($this->c, CURLOPT_HTTPHEADER, $header);
		if ($this->debug) {
			curl_setopt($this->c, CURLOPT_VERBOSE, true);
		}
	}

	/**
	 * Set ir unset debug:
	 */
	function debug($tf = true) {
		$this->debug = $tf;
	}

	/** 
     * Destructor to force connexion closing
	 */
	function __destruct() {
		curl_close($this->c);
	}

	/**
     * Header get or set
     */
	function header($hdr) {
		curl_setopt($this->c, CURLOPT_HTTPHEADER, $hdr);
		return $hdr;
	}

	/**
	 * Cookie get or set
	 */
	function cookie($cookie = "") {
		if ($cookie == "") {
			return cur_getopt($this->c, CURLOPT_COOKIE);
		}
		curl_setopt($this->c, CURLOPT_COOKIE, $cookie);
		return $cookie;
	}
	
	private function _url($what) {
		$url = $this->baseurl;
		if (substr($url, -1) != "/") $url .= "/";
		return $url . $what;
	}

	/**
     * Call API entry point w/ post method:
	 * @param $what		is the entry point
	 * @param $params 	is an associative array of parameters [ $k1 => $v1, $k2 => $v2 ...]
	 * @return the result of curl_exec or false if an error occured
     */
	function post($what, $param = "") {
		curl_setopt($this->c, CURLOPT_URL, $this->_url($what));
		curl_setopt($this->c, CURLOPT_POST, true); 
		curl_setopt($this->c, CURLOPT_POSTFIELDS, $param);
	
		if ($this->debug) {
			_dbg("curl post url: $this->baseurl/$what");
			_dbg("curl post param: ".  json_encode($param));
		}

		$d = false;
		$r = curl_exec($this->c);
		if (($e = curl_error($this->c))) {
			_err("curl error: $e" .  print_r(curl_getinfo($this->c), true));
			return false;
		}
		if ($r === false) {
			_err("curl returned false" . print_r(curl_getinfo($this->c), true));
			return false;
		}
		return $r;
	}

	/**
     * Call API entry point w/ get method:
	 * @param $what		the entry point
	 * @param $params 	an associative array of parameters [ $k1 => $v1, $k2 => $v2 ...]
	 * @return the result of curl_exec or false if an error occured
	 */
	function get($what, $param = []) {
		if ($what != '') {
			$p = "";
			foreach ($param as $k => $v) {
				if ($p != "") $p .= "&";
				$p .= "$k=".urlencode($v);
			}
			if ($p != "") $p = "?$p";
			
			$url = "$this->baseurl$what$p";
		} else {
			$url = "$this->baseurl";
		}
		if ($this->debug) _dbg(">>>>>> url = '$url'");
		curl_setopt($this->c, CURLOPT_URL, "$url");
		
		curl_setopt($this->c, CURLOPT_POST, false); 
		curl_setopt($this->c, CURLOPT_HTTPGET, TRUE);
		$d = false;
		$r = curl_exec($this->c);
		if ($r === false) {
			_dbg("curl returned false with info: " . print_r(curl_getinfo($this->c)), true);
		}
		return $r;
	}

	/**
     * Send is a generic method to pass rest actions (GET/POST/PUT/DEL).
	 * Note that PUT and DEL are not part of html protocol and are coded as custom names of the POST action.
	 * @param $act	 	the rest action
	 * @param $what   	the entry point
	 * @param $params 	an associative array of parameters [ $k1 => $v1, $k2 => $v2 ...]
	 * @return the result of curl_exec or false if an error occured
	 */
	function send($act, $what, $param = "") {
		if ($what == "") {
			_err("$act is not a defined action (try get, post, put or delete)");
			return false;
		}

		curl_setopt($this->c, CURLOPT_POST, 0);
		curl_setopt($this->c, CURLOPT_CUSTOMREQUEST, null);
		switch (strtoupper($act)) {
		case 'GET':
			curl_setopt($this->c, CURLOPT_HTTPGET, 1);
			if ($param != null) curl_setopt($this->c, CURLOPT_POSTFIELDS, $param);
			break;
		case 'PST':
		case 'POST':
			curl_setopt($this->c, CURLOPT_POST, 1);
			if ($param != null) curl_setopt($this->c, CURLOPT_POSTFIELDS, $param);
			break;
		case 'PUT':
			curl_setopt($this->c, CURLOPT_CUSTOMREQUEST, "PUT");
			if ($param != null) curl_setopt($this->c, CURLOPT_POSTFIELDS, $param);
			break;
		case 'DEL':
		case 'DELETE':
			curl_setopt($this->c, CURLOPT_CUSTOMREQUEST, "DELETE");
			if ($param != null) curl_setopt($this->c, CURLOPT_POSTFIELDS, $param);
			break;
		default:
			_err("$act is not a defined action (try get, post, put or delete)");
			return false;
		}
	
		$url = $this->baseurl;
		if (substr($url, -1) !=  '/') $url .= "/";
		$url .= $what;

		curl_setopt($this->c, CURLOPT_URL, $url);
		curl_setopt($this->c, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		if ($this->debug) {
				_dbg("curl ".strtoupper($act)." $url");
			_dbg("curl send param: <<" . print_r($param,true) . ">>");
		}

		$output = curl_exec($this->c);
		$error  = curl_error($this->c);

		curl_setopt($this->c, CURLOPT_CUSTOMREQUEST, null);

		if ($output !== false) return $output;

		_err("An error occured on $act $what: " . print_r($error, true));
		return false;
	}

	/**
     * Call API entry point w/ DEL rest action (note that DEL is a custom name over html POST method)
	 * @param $what		the entry point
	 * @param $params 	an associative array of parameters [ $k1 => $v1, $k2 => $v2 ...]
	 * @return the result of curl_exec or false if an error occured
	 */
	function delete($what, $param = []) {
		if ($this->debug) {
			_dbg("curl delete url: $this->baseurl/$what");
			_dbg("curl delete param: <<" . print_r($param, true) . ">>");
		}
		return $this->send("DEL", $what, $param); 
	}

	/**
     * Call API entry point w/ PUT rest action (note that PUT is a custom name over html POST method)
	 * @param $what	 	the entry point
	 * @param $params 	an associative array of parameters [ $k1 => $v1, $k2 => $v2 ...]
	 * @return the result of curl_exec or false if an error occured
	 */
	function put($what, $param = []) {
		if ($this->debug) {
			_dbg("curl put url: ". $this->_url($what));
			_dbg("curl put param: <<" . print_r($param) . ">>");
		}

		return $this->send("PUT", $what, $param);
	}
}

?>
