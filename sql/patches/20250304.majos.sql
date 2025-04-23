create table infra.majos (
	host varchar(100) not null,
	nbpkg int default null,
	nbmaj int default null,
	stamp date default now(),
	unique  (host, stamp)
);

insert into db.changelog (action) values ('patch 20250304.api.sql');
