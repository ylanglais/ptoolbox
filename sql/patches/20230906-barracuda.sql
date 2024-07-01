create table infra.barracuda (
	dstamp           date    unique not null default now(),
	in_blocked       integer,
	in_virus         integer,
	in_ctrl	         integer,
	in_quarantined   integer,
	in_allowedwtag   integer,
	in_allowed       integer,
	ou_policy		 integer,
	ou_spam			 integer,
	ou_virus		 integer,
	ou_ctrl	         integer,
	ou_quarantined   integer,
    ou_encrypted     integer,
	ou_redirected    integer,
	ou_sent          integer
);
insert into db.changelog (action) values ('patch 20230906-barracuda.sql');
