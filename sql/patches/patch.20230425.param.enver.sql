
CREATE TABLE param.enver (
    env character varying(50) NOT NULL,
    ver character varying(10) NOT NULL,
    rev integer DEFAULT 0
);

COPY param.enver (env, ver, rev) FROM stdin;
DEV	1.0	2
\.


