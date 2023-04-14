<?php
require_once("lib/curl.php");
require_once("lib/dbg_tools.php");
require_once("lib/date_util.php");
require_once("lib/csv.php");

#
# Doc at https://www.actito.be/ActitoWebServices/doc/index.html

class Actito {
	#"get" "/entity/$entity/customTable"
	private $ctdata;
	private $aid   = "idAmabis";
	private $email = "email";
	private $first = "first";
	private $last  = "last";
	private $key_file = "idAmabis"; 
	private $cspec = null; 

	function __construct() {
		$this->url        = "";
		$this->hdr        = "";
		$this->bypass_pxy = false;
		$this->debug      = false;
		if (!file_exists("conf/actito.php")) {
			return;
		}
		include("conf/actito.php");
		$this->token   = base64_encode("$actito_license/$actito_login:$actito_passwd");
		$this->base    = $actito_url;
		$this->hdr     = [ "Content-Type: application/json",    "Authorization: Basic $this->token" ];
		$this->hdr2    = [ "Content-Type: multipart/form-data", "Authorization: Basic $this->token" ];
		$this->entity  = $this->entity();
		$this->ctdata  = $this->table_spec("Contacts");

		if (isset($actito_amabis_id))  $this->aid     = $actito_amabis_id;
		if (isset($actito_email))      $this->aemail  = $actito_email;
		if (isset($actito_first_name)) $this->first   = $actito_first_name;
		if (isset($actito_last_name))  $this->last    = $actito_last_name;
	}
	function hdr() {
		return $this->hdr;
	}
	function set_key_file($k){
		if (empty($k)){
			_err("Key_file could not be empty".PHP_EOL); 
			return false;
		} else {
			$this->key_file=$k;
		}
		return;
	}

	function get($endpoint, $param = null) {
		#print_r($this->hdr());
		$c = new curl($this->base, $this->hdr(), false, false);
		$r = $c->send("get", $endpoint, $param);
		if ($r === false) return false;
		return json_decode($r);		
	}
	function download($endpoint, $param = null) {
		#print_r($this->hdr());
		$c = new curl($this->base, $this->hdr(), false, false);
		$r = $c->send("get", $endpoint, $param);
		if ($r === false) return false;
		return $r;		
	}
	function post($endpoint, $param = null) {
		$c = new curl($this->base, $this->hdr());
		$r = $c->send("post", $endpoint, $param);
		if ($r === false) return false;
		return json_decode($r);		
	}
	function upload($endpoint, $param = null) {
		$c = new curl($this->base, $this->hdr2, false, false);
		$r = $c->send("post", $endpoint, $param);
		if ($r === false) return false;
		return $r;		
	}
	
	function put($endpoint, $param = null) {
		$c = new curl($this->base, $this->hdr());
		$r = $c->send("put", $endpoint, $param);
		if ($r === false) return false;
		return json_decode($r);		
	}
	function del($endpoint, $param = null) {
		$c = new curl($this->base, $this->hdr());
		$r = $c->send("delete", $endpoint, $param);
		if ($r === false) return false;
		return json_decode($r);		
	}

	function entity() {
		$r = $this->get("entity");
		if ($r === false) {
			err("cannot get entity");
			return false;
		}
		#print("---> r:\n");
		#print_r($r);
		#print("\n----<\n");
		#print("--> entity= ".$r->entities[0]->name."\n");
		return $r->entities[0]->name;
	}

	function import_status($id) {
		return $this->get("entity/$this->entity/import/$id/status");
	}

	function import_errors($id) {
		_info("Download rejects into ImportError_".$id.".zip");
		$x = $this->download("entity/$this->entity/import/$id/errors");
		$fh = fopen("ImportError_".$id.".zip", "wb");
		fwrite($fh, $x); 
		fclose($fh); 
		return $this->import_result($id);
	}
	
