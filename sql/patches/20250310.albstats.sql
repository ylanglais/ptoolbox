create table stats.alconf (
	name	varchar(50) unique not null,
	fntmpl  varchar(100),
);
create table stats.alfmts( 
	name    varchar(50) references stats.alconf(name),
	rexexp  varchar(1000) not null,
	vars    varchar(500)  not null
)
create table stats.albstats (
	name 	varchar(50) references stats.alconf(name),
	stamp 	datetime,
	hits 	integer,
	uip  	integer,
	unique (name, stamp)
);

create table stats.alfiles (	
	cksum character varying(50) unique not null,
	name varchar(50) references stats.alconf(name),
	in_lines int,
	log_lines int,
	bad_lines int,
	start_stamp timestamp,
	end_stamp timestamp,
	start_import timestamp default now(),
	end_import   timestamp 
);
insert into db.changelog (action) values ('patch 20240310.albstats.sql');
