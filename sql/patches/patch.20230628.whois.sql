create table tech.rdap (
	inetnum 	varchar(100) unique not null, 
	name		varchar(100) not null, 
	country		varchar(50)  default null, 
	net_min_lg	bigint not null, 
	net_max_lg	bigint not null,
	created 	timestamp not null default now(),
	modified 	timestamp not null default now(),
	ipver		varchar(10)	 default null,
	type		varchar(100) default null,
	ident 	 	varchar(100) default null,
	subident 	varchar(100) default null,
	comment 	varchar(100) default null
);

alter table tech.rdap add constraint rdap_net_min_max_lg_keys UNIQUE (net_min_lg, net_max_lg);

CREATE FUNCTION ip2int(text) RETURNS bigint AS $$ 
SELECT split_part($1,'.',1)::bigint*16777216 + split_part($1,'.',2)::bigint*65536 + split_part($1,'.',3)::bigint*256 + split_part($1,'.',4)::bigint;
$$ LANGUAGE SQL  IMMUTABLE RETURNS NULL ON NULL INPUT;

create function ip2net(text) returns table like tech.rdap  as $$
select * from tech.rdap where ip2int($1) > net_min_lg and ip2int($1) < net_max_lg; 
$$LANGUAGE SQL  IMMUTABLE RETURNS NULL ON NULL INPUT;