	function import_rejects($id) {
		$fname = getcwd()."/tmp/import_rejects.$id.zip";
		$x = $this->download("entity/$this->entity/import/$id/errors");
		$fh = fopen($fname, "wb"); fwrite($fh, $x); fclose($fh); 
		$z  = new ZipArchive();
		$z->open($fname);
		if ($z->numFiles < 1) {
			err("no reject file");
			$r->close();
			unlink($fname);
			return false;
		}	
		$name = $z->getNameIndex(0);
		if ($name === false) {
			err("empty reject file");
			$r->close();
			unlink($fname);
			return false;
		}
		$d = getcwd(). "/tmp";
		$z->extractTo(getcwd()."/tmp");
		$z->close();
		unlink($fname);

		$csv = new CSV("$d/$name", ",");

		unlink("$d/$name");

		$files = [ "Commandes"  => "idCommande", "Agregats"   => "idAgregat", "Appetences" => "idAppetence", "Contacts"   => "idAmabis" ];
		$ftype  = "";
		$idname = "";

		foreach ($files as $ft => $id) {
			if ($csv->has_key($id)) {
				$ftype  = $ft;
				$idname = $id;
				continue;
			}
		}

		if ($idname == "") {
			err("invalid input file");
			return false;
		}

		$nl = $csv->nlines();
		$r   = [];
		print("$nl rejected lines\n");
		for ($i = 1; $i < $nl; $i++) {
			$ercod = $csv->get($i, "errorCode");
			$ercol = $csv->get($i, "errorColumn");
			$erval = $csv->get($i, $ercol);
			$errid = $csv->get($i, $idname);

			if (! array_key_exists($ercod, $r)) {
				$r[$ercod] = [];
			}
			if (! array_key_exists($ercol, $r[$ercod])) {
				$r[$ercod][$ercol] = [];
				$r[$ercod][$ercol][$erval] = [];
				array_push($r[$ercod][$ercol][$erval], $errid);
			} else { 
				if (! array_key_exists($erval, $r[$ercod][$ercol])) {
					$r[$ercod][$ercol][$erval] = [];
				} 
				array_push($r[$ercod][$ercol][$erval], $errid);
			}
		}
		return $r;	
	}

	function import_rejects_print($data, $details = false) {
		foreach ($data as $errc => $arr) {
			foreach ($arr as $col => $vals ) {
				foreach ($vals as $k => $ids) {
					print("$errc.$col.$k: ". count($ids) ."\n");
					if ($details) {
						foreach ($ids as $id) {
							print("\t$id\n");
						}
					}
				}
			}	
		}
	}

	function import_result($id) {
		return $this->get("entity/$this->entity/import/$id/result");
	}

	function ctables() {
		return $this->get("entity/$this->entity/customTable");
	}

	function ct_spec($table) {
		//@TODO faire un peu plus propre pour éviter de rentrer dans la fonction. Déporter le test de null dans le/les appelants
		if ($this->cspec == null){
			$spec =  $this->get("entity/$this->entity/customTable/$table");
			$this->cspec = [];

			foreach ($spec->attributes as $fld) {
				if ($fld->fieldName == "updateMoment" || $fld->fieldName == "creationMoment" || $fld->fieldName == "id") continue;
				array_push($this->cspec, $fld);	
			}
		}
		return $this->cspec;
	}

	function tables() {
		return $this->get("entity/$this->entity/table/");
	}

	function table_spec($table) {
		return $this->get("entity/$this->entity/table/$table");
	}

	function field_check($name, $type, $required, $o) {
		if (($required == 1 || $required === true) && !property_exists($o, $name)) {
			_err("field $name is required but absent from data");
			return false;
		}
		if (!property_exists($o, $name)) return true;
		if ($o->$name === null)    	     return true;
		if ($o->$name ==  '') 		     return true;
		$v = $o->$name;
		if ($type == "Boolean") {
			if      ($v == 0)       $o->$name = false;
			else if ($v == "false") $o->$name = false;
			else if ($v == 1)       $o->$name = true;
			else if ($v == "true")  $o->$name = true;
			else if (!is_bool($v)) {
				_warn("$v is not a boolean, skip it");
				unset($o->$name);
			}	
		} else if ($type == "PhoneNumber") {
			$v= str_replace(" ", "", $v);
			if (strlen($v) == 10 && substr($v, 0, 1) == "0") {
				$o->$name = "+33" . substr($v, 1);
			} else if (!substr($v, 0, 1) == "+") {
				_warn("$v in not a valid phone number, skip it");
				unset($o->$name);
			} else {
				$o->name = $v;
			}
		} else if ($type == "Datum" || $type == "Moment") {
			if ($v == "0000-00-00") $o->$name = "";
		#} else if ($type == "String") {
		#} else if ($type == "EmailAddress") {
		}
		return true;
	}


