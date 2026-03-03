<?php

require_once("lib/date_util.php");
require_once("lib/dbg_tools.php");
require_once("lib/db.php");
require_once("lib/query.php");

$db = new db("exploit");

$today = today();


print("<h2>Journée du $today</h2>");

$q = new query($db, "
select 
	p.date_jour    as date 
	,ph.lib_plage  as plage 
	,p.ordre       as ordre
	,t.libelle     as tache
	,a.type        as application
	,p.fait		   as status
	,p.commentaire as commentaire
	,p.num_lot     as lot
	,p.veille      as veille
	,p.num  	   as num
from 
	tb_planning       p
	,tb_plage_horaire ph
	,lst_application  a
	,lst_tache        t
where 
	date_jour       = '$today' 
	and ph.plage    = p.plage_horaire
	and p.num_tache = t.num
	and a.num       = t.application
order by 
	plage_horaire
	,ordre
");

print("<table><tr><th>Plage</th><th>Ordre</th><th>Tache</th><th>Application</th><th>Commentaire</th><th>Lot</th><th>Veille</th><th>Statut</th></tr>\n");

while ($o = $q->obj()) {
	print("<tr><td>$o->plage</td><td>$o->ordre</td><td>$o->tache</td><td>$o->application</td><td>$o->commentaire</td><td>$o->lot</td><td>$o->veille</td><td>$o->status</td></tr>\n");
}
print("</table>\n");

?>
