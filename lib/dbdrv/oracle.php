<?php
class _oracle {
	static function version_qry() {
		return 'select banner as "version" from V$VERSION';
	}
	static function version_strings() {
		return [ "oracle" ];
	}
	static function dbname_qry() {
		return 'select V$datase as "dbname"';
	}
	static function databases_qry() {
		return false;
	}
	static function schemas_qry() {
		return "SELECT distinct owner as schemas FROM all_tables";
	}
	static function tables_qry($schema = false) {
		return "SELECT TABLE_NAME as tables from all_tables where Owner = '$schema'";
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