	function contact_count($crit="") {
		$search = "";
		if ($crit != "") $search="?search=" . urlencode($crit);
		return $this->get("entity/$this->entity/table/Contacts/profile/count$search");
	}

	function contacts_get($crit="") {
		$search = "";
		if ($crit != "") $search="?search=" . urlencode($crit);
		return $this->get("entity/$this->entity/table/Contacts/profile/$search");
	}

	function contact_del($id) {
		return $this->del("entity/$this->entity/table/Contacts/profile/$id");
	}

	function contact_post($contact) {
		#$param->profile = [ "attributes" => [ $contact] ];
		$param= [ "attributes" => $contact ];
		#print("json : " . json_encode($param) . "\n");	
		return $this->post("entity/$this->entity/table/Contacts/profile", json_encode($param));
	}

	function ct_check($table, $o) {
		if ($o == null || $o == false || !is_object($o)) {
			_err("null or invalid data");
			return false;
		}
		if (!($spec = $this->ct_spec($table))) {
			_err("cannot load specifications for table $table");
			return false;
		}
		foreach ($spec as $fld) {
			if (!$this->field_check($fld->fieldName, $fld->type, $fld->required, $o)) {
				_err("bad field data for $table.$fld->fieldName");
				return false;
			}
		}
		return $o;
	}
	function ct_csv_hdr($attrs, $sep = ",") {
		$i = 0;
		$hdr = "";
		foreach ($attrs as $fld) {
			if ($i > 0) $hdr .= "$sep";
			$i++;
			$hdr .= $fld->fieldName;
		}
		return $hdr;
	}
	function ct_csv_fmt($o, $attrs, $sep = ",") {
		$i = 0;
		$l = "";
		foreach ($attrs as $fld) {
			if ($i > 0) $l .= "$sep";
			$i++;
			$k = $fld->fieldName;
			if (!property_exists($o, $k)) continue;
			if ($o->$k === null) 		  continue;
			if ($o->$k ==  '') 		  	  continue;
			$l .= "\"".html_entity_decode($o->$k,ENT_QUOTES)."\"";
		}
		return $l;
	}

	function ct_json_fmt($o, $attrs) {
		$d = [];
		foreach ($attrs as $spec) {
			$k = $spec->fieldName;
			if (!property_exists($o, $k)) continue;
			if ($o->$k === null) 		  continue;
			if ($o->$k ==  '') 		  	  continue;
			array_push($d, [ "name" => $k, "value" => $o->$k ]);
		}
		return json_encode([ "properties" => $d ]);
	}

	function contact_check($o) {
		if ($o === false || !is_object($o)) {
			_err("invalid contact data " . print_r($o, true));
			return false;
		}
		
		if ($this->ctdata == false) {
			_err("no contact specification");
			return false;
		}
		if (!property_exists($o, $this->aid)) {
			if (property_exists($o, $this->email) && property_exists($o, $this->first) && property_exists($o, $this->last)) {
				$mail  = $this->email;
				$first = $this->first;
				$last  = $this->last;
				_err("no $this->aid present in contact (email: $o->$mail, nom: $o->$last, prenom: $o->$first), skipping\n");
			} else _err("no $this->aid present in contact:\n" . print_r($o, true));
			return false;
		}
		foreach ($this->ctdata->attributes as $k => $spec) {
			if (!$this->field_check($k, $spec->valueType, $spec->mandatory, $o)) {
				_err("field $k is not valid : '". $o->$k ."'");
				return false;
			}
		}
		return $o;
	}

