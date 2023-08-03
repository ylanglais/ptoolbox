<?php
require_once("lib/session.php");
require_once("lib/util.php");

function _scrm_init_() {
	global $_session_;
	if (!isset($_session_)) $_session_ = new session();
	include("conf/scrm.php");
	$sid   = $_session_->id();;
	$ip    = get_ip(); 
	$iv    = substr($scrm_ivb, 0, openssl_cipher_iv_length($scrm_cyfr));
	$k     = $scrm_ka . $sid . $scrm_kb . $ip. $scrm_kc;
	return [ $scrm_cyfr, $iv, $k ];
}
function   scrm_do($data) {
	[ $cyfr, $iv, $k ] = _scrm_init_();
	return openssl_encrypt(json_encode($data), $cyfr, $k, 0, $iv);
}
function scrm_un($data) {
	[ $cyfr, $iv, $k ] = _scrm_init_();
	return json_decode(openssl_encrypt($data, $cyfr, $k, 0, $iv));
}
?>
