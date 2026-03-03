<?php
require_once("lib/curl.php");

$js=<<<EOB
{
   "field_days_to_advertise" : [
      {
         "value" : "90"
      }
   ],
   "field_headquarter_agency" : [
      {
         "value" : "headquarter"
      }
   ],
   "field_introduction" : [
      {
         "format" : "full_html",
         "value" : "<p style=\"font-weight: 400;\"> </p><p style=\"font-weight: 400;\">Rejoindre La France Mutualiste, c'est rejoindre une mutuelle d'épargne individuelle du Groupe Malakoff Humanis, fondamentalement engagée.</p><p style=\"font-weight: 400;\">Engagée pour ses adhérents, pour ses collaborateurs, pour la diversité, et plus largement, pour la société. Ce sont les réussites de chacun qui font le succès de La France Mutualiste.</p><p style=\"font-weight: 400;\">Nous sommes tous engagés pour réussir, ensemble. </p>"
      }
   ],
   "field_job_reference" : [
      {
         "value" : "Test_1740150360"
      }
   ],
   "field_job_type" : [
      {
         "value" : "cdi"
      }
   ],
   "field_link" : [
      {
         "options" : [],
         "title" : null,
         "uri" : "https://www.aplitrak.com/?adid=bS5jb3BwaW4uMTQ2MDMuMTEyOTNAZnJtdXR1YWxpc3RlLmFwbGl0cmFrLmNvbQ"
      }
   ],
   "field_location" : [
      {
         "value" : "Paris, Île-de-France"
      }
   ],
   "field_mission" : [
      {
         "format" : "full_html",
         "value" : "<p>La France Mutualiste valorise la diversité des personnes qu'elle embauche et favorise un milieu de travail où les différences individuelles sont appréciées et respectées, de façon à accompagner chacun dans le développement de son potentiel. </p><p><strong>Pour candidater à ce poste il est impératif de justifier d'un casier judiciaire vierge.</strong></p><img src=\"https://counter.adcourier.com/bS5jb3BwaW4uMTQ2MDMuMTEyOTNAZnJtdXR1YWxpc3RlLmFwbGl0cmFrLmNvbQ.gif\">"
      }
   ],
   "field_profile" : [
      {
         "format" : "full_html",
         "value" : "<p>Test</p>"
      }
   ],
   "title" : [
      {
         "value" : "Test3"
      }
   ],
   "type" : [
      {
         "target_id" : "job"
      }
   ]
}
EOB;

#print( $js . "\n");
$c = new curl("https://www.la-france-mutualiste.fr/", [ "Content-type: application/json; charset=utf-8", "Authorization:  Basic YnJvYWRiZWFuOkU0aXJYeDJLUEtfZmZBR0VQUW5AY1o5Ug=="], false, true);
$r = $c->post("node?_format=json", $js);

print("$r = " . print_r($r, true) . "\n");

?>
