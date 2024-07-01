CREATE TABLE audit.log (
    tstamp character(24) DEFAULT to_char(now(), 'YYYY-MM-DD HH24:MI:SS.MS'::text) NOT NULL,
    ip varchar(32)     DEFAULT NULL,
    login varchar(50)  NOT NULL,
	level varchar(20)  NOT NULL default 'LOG',
    msg   varchar(500) NOT NULL,
	primary key (tstamp, ip)
);


insert into db.changelog (action) values ('patch 20230425.audit.log.sql');
