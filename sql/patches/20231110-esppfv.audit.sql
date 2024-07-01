create table espp.audit (
	stamp  timestamp not null default now(),
	src    varchar(20) not null,
	ip     varchar(50) not null,
	cmd	   varchar(50) not null,
	api    varchar(200) not null,
	ver	   varchar(20) not null,
	id	   varchar(200) not null,
	action varchar(200) not null,
	status varchar(200) not null
);
insert into db.changelog (action) values ('patch 20231110-esppfv.audit.sql');