	function contact_csv_hdr($sep = ",") {
		$i = 0;
		$hdr = "";
		foreach ($this->ctdata->attributes as $k => $spec) {
			if ($i > 0) $hdr .= "$sep";
			$i++;
			$hdr .= $k;
		}
		foreach ($this->ctdata->subscriptions as $k => $spec) {
			#if ($i > 0) $hdr .= "$sep";
			$hdr .= ",subscriptions#$spec->name";
		}
		return $hdr;
	}

	function contact_csv_fmt($o, $sep = ",") {
		$i = 0;
		$l = "";
		foreach ($this->ctdata->attributes as $k => $spec) {
			if ($i > 0) $l .= "$sep";
			$i++;
			if (!property_exists($o, $k)) continue;
			if ($o->$k === null) 		  continue;
			if ($o->$k ==  '') 		  	  continue;
			$l .= "\"".$o->$k."\"";
		}
		foreach ($this->ctdata->subscriptions as $k => $spec) {
			$k = $spec->name;
			$v = false;
			if (property_exists($o, $k)) {
				$v = $o->$k;
			} 
			$l .= ",$v";
		}
		return $l;
	}

	function contact_json_fmt($o) {
		$d = [];
		foreach ($this->ctdata->attributes as $k => $spec) {
			if (!property_exists($o, $k)) continue;
			if ($o->$k === null) 		  continue;
			if ($o->$k ==  '') 		  	  continue;
			array_push($d, [ "name" => $k, "value" => $o->$k ]);
		}
		#return json_encode([ "attributes" => $d ]);,
		$s = [];
		foreach ($this->ctdata->subscriptions as $i => $spec) {
			$k = $spec->name;
			if (!property_exists($o, $k) || ($o->$k !== true))
				array_push($s, [ "name" => $k, "subscription" => false ]);
			else
				array_push($s, [ "name" => $k, "subscription" => true ]);
		}
		return json_encode([ "attributes" => $d, "subscriptions" => $s ]);
	}

	function contact_send($o) {
		if ($o === false || !is_object($o)) {
			_err("invalid contact data " . print_r($o, true));
			return false;
		}
		if ($this->ctdata == false) {
			_err("no contact specification");
			return false;
		}
		if (!property_exists($o, $this->aid)) {
			if (property_exists($o, $this->email) && property_exists($o, $this->first) && property_exists($o, $this->last)) {
				$mail  = $this->email;
				$first = $this->first;
				$last  = $this->last;
				_err("no $this->aid present in contact (email: $o->$mail, nom: $o->$last, prenom: $o->$first), skipping\n");
			} else _err("no $this->aid present in contact:\n" . print_r($o, true));
			return false;
		}

		if (!($o2 = $this->contact_check($o)))  {
			_err("invalid contact");
			return false;
		}

		$js = $this->contact_json_fmt($o2);
		
		$r = $this->post("entity/$this->entity/table/Contacts/profile", $js);

		#info("ACT RETURN = " . print_r($r, TRUE));

		if ($r === false) {
			_warn("cannot insert data");
			return false;
		}
		if (property_exists($r, "status")) {
			_warn("error inserting data, status: $r->status, message: $r->message");
			return false;
		}
		return $r->profileId;
	}
	function contacts_send($lo) {
		if ($lo === false || !is_array($lo)) {
			_err("invalid list of contact data");
			return false;
		}
		if ($this->ctdata == false) {
			_err("no contact specification");
			return false;
		}
		$str  = $this->contact_csv_hdr();
		$str .= "\n";
		foreach ($lo as $o) {
			$o2 = $this->contact_check($o);
			$str .= $this->contact_csv_fmt($o2) . "\n";
		}
		
		$filename = tempnam(getcwd()."/tmp", "contacts_");
		file_put_contents(getcwd() . "/tmp" . $filename, $str);
		$zip = new ZipArchive();
		if (!$zip->open($filename . ".zip", ZipArchive::CREATE)) {
			_err("cannot open $filename.zip");
			unlink($filename);
			return;
		}
		if (!$zip->addFile($filename)) {
			_err("cannot add $filename to zip");
			#unlink($filename);
			unlink("$filename.zip");
			return;
		}
	  	$zip->close();
		$r = $this->upload("entity/$this->entity/table/Contacts/import", [
			"format"          => "COMMA_SEPARATED_VALUES",
			"mode"            => "CREATE_UPDATE",
			"headerKeyColumn" => $this->key_file,  
			"inputFile"       => curl_file_create("$filename.zip", "text/csv", "$filename.zip")
		]);

		#unlink($filename);
		#unlink("$filename.zip");

		if (!$r) {
			_err("problem uploading file");
			return false;
		}
		if (!($r = json_decode($r))) {
			_err("problem with return");
			return false;
		}
		if (property_exists($r, "id")) {
			_log("import id = '$r->id'");
			return $r->id;
		} 
		_err("could not perform import: " . print_r($r, true));
		return false;	
	}
	function cts_send2csv($ctable, $lo) {
		$i=0;
		if ($ctable == "Contacts")  return $this->contacts_send($lo);

		if ($lo === false || !is_array($lo)) {
			_err("invalid list of contact data");
			return false;
		}
		if (!($spec = $this->ct_spec($ctable))) {
			_err("no custom table specification for $ctable");
			return false;
		}

		$filename = $ctable.".csv";
		if (!($handle = fopen($filename, "wb"))){
			_err("Cannot create file ".$filename);
			return false;
		} else {
			_info("Create  file ".$filename);
		} 

		fwrite($handle, $this->ct_csv_hdr($spec,";").PHP_EOL);
		
		foreach ($lo as $o) {
			if ($i==0){
				if (!$this->ct_check($ctable, $o)){
					_err("Structure non conforme");
					return false; 
				}
			}
			fwrite($handle, $this->ct_csv_fmt($o, $spec,";") . PHP_EOL);
			if ($i%2000==0)
				_info($i." lines written");
			$i++;
		}
		fclose($handle);
		return true; 	
	}

