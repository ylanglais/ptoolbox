ALTER TABLE ONLY param."right"
    ADD CONSTRAINT right_type_link_role_id_user_id_key UNIQUE (type, link, role_id, user_id);

insert into db.changelog (action) values ('patch 20230426.param.right.unique.sql');
