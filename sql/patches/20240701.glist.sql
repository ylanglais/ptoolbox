create table param.glist2 ( user_id  integer, role_id  integer,
    provider character varying(50),
    columns  character varying(1000),
    filter   character varying(1000),
    sortby   character varying(50),
    orderby  character varying(10)
);

insert into param.glist2 (user_id,role_id,provider, columns, sortby, orderby) select user_id,role_id,provider, columns, sortby, orderby from param.glist;
ALTER TABLE param.glist rename to glist1;
ALTER TABLE param.glist2 rename to glist;
drop TABLE  param.glist1;
insert into db.changelog (action) values ('patch 20240701.glist.sql'); 

