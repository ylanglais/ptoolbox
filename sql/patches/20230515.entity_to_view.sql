update ref.etype set id = 3 where id= 2;
insert into ref.etype values (2, 'view');
update param.right set type = 'view' where type = 'entity';
delete from ref.etype where id = 3;

insert into db.changelog (action) values ('patch 20230515.entity_to_view.sql');
