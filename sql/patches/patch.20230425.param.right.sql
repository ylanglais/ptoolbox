CREATE TABLE param."right" (
    type character varying(20),
    link character varying(50),
    role_id integer,
    user_id integer,
    perm character varying(20)
);

ALTER TABLE ONLY param."right" ADD CONSTRAINT right_perm_fkey FOREIGN KEY (perm) REFERENCES ref.perm(name);


COPY param."right" (type, link, role_id, user_id, perm) FROM stdin;
table	default.tech.dbs	1	\N	ALL
table	default.tech.role	1	\N	ALL
table	default.tech.user	1	\N	ALL
table	default.param.folder	1	\N	ALL
table	default.param.page	1	\N	ALL
table	default.param.page_perm	1	\N	ALL
table	default.param.folder_perm	1	\N	ALL
table	default.audit.connection	20	\N	RONLY
table	default.audit.log	20	\N	RONLY
table	default.param.folder_page	1	\N	ALL
\.

-- entity	Folders	100	\N	ALL

