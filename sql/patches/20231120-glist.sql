create table param.glist_pref (
	glist 	varchar(50) unique not null,
	login 	varchar(50)  default null references tech.user (login),
	role 	varchar(100) default null referencese tech.role (name),
	col		varchar(100) not null,
	corder  int not null,
	-- sort:  empty/null or asc or desc
	sort	varchar(10) default null,
	unique (glist, login, corder),
	unique (glist, role,  corder)
);

create table param.glist_filter (
	id 		integer unique not null,
	glist 	varchar(50) unique not null,
	login 	varchar(50) default null references tech.user (login),
	role 	varchar(100) default null referencese tech.role (name),
	name  	varchar(30)	default null,
	unique (glist, login, name),
	unique (glist, role,  name)
);

create table param.filter_kv (
	id		integer not null reference param.glis_filter (id),
	key 	varchar(100) not null,
	val		varchar(100) not null,
	unique (id, key, val) 
);
insert into db.changelog (action) values ('patch 20231120-glist.sql');
