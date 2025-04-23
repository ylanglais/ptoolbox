alter table infra.host add column type varchar(20) default null;
alter table infra.host add column obsolete boolean default false;
insert into db.changelog (action) values ('patch 20240307.host_type_obso.sql');

