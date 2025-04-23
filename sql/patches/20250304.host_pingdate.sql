alter table infra.host add column lastping timestamp default null;
insert into db.changelog (action) values ('patch 20240304.host_pingdate.sql')

