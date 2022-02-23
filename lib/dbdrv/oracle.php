<?php
class _oracle {
	static function version_qry() {
		return "select banner as vers from V$VERSION";
	}
	static function version_strings() {
		return [ "oracle" ];
	}
	static function dbname_qry() {
		return 'select V$datase as dbname';
	}
	static function databases_qry() {
		return false;
	}
	static function schemas_qry() {
		return false;
	}
	static function tables_qry($schema = false) {
		return false;
	}
	static function columns_qry($table) {
		return false;
	}
	static function keys_qry($table) {
		return false;
	}
	static function fkeys_qry($table) {
		return false;
	}
}
