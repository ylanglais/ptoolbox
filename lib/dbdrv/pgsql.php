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
		return "select distinct table_schema as schemas from information_schema.tables where table_catalog = current_database() and table_schema not in ('information_schema','pg_catalog') order by 1";
	}
	static function tables_qry($schema = false) {
		if ($schema === false) {
			return "select case when table_schema = 'public' or table_schema = '' then table_name else concat(table_schema, '.', table_name) end as tables from information_schema.tables where table_catalog = current_database() and table_schema not in ('information_schema','pg_catalog') order by table_schema, table_name order";
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
		return "with t as (SELECT c.column_name as column_name FROM information_schema.table_constraints tc JOIN information_schema.constraint_column_usage AS ccu USING (constraint_schema, constraint_name) JOIN information_schema.columns AS c ON c.table_schema = tc.constraint_schema AND tc.table_name = c.table_name AND ccu.column_name = c.column_name WHERE  tc.table_catalog = current_database() and tc.table_name = '$table' and tc.table_schema = '$schema' and constraint_type = 'PRIMARY KEY') select * from t union all SELECT c.column_name as column_name FROM information_schema.table_constraints tc JOIN information_schema.constraint_column_usage AS ccu USING (constraint_schema, constraint_name) JOIN information_schema.columns AS c ON c.table_schema = tc.constraint_schema AND tc.table_name = c.table_name AND ccu.column_name = c.column_name WHERE  tc.table_catalog = current_database() and tc.table_name = '$table' and tc.table_schema = '$schema' and constraint_type = 'UNIQUE' and (select count(*) from t) = 0";
		//return "SELECT distinct column_name FROM information_schema.key_column_usage WHERE table_catalog = current_database() and table_schema = '$schema' and table_name = '$table'";
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
