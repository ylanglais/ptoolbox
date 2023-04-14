<?php

require_once("lib/curl.php");

class Emarsys {
	private $flds = [];
	private $fids = [];
	private $fnms = [];
	private $vls  = [];

	function __construct() {
		include("conf/emarsys.php");
		$this->base   = $emarsys_base;
		$this->user   = $emarsys_user;
		$this->pass   = $emarsys_pass;
	
		$this->fields();
	}

	function wsse_hdr() {
		$nonce = md5(rand());
		$created = new \DateTime();
		$iso8601 = $created->format(\DateTime::ISO8601);

		// create digest
		$digest = base64_encode(sha1($nonce . $iso8601 . $this->pass));

		// echo header data
		return sprintf('X-WSSE: UsernameToken Username="%s", PasswordDigest="%s", Nonce="%s", Created="%s"',
			$this->user, $digest, $nonce, $iso8601);
	}

	function i2f($id) {
		if (key_exists($id, $this->fids)) {
			return $this->fids[$id];
		} 
		return false;
	}

	function f2i($field) {
		if (key_exists($field, $this->fnms)) {
			return $this->fnms[$field];
		} 
		return false;
	}

	function hdr() {
		return [ $this->wsse_hdr($this->user, $this->pass), "Content-type: application/json; charset='utf-8'" ];
	}
	function get($endpoint, $param = null) {
		#print_r($this->hdr());
		$c = new curl($this->base, $this->hdr(), false, false);
		$r = $c->send("get", $endpoint, $param);
		if ($r === false) return false;
		return json_decode($r);		
	}
	function post($endpoint, $param = null) {
		$c = new curl($this->base, $this->hdr());
		$r = $c->send("post", $endpoint, $param);
		if ($r === false) return false;
		return json_decode($r);		
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

	function raw_fields() {
		return  $this->get("field");
	}

	function fields() {
		if ($this->flds == []) {
			$flds = $this->get("field");
			if ($flds === false) {
				_err("no data returned");
				return false;
			}
			if (!property_exists($flds, "data")) {
				_err("no data field");
				return false;
			}	
			if (!is_array($flds->data)) {
				_err("empty data field");
				return false;
			}	
			$this->flds = $flds->data;
			foreach ($flds->data as $f) {
				if ($f->string_id != '') {
					$this->fids[$f->id]        = $f->string_id; 
					$this->fnms[$f->string_id] = $f->id;
				} else {
					#warn("$f->id has no string_id but a name ($f->name)");
					$this->fids[$f->id]        = $f->name; 
					$this->fnms[$f->name]      = $f->id;
				}
				if ($f->application_type == "singlechoice") {
					$lvn = $this->fids[$f->id];
					$this->vls[$lvn] = [];
					$r = $this->get("field/$f->id/choice/translate/fr");
					foreach ($r->data as $d) {
						$this->vls[$lvn][$d->choice] = $d->id;
					}
				}
			}
		}
		return $this->fids;
	}

	function table_spec() {
		return $this->flds;
	}

	function contact_check($o) {
	}

	function contact_send($o) {
		if ($o === false || !is_object($o)) {
			_err("invalid contact data " . print_r($o, true));
			return false;
		}
		if (!property_exists($o, "id_rcu")) {
			if (property_exists($o, "email") && property_exists($o, "last_name") && property_exists($o, "first_name")) {
				_err("no id_rcu present in contact (email: $o->email, nom: $o->last_name, first_name: $o->fist_name), skipping\n");
			} else _err("no id_rcu present in contact:\n" . print_r($o, true));
			return false;
		}

		if (!property_exists($o, "email") || $o->email == "") {
			_err("contact has no email => skip");
			return false;
		}

		$c = new stdclass();

		foreach ($this->flds as $f) {
			$k  = $f->string_id;
			if ($k == '') $k = $f->name;
			#
			$id = $f->id;
			if (!property_exists($o, $k)) continue;
			if ($o->$k === null) 		  continue;
			if ($o->$k ==  '') 		  	  continue;

			$v = $o->$k;

			if ($f->application_type == 'singlechoice') {
				
				#
				# Try to find id from value:
				if (!key_exists($k, $this->vls)) {
					warn("no $k value list found, skip \"$k\" field");
					continue;
				} 
				if (!key_exists($o->$k, $this->vls[$k])) {
					warn("value list $k hash no value \"". $o->$k ."\", skip \"$k\" field");
					continue;	
				}
				$v = $this->vls[$k][$o->$k];
			}
			$c->$id = $v;
		} 
		$d = new stdclass();
		$d->keyid = $this->f2i("id_rcu");
		$d->contacts = [ $c ];
		#print(">>>>>>".print_r( $c, true) . "\n");;
		#print(">>>>>>". json_encode($d) . "\n");;

		$r = $this->put('contact/create_if_not_exists=1', json_encode($d));
		if (!is_object($r)) {
			err("cannot create contact $o->last_name $o->first_name $o->email ($o->id_rcu)");
		} else if (!property_exists($r, "replyCode")) {
			err("problem creating contact $o->last_name $o->first_name $o->email ($o->id_rcu) ==> ". print_r($r, true));
		} else if ($r->replyCode != 0) {
			err("problem creating contact $o->last_name $o->first_name $o->email ($o->id_rcu): $r->replyText");
		} else {
			if (key_exists("0", $r->data->ids)) {
				info("Created contact $o->last_name $o->first_name $o->email ($o->id_rcu) with Emarsys id ". $r->data->ids[0]);
			} else { 
				err("Creation of contact $o->last_name $o->first_name $o->email ($o->id_rcu): ". print_r($r->data->errors, true));
				return false;
			}

		}
		return $r;	
	}

/**********************
	function contacts_send() {
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
	}
}
**********************/

?>
