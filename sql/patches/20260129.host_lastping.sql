alter table infra.host alter column lastping type date;
insert into db.changelog (action) values ('20260129.host_lastping.sql');
