create table tech.purge (
	stable_name	varchar(100) not null,
	column_name varchar(100) not null,
	rentention  varchar(100) not null default '1 year',
	arch_stable varchar(100) default null,
	unique (stable_name, column_name)
);

insert into db.changelog (action) values ('patch 202412.purges.sql');

-- test:
-- create table tpurge1 ( stamp date unique not null, val int); insert into tpurge1 values( '2010-12-01', 1), ('2022-12-01', 2), ('2023-12-01', 3), ('2024-12-16', 4); create table tech.tpurge2 ( stamp timestamp unique not null, val int); insert into tech.tpurge2 values( '2010-12-01', 1), ('2022-12-01', 2), ('2023-12-01', 3), ('2023-12-18', 4), ('2024-12-16', 5);
