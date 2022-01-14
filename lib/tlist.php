<?php
require_once("lib/query.php");
require_once("lib/dbg_tools.php");
require_once("lib/db.php");
if(!isset($_SESSION)) session_start(); 

function tlist($table, $fieldlist = null, $start = 0, $page = 25, $where = "", $edit = "") {
dbg("page = $page");
	$fields = $fieldlist;
	$edit   = $edit;
	$where  = $where;
	$src_type = ",\"type\": \"table\"";

	$delimiter = "-*-";
	$listefield = "";
	$detail_fields = "";
        
	if (is_array($fieldlist) && !is_null($fieldlist)) {
		$detail_fields = detail_fields($fieldlist);
		foreach ($fieldlist as $key => $val) {
			if (gettype($key) == "string") {
				$listefield .= empty($listefield) ?  $key : ",".$key ;                    
			} else {
				$listefield .= empty($listefield) ?  $val : ",".$val;
			}
		}
		
		$fieldlist = $listefield;
	}

	if (is_array($table) && count($table) == 2 && gettype($table[1]) == "string") {
		$table = $table[0].$delimiter.$table[1];
		$data_type = "from_file";
	}
        
	if(isset($data_type) && $data_type == "from_file")
		$type = explode($delimiter, $table);

	if (isset($type) && count($type)>1) {
		$infos = csvinfos($type, $fieldlist, $start, $page, $where, $edit);
		/*if(is_array($fieldlist) && !is_null($fieldlist))
                $fieldlist = implode(",",$fieldlist);*/

		$table = $type[0].$delimiter.$type[1]; 
		$filename = explode('.',$type[0]);
		$filename = array_shift($filename);
		$filename = explode('/',$filename);
		$tid = end($filename);
		$src_type = ",\"type\": \"csv\"";
            
	} else if(is_array($table) || $table == "glist_data") {
		if (is_array($table)) {
			$infos = arrayinfos($table, $fieldlist,$start,$page,$where,$edit);
			$_SESSION["glist_data"] = $table;
			$table = "glist_data"; 
			$tid = "array_glist";
			$src_type = ",\"type\": \"array\"";
		} else if ($table == "glist_data") {
			$infos = arrayinfos($_SESSION["glist_data"], $fieldlist,$start,$page,$where,$edit);                
			$table = "glist_data"; 
			$tid = "array_glist";
			$src_type = ",\"type\": \"array\"";
		}
	} else {
		$infos = dbinfos($table, $fieldlist,$start,$page,$where,$edit);
		/*if(is_array($fieldlist) && !is_null($fieldlist))
			$fieldlist = implode(",",$fieldlist);*/
		if (preg_match("/^([^.]*)\.(.*)$/", $table, $m)) $tid = $m[2];
		else $tid = $table ;
	}


        
	$id = "glist_$tid";

	$out = "";

	if ($page == 0) err("page size == 0");

	$np = ceil($infos["nb"] / $page);

	$las_off  = ($np - 1) * $page;

	if ($start < 0) $start = 0;
	if ($start >  $las_off) $start = $las_off;

	$nex_off  = $start + $page;
	$pre_off  = $start - $page;
	$fir_off  = 0;

	$sp = intdiv($start, $page) + 1;

	
	$out .= "<table class='glist' id='glist_$tid'>\n";
	$hdr = "<tr><th>#</th>";
        
        $fields = [];
        if(isset($infos["data"][0]) && count((array)$infos["data"][0]) >0)    
        foreach($infos["data"][0] as $f => $v){
            array_push($fields, $f);
            $hdr .= "<th>$f</th>";
        }

	$hdr .= "</tr>\n";

	$nfields = count($fields);

	$out .= $hdr;

        if (count($infos["data"]) == 0) {
		$out .= "<tr><td class='hdr' colspan='".($nfields + 1)."'>Pas de données</td></tr>\n";
	} else {
		$i = $start + 1;
		foreach($infos["data"] as $data){
			if ($i % 2) $odd = "class='odd'";
			else        $odd = "";
			$kl = "";

		foreach ($infos["tkeys"] as $k => $v) {
			if ($kl != "") $kl .=', ';
			$kl .= "{\"$v\": \"".$data->$v."\"}";
		}
		$vdata = "{\"table\": \"$table\", \"keys\": [ $kl ]$src_type $detail_fields}";
					
		//echo $vdata; exit();
		$out .= "<tr $odd onmouseover='this.classList.add(\"over\")' onmouseout='this.classList.remove(\"over\")' onclick='tlist_view(\"".$tid."_ui\", $vdata)'>";
		$out .= "<td class='num'>$i</td>";
		foreach($fields as $f){
			$cl   = "";
			$type = $infos["tflds"][$f]->data_type;
			if ($type == 'int') $cl = " class='number'";

			if (preg_match('/files$/', $tid) && preg_match('/^\//', $data->$f)) {
				$out .= "<td$cl><a href=".$data->$f." download=".basename($data->$f).">".$data->$f."</a></td>";
			} else { 
				$out .= "<td$cl>".$data->$f."</td>";}
			}
			$out .= "</tr>\n";
			$i++;
		}

		$out .= $hdr;
		$out .= "<tr><td class='hdr' colspan='".($nfields + 1)."'><div class='navigation'>";

		if ($start > 0) 
			$out .= "<a onclick='tlist_go(\"$id\", \"$table\", \"$fieldlist\", $fir_off, $page, \"$where\", \"$edit\")'><img height='25px' src='images/start.3f5a94.png'/></a>";
		if ($start > 0) 
			$out .= "<a onclick='tlist_go(\"$id\", \"$table\", \"$fieldlist\", $pre_off, $page, \"$where\", \"$edit\")'><img height='25px' src='images/sarrow.left.3f5a94.png'/></a>";
		$out .= "<input type='text' style='width: 15px; text-align: right;' value='$sp' id='".$table. "_".$page."'onchange='tlist_go(\"$id\", \"$table\", \"$fieldlist\", (this.value - 1) * $page, \"$page\", \"$where\", \"$edit\")'/> on $np";
		if ($nex_off <= $las_off)
			$out .= "<a onclick='tlist_go(\"$id\", \"$table\", \"$fieldlist\", $nex_off, $page, \"$where\", \"$edit\")'><img height='25px' src='images/sarrow.right.3f5a94.png'/></a>";
		if ($start < $las_off)
			$out .= "<a onclick='tlist_go(\"$id\", \"$table\", \"$fieldlist\", $las_off, $page, \"$where\", \"$edit\")'><img height='25px' src='images/end.3f5a94.png'/></a>";
		#$out .= "<span style='text-align: right'><label>Lignes par page:</label> <input type='text' style='width: 15px; text-align: right;' value='$page' onchange='tlist_go(\"$id\", \"$table\", \"$fieldlist\", $start, this.value, \"$where\", \"$edit\")'/></span>";
		$out .= "</div></td></tr></table>";
	}
	return $out;
}

