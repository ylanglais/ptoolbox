create table infra.env (
	name varchar(50) unique not null,
	description varchar (200) default null
);

insert into infra.env values
 ('test', null),
 ('dev', null),
 ('rec', 'recette'),
 ('pprd', 'Préproduction'),
 ('prod', 'Production'),
 ('other', 'Autre');
	
create table infra.host (
	name 	    varchar(100) unique not null,
	ip 		    varchar(100)  not null,
	env 	    varchar(20)  default null references infra.env(name),
	ison		boolean      default false,
	isvm		boolean      default false,
	osstring	varchar(20)  default null,
	osfamily    varchar(20)  default null,
	os  	    varchar(20)  default null,
	version     varchar(20)  default null,
	description varchar(200) default null,
	distrib     varchar(20)  default null,
	cstamp	    timestamp    default now(),
	mstamp	    timestamp    default now()
);
create table infra.hgrp (
	name        varchar(20)  unique not null,
	description varchar(200) default null

);
create table infra.hgrp_host (
	hgrp_name   varchar(20) not null references infra.hgrp(name),
	host_name   varchar(20) not null references infra.host(name)
);

ALTER TABLE ONLY infra.hgrp_host ADD CONSTRAINT infra_hgrp_host_unique unique (hgrp_name, host_name);

insert into db.changelog (action) values ('patch 202410.hosts.sql');

