<?php
class _mysql {
	static function version_qry() {
		return "select version() as version";
	}
	static function version_strings() {
		return [ "mysql", "mariadb" ];
	}
	static function dbname_qry() {
		return "select database() as dbname";
	}
	static function databases_qry() {
		return "select distinct table_catalog as db from information_schema.tables order by table_catalog";
	}
	static function schemas_qry() {
		return false;
	}
	static function tables_qry($schema = false) {
		return "select table_name as tables from information_schema.tables where table_schema = database() order by table_name";
	}
	static function columns_qry($table) {
		return "select column_name, is_nullable, data_type, column_default, character_maximum_length from information_schema.COLUMNS where TABLE_SCHEMA = database() and TABLE_NAME = '$table'";
	}
	static function keys_qry($table) {
		return "select column_name from information_schema.key_column_usage where TABLE_SCHEMA = database() and TABLE_NAME = '$table' and CONSTRAINT_NAME = 'PRIMARY'";
	}
	static function fkeys_qry($table) {
		return "select column_name as col, REFERENCED_TABLE_NAME as rtable, REFERENCED_COLUMN_NAME as rcol  from INFORMATION_SCHEMA.KEY_COLUMN_USAGE where   REFERENCED_TABLE_SCHEMA is not null and REFERENCED_TABLE_NAME is not null and REFERENCED_COLUMN_NAME is not null and REFERENCED_TABLE_SCHEMA = TABLE_SCHEMA and table_schema = database() and table_name = '$table'";
	}
}
