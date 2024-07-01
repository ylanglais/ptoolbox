create table param.logfile (
	type 	 varchar(20)   not null,
	destlnk  varchar(100)  not null,
	regexp   varchar(1000) not null,
	flds     varchar(1000) not null
);
	
		

insert into db.changelog (action) values ('patch 20231120.logimp.sql');
