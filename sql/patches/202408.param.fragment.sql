CREATE TABLE param.fragment2 (
    entity character varying(50) NOT NULL REFERENCES param.entity(name),
    type   character varying(50),
	datatype character varying(50) references ref.datatype(name),
    name   character varying(50) NOT NULL,
	corder int                   not NULL,
    sc     character varying(50) NOT NULL, -- source column (replaces cname) 
	
	-- Join table:
    jt     character varying(50) DEFAULT NULL::character varying, -- join table 
	jst    character varying(50) DEFAULT NULL::character varying, -- join source table name (if exists)
    jsc    character varying(50) DEFAULT NULL::character varying, -- join source column
	jft    character varying(50) DEFAULT NULL::character varying, -- join foreign table name (if exists)
    jfc    character varying(50) DEFAULT NULL::character varying, -- join foreign column
	
	-- Foreign table:
    ft     character varying(50) DEFAULT NULL::character varying, -- foreign table (replaces ftname)
    fjc    character varying(50) DEFAULT NULL::character varying, -- foreign join column (replaces finame)
    fdc    character varying(50) DEFAULT NULL::character varying, -- foreign data column (replaces flname) 
	ftag   character varying(200)  DEFAULT NULL::character varying  -- foreign data tag (if exists); 
);

insert into param.fragment2 (entity, type, name, corder, sc, ft, fjc, fdc) 
	select entity, type, name, forder, cname, ftname, finame, flname  from param.fragment;


alter table param.fragment rename to fragment_ori;

alter table param.fragment_ori rename constraint fragment_pkey to fragment_ori_pkey;
alter table param.fragment2 rename to fragment;

ALTER TABLE ONLY param.fragment
    ADD CONSTRAINT fragment_pkey PRIMARY KEY (entity, name);

insert into db.changelog (action) values ('patch 202408.param.fragment.sql');

