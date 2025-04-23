create table tech.api_ip (
	ip varchar(20) not null,
	hostname varchar(100) default null
);
insert into db.changelog (action) values ('patch 20240228.api.sql')
