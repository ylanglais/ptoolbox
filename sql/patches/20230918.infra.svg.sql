create schema infra;
create table infra.svg (
	dstamp date not null,
	host 	varchar(100) not null,
	logical_used float,
	total float,
 	used  float,
	spare float
);
ALTER TABLE ONLY infra.svg ADD CONSTRAINT dstamp_host_key unique (dstamp, host);


create table infra.svg_daily (
	display_name varchar(100) not null,
	type varchar(100),
	status_code_summary varchar(100),
	status_code varchar(100),
	started timestamp not null,
	completed timestamp,
	bytes_scanned float,
	bytes_modified_sent float,
	client_os varchar(100),
	plugin_name varchar(100)
);	
\copy infra.svg_daily (display_name,type,status_code_summary,status_code,started,completed,bytes_scanned,bytes_modified_sent,client_os,plugin_name) from 'svg_history' csv delimiter ';' quote '"' encoding 'UTF-8' HEADER 

	
 
insert into db.changelog (action) values ('patch 20230918.infra.svg.sql');
