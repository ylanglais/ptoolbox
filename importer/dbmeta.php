<?php

require_once("lib/db.php");
require_once("lib/query.php");
require_once("lib/dbg_tools.php");

class dbmeta {
	function __construct($schema, $table,array $opt = [] ) {
		$popts = [ "dsn", "user", "pass", "file" ];


		if ($opt != [] && 
			array_key_exists("dsn",  $opt) && 
			array_key_exists("user", $opt) &&
			array_key_exists("pass", $opt)){
			$this->odb = new db($opt["dsn"], $opt["user"], $opt["pass"]); 
			$dsn =  $opt["dsn"];
		} else { 
			if ($opt != [] && array_key_exists("file", $opt)) {
				$conffile = $opt["file"];
			} else $conffile = "conf/dbmeta.php";

			if (!file_exists($conffile)) 
				_err("no $conffile file");

			return;
			include($conffile);
			$this->odb = new db($dbmeta_dsn, $dbmeta_user, $dbmeta_pass);
			$dsn =  $dbmeta_dsn;
		}

		if ($this->odb === false || ($r = $this->odb->error()) !== false) {
			_err("bad db connexion : $r");
			return;
		}
		preg_match("/^([^:]*):(host=[^;]*);(port=([0-9]*);)?(dbname=(.*))$/", $dsn, $m);

		$db_drv  = $m[1];
		$db_name = $m[6];

	
		if ($db_drv == "mysql") {
			$s = "select COLUMN_NAME as name, IS_NULLABLE as nullable, DATA_TYPE as data_type, COLUMN_DEFAULT as 'default', CHARACTER_MAXIMUM_LENGTH as len from information_schema.COLUMNS where TABLE_SCHEMA = '$db_name' and TABLE_NAME = '$table'";
		} else if ($db_drv == "pgsql") {
                        
			if (empty($schema) && array_key_exists("schema", $opt) && ($opt['schema'] != null || !empty($opt['schema']))){
				$schema = $opt['schema']; 
			}

			if (empty($table) && array_key_exists("table", $opt) && ($opt['table'] != null || !empty($opt['table']))){
				$table = $opt['table']; 
			}
            
			$s = "select COLUMN_NAME as name, IS_NULLABLE as nullable, data_type, COLUMN_DEFAULT as default, CHARACTER_MAXIMUM_LENGTH as len from information_schema.COLUMNS where TABLE_CATALOG = '$db_name' and TABLE_SCHEMA = '$schema' and TABLE_NAME = '$table'";
		}

		#$s = "select column_name as name,data_type as type,udt_name, character_maximum_length as len, is_nullable 
		#as nullable, column_default as default from information_schema.columns where table_schema = '$schema' and table_name = '$table'";

		$q = new query($s, $this->odb);
		$this->meta = [];
		while ($o = $q->obj()) {
			$this->meta[$o->name] = (object) [];
			$this->meta[$o->name]->type     = $o->data_type;	
			#$this->meta[$o->name]->udt_name = $o->udt_name;	
			$this->meta[$o->name]->len      = $o->len;	
			$this->meta[$o->name]->nullable = ($o->nullable == 'YES') ? true : false;	
			$this->meta[$o->name]->default  = ($o->default == 'NULL') ? null : $o->default; 
		}
	}
	function db_connection() {
		if (!isset($this->odb)) return false;
		return $this->odb;
	}
	
	function __dbg() {
		print_r($this->meta);
		print("\n");
	}

	function columns() {
		return array_keys($this->meta);
	}

	function column_type($name) {
		if (!array_key_exists($name, $this->meta)) return false;
		return $this->meta[$name]->type;
	}

	function check($column, $value) {
		if (!array_key_exists($column, $this->meta)) return false;

		$c = $this->meta[$column];
		if ($value == '' || $value == 'null' || $value === null) {
			if ($this->meta[$column]->default !== null ) {
				$value =  $this->meta[$column]->default;
			} else {
				if ($this->meta[$column]->nullable === false) return false;
				return 'null';
			}
		}

		switch($c->type) {
		case "date":
			if (strlen($value) <  8) return "null";
			if (substr($value, -3) == ".00") $value = substr($value, 0, -3);
			if (strlen($value) == 8) return "'". date_yyyymmdd_to_db($value) . "'";
			return "'$value'";
			break;
		case "integer":
		case "smallint":
		case "decimal":
		case "numeric":
		case "real":
		case "double":
		case "smallserial":
		case "serial":
		case "bigserial":
			return $value;
			break;
		case "character":
		case "character varying":
			#if ($c->len !== false && strlen($value) > $c->len) {
			#	_warn("'$value' truncated to $c->len"); 
			#	$value = substr($value, 0, $c->len);
			#}
			break;	
		}
		return "'". esc($value) ."'";
	}
}