function dbinfos($table, $fieldlist,$start,$page,$where,$edit){
    $db = new db();
    $data = [];

    $tflds = $db->table_columns($table);
    $tkeys = $db->table_keys($table);

    if ($fieldlist === null) 
		$sql     = "select * ";
    else {
        if (is_array($fieldlist) )
			$fieldlist = implode(', ',  $fieldlist);

		$sql     = "select $fieldlist";
    }

    $keylist = "";
    if ($edit != "") {	
		foreach ($tkeys as $k) {
			if ($keylist != '') $keylist .= ',';
				$keylist .= "$k as ___key_$k"; 
		}
		if ($keylist != '') $sql .= ", $keylist";
    }
    $sql .= " from $table $where";

    $q = new query($sql);

    $nb_rows = $q->nrows();
    if ($page !== null) {
        $sql .= " limit $page";
        if ($start !== null) {
                $sql .= " offset $start";
        }
    }    
    $q = new query($sql);
    while ($o = $q->obj()) {        
        $data[]= $o;
    }
    
    return array("data" => $data,"nb" => $nb_rows,"tflds" => $tflds,"tkeys" => $tkeys);

}

function csvinfos(array $source,$fieldlist,$start,$page,$where,$edit=""){
    $r = 0;
    if(!is_array($fieldlist) && !is_null($fieldlist))
        $fieldlist = explode (",", $fieldlist);
    
    if (($handle = fopen($source[0], "r")) !== FALSE) {
        while (($data = fgetcsv($handle, 1000, $source[1])) !== FALSE) {
            $num = count($data);	
            if($r == 0){
                for ($c=0; $c < $num; $c++) 
                    $head[] = $data[$c];
            }

            for ($c=0; $c < $num; $c++) {
                //Contruire de l'entete
                if($r == 0){
                        if(is_array($fieldlist) && in_array($head[$c],$fieldlist))
                        $header[] = $head[$c];
                        else if(is_null($fieldlist))
                        $header[] = $head[$c];
                }
                //Contruire les données du tableau
                if($r > 0){
                if(is_array($fieldlist) && in_array($head[$c],$fieldlist)){
                        $tab[($r-1)][$head[$c]] = $data[$c];
                        $tflds[$head[$c]] = (object) ["data_type" => gettype($data[$c])];
                }else if(is_null($fieldlist)){
                        $tab[($r-1)][$head[$c]] = $data[$c];
                        $tflds[$head[$c]] = (object) ["data_type" => gettype($data[$c])];
                        }				
                }


            }
            $r++;
        }
        fclose($handle);

        $nb_rows = count($tab);	    
        $tab = json_encode($tab);
        $tab = json_decode($tab);
    }
    return array("data" => array_slice($tab, $start, $page),"nb" => $nb_rows,"tflds" => $tflds,"tkeys" => $header);
}

