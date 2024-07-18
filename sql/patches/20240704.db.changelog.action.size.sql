ALTER TABLE db.changelog alter column action type varchar(200);
insert into db.changelog (action) values ('patch 20240704.db.changelog.action.size.sql');
