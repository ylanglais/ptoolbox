<?php
require_once("lib/dbg_tools.php");
require_once("lib/date_util.php");
require_once("lib/args.php");
require_once("lib/user.php");
require_once("lib/session.php");
require_once("lib/tlist.php");

global $_session_;

#
# Start/Restore session:
if (!isset($_session_)) $_session_ = new session();
unset($_SESSION["glist_data"]);
#
# Check 
$_session_->check();
if (!$_session_->has_role("admin")) exit;
print("<div id='connections'>\n"); 
print("<h1>Gestion des accès</h1><br/>\n");
print("<table class='form'><tr><td>\n");

/*************************************************
 ******** Envoyer une table DB a glist *************
**************************************************/
print(tlist("tech.connection"));
//print(glist("tech.connection",array("id","login","since","until","ip","state"),0,5));

/*************************************************
 ******** Envoyer un tableau php a glist *********
**************************************************/
/*$data = array(array("col_1"=>"data_1_1","col_2"=>"data_2_1","col_3"=>"data_3_1","col_4"=>"data_4_1"),
            array("col_1"=>"data_1_2","col_2"=>"data_2_2","col_3"=>"data_3_2","col_4"=>"data_4_2"),
            array("col_1"=>"data_1_3","col_2"=>"data_2_3","col_3"=>"data_3_3","col_4"=>"data_4_3"),
            array("col_1"=>"data_1_4","col_2"=>"data_2_4","col_3"=>"data_3_4","col_4"=>"data_4_4"),
            array("col_1"=>"data_1_5","col_2"=>"data_2_5","col_3"=>"data_3_5","col_4"=>"data_4_5"),
            array("col_1"=>"data_1_6","col_2"=>"data_2_6","col_3"=>"data_3_6","col_4"=>"data_4_6")
);
print(glist($data,array("col_2","col_3","col_4"),0,5));*/

/*************************************************
 ******** Envoyer un fichier a glist *************
**************************************************/
//print(glist(array("data/process/pays.csv",'|'),array("paysid","codealpha2","libelle","zone_tva"),0,10));


print("</td><td>\n");
print("<div id='connection_ui'></div>\n");
//print("<div id='pays_ui'></div>\n");
print("</td></tr></table>\n");
print("</div>\n");
?>