function arrayinfos(array $source, $fieldlist, $start, $page, $where, $edit="") {
    if (!is_array($fieldlist) && !is_null($fieldlist))
        $fieldlist = explode(",", $fieldlist);
	$num = count($source);
    foreach($source[0] as $k => $v) $head[] = $k;
    
	$j=0;
    foreach($source as $ligne){
        $i=0;   
        foreach($ligne as $k => $v){
			if (is_array($fieldlist) && in_array($k,$fieldlist)){
				$tab[$j][$k] = $v;
				$tflds[$k]   = (object) ["data_type" => gettype($v)];
				$header[$j]  = $k;
			} else if (is_null($fieldlist)) {
				$tab[$j][$k] = $v;
				$tflds[$k]   = (object) ["data_type" => gettype($v)];
				$header[$j]  = $k;
           }
           $i++;
        }
        $j++;
    } 
   
    $nb_rows = count($tab);	    
    $tab = json_encode($tab);
    $tab = json_decode($tab);    
    
    return array("data" => array_slice($tab, $start, $page), "nb" => $nb_rows, "tflds" => $tflds,"tkeys" => $header);
}

function detail_fields($fieldlist) {
	$detail_fields = "{";
	foreach ($fieldlist as $key => $val) {
		$kl = "";               
		if (gettype($key) == "string") {
			$detail_fields .= ($detail_fields == "{") ? "\"".$key."\":" : ",\"".$key."\":";
			foreach ($val as $k => $v) {
				if ($kl != "") $kl .=', ';
					$kl .= "\"$k\": \"".$v."\"";
			}
			$detail_fields .= "{".$kl."}";

		} else {
			$detail_fields .= ($detail_fields == "{") ? "\"".$val."\": \"".$val."\"" : ",\"$val\": \"".$val."\"";
		}

	}
	$detail_fields .= "}";
	$json = json_decode($detail_fields);
	if (($json && $detail_fields != $json) === true) {
		$detail_fields = ",\"detail_fields\": $detail_fields";
		return $detail_fields;
	}
    return "";
}
?>
