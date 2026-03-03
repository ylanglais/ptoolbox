create table espp.redip (
	ip			varchar(100) unique not null,
	comment		varchar(500) default null,
	crea		timestamp default now()
);
create table espp.rednet (
	inetnum varchar(100) unique not null references tech.rdap(inetnum),
	comment		varchar(500) default null,
	crea		timestamp default now()
);	
	
