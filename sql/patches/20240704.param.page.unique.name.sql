ALTER TABLE ONLY param.page ADD CONSTRAINT param_page_name_unique unique (name);
insert into db.changelog (action) values ('patch 20240704.param.page.unique.name.sql');
