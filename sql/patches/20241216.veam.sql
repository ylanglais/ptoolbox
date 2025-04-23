
create table infra.vjob (
	jid			 uuid not null,
	jtype		 varchar(50),
	name		 varchar(100) not null,
	backupserverid int ,
	description    varchar(200),
	status          varchar(50),
	lastrun		   timestamp not null,
	avgdurationsec	int,
	lastrundurationsec		int,
 	lasttransferreddatabytes bigint,
	unique (jid, lastrun)
);

insert into db.changelog (action) values ('patch 20241216.veeam.sql');
