alter table ref.status     add  constraint ref_status_id_uniqe unique( id )
alter table ref.status     add  constraint ref_status_type_entity_status_uniqe unique(type, entity, status );
alter table ref.status     add  column  val varchar(100);
alter table ref.status     drop constraint status_entity_fkey;
alter table param.workflow drop column entity;
alter table param.workflow add  column type varchar(50);
alter table param.workflow add  column entity varchar(50);
alter table param.wfaction alter column initial     type boolean USING initial::boolean;
alter table param.wfaction alter column othuid      type boolean USING initial::boolean;
alter table param.wfaction alter column mustcomment type boolean USING initial::boolean;
alter table param.wfaction alter column initial     drop not null;
alter table param.wfaction alter column othuid      drop not null;
alter table param.wfaction alter column mustcomment drop not null;

create table param.checklist (
	name 	varchar(50) unique not null,
	owner	int default null references tech.user,
	creat	timestamp default now(),
	modif   timestamp default now()
);

create table param.cklst_item (
	checklist	varchar(50)  references param.checklist(name)  default null,
	seq         int not null default 0,
	item        varchar(50)  not null,
	description varchar(200) not null,
	dstatus		varchar(50) default null, 
	unique (checklist, item),
	unique (checklist, seq)
);

create table param.cklst_rsc (
	checklist	varchar(50)  references param.checklist(name)  default null,
	item		varchar(50)  references param.cklst_item(idem) default null,	
	status		varchar(20)  references ref.status(status)     default null,
	action      varchar(50)  references param.wfaction(action) default null,
	filename    varchar(500),
	filetype	varchar(50)
);

create schema cklst;

create table cklst.instance (
	instid 		uuid unique not null default uuid_generate_v1(),
	creat		timestamp not null default now(),
	modif   	timestamp default now(),
	checklist 	varchar(50) references param.checklist(name) default null   	
);

create table cklst.item_instance (
	instid 		uuid unique not null references cklst.instance(instid),
	institem	varchar(50), 
	creat		timestamp not null default now(),
	modif   	timestamp default now(),
	status  	varchar(50) not null,
	description varchar(200)
);

create cklst.history (
	isntcklst	uuid not null references cklst.instance(id),
	institem	varchar(50) not null references cklst_item.instance(id),
	status1 	varchar(50) not null references ref.status(status),
	status2		varchar(50) not null references ref.status(status),
	who			integer references tech.user(id),
	modif		timestamp default not null 
	description varchar(200);
);


-- Testing checklist:
insert into param.checklist (name) values ("lst1");
insert into ref.status (id, type, entity, status, description, val) values 
	(1, 'checklist', 'lst1', 'unchk',   'not checked', 'Unchecked'), 
	(2, 'checklist', 'lst1', 'checked', 'checked',     'Checked');
 
insert into param.workflow values (2, 'cklsttest', 'lst1 checklist test workflow', 'checklist', 'lst1');

insert into param.cklst_item (checklist, seq, item, description, dstatus) values 
	('lst1', 1, 'item1', 'item de test 1', 'unchk'), 
	('lst1', 2, 'item2', 'item de test 2', 'unchk'), 
	('lst1', 3, 'item3', 'item de test 3', 'unchk');

insert into param.wfaction values 
(1, 'cklsttest', null, 'unchk',   'checked', 'Check',   true,  false, false, null),
(2, 'cklsttest', null, 'checked', 'unchk',   'Uncheck', false, false, false, null);
