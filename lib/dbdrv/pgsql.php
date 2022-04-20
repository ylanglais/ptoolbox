<?php
class _pgsql {
	static function version_qry() {
		return "select version() as version";
	}
	static function version_strings() {
		return [ "postgresql" ];
	}
	static function dbname_qry() {
		return "select current_database() as dbname";
	}
	static function databases_qry() {
		return "select distinct table_catalog as db from information_schema.tables";
	}
	static function schemas_qry() {
		return "select distinct table_schema as schemas from information_schema.tables where table_catalog = current_database() and table_schema not in ('information_schema','pg_catalog')";
	}
	static function tables_qry($schema = false) {
		if ($schema === false) {
			return "select case when table_schema = 'public' or table_schema = '' then table_name else concat(table_schema, '.', table_name) end as tables from information_schema.tables where table_catalog = current_database() and table_schema not in ('information_schema','pg_catalog') order by table_schema, table_name";
		}
		return "select concat('$schema.', table_name) as tables from information_schema.tables where table_catalog = current_database() and table_schema = '$schema' order by 1";
	}
	static function columns_qry($table) {
		if (preg_match("/^([^.]*)\.(.*)$/", $table, $m)) {
			$schema = $m[1];
			$table  = $m[2];
		} else {
			$schema = "public";
		}
		return "select COLUMN_NAME, IS_NULLABLE, udt_name as DATA_TYPE, COLUMN_DEFAULT, CHARACTER_MAXIMUM_LENGTH from information_schema.COLUMNS where TABLE_CATALOG = current_database() and TABLE_SCHEMA = '$schema' and TABLE_NAME = '$table'";
	}
	static function keys_qry($table) {
		if (preg_match("/^([^.]*)\.(.*)$/", $table, $m)) {
			$schema = $m[1];
			$table  = $m[2];
		} else {
			$schema = "public";
		}
		return "SELECT constraint_name, table_schema, table_name, column_name, ordinal_position FROM information_schema.key_column_usage WHERE table_catalog = current_database() and table_schema = '$schema' and table_name = '$table'";
	}
	static function fkeys_qry($table) {
		if (preg_match("/^([^.]*)\.(.*)$/", $table, $m)) {
			$schema = $m[1];
			$table  = $m[2];
		} else {
			$schema = "public";
		}
		return "select s.column_name as col, r.table_schema || '.' || r.table_name as ftable, r.column_name as fcol from "
			.  "information_schema.table_constraints c ,information_schema.key_column_usage s ,information_schema.constraint_column_usage r where "
			.  "c.constraint_type = 'FOREIGN KEY' and c.constraint_name = s.constraint_name and s.constraint_name = r.constraint_name "
			.  " and c.constraint_catalog = current_database() and c.table_schema = '$schema' and c.table_name = '$table' order by 1";
	}
}
