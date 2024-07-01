create table tech.iprep (
	ip			varchar(20) unique not null,
	isocode		varchar(10)  default null, 
	country		varchar(100) default null,
	state		varchar(100) default null,
	city		varchar(100) default null,
	discover	timestamp 	 default null,
	threat		varchar(100) default null,
	risk		int			 default 0,
	created 	timestamp not null default now(),
	modified 	timestamp not null default now(),
	change		varchar(100) default null,
	comment 	varchar(100) default null
);

insert into db.changelog (action) values ('patch 20230628.iprep.sql');
