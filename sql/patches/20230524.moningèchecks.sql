create table ref.domain (
	name varchar(50) primary key, 
	description varchar(200)
);

COPY ref.domain (name, description) FROM stdin;
Infra	Infrastructure
Middleware		Middelware (BdD, ETL,...)
Application		Applications métier
\.

create table ref.application (
	name varchar(50) primary key,
	description varchar(200)
);

COPY ref.domain (name, description) from stdin;
Sicare	Application de gestion Sicare
Kélia	Application de gestion Kélia
DNA		GED
Graal	CRM
FV		Front Vie
ESPP	Espace personnel
QDD	Qualité de données
BI	BI
\.

create table ref.type_verif (
	name varchar(50) primaty key,
	description varachar(200)
);

COPY ref.type_verif (name, description) from stdin;
Automatique	Vérification automatique
Manuelle	Vérification manuelle
\.

create table ref.check_status (
	name varchar(50) primaty key,
	description varachar(200)
);

COPY ref.check_status (name, description) from stdin;
Unchecked	Pas encode vérifié
N/A	Pas applicable
Ok	Pas de souci
Warning	Attention
Error	Erreur
Problem	Problème
Critical	Problème critique
\.

create table ref.frequency (
	name varchar(50) primaty key,
	description varachar(200)
);

COPY ref.check_status (name, description) from stdin;
None	Pas de fréquence
Daily	Quotidien
Busday	Jour ouvré
Weekly	Hebdo
Monthly	Mensuel
Quarterly	Trimestriel
Half-yearly			Semestriel
Yearly		Annuel
\.



create table morning_check (
	id 			serial primary key,
	domain 		varchar(50) references ref.domain (name) default null,
	categorie	varchar(50) default null,
	item		varchar(100) not null,
	plan		varchar(200),
	type_verif	varchar(50) default 'Manuelle' not null references ref.type_verif (name),
	verification	varchar(500) 
);

create table action_check (
	check_id	integer not null references morning_check (id),
	status		varchar(50) not null references ref.check_status (name),
	type_action	varchar(50) not bykk²	
);

create table item_check (
	check_id		integer not null references morning_check (id),
	plan_id			integer not null references plan (id),
	check_status	varchar(50) not null default 'Unchecked' references ref.check_status (name),
	createstamp		timestamp 	to_char(now(), 'YYYY-MM-DD HH24:MI:SS.MS'::text),
	checkstamp		timestamp	default null,
	taken			boolean     default false,
	takenstamp		timestamp   default null,	
	closed			boolean     default false,
	closestamp		timestamp 	default null,
};
	
insert into db.changelog (action) values ('patch 20230524.moningèchecks.sql');
