create schema laf;
create table laf.multi_tel_check (
	tel varchar(20) not null,
	adhid varchar(30) not null,
	stamp timestamp default now(),
	unique (tel, adhid)
);
create table laf.multi_mail_check (
	mail varchar(200) not null,
	adhid varchar(30) not null,
	stamp timestamp default now(),
	unique (mail, adhid)
);
insert into db.changelog (action) values ('patch 20250703.security-check.sql');
