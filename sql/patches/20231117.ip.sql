create table infra.zonesip (
	name  varchar(100) unique not null,
	inet varchar(100) unique not null,
	net_min_lg bigint not null,
	net_max_lg bigint not null,
	usage varchar(200) 
);

create table infra.dhcp (
	ip   varchar(100) unique not null,
	name varchar(100) not null,
	arp	 varchar(200)
);

create table infra.vip (
	ip   varchar(100) unique not null,
	name varchar(100) not null,
	usage varchar(200)
);
	
insert into db.changelog (action) values ('patch 20231117.ip.sql');
