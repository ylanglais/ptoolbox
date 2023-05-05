alter table param.fragment add column forder int;

alter table param.fragment alter column forder set not null

alter table param.fragment add constraint param_fragment_entity_type_forder_unique unique (entity, type, forder);

alter table param.fragment ADD CONSTRAINT param_folder_page_type_fkey FOREIGN KEY (type) references ref.fragment_type (type);
