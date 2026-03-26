<?php

require_once("lib/dbg_tools.php");
require_once("lib/args.php");
require_once("lib/user.php");
require_once("lib/session.php");
require_once("lib/style.php");
require_once("lib/util.php");

global $_session_;

function acct_connect() {
	return '<section>
<h2>Connectez-vous</h2>
<section>
	<article class="panel" id="login">
		<h3>Vous avez déjà un compte</h3>
		<label for="email">Email</label><br/>
		<input type="text" id="email"/><br/>
		<label for="passwd">Mot de passe</label><br/>
		<input type="password" id="passwd"/><br/>
		<input id="connect" onclick="acct_login()" type="button" value="Se connecter" style="width: 20em;"/>
		<div id="message"></div>
	</article>
	<article class="panel" id="createacct">
		<h3>Créez votre compte</h3>
		<input type="button" id="professionnel" onclick="acct_professionnel()" value="Professionnel" style="width: 20em;"/><br/>
		<input type="button" id="particulier"   onclick="acct_particulier()"   value="Particulier"   style="width: 20em;"/><br/>
	</article>
</section>';
}
function acct_login() {
	return '';
}
function acct_create_pro() {
	return '';
}
function acct_create_par() {
	$a = new args();
	$str = "<h2>Création de compte particulier</h2>
	<article>
	";

	$flds = [
		[ "name" => "Titre"               ],
		[ "name" => "Prénom"              ],
		[ "name" => "Nom"                 ],
		[ "name" => "Téléphone principal" ],
		[ "name" => "Email"               ],
		[ "name" => "Ligne 1"             ],
		[ "name" => "Ligne 2"             ],
		[ "name" => "Ligne 3"             ],
		[ "name" => "Ligne 4"             ],
		[ "name" => "Code postal", "onchange" => "ref_get('ville_cp', 'cp', 'Code postal', 'nom_standard' 'Ville')" ],
		[ "name" => "Ville",       "onchange" => "ref_get('ville_cp', 'nom_standard' 'Ville', 'cp', 'Code postal')" ],
		[ "name" => "Pays"                ]
	];
	
	foreach ($flds as $f) {
		$str .= "\t<label for='$f->name'>$f->name</label></br><input type='text' id='$tit' value='";
		if ($a->has($f->name)) $str .= $a->val($f->name);
		$str .= "'";
		if (property_exists($f, "onchange")) {
			$str .= " onchange='$f->onchange'";
		}
	}
	$str .= "/></br></article><article>
			<label for='passwd1'>Saisissez votre mot de passe</label><br/>
			<input type='password' id='passwd1'/><br/>
			<label for='passwd2'>Resaisissez votre mot de passe</label><br/>
			<input type='password' id='passwd2'/><br/>
			</article><article>	
			<input id='connect' onclick='acct_create_particulier()' type='button' value='Se connecter' style='width: 20em;'/>
			</article>
			</section>";

	return $str;
}
function acct_detail_pro() {
    $id = get_user_id();
	$sql = "select 
	seller.alias   as \"Pseudo\"
	,seller.type    as \"Type\"
	,company.name  as \"Entreprise\"
	,company.name  as \"Raison sociale\"
	,company.siren as \"SIREN\"
	,company.siret as \"SIRET\"
	,company.ape   as \"APE\"
	,company.naf   as \"NAF\"
from seller
left join company  on company.id = seller.company_id
left join person   on person.id  = seller.contact_id
where
	person.uid = $id";
#dbg($sql);
	$q = new query($sql);

	$o = $q->obj();

#dbg($o);
	$flds = [
		"Pseudo",
		"Type",
		"Entreprise",
		"Raison sociale",
		"SIREN",
		"SIRET",
		"APE",
		"NAF"
	];
	$str = " <section> <article>";
	foreach ($flds as $n => $key) {
		$str .= "\t<label for='$key'>$key</label></br>\n<input type='text' id='$key' value='".$o->$key."'></br>";
	}
	$str .= "</article></section>";
	return $str;
}
function acct_detail_par() {
	$id = get_user_id();
	$sql = "select 
	ref.title.value		as \"Titre\"
	,person.first_name	as \"Prénom\"
	,person.last_name	as \"Nom\"
	,phone.phone		as \"Téléphone principal\"
	,email.email		as \"Email\"
	,address.line_1 	as \"Ligne 1\"
	,address.line_2     as \"Ligne 2\"
	,address.line_3		as \"Ligne 3\"
	,address.line_4		as \"Ligne 4\"
	,address.zipcode	as \"Code postal\"
	,address.city       as \"Ville\"
	,ref.country.name	as \"Pays\"
from 
	person
left join ref.title         on ref.title.id = person.title
right join link_channel l1  on l1.entity      = 'person'    and l1.entity_id  = person.id
right join phone            on l1.channel     = 'phone'     and l1.channel_id = phone.id
left  join entity_tags  e1  on e1.tag         = 'principal' and e1.entity     = 'phone'     and e1.id = phone.id
right join link_channel l2  on l2.entity      = 'person'    and l2.entity_id  = person.id
right join email            on l2.channel     = 'email'     and l2.channel_id = email.id
right join link_channel l3  on l3.entity      = 'person'    and l3.entity_id  = person.id
right join address          on l3.channel     = 'address'   and l3.channel_id = address.id
left  join entity_tags  e2  on e2.tag         = 'principal' and e2.entity     = 'address'   and e2.id = address.id	
left  join ref.country      on ref.country.id = address.country
where 
	person.uid = '$id'";

	$q = new query($sql);

	$o = $q->obj();

#dbg($o);
	$tit = "Titre";
	$pre = "Prénom";
	$nom = "Nom";
	$tel = "Téléphone principal";
	$mai = "Email";
	$l_1 = "Ligne 1";
	$l_2 = "Ligne 2";
	$l_3 = "Ligne 3";
	$l_4 = "Ligne 4";
	$cop = "Code postal";
	$vil = "Ville";
	$pay = "Pays";
	
	$str = " <section> <article>
		<label for='$tit'>$tit</label></br>
		<input type='text' id='$tit' value='".$o->$tit."'></br>
		<label for='$pre'>$pre</label></br>
		<input type='text' id='$pre' value='".$o->$pre."'> </br>
		<label for='$nom'>$nom</label></br>
		<input type='text' id='$nom' value='".$o->$nom."'> </br>
		<label for='$tel'>$tel</label></br>
		<input type='text' id='$tel' value='".$o->$tel."'> </br>
		<label for='$mai'>$mai</label></br>
		<input type='text' id='$mai' value='".$o->$mai."'> </br>
		<label for='$l_1'>$l_1</label></br>
		<input type='text' id='$l_1' value='".$o->$l_1."'> </br>
		<label for='$l_2'>$l_2</label></br>
		<input type='text' id='$l_2' value='".$o->$l_2."'> </br>
		<label for='$l_3'>$l_3</label></br>
		<input type='text' id='$l_3' value='".$o->$l_3."'> </br>
		<label for='$l_4'>$l_4</label></br>
		<input type='text' id='$l_4' value='".$o->$l_4."'> </br>
		<label for='$cop'>$cop</label></br>
		<input type='text' id='$cop' value='".$o->$cop."'> </br>
		<label for='$vil'>$vil</label></br>
		<input type='text' id='$vil' value='".$o->$vil."'> </br>
		<label for='$pay'>$pay</label></br>
		<input type='text' id='$pay' value='".$o->$pay."'> </br>
	";

	$str .= "</article></section>";
	return $str;
}

function acct_ctrl() {
	global $_session_;
	$a = new args();
	# Start/Restore session:
	if (!isset($_session_)) $_session_ = new session();
	if ($_session_->isnew() || get_user_id() == -1) {
		if ($a->has("login") && $a->has("passwd")) {
			$ip = false;
			if ($a->has("ip")) $ip = $a->val("ip");
			else               $ip = get_ip();
			$u = new user();
			if (!($u->auth_check($a->val("login"), $a->val("passwd"), $ip))) {
				print(acct_connect());
			}
			$_session_->create($u, $ip);
			$a->clean("passwd");
			$a->clean("login");
			if (has_role("notconnected")) {
				remove_role("notconnected");
			}
		//header("Location: index.php");
			return;
		} else {
			print(acct_connect());
			return;
		}
	}

	$reply = "";
	if (has_role("Entreprise")) {
		$reply .= "<h2>Détails du compte</h2>";
		$reply .= acct_detail_pro();
	} 
	$reply .= "<h2>Détails du contact</h2>";
	$reply .= acct_detail_par();
	print( $reply);
}
