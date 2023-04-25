-- UUID:
CREATE EXTENSION IF NOT EXISTS "uuid-ossp" WITH SCHEMA public;
COMMENT ON EXTENSION "uuid-ossp" IS 'generate universally unique identifiers (UUIDs)';

-- Audit schema:
CREATE SCHEMA audit;

ALTER TABLE tech.connection set schema audit;
-- Change conf/audit.php file : 
-- $audit_schema      = "audit.";

CREATE TABLE audit.action (
    id integer NOT NULL,
    entity character varying(60) NOT NULL,
    entity_id integer NOT NULL,
    version integer NOT NULL,
    stamp character varying(60) NOT NULL,
    login character varying(50) DEFAULT NULL::character varying,
    action character varying(60) DEFAULT NULL::character varying,
    comment character varying(500) DEFAULT NULL::character varying
);

ALTER TABLE ONLY audit.action ADD CONSTRAINT action_pkey PRIMARY KEY (id);


-- db schema:
CREATE SCHEMA db; -- DB changelog
CREATE TABLE db.changelog (
    stamp character varying(40) DEFAULT NULL::character varying NOT NULL,
    action character varying(30) DEFAULT NULL::character varying NOT NULL,
    entity character varying(30) DEFAULT NULL::character varying NOT NULL,
    request character varying(2000) DEFAULT NULL::character varying
);

ALTER TABLE ONLY db.changelog
    ADD CONSTRAINT changelog_pkey PRIMARY KEY (stamp, action, entity);

-- ref schema:
CREATE SCHEMA ref;

CREATE TABLE ref.perm (
    name character varying(20) NOT NULL,
    description character varying(100)
);
ALTER TABLE ONLY ref.perm ADD CONSTRAINT perm_name_key UNIQUE (name);

insert into ref.perm values 
('SYSTEM', 'System defined'), 
('HIDDEN', 'Not to be displayed'),
('PRIVATE', 'For admin only'),
('RONLY',   'Read Only / consult'),
('ALL',     'Read and write permission');


CREATE TABLE ref.page_type (
    name character varying(30) NOT NULL,
    description character varying(100)
);
ALTER TABLE ONLY ref.page_type ADD CONSTRAINT page_type_key PRIMARY KEY (name);

insert into ref.page_type values 
('Table', 'Generic page from table'),
('View',  'Generic page from application view'),
('System', 'Page is a system specific page'),
('Form',   'Page is a generic form'),
('Report', 'Page is a generic report'),
('External', 'Page is an external link'),
('Client', 'Page is a client defined specific page');

-- Param:
CREATE TABLE param.entity (
    name character varying(50) NOT NULL,
    stated character(1) DEFAULT 'n'::bpchar,
    owned character(1) DEFAULT 'n'::bpchar,
    versioned character(1) DEFAULT 'n'::bpchar,
    historized character(1) DEFAULT 'n'::bpchar,
    audited character(1) DEFAULT 'n'::bpchar,
    stamped character(1) DEFAULT 'n'::bpchar,
    dsrc character varying(50),
    tname character varying(50) NOT NULL
);

ALTER TABLE ONLY param.entity
    ADD CONSTRAINT entity_pkey PRIMARY KEY (name);


CREATE TABLE param.field (
    entity character varying(50) NOT NULL,
    name character varying(50) DEFAULT NULL::character varying NOT NULL,
    tab character varying(100) NOT NULL,
    col character varying(100) NOT NULL,
    rsrc character varying(50) DEFAULT NULL::character varying,
    rtab character varying(100) DEFAULT NULL::character varying,
    rref character varying(100) DEFAULT NULL::character varying,
    rval character varying(100) DEFAULT NULL::character varying,
    multiple character(1) DEFAULT 'n'::bpchar,
    num integer NOT NULL
);


ALTER TABLE ONLY param.field ADD CONSTRAINT field_pkey PRIMARY KEY (entity, name);

CREATE TABLE param.fragment (
    entity character varying(50) NOT NULL,
    type character varying(50),
    name character varying(50) NOT NULL,
    fsrc character varying(50) DEFAULT NULL::character varying,
    cname character varying(50) NOT NULL,
    jtname character varying(50) DEFAULT NULL::character varying,
    ftname character varying(50) DEFAULT NULL::character varying,
    finame character varying(50) DEFAULT NULL::character varying,
    flname character varying(50) DEFAULT NULL::character varying
);
ALTER TABLE ONLY param.fragment
    ADD CONSTRAINT fragment_pkey PRIMARY KEY (entity, name);

CREATE TABLE param.glist (
    user_id integer,
    role_id integer,
    provider character varying(50),
    columns character varying(200),
    sortby character varying(50),
    orderby character varying(10)
);

CREATE TABLE param.item (
    list_id integer NOT NULL,
    item character varying(50) NOT NULL
);
ALTER TABLE ONLY param.item ADD CONSTRAINT item_pkey PRIMARY KEY (list_id, item);

