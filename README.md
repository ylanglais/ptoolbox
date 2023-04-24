# ptoolbox

conf		Répertoire contenant les fichiers de configuration
lib			Répertoire des classes principales
js			Répertoire des javascripts uilisés
wsd			Répertoire des drivers pour connexions des applications web (actito, emarsys, google analytics, jira, projector, Sirene, Zabbix) ou services (Sirene, msdq)
api			Répertoire pour exposer des API
parts		Répertoire contenant le éléments graphiques des pages (layout, header, menu, tailer)
scripts 	Outils divers
reports		Generic reports definition directory		
forms		Generic forms definition directory
usr			User pages


lib
apic.php			Gestion d'API
args.php			Gestions unifiée de l'accès aux arguments (CLI/GET/POST/json...)
audit.php			logging d'indormations de connexion
auth_ldap.php		Authentification via LDAP/AD
auth_local.php		Authentification locale
cachecsv.php		Caching de fichiers CSV (pour requetage)
csv.php				Manipulation de fichier CSV
curl.php			Interface curl pour connexion à des web services
date_util.php		Utilitaires de date
db.php				Abstraction bases de données 
dbdrv/				Drivers de bases de données (MySQL, Postgres, support partiel oracle, ODBC)
dbg_tools.php		Outils de debug
form.php			Gestion de formulaires. 
gform.php
glist.php
locl.php			Gestion de l'affichage localisé des nombres et pourcentages
ora.php
prov_db.php			DB driver for data provider abstraction
prov.php			Data provider abstraction
query.php			Query abstraction (no need to use connection if requests on default database)
rpt.php				Reporting facility
session.php			Session Management
store.php			Session persistant light storage
style.php			Style management
svg.php				SVG graphics (Charts, XY, Bars, pie charts)
user.php			user management
util.php			various utils


Database description:
audit			schema containing audit data
	action			log actions on entities
	connection		user connections	
db  			Shema containing datastructure version,
	changelog		list of database patches
history			Shema to store historised tables
param			Shema for all application paramters
	entity			Definition of entities
	field
	folder
	folder_page
	folder_perm
	fragment
	glist	
	item
	list
	page
	page_perm
	rights			Difinition of rights on tables and entities (per role, user, ...);
	style
	wfaction
	wfcheck
	workflow
public
ref
	page_type
	perm
stats
	duration
tech
	dbs
	role
	user
	user_role

public
ref  
stats
tech



Pages are defined in param.page table.
Page is defined by an id, a name, a page type, a datalink, a pagefile;

	