	function ct_send($ctable, $o) {
		if ($ctable == "Contacts")  return $this->contact_send($o);

		if ($o === false || !is_object($o)) {
			_err("invalid contact data " . print_r($o, true));
			return false;
		}
		if (!($spec = $this->ct_spec($ctable))) {
			_err("no custom table specification for $ctable");
			return false;
		}

		if (!($o2 = $this->ct_check($ctable, $o))) {
			_err("bad $ctable data");
			return false;
		}
		$js = $this->ct_json_fmt($o, $spec);
		
		$r = $this->post("entity/$this->entity/customTable/$ctable/record", $js);
		if ($r === false) {
			_warn("cannot insert data");
			return false;
		}
		if (property_exists($r, "status")) {
			_warn("error inserting data, status: $r->status, message: $r->message");
			return false;
		}
		if (property_exists($r, "profileId")) return $r->profileId;
		if (property_exists($r, "businessKey")) return $r->businessKey;
		return true;
	}
	function cts_send($ctable, $lo) {
		if ($ctable == "Contacts")  return $this->contacts_send($lo);

		if ($lo === false || !is_array($lo)) {
			_err("invalid list of contact data");
			return false;
		}
		if (!($spec = $this->ct_spec($ctable))) {
			_err("no custom table specification for $ctable");
			return false;
		}
		$str  = $this->ct_csv_hdr($spec);
		$str .= "\n";
		_info("Data validation...");
		foreach ($lo as $o) {
			if ($this->ct_check($ctable, $o))
				$str .= $this->ct_csv_fmt($o, $spec) . "\n";
		}
		
		$filename = tempnam('', $ctable.".csv");
		_info("Create temp file $filename");
		file_put_contents($filename, $str);
		_info("Create archive file");
		$zip = new ZipArchive();
		if (!$zip->open($filename . ".zip", ZipArchive::CREATE)) {
			_err("cannot open $filename.zip");
			unlink($filename);
			return;
		}
		if (!$zip->addFile($filename, basename($filename))) {
			_err("cannot add $filename to zip");
			#unlink($filename);
			unlink("$filename.zip");
			return;
		}
		$zip->close();
		_info("Sending data...");
		$r = $this->upload("entity/$this->entity/customTable/$ctable/import", [
			"format"          => "COMMA_SEPARATED_VALUES",
			"mode"            => "CREATE_UPDATE",
			"headerKeyColumn" => $this->key_file,
			"inputFile"       => curl_file_create("$filename.zip", "text/csv", "$filename.zip") 
		]);

		unlink($filename);
		#unlink("$filename.zip");

		if (!$r) {
			_err("problem uploading file");
			return false;
		}
		if (!($r = json_decode($r))) {
			_err("problem with return");
			return false;
		}
		if (property_exists($r, "id")) {
			_log("import id = '$r->id'");
			return $r->id;
		} 
		_err("could not perform inport: " . print_r($r, true));
		return false;	
	}
}
/*************
#
#
class actito_ci_part () {
	private $maxsise;
	private $fname;
	private $file;
	private $status;
	private $import_id;

	function __construct($maxsize = 10000) {
		$this->inid  = [];
		$this->a     = new Actito();
		$this->grain = $grain;
		$this->count = 0;
		# 
		$this->fname = tempnam(getcwd()."/tmp", "contacts_".yyymmddhhmmss());
		#	
		if (($this->file  = fopen($this->fname, "w")) === false) {
			_err("cannont create file $this->fname");
		}
		fwrite($this->file,  $a->contact_csv_hdr() . "\n");
	}
	function send() {
		fclose($this->file);
		exec("zip $this->fname");
		$r = $this->a->upload("entity/$this->entity/table/Contacts/import", [
			"format"          => "COMMA_SEPARATED_VALUES",
			"mode"            => "CREATE_UPDATE",
			"headerKeyColumn" => "idAmabis",  
			"inputFile"       => curl_file_create("$this->fname.zip", "text/csv", "$this->fname.zip") 
		]);

		if (!$r) {
			_err("problem uploading file");
			return false;
		}
		if (!($r = json_decode($r))) {
			_err("problem with return, data: <<\n" . print_r($r, true) . "\n>>\n");
			return false;
		}
		if (property_exists($r, "id")) {
			_log("import id = '$r->id'");
			array_push($this->inid, $r->id);
			return $r->id;
		} 
		_err("could not perform import: " . print_r($r, true));
		return false;	
	}	
	function append() {
		if (($o2 = $this->a->contact_check($o)) === false) {
			return false;	
		}
		fwrite($this->file, $this->a->contact_csv_fmt($o2) . "\n");
		$this->count++;
		if ($this-count >= $this->grain) {
			if ($this->status = $this->a->send() === false) {
				_err("import failed");
			}
		}
	}
	function clean() {
		if (file_exists($this->fname)) {
			unlink($this->fname);
		}	
	}
	function report() {
	}
	function status() {
	}

}
class actito_import {
	private $parts;
	function __construct($grain = 10000) {
		$this->parts  = [];
	}
	function init_part() {
	}
	function append($o) {
		if (($o2 = $this->a->contact_check($o)) === false) {
			return false;	
		}
		fwrite($this->file, $this->a->contact_csv_fmt($o2) . "\n");
		$this->count++;
		if ($this-count >= $this->grain) {
			if ($this->send() === false) {
				_err("import failed");
			}
			$this->count = 0;
		}
	}

	function append($lo) {
		foreach ($lo as $o) {
			append_one($o);
		}
	}
	function send() {
		fclose($this->file);
		exec("zip $this->fname");
		$r = $this->a->upload("entity/$this->entity/table/Contacts/import", [
			"format"          => "COMMA_SEPARATED_VALUES",
			"mode"            => "CREATE_UPDATE",
			"headerKeyColumn" => "idAmabis",  
			"inputFile"       => curl_file_create("$this->fname.zip", "text/csv", "$this->fname.zip") 
		]);

		if (!$r) {
			_err("problem uploading file");
			return false;
		}
		if (!($r = json_decode($r))) {
			_err("problem with return, data: <<\n" . print_r($r, true) . "\n>>\n");
			return false;
		}
		if (property_exists($r, "id")) {
			_log("import id = '$r->id'");
			array_push($this->inid, $r->id);
			return $r->id;
		} 
		_err("could not perform import: " . print_r($r, true));
		return false;	
	}	
	function wait_import_status() {
		$end = false;
		#while ($end) {
		#}
	}
}
************************/

?> 
