alter table tech.user alter column  active drop default ;
alter table tech.user alter column  active type boolean using case active when 'Y' then True else false end;
alter table tech.user alter column active set default false;

insert into db.changelog (action) values ('patch 20230512.change_user_active_to_bool.sql');
