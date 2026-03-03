<?php

require_once("lib/csv.php");
require_once("lib/cachecsv.php");
require_once("rcu/env.php");

class dst_csv {

	function validate($conf) {
		return true;
				
	}
	function __construct($conf) {
		$this->buf = [];
		$this->key = "";
		$this->fragments = $conf->fragments;
		$this->fields = [];
		$file = [];
		$this->params = $conf->dest->params;
		$this->main_file_get();
		$this->last_err = null;
		
		$sep = $this->params->sep; 
		$del = $this->params->del;
		
		$this->maincsv = new csv(rcu_process_dir()."/".$this->file, $sep, $del);
		$cols = $this->maincsv->hdr;
		
		foreach ($conf->map as $map) {
                    $re = '/^map_link\(.*/';
                    preg_match($re, $map->dest, $m);
			if (!in_array($map->dest, $cols) && count($m) == 0) {
				_warn("ignore field $map->dest since not present in destination file");
                        }else if(count($m) > 0){
                            $re = '/(map_[a-z_]*)\(((?>[^()]|(?R))*)\)/';
                            preg_match($re, $map->dest, $m);
                            $param = explode(",",$m[2]);
                            $this->fil_link =$param[0];
                        } else {
				array_push($this->fields, $map->dest);
			}
		}

		
		foreach ($this->fragments as $frag){
			$fil = $frag->file;
			array_push($file, $fil);
		}
		
		#Verifier si les fichiers de sorties (dest) existent, sinon on les créés
		foreach ($file as $fi) {
			if (!file_exists($fi)){
				$fileopen = fopen(rcu_process_dir() . "/" . "$fi","w+");
				fclose($fileopen);
			}
		}
	}
	
	function main_file_get(){
            foreach($this->fragments as $fragment){
                if($fragment->type== "main")
                    $this->file = $fragment->file;
            }
            return false;
        }
	
	function mode() {
		return $this->mode();	
	}
	
	function set($field, $value) {
		$this->buf[$field] = $value;
		return true;  
	
	}
	function last_error() {
		return $this->last_err;
	}
	function line_validate() {
		try {
			$this->last_err = null;
			$str = "";
			$key = "";
			$sep = $this->params->sep; 
			$del = $this->params->del;
			$re = '/^map_link\(.*/';
			
			$this->maincsv = new csv(rcu_process_dir()."/".$this->file, $sep, $del);
						
			if ($this->maincsv->nlines == 0){
			 	foreach(array_keys($this->buf) as $k){
			 		if(!preg_match($re, $k)){
		      				if (empty($key)) $key = $k;
						else $key .= $this->params->sep.$k;
					}
				}
				foreach ($this->buf as $v){
					if(!empty($v)){		//ATTENTION CAR SI ON A UNE VALEUR VIDE, ELLE N'EST PAS ENREGISTREE !!
						if (empty($str)) $str = $v; 
						else $str .= $this->params->sep.$v;
					}
				}
				$key = $key."\n".$str;
            			file_put_contents(rcu_process_dir() . "/" . $this->file, $key);
           		 }else {
				foreach ($this->buf as $v){
					if(!empty($v)){	
						if (empty($str)) $str = $v; 
						else $str .= $this->params->sep.$v;
					}
				}
				file_put_contents(rcu_process_dir() . "/" . $this->file, "\n".$str, FILE_APPEND);
			}
			$this->buf = [];
			$this->maincsv = new csv(rcu_process_dir()."/".$this->file, $sep, $del);
			
			$i = array_search("link", array_column($this->fragments, 'type'));
			$element = ($i !== false ? true : false);
			if($element=== true){
				$this->map_link_insert($this->maincsv->data ,$this->fil_ref);
			}
                        
			
		} catch(Exception $e){
			$this->last_err = $e->getMessage();
			return false;
		}
        return true; 
	}
	
	function flux_validate() {
		return true;
	}
	
	function value($field) {
		if ($this->type == "main")
			return $this->csv->get($this->current, $field);
		return false;
	}

	function map_dst($src,$value,$mode){
		
		$sep = $this->params->sep; 
		$del = $this->params->del;
		$ref = "";
		
		$this->refcsv = new csv(rcu_process_dir() . "/" .$src, $sep, $del);
		
		$fields = [];
		$values = [];
		foreach($this->fragments as $fragment){
			foreach($fragment as $k => $v){
				if($k == "file" && $src == $v){
		                $ref = $fragment->ref;
		                $id = $fragment->id->field;
		                $type = $fragment->id->type;
				}
			}
		}
		    

		if ($this->refcsv->nlines == 0){
			$contains = "$id;$ref\n1;$value";
		    	file_put_contents(rcu_process_dir() . "/" . $src, $contains);
		    	$cvid = 1;
		}else {
		    	$lastline = max($this->refcsv->data);
		    	$lastid = $lastline->id;
		    	$exist = false;
		    	
		    	foreach ($this->refcsv->data as $v){
				if ($v->libelle == $value){
			    		$exist = true;
			    		$cvid = $v->id;
			    	}if ($v->name == $value){
			    		$exist = true;
			    		$cvid = $v->id;
			    	}
			}
			
			
			if ($exist === false){
				$cvid = $lastid+1;
				file_put_contents(rcu_process_dir() . "/" . $src, "\n".($lastid+1).";$value", FILE_APPEND);
			}
		}
		return $cvid;
            
	}
	
	
	function map_link_insert($data,$values){
	
		//echo "\r\n************\r\n";
		//print_r($values);
		
		$sep = $this->params->sep; 
		$del = $this->params->del;
	
            foreach($this->fragments as $fragment){
                foreach($fragment as $k => $v){
                    if($k == "file"){
                        if(isset($fragment->id->field))
                	$file[$v] = $fragment->id->field;
		        
                    }
                    
                    if($k == "file" && $this->fil_link == $v){
                        $from_file = $fragment->link->from_file;
                        $to_file = $fragment->link->to_file;
                        $from_id = $fragment->link->from_id;
                        $to_id = $fragment->link->to_id;
                        $from_ref = $fragment->link->from_ref;
                        $to_ref = $fragment->link->to_ref;
                    }
                }
            }
            
            
            $r = new csv(rcu_process_dir() . "/" .$to_file, $sep, $del);
            
            $l = new csv(rcu_process_dir() . "/" .$this->fil_link, $sep, $del);
            
            $q = "";
		/*echo "dr : ";
		print_r($dr);
		echo "\n\r";
		echo "D !! : ";
		print_r($d);
		echo "\n\r";*/
		
            foreach($data as $d){
            	$id = $d->id;
            }
	 	foreach($values as $va){
			 if($r->nlines > 0 ) {
				foreach($r->data as $dr){
					if($va === $dr->name){
						if(empty($q) && $l->nlines == 0) $q = $from_id.$sep.$to_id."\n".$id.$sep.$dr->id."\n"; 
						else $q .= $id.$sep.$dr->id."\n";
					}
				}
			}
	  	 }
	  
            
            if(!empty($q)){
              	$res = file_put_contents(rcu_process_dir() . "/" . $this->fil_link, $q, FILE_APPEND);
                if($res === false){
                    _warn("ignore insert");
                }
                unset($this->fil_ref);
            }
            
            
        }
        
        function map_link($val_fil_ref){
            $this->fil_ref[] = $val_fil_ref;            
        }
        

}