CREATE TABLE param.list (
    id integer NOT NULL,
    entity character varying(50) DEFAULT NULL::character varying,
    dsrc character varying(50) DEFAULT NULL::character varying,
    tname character varying(50) DEFAULT NULL::character varying,
    name character varying(40) NOT NULL,
    role character varying(50) DEFAULT NULL::character varying,
    "user" character varying(50) DEFAULT NULL::character varying
);

CREATE SEQUENCE param.list_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

ALTER TABLE ONLY param.list ALTER COLUMN id SET DEFAULT nextval('param.list_id_seq'::regclass);
ALTER TABLE ONLY param.list ADD CONSTRAINT list_pkey PRIMARY KEY (id);
ALTER TABLE ONLY param.list ADD CONSTRAINT list_name_role_user_key UNIQUE (name, role, "user");

CREATE TABLE param.folder (
    id integer NOT NULL,
    name character varying(30),
    description character varying(100)
);
ALTER TABLE ONLY param.folder ADD CONSTRAINT folder_id_key UNIQUE (id);

CREATE TABLE param.page (
    id integer NOT NULL,
    name character varying(30) NOT NULL,
    ptype character varying(30) NOT NULL,
    datalink character varying(50) DEFAULT NULL::character varying,
    pagefile character varying(500) DEFAULT NULL::character varying
);
ALTER TABLE ONLY param.page ADD CONSTRAINT page_id_key UNIQUE (id);


CREATE TABLE param.page_perm (
    page_id integer NOT NULL,
    role_id integer DEFAULT 0 NOT NULL,
    perm_name character varying(20) NOT NULL
);
ALTER TABLE ONLY param.page_perm ADD CONSTRAINT page_perm_id_key         UNIQUE (page_id, role_id);
ALTER TABLE ONLY param.page_perm ADD CONSTRAINT page_perm_page_id_fkey   FOREIGN KEY (page_id)   REFERENCES param.page(id);
ALTER TABLE ONLY param.page_perm ADD CONSTRAINT page_perm_perm_name_fkey FOREIGN KEY (perm_name) REFERENCES ref.perm(name);
ALTER TABLE ONLY param.page_perm ADD CONSTRAINT page_perm_role_id_fkey   FOREIGN KEY (role_id)   REFERENCES tech.role(id);

CREATE TABLE param.folder_page (
    folder_id integer NOT NULL,
    page_id integer NOT NULL,
    page_order integer NOT NULL
);
ALTER TABLE ONLY param.folder_page ADD CONSTRAINT folder_page_id_key         UNIQUE (folder_id, page_id);
ALTER TABLE ONLY param.folder_page ADD CONSTRAINT folder_page_folder_id_fkey FOREIGN KEY (folder_id) REFERENCES param.folder(id);
ALTER TABLE ONLY param.folder_page ADD CONSTRAINT folder_page_page_id_fkey   FOREIGN KEY (page_id)   REFERENCES param.page(id);


CREATE TABLE param.folder_perm (
    folder_id  integer NOT NULL,
    role_id    integer DEFAULT 0 NOT NULL,
    perm_name character varying(20) NOT NULL
);

ALTER TABLE ONLY param.folder_perm ADD CONSTRAINT folder_perm_folder_id_fkey FOREIGN KEY (folder_id) REFERENCES param.folder(id);
ALTER TABLE ONLY param.folder_perm ADD CONSTRAINT folder_perm_perm_name_fkey FOREIGN KEY (perm_name) REFERENCES ref.perm(name);
ALTER TABLE ONLY param.folder_perm ADD CONSTRAINT folder_perm_role_id_fkey 	 FOREIGN KEY (role_id) 	 REFERENCES tech.role(id);

-------------

-- 
-- Workflows:
-- 
CREATE TABLE param.wfaction (
    id integer NOT NULL,
    workflow character varying(60) NOT NULL,
    role character varying(60) DEFAULT NULL::character varying,
    state0 character varying(60) NOT NULL,
    state1 character varying(60) NOT NULL,
    action character varying(60) NOT NULL,
    initial character(1) NOT NULL,
    othuid character(1) NOT NULL,
    mustcomment character(1) NOT NULL,
    script character varying(60) DEFAULT NULL::character varying
);
ALTER TABLE ONLY param.wfaction ADD CONSTRAINT wfaction_pkey PRIMARY KEY (id);

CREATE TABLE param.wfcheck (
    version integer NOT NULL,
    login character varying(30) NOT NULL,
    id integer NOT NULL,
    entity_state character varying(60) NOT NULL,
    f_admin character varying(30) DEFAULT NULL::character varying,
    f_oper character varying(30) DEFAULT NULL::character varying,
    f_consult character varying(30) DEFAULT NULL::character varying
);
ALTER TABLE ONLY param.wfcheck ADD CONSTRAINT wfcheck_pkey PRIMARY KEY (id);

CREATE TABLE param.workflow (
    id integer NOT NULL,
    name character varying(30) DEFAULT NULL::character varying,
    dsc character varying(30) DEFAULT NULL::character varying,
    entity character varying(60) DEFAULT NULL::character varying
);
ALTER TABLE ONLY param.workflow ADD CONSTRAINT workflow_pkey PRIMARY KEY (id);

