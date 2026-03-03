create table inc2 (                                                                  
month date not null,
appmet integer default 0, tout integer default 0)
;
insert into db.changelog (action) values ('patch 20250701.incidents.sql');

