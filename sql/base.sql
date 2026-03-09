--
-- PostgreSQL database dump
--

\restrict 4yD9DE5fHnvgsMM6ziSqbsgLFFYVUDRmBNAxde2fVXOHX28Dcj9tCiXTnH1dvxE

-- Dumped from database version 18.1
-- Dumped by pg_dump version 18.1

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- Name: audit; Type: SCHEMA; Schema: -; Owner: ylanglais
--

CREATE SCHEMA audit;


ALTER SCHEMA audit OWNER TO ylanglais;

--
-- Name: db; Type: SCHEMA; Schema: -; Owner: ylanglais
--

CREATE SCHEMA db;


ALTER SCHEMA db OWNER TO ylanglais;

--
-- Name: param; Type: SCHEMA; Schema: -; Owner: ylanglais
--

CREATE SCHEMA param;


ALTER SCHEMA param OWNER TO ylanglais;

--
-- Name: public; Type: SCHEMA; Schema: -; Owner: postgres
--

-- *not* creating schema, since initdb creates it


ALTER SCHEMA public OWNER TO postgres;

--
-- Name: ref; Type: SCHEMA; Schema: -; Owner: ylanglais
--

CREATE SCHEMA ref;


ALTER SCHEMA ref OWNER TO ylanglais;

--
-- Name: stats; Type: SCHEMA; Schema: -; Owner: ylanglais
--

CREATE SCHEMA stats;


ALTER SCHEMA stats OWNER TO ylanglais;

--
-- Name: tech; Type: SCHEMA; Schema: -; Owner: ylanglais
--

CREATE SCHEMA tech;


ALTER SCHEMA tech OWNER TO ylanglais;

--
-- Name: uuid-ossp; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS "uuid-ossp" WITH SCHEMA public;


--
-- Name: EXTENSION "uuid-ossp"; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION "uuid-ossp" IS 'generate universally unique identifiers (UUIDs)';


SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: action; Type: TABLE; Schema: audit; Owner: ylanglais
--

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


ALTER TABLE audit.action OWNER TO ylanglais;

--
-- Name: connection; Type: TABLE; Schema: audit; Owner: ylanglais
--

CREATE TABLE audit.connection (
    id character varying(100) NOT NULL,
    login character varying(50) NOT NULL,
    since timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    until timestamp without time zone,
    ip character varying(32) DEFAULT NULL::character varying NOT NULL,
    state character varying(30) DEFAULT NULL::character varying NOT NULL
);


ALTER TABLE audit.connection OWNER TO ylanglais;

--
-- Name: log; Type: TABLE; Schema: audit; Owner: ylanglais
--

CREATE TABLE audit.log (
    tstamp character(24) DEFAULT to_char(now(), 'YYYY-MM-DD HH24:MI:SS.MS'::text) NOT NULL,
    ip character varying(32) DEFAULT NULL::character varying NOT NULL,
    login character varying(50) NOT NULL,
    level character varying(20) DEFAULT 'LOG'::character varying NOT NULL,
    msg character varying(500) NOT NULL
);


ALTER TABLE audit.log OWNER TO ylanglais;

--
-- Name: changelog; Type: TABLE; Schema: db; Owner: ylanglais
--

CREATE TABLE db.changelog (
    stamp character varying(40) DEFAULT to_char(now(), 'YYYY-MM-DD HH24:MI:SS.MS'::text) NOT NULL,
    action character varying(200) DEFAULT NULL::character varying NOT NULL,
    entity character varying(30) DEFAULT NULL::character varying,
    request character varying(2000) DEFAULT NULL::character varying
);


ALTER TABLE db.changelog OWNER TO ylanglais;

--
-- Name: entity; Type: TABLE; Schema: param; Owner: ylanglais
--

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


ALTER TABLE param.entity OWNER TO ylanglais;

--
-- Name: enver; Type: TABLE; Schema: param; Owner: ylanglais
--

CREATE TABLE param.enver (
    env character varying(50) NOT NULL,
    ver character varying(10) NOT NULL,
    rev integer DEFAULT 0
);


ALTER TABLE param.enver OWNER TO ylanglais;

--
-- Name: field; Type: TABLE; Schema: param; Owner: ylanglais
--

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


ALTER TABLE param.field OWNER TO ylanglais;

--
-- Name: folder; Type: TABLE; Schema: param; Owner: ylanglais
--

CREATE TABLE param.folder (
    id integer NOT NULL,
    name character varying(30),
    description character varying(100)
);


ALTER TABLE param.folder OWNER TO ylanglais;

--
-- Name: folder_page; Type: TABLE; Schema: param; Owner: ylanglais
--

CREATE TABLE param.folder_page (
    folder_id integer NOT NULL,
    page_id integer NOT NULL,
    page_order integer NOT NULL
);


ALTER TABLE param.folder_page OWNER TO ylanglais;

--
-- Name: folder_perm; Type: TABLE; Schema: param; Owner: ylanglais
--

CREATE TABLE param.folder_perm (
    folder_id integer NOT NULL,
    role_id integer DEFAULT 0 NOT NULL,
    perm_name character varying(20) NOT NULL
);


ALTER TABLE param.folder_perm OWNER TO ylanglais;

--
-- Name: fragment; Type: TABLE; Schema: param; Owner: ylanglais
--

CREATE TABLE param.fragment (
    entity character varying(50) NOT NULL,
    type character varying(50),
    name character varying(50) NOT NULL,
    fsrc character varying(50) DEFAULT NULL::character varying,
    cname character varying(50) NOT NULL,
    jtname character varying(50) DEFAULT NULL::character varying,
    ftname character varying(50) DEFAULT NULL::character varying,
    finame character varying(50) DEFAULT NULL::character varying,
    flname character varying(50) DEFAULT NULL::character varying,
    forder integer
);


ALTER TABLE param.fragment OWNER TO ylanglais;

--
-- Name: glist; Type: TABLE; Schema: param; Owner: ylanglais
--

CREATE TABLE param.glist (
    user_id integer,
    role_id integer,
    provider character varying(50),
    columns character varying(200),
    sortby character varying(50),
    orderby character varying(10)
);


ALTER TABLE param.glist OWNER TO ylanglais;

--
-- Name: item; Type: TABLE; Schema: param; Owner: ylanglais
--

CREATE TABLE param.item (
    list_id integer NOT NULL,
    item character varying(50) NOT NULL
);


ALTER TABLE param.item OWNER TO ylanglais;

--
-- Name: list; Type: TABLE; Schema: param; Owner: ylanglais
--

CREATE TABLE param.list (
    id integer NOT NULL,
    entity character varying(50) DEFAULT NULL::character varying,
    dsrc character varying(50) DEFAULT NULL::character varying,
    tname character varying(50) DEFAULT NULL::character varying,
    name character varying(40) NOT NULL,
    role character varying(50) DEFAULT NULL::character varying,
    "user" character varying(50) DEFAULT NULL::character varying
);


ALTER TABLE param.list OWNER TO ylanglais;

--
-- Name: list_id_seq; Type: SEQUENCE; Schema: param; Owner: ylanglais
--

CREATE SEQUENCE param.list_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE param.list_id_seq OWNER TO ylanglais;

--
-- Name: list_id_seq; Type: SEQUENCE OWNED BY; Schema: param; Owner: ylanglais
--

ALTER SEQUENCE param.list_id_seq OWNED BY param.list.id;


--
-- Name: page; Type: TABLE; Schema: param; Owner: ylanglais
--

CREATE TABLE param.page (
    id integer NOT NULL,
    name character varying(30) NOT NULL,
    ptype character varying(30) NOT NULL,
    datalink character varying(30) DEFAULT NULL::character varying,
    pagefile character varying(30) DEFAULT NULL::character varying
);


ALTER TABLE param.page OWNER TO ylanglais;

--
-- Name: page_perm; Type: TABLE; Schema: param; Owner: ylanglais
--

CREATE TABLE param.page_perm (
    page_id integer NOT NULL,
    role_id integer DEFAULT 0 NOT NULL,
    perm_name character varying(20) NOT NULL
);


ALTER TABLE param.page_perm OWNER TO ylanglais;

--
-- Name: right; Type: TABLE; Schema: param; Owner: ylanglais
--

CREATE TABLE param."right" (
    type character varying(20),
    link character varying(50),
    role_id integer,
    user_id integer,
    perm character varying(20)
);


ALTER TABLE param."right" OWNER TO ylanglais;

--
-- Name: style; Type: TABLE; Schema: param; Owner: ylanglais
--

CREATE TABLE param.style (
    key character varying(50) NOT NULL,
    type character varying(50),
    value character varying(200)
);


ALTER TABLE param.style OWNER TO ylanglais;

--
-- Name: wfaction; Type: TABLE; Schema: param; Owner: ylanglais
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


ALTER TABLE param.wfaction OWNER TO ylanglais;

--
-- Name: wfcheck; Type: TABLE; Schema: param; Owner: ylanglais
--

CREATE TABLE param.wfcheck (
    version integer NOT NULL,
    login character varying(30) NOT NULL,
    id integer NOT NULL,
    entity_state character varying(60) NOT NULL,
    f_admin character varying(30) DEFAULT NULL::character varying,
    f_oper character varying(30) DEFAULT NULL::character varying,
    f_consult character varying(30) DEFAULT NULL::character varying
);


ALTER TABLE param.wfcheck OWNER TO ylanglais;

--
-- Name: workflow; Type: TABLE; Schema: param; Owner: ylanglais
--

CREATE TABLE param.workflow (
    id integer NOT NULL,
    name character varying(30) DEFAULT NULL::character varying,
    dsc character varying(30) DEFAULT NULL::character varying,
    entity character varying(60) DEFAULT NULL::character varying
);


ALTER TABLE param.workflow OWNER TO ylanglais;

--
-- Name: address; Type: TABLE; Schema: public; Owner: ylanglais
--

CREATE TABLE public.address (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    line_1 character varying(50),
    line_2 character varying(50),
    line_3 character varying(50),
    line_4 character varying(50),
    zipcode character varying(10),
    city character varying(50),
    country integer,
    ctime timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    mtime timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.address OWNER TO ylanglais;

--
-- Name: car; Type: TABLE; Schema: public; Owner: ylanglais
--

CREATE TABLE public.car (
    owner uuid,
    brand character varying(50) NOT NULL,
    model character varying(50) NOT NULL
);


ALTER TABLE public.car OWNER TO ylanglais;

--
-- Name: email; Type: TABLE; Schema: public; Owner: ylanglais
--

CREATE TABLE public.email (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    email character varying(200) NOT NULL,
    ctime timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    mtime timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.email OWNER TO ylanglais;

--
-- Name: link_channel; Type: TABLE; Schema: public; Owner: ylanglais
--

CREATE TABLE public.link_channel (
    entity character varying(50) NOT NULL,
    entity_id uuid NOT NULL,
    channel character varying(50) NOT NULL,
    channel_id uuid NOT NULL,
    ctime timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    mtime timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.link_channel OWNER TO ylanglais;

--
-- Name: morning_check; Type: TABLE; Schema: public; Owner: ylanglais
--

CREATE TABLE public.morning_check (
    id integer NOT NULL,
    domain character varying(50) DEFAULT NULL::character varying,
    categorie character varying(50) DEFAULT NULL::character varying,
    item character varying(100) NOT NULL,
    plan character varying(200),
    type_verif character varying(50) DEFAULT 'Manuelle'::character varying NOT NULL,
    verification character varying(500)
);


ALTER TABLE public.morning_check OWNER TO ylanglais;

--
-- Name: morning_check_id_seq; Type: SEQUENCE; Schema: public; Owner: ylanglais
--

CREATE SEQUENCE public.morning_check_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.morning_check_id_seq OWNER TO ylanglais;

--
-- Name: morning_check_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: ylanglais
--

ALTER SEQUENCE public.morning_check_id_seq OWNED BY public.morning_check.id;


--
-- Name: person; Type: TABLE; Schema: public; Owner: ylanglais
--

CREATE TABLE public.person (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    title integer,
    first_name character varying(25),
    last_name character varying(50),
    maiden_name character varying(25),
    dob date,
    status integer,
    gender integer,
    ctime timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    mtime timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.person OWNER TO ylanglais;

--
-- Name: phone; Type: TABLE; Schema: public; Owner: ylanglais
--

CREATE TABLE public.phone (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    phone character varying(20) NOT NULL,
    ctime timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    mtime timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.phone OWNER TO ylanglais;

--
-- Name: application; Type: TABLE; Schema: ref; Owner: ylanglais
--

CREATE TABLE ref.application (
    name character varying(50) NOT NULL,
    description character varying(200)
);


ALTER TABLE ref.application OWNER TO ylanglais;

--
-- Name: check_status; Type: TABLE; Schema: ref; Owner: ylanglais
--

CREATE TABLE ref.check_status (
    name character varying(50) NOT NULL,
    description character varying(200)
);


ALTER TABLE ref.check_status OWNER TO ylanglais;

--
-- Name: country; Type: TABLE; Schema: ref; Owner: ylanglais
--

CREATE TABLE ref.country (
    id integer NOT NULL,
    code integer,
    code2 character(2),
    code3 character(3),
    name character varying(100),
    cap_name character varying(100),
    ori_name character varying(100)
);


ALTER TABLE ref.country OWNER TO ylanglais;

--
-- Name: domain; Type: TABLE; Schema: ref; Owner: ylanglais
--

CREATE TABLE ref.domain (
    name character varying(50) NOT NULL,
    description character varying(200)
);


ALTER TABLE ref.domain OWNER TO ylanglais;

--
-- Name: etype; Type: TABLE; Schema: ref; Owner: ylanglais
--

CREATE TABLE ref.etype (
    id integer NOT NULL,
    name character varying(20) NOT NULL
);


ALTER TABLE ref.etype OWNER TO ylanglais;

--
-- Name: fragment_type; Type: TABLE; Schema: ref; Owner: ylanglais
--

CREATE TABLE ref.fragment_type (
    type character varying(50) NOT NULL,
    description character varying(200) DEFAULT NULL::character varying
);


ALTER TABLE ref.fragment_type OWNER TO ylanglais;

--
-- Name: frequency; Type: TABLE; Schema: ref; Owner: ylanglais
--

CREATE TABLE ref.frequency (
    name character varying(50) NOT NULL,
    description character varying(200)
);


ALTER TABLE ref.frequency OWNER TO ylanglais;

--
-- Name: gender; Type: TABLE; Schema: ref; Owner: ylanglais
--

CREATE TABLE ref.gender (
    id integer NOT NULL,
    value character varying(50) NOT NULL,
    description character varying(50)
);


ALTER TABLE ref.gender OWNER TO ylanglais;

--
-- Name: id_type; Type: TABLE; Schema: ref; Owner: ylanglais
--

CREATE TABLE ref.id_type (
    name character varying(50) NOT NULL,
    description character varying(200) DEFAULT NULL::character varying
);


ALTER TABLE ref.id_type OWNER TO ylanglais;

--
-- Name: page_type; Type: TABLE; Schema: ref; Owner: ylanglais
--

CREATE TABLE ref.page_type (
    name character varying(30) NOT NULL,
    description character varying(100)
);


ALTER TABLE ref.page_type OWNER TO ylanglais;

--
-- Name: perm; Type: TABLE; Schema: ref; Owner: ylanglais
--

CREATE TABLE ref.perm (
    name character varying(20) NOT NULL,
    description character varying(100)
);


ALTER TABLE ref.perm OWNER TO ylanglais;

--
-- Name: title; Type: TABLE; Schema: ref; Owner: ylanglais
--

CREATE TABLE ref.title (
    id integer NOT NULL,
    value character varying(50) NOT NULL,
    description character varying(50)
);


ALTER TABLE ref.title OWNER TO ylanglais;

--
-- Name: type_verif; Type: TABLE; Schema: ref; Owner: ylanglais
--

CREATE TABLE ref.type_verif (
    name character varying(50) NOT NULL,
    description character varying(200)
);


ALTER TABLE ref.type_verif OWNER TO ylanglais;

--
-- Name: duration; Type: TABLE; Schema: stats; Owner: ylanglais
--

CREATE TABLE stats.duration (
    key character varying(50) NOT NULL,
    n integer,
    min double precision,
    avg double precision,
    max double precision
);


ALTER TABLE stats.duration OWNER TO ylanglais;

--
-- Name: dbs; Type: TABLE; Schema: tech; Owner: ylanglais
--

CREATE TABLE tech.dbs (
    dbs character varying(50) NOT NULL,
    drv character varying(20) NOT NULL,
    host character varying(200) DEFAULT NULL::character varying,
    port integer,
    name character varying(50) NOT NULL,
    "user" character varying(50) DEFAULT NULL::character varying,
    pass character varying(50) DEFAULT NULL::character varying,
    opts character varying(200) DEFAULT NULL::character varying
);


ALTER TABLE tech.dbs OWNER TO ylanglais;

--
-- Name: role; Type: TABLE; Schema: tech; Owner: ylanglais
--

CREATE TABLE tech.role (
    id integer NOT NULL,
    name character varying(100) NOT NULL,
    description character varying(100)
);


ALTER TABLE tech.role OWNER TO ylanglais;

--
-- Name: user; Type: TABLE; Schema: tech; Owner: ylanglais
--

CREATE TABLE tech."user" (
    id integer NOT NULL,
    login character varying(50) NOT NULL,
    passwd character varying(256) DEFAULT NULL::character varying,
    name character varying(30) DEFAULT NULL::character varying,
    surname character varying(30) DEFAULT NULL::character varying,
    mail character varying(50) DEFAULT NULL::character varying,
    active character varying(1) DEFAULT 'N'::character varying NOT NULL,
    since date,
    until date
);


ALTER TABLE tech."user" OWNER TO ylanglais;

--
-- Name: user_role; Type: TABLE; Schema: tech; Owner: ylanglais
--

CREATE TABLE tech.user_role (
    user_id integer NOT NULL,
    role_id integer NOT NULL
);


ALTER TABLE tech.user_role OWNER TO ylanglais;

--
-- Name: wfaction; Type: TABLE; Schema: tech; Owner: ylanglais
--

CREATE TABLE tech.wfaction (
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


ALTER TABLE tech.wfaction OWNER TO ylanglais;

--
-- Name: wfcheck; Type: TABLE; Schema: tech; Owner: ylanglais
--

CREATE TABLE tech.wfcheck (
    version integer NOT NULL,
    login character varying(30) NOT NULL,
    id integer NOT NULL,
    entity_state character varying(60) NOT NULL,
    f_admin character varying(30) DEFAULT NULL::character varying,
    f_oper character varying(30) DEFAULT NULL::character varying,
    f_consult character varying(30) DEFAULT NULL::character varying
);


ALTER TABLE tech.wfcheck OWNER TO ylanglais;

--
-- Name: workflow; Type: TABLE; Schema: tech; Owner: ylanglais
--

CREATE TABLE tech.workflow (
    id integer NOT NULL,
    name character varying(30) DEFAULT NULL::character varying,
    dsc character varying(30) DEFAULT NULL::character varying,
    entity character varying(60) DEFAULT NULL::character varying
);


ALTER TABLE tech.workflow OWNER TO ylanglais;

--
-- Name: list id; Type: DEFAULT; Schema: param; Owner: ylanglais
--

ALTER TABLE ONLY param.list ALTER COLUMN id SET DEFAULT nextval('param.list_id_seq'::regclass);


--
-- Name: morning_check id; Type: DEFAULT; Schema: public; Owner: ylanglais
--

ALTER TABLE ONLY public.morning_check ALTER COLUMN id SET DEFAULT nextval('public.morning_check_id_seq'::regclass);


--
-- Data for Name: action; Type: TABLE DATA; Schema: audit; Owner: ylanglais
--

COPY audit.action (id, entity, entity_id, version, stamp, login, action, comment) FROM stdin;
\.


--
-- Data for Name: connection; Type: TABLE DATA; Schema: audit; Owner: ylanglais
--

COPY audit.connection (id, login, since, until, ip, state) FROM stdin;
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2021-09-10 13:27:05.51759	\N	127.0.0.1	login
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2021-09-22 08:58:17.479093	\N	127.0.0.1	login
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2021-09-28 15:34:30.277409	\N	127.0.0.1	login
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2021-10-04 21:56:13.773651	\N	127.0.0.1	login
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2021-10-05 21:59:39.005256	2021-10-08 17:20:37.73763	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-01-21 17:22:10.550674	2022-01-21 17:49:57.773193	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	alupin	2021-10-08 18:58:24.876405	2021-10-08 18:58:50.340063	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	alupin	2021-10-08 19:20:10.188752	2021-10-08 19:20:21.153178	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-01-21 19:39:26.440001	2022-01-21 20:20:28.711175	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	alupin	2021-10-08 19:20:45.242238	2021-10-08 19:20:47.33493	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	asanantonio	2021-10-08 19:21:36.757941	2021-10-08 19:21:41.588183	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-02-11 16:00:35.681291	2022-02-11 16:25:11.676674	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	adupin	2021-10-08 19:32:38.529546	2021-10-08 19:32:40.862344	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	adupin	2021-10-08 19:41:15.491734	2021-10-08 19:41:20.14727	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-02-23 16:58:16.620082	2022-02-23 17:24:36.055365	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	nburma	2021-10-08 19:43:35.059105	2021-10-08 19:43:39.149325	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	jmaigret	2021-10-08 19:43:54.285566	2021-10-08 19:43:56.884561	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	nlefloch	2021-10-08 19:44:05.845416	2021-10-08 19:44:08.559284	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	jjosephin	2021-10-08 19:44:17.083131	2021-10-08 19:44:19.019588	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	asanantonio	2021-10-08 19:44:25.653335	2021-10-08 19:44:27.327053	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2021-10-08 19:47:25.387298	\N	127.0.0.1	login
	admin	2021-10-17 14:08:04.444556	\N		bad_admin_pass
	admin	2021-10-17 14:08:14.275635	\N		bad_admin_pass
	admin	2021-10-17 14:08:24.240694	\N		bad_admin_pass
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2021-11-16 22:39:17.657858	\N	127.0.0.1	login
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2021-12-03 12:45:43.666939	\N	127.0.0.1	login
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2021-12-10 10:51:18.138128	2021-12-10 10:55:40.713856	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2021-12-10 10:56:47.155536	2021-12-10 10:58:58.879818	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2021-12-10 10:59:09.39578	\N	127.0.0.1	login
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2021-12-10 11:40:51.992633	2021-12-10 12:12:58.049853	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2021-12-15 14:14:31.148551	2021-12-15 14:26:14.477244	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2021-12-15 16:37:35.145444	2021-12-15 16:37:37.665485	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2021-12-15 17:33:02.385608	2021-12-15 17:43:05.810582	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2021-12-21 19:06:40.76111	2021-12-21 19:17:04.328596	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-01-05 10:12:51.775372	2022-01-05 10:37:28.401025	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-01-05 14:29:50.295937	2022-01-05 15:26:24.141821	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-01-06 10:47:27.36811	2022-01-06 10:57:28.125294	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-01-06 11:02:04.381736	2022-01-06 11:12:28.814503	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-01-06 11:30:16.369457	2022-01-06 11:36:48.644243	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-01-06 11:36:52.163493	2022-01-06 11:46:57.935095	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-01-06 14:27:42.915878	2022-01-06 14:41:06.635848	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-01-06 17:57:05.19253	2022-01-06 17:57:13.117923	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	adupin	2022-01-11 21:08:32.879674	2022-01-11 21:09:07.519043	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	jmaigret	2022-01-11 21:10:04.006068	2022-01-11 21:10:07.72626	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-01-11 21:10:11.624801	2022-01-11 21:11:27.538856	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	asanantonio	2022-01-11 21:11:31.886876	2022-01-11 21:43:12.834341	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-01-14 19:01:02.966577	2022-01-14 19:19:19.963963	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-01-14 19:24:52.597327	2022-01-14 19:24:54.079865	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-01-14 19:24:59.867839	2022-01-14 20:28:57.864875	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-01-15 11:24:09.469458	2022-01-15 11:46:40.176022	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-01-17 10:59:47.002398	2022-01-17 12:05:14.96749	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-01-17 14:19:31.281561	2022-01-17 14:30:40.242384	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-01-17 18:26:37.496312	2022-01-17 18:59:11.684955	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-01-17 19:02:20.584823	2022-01-17 19:12:31.37397	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-01-17 22:37:20.85212	2022-01-17 23:25:01.927068	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-01-17 23:25:10.60922	2022-01-18 00:20:56.078904	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-01-18 10:06:35.401696	2022-01-18 10:16:52.092639	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-01-18 10:22:15.638917	2022-01-18 10:55:48.798232	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-01-18 11:00:46.444456	2022-01-18 11:11:33.146834	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-01-18 17:05:45.902648	2022-01-18 17:16:42.160284	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-01-18 17:16:46.97601	2022-01-18 17:25:03.352808	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	adupin	2022-01-18 17:25:07.386384	2022-01-18 17:25:54.904386	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	adupin	2022-01-18 17:25:58.279963	2022-01-18 17:26:09.594932	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-01-18 17:26:55.528653	2022-01-18 17:27:11.456326	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	adupin	2022-01-18 17:27:14.381923	2022-01-18 17:30:43.024016	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	adupin	2022-01-18 17:30:46.604355	2022-01-18 17:30:53.949364	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-01-18 17:30:58.716167	2022-01-18 17:31:04.503946	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-01-20 16:09:09.598618	2022-01-20 16:40:06.239791	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-01-20 16:44:59.314731	2022-01-20 17:24:01.584271	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-01-20 18:41:57.938005	2022-01-20 19:53:26.642563	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-01-20 20:42:54.878269	2022-01-20 21:37:53.701274	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-01-20 21:58:16.055176	2022-01-20 23:26:15.221048	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-01-21 09:20:57.809284	2022-01-21 09:51:12.086485	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-01-21 15:13:15.186979	2022-01-21 15:24:42.862973	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-01-21 15:44:23.657188	2022-01-21 15:58:11.753096	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-02-28 10:03:37.083059	2022-02-28 10:05:15.581482	127.0.0.1	logout
	admin	2022-02-28 10:05:21.496563	\N		bad_admin_pass
	admin	2022-02-28 10:05:40.871038	\N		bad_admin_pass
	admin	2022-02-28 10:05:52.672932	\N		bad_admin_pass
r9sm0r7adr4ujghot2sttkmm68	api	2022-02-28 19:47:06.698548	2022-02-28 19:47:06.725748	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	admin	2022-02-28 10:13:38.810813	2022-02-28 10:23:50.833969	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-02-28 13:32:21.839766	2022-02-28 14:29:18.287649	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-02-28 14:44:34.925324	2022-02-28 14:56:21.286368	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-02-28 14:56:25.713104	2022-02-28 14:56:29.769459	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-02-28 14:33:16.335512	2022-02-28 15:04:37.650161	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-02-28 15:05:18.669261	2022-02-28 15:05:57.466711	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-02-28 14:56:33.428519	2022-02-28 15:09:25.128746	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-02-28 15:10:43.480773	2022-02-28 15:10:43.494906	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-02-28 15:11:32.586784	2022-02-28 15:11:32.601471	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-02-28 15:12:36.391679	2022-02-28 15:13:39.425834	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-02-28 15:14:33.462144	2022-02-28 15:15:46.700802	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-02-28 15:15:56.610768	2022-02-28 15:16:32.788392	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-02-28 15:17:49.223067	2022-02-28 15:18:22.878991	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-02-28 15:18:26.93516	2022-02-28 15:19:19.263354	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-02-28 15:19:20.65525	2022-02-28 15:19:22.314483	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-02-28 15:19:23.470738	2022-02-28 15:19:49.338227	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-02-28 15:19:53.74017	2022-02-28 15:20:17.104957	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-02-28 15:26:27.703824	2022-02-28 15:26:29.968732	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-02-28 15:26:59.319424	2022-02-28 15:31:22.897938	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-02-28 15:31:50.059826	2022-02-28 15:32:30.401371	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-02-28 15:32:32.321255	2022-02-28 15:33:03.445517	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-02-28 15:33:07.808526	2022-02-28 15:34:03.382748	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-02-28 15:34:04.89941	2022-02-28 15:35:22.444886	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-02-28 15:35:37.184272	2022-02-28 15:36:12.303194	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-02-28 15:36:15.828248	2022-02-28 15:38:25.266777	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-02-28 15:38:29.937152	2022-02-28 15:39:07.645274	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-02-28 15:39:28.472236	2022-02-28 15:39:31.984228	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-02-28 15:41:10.869649	2022-02-28 15:41:13.355811	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-02-28 15:41:14.866174	2022-02-28 15:42:20.247073	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-02-28 15:42:42.613488	2022-02-28 15:43:54.430223	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-02-28 15:45:40.83155	2022-02-28 15:45:40.851345	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-02-28 15:46:17.555194	2022-02-28 15:46:17.569208	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-02-28 15:46:19.313806	2022-02-28 15:46:19.327855	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-02-28 15:46:41.677312	2022-02-28 15:46:41.697877	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-02-28 15:46:43.50316	2022-02-28 15:46:43.516286	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-02-28 15:46:47.325805	2022-02-28 15:46:47.34085	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-02-28 15:48:35.433156	2022-02-28 15:48:35.447988	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-02-28 15:50:33.24869	2022-02-28 15:50:33.262413	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-02-28 16:08:41.131154	\N	127.0.0.1	login
r9sm0r7adr4ujghot2sttkmm68	api	2022-02-28 16:08:43.684282	\N	127.0.0.1	login
r9sm0r7adr4ujghot2sttkmm68	api	2022-02-28 16:09:38.862363	2022-02-28 16:09:38.876646	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-02-28 16:11:07.727219	2022-02-28 16:11:07.742652	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-02-28 16:11:18.203268	2022-02-28 16:11:18.216842	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-02-28 16:12:41.453834	2022-02-28 16:12:41.465963	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-02-28 16:14:33.447285	2022-02-28 16:14:33.454116	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-02-28 16:15:26.69656	2022-02-28 16:15:26.709802	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-02-28 16:16:45.967149	2022-02-28 16:16:45.978964	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-02-28 16:17:36.293706	2022-02-28 16:17:36.308644	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-02-28 16:19:21.447644	2022-02-28 16:19:21.465054	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-02-28 16:22:30.419979	2022-02-28 16:22:30.437261	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-02-28 16:22:41.260068	2022-02-28 16:22:41.280257	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-02-28 16:23:02.223308	\N	127.0.0.1	login
r9sm0r7adr4ujghot2sttkmm68	api	2022-02-28 16:23:10.446101	2022-02-28 16:23:10.461029	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-02-28 16:24:07.070655	2022-02-28 16:24:07.087227	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-02-28 16:24:23.502443	\N	127.0.0.1	login
r9sm0r7adr4ujghot2sttkmm68	api	2022-02-28 16:40:01.729962	2022-02-28 16:40:01.746316	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-02-28 16:51:50.779919	2022-02-28 16:51:50.795754	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-02-28 16:52:36.458596	2022-02-28 16:52:36.473634	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-02-28 16:52:39.962424	2022-02-28 16:52:39.97696	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-02-28 16:52:41.139391	2022-02-28 16:52:41.153766	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-02-28 19:17:53.69167	2022-02-28 19:17:53.724479	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-02-28 19:19:04.324619	2022-02-28 19:19:04.354953	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-02-28 19:19:13.046517	2022-02-28 19:19:13.087902	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-02-28 19:19:41.664389	2022-02-28 19:19:41.693399	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-02-28 19:20:06.36177	2022-02-28 19:20:06.389662	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-02-28 19:20:52.337288	2022-02-28 19:20:52.368372	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-02-28 19:21:18.904476	2022-02-28 19:21:18.93952	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-02-28 19:21:46.581912	2022-02-28 19:21:46.613127	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-02-28 19:24:53.915036	2022-02-28 19:24:53.940921	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-02-28 19:25:56.871716	2022-02-28 19:25:56.897584	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-02-28 19:29:48.829742	2022-02-28 19:29:48.861386	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-02-28 19:34:11.320711	2022-02-28 19:34:11.349819	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-02-28 19:34:54.398301	2022-02-28 19:34:54.429662	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-02-28 19:35:25.217375	2022-02-28 19:35:25.244994	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-02-28 19:36:01.93214	2022-02-28 19:36:01.962983	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-02-28 19:40:52.701148	2022-02-28 19:40:52.732078	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-02-28 19:50:21.444981	2022-02-28 19:50:21.47238	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-02-28 19:44:52.781707	2022-02-28 19:44:52.811606	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-02-28 19:50:49.947561	2022-02-28 19:50:49.978165	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-02-28 19:52:06.082133	2022-02-28 19:52:06.112622	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-02-28 20:57:00.279458	2022-02-28 20:57:00.301711	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-02-28 21:01:56.960134	2022-02-28 21:01:56.992988	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-02-28 21:06:32.925705	2022-02-28 21:06:32.952877	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-02-28 21:09:46.018007	2022-02-28 21:09:46.036224	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-02-28 21:20:31.439865	\N	127.0.0.1	login
r9sm0r7adr4ujghot2sttkmm68	api	2022-02-28 21:25:15.855694	\N	127.0.0.1	login
r9sm0r7adr4ujghot2sttkmm68	api	2022-02-28 21:26:19.686734	2022-02-28 21:26:19.705394	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-02-28 21:27:42.578583	2022-02-28 21:27:42.604582	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-02-28 21:29:16.881165	2022-02-28 21:29:16.909101	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-02-28 21:30:03.305262	2022-02-28 21:30:03.339812	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-02-28 21:30:23.944919	2022-02-28 21:30:23.969973	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-02-28 21:31:14.807767	2022-02-28 21:31:14.836437	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-02-28 21:53:54.928624	2022-02-28 22:19:40.732669	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-03-02 18:27:29.524976	2022-03-02 18:27:29.544693	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-03-02 18:32:12.25761	\N	127.0.0.1	login
r9sm0r7adr4ujghot2sttkmm68	api	2022-03-02 18:33:36.437569	\N	127.0.0.1	login
r9sm0r7adr4ujghot2sttkmm68	api	2022-03-02 18:34:11.554852	\N	127.0.0.1	login
r9sm0r7adr4ujghot2sttkmm68	api	2022-03-02 18:34:27.302882	\N	127.0.0.1	login
r9sm0r7adr4ujghot2sttkmm68	api	2022-03-02 18:35:56.043064	\N	127.0.0.1	login
r9sm0r7adr4ujghot2sttkmm68	api	2022-03-02 18:36:13.793161	\N	127.0.0.1	login
r9sm0r7adr4ujghot2sttkmm68	api	2022-03-02 18:36:42.263001	\N	127.0.0.1	login
r9sm0r7adr4ujghot2sttkmm68	api	2022-03-02 18:37:26.888128	2022-03-02 18:37:26.966714	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-03-02 18:39:59.149949	2022-03-02 18:39:59.177794	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-03-02 18:48:04.059174	2022-03-02 18:48:04.144325	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-03-02 18:48:34.31799	2022-03-02 18:48:34.40473	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-03-02 18:49:20.081901	2022-03-02 18:49:20.170238	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-03-02 18:50:52.433367	2022-03-02 18:50:52.519474	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-03-02 18:51:19.121444	2022-03-02 18:51:19.211861	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-03-02 19:06:26.510608	\N	127.0.0.1	login
r9sm0r7adr4ujghot2sttkmm68	api	2022-03-02 19:06:45.284349	\N	127.0.0.1	login
r9sm0r7adr4ujghot2sttkmm68	api	2022-03-02 19:10:57.097698	\N	127.0.0.1	login
r9sm0r7adr4ujghot2sttkmm68	api	2022-03-02 19:14:52.735391	2022-03-02 19:14:52.823809	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-03-02 19:14:58.173844	2022-03-02 19:14:58.260073	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-03-02 19:18:42.019868	2022-03-02 19:18:42.107547	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-03-02 19:19:05.441968	2022-03-02 19:19:05.466351	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-03-02 19:19:33.30056	2022-03-02 19:19:33.386471	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-03-02 19:20:33.593399	2022-03-02 19:20:33.680977	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-03-02 19:22:09.026599	2022-03-02 19:22:09.109395	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-03-02 19:23:03.809688	2022-03-02 19:23:03.893987	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-03-02 19:23:36.830964	2022-03-02 19:23:36.913405	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-03-02 19:25:01.729616	2022-03-02 19:25:01.820386	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-03-02 19:26:37.633711	2022-03-02 19:26:37.722924	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-03-02 19:26:52.997918	2022-03-02 19:26:53.08676	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-03-02 19:27:14.291275	2022-03-02 19:27:14.32623	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-03-02 19:36:38.448163	2022-03-02 19:36:38.474464	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-03-02 19:37:40.826776	2022-03-02 19:37:40.906895	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-03-02 19:38:19.893669	2022-03-02 19:38:19.979962	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-03-02 19:39:38.914766	2022-03-02 19:39:38.936027	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-03-02 19:41:03.325595	2022-03-02 19:41:03.35275	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-03-02 19:41:23.460603	2022-03-02 19:41:23.490334	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-03-02 19:42:08.230621	2022-03-02 19:42:08.257534	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-03-02 19:43:24.239695	2022-03-02 19:43:24.266537	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-03-02 19:43:52.430936	2022-03-02 19:43:52.456919	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-03-02 19:45:23.877116	2022-03-02 19:45:23.903514	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-03-02 19:45:33.780418	2022-03-02 19:45:33.802529	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-03-02 19:46:04.509902	2022-03-02 19:46:04.536135	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-03-02 19:46:16.245613	2022-03-02 19:46:16.332526	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-03-02 19:51:05.79402	2022-03-02 20:01:45.976667	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-03-02 20:49:53.702651	2022-03-02 21:00:05.187499	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-03-02 21:17:37.397847	2022-03-02 21:30:52.498065	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-03-01 17:34:10.179481	2022-04-19 09:50:28.576522	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-04-14 19:29:25.014284	2022-04-14 19:39:52.553694	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-04-15 09:40:09.312086	2022-04-15 09:53:41.619795	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-04-15 10:03:47.498943	2022-04-15 10:30:00.7078	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-04-15 11:15:01.362853	2022-04-15 11:15:01.382572	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-04-15 11:15:44.233578	2022-04-15 11:15:44.254171	127.0.0.1	logout
r9sm0r7adr4ujghot2sttkmm68	api	2022-04-15 11:15:46.605538	2022-04-15 11:15:46.689078	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-04-15 14:36:52.221207	2022-04-15 15:07:39.371984	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-04-15 15:21:24.663167	2022-04-15 15:31:47.097515	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-04-15 15:57:26.142222	2022-04-15 16:41:08.465812	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-04-15 16:41:21.69414	2022-04-15 16:55:43.314302	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-04-15 17:06:13.011948	2022-04-15 17:52:29.440436	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-04-15 17:55:43.514777	2022-04-15 18:12:28.861945	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-04-15 18:17:26.952538	2022-04-15 19:01:08.011882	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-04-15 19:21:23.266732	2022-04-15 19:33:31.521602	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-04-15 19:33:35.06282	2022-04-15 19:35:10.358315	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-04-15 19:35:14.012693	2022-04-15 19:48:06.389519	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-04-17 11:13:55.158195	2022-04-17 11:24:00.292647	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-04-19 09:29:12.632063	\N	127.0.0.1	login
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-04-19 09:50:35.380439	2022-04-19 10:35:08.626901	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-04-19 11:15:55.123767	2022-04-19 11:26:56.747024	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-04-20 12:53:13.148187	2022-04-20 13:04:36.16966	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-04-20 13:08:43.445823	2022-04-20 13:34:50.401277	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-04-20 13:41:01.791695	2022-04-20 13:55:59.992793	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-04-20 14:20:18.980287	2022-04-20 15:01:42.204218	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-04-22 17:27:08.488237	2022-04-22 17:40:14.557129	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-04-22 18:11:05.498634	2022-04-22 18:25:41.306997	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-04-22 18:28:59.269786	2022-04-22 18:47:56.673891	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-04-22 19:08:25.29071	2022-04-22 19:25:47.575384	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-04-25 15:28:20.723909	2022-04-25 15:56:19.061476	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-04-25 16:52:06.381479	2022-04-25 17:16:51.567825	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-04-26 16:11:37.846426	2022-04-26 16:28:06.945333	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-05-11 15:03:54.323351	2022-05-11 15:18:10.490286	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-05-11 18:29:02.474526	2022-05-11 19:11:45.398466	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-05-11 20:48:39.450607	2022-05-11 21:10:30.611953	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-05-11 21:14:02.163747	2022-05-11 21:58:26.432149	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-05-11 22:21:11.67995	2022-05-11 23:06:16.532628	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-05-12 10:24:21.643187	2022-05-12 10:50:37.093499	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-05-12 10:50:40.961089	2022-05-12 11:16:37.070824	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-05-12 11:16:54.254293	2022-05-12 11:31:01.622964	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-05-12 12:32:22.303779	2022-05-12 12:44:09.313597	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-05-12 13:24:48.437536	2022-05-12 13:34:48.843776	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-05-12 13:37:54.874596	2022-05-12 13:54:23.629433	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-05-12 14:13:23.013368	2022-05-12 14:23:24.599379	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-05-12 14:23:33.953286	2022-05-12 14:33:55.08984	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-05-15 16:54:05.900486	2022-05-15 17:06:05.960279	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-10-11 15:52:54.492669	2022-10-11 16:13:37.853999	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-10-11 16:18:01.896659	2022-10-11 16:28:21.232928	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-10-11 16:35:22.391509	2022-10-11 16:46:12.315542	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-10-11 17:08:54.746896	2022-10-11 17:21:25.060153	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-10-12 15:50:30.832351	2022-10-12 16:30:30.865738	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-10-20 15:29:09.125678	\N	127.0.0.1	login
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-10-24 09:51:09.790095	\N	127.0.0.1	login
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-11-03 10:18:38.554281	2022-11-09 14:24:47.673994	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2022-11-09 14:24:55.338063	\N	127.0.0.1	login
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2023-01-04 17:48:13.673428	\N	127.0.0.1	login
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2023-01-21 11:27:51.185587	\N	127.0.0.1	login
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2023-01-31 19:23:58.719365	\N	127.0.0.1	login
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2023-03-14 15:25:48.648959	2023-03-14 16:20:15.948941	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2023-03-14 16:20:23.006049	\N	127.0.0.1	login
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2023-03-24 11:24:54.561296	\N	127.0.0.1	login
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2023-04-12 10:20:06.06648	2023-04-12 10:20:14.811807	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2023-04-12 10:21:11.633829	2023-04-12 10:22:17.288652	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2023-04-12 10:22:25.106566	2023-04-12 10:32:12.220734	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2023-04-12 10:32:18.512469	2023-04-12 10:44:33.449784	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2023-04-12 10:44:39.972988	2023-04-12 10:48:26.082634	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2023-04-12 10:48:32.767912	2023-04-12 10:49:54.177385	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2023-04-12 10:50:01.357971	2023-04-12 10:51:36.417372	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2023-04-12 10:51:44.825442	2023-04-12 10:59:11.898381	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2023-04-12 10:59:18.578599	2023-04-12 11:06:19.42863	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2023-04-12 11:06:24.281772	2023-04-12 13:12:45.936264	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2023-04-13 22:52:18.648652	2023-04-14 00:09:18.030228	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2023-04-25 15:13:21.626494	2023-04-25 16:14:34.975284	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2023-04-25 16:49:05.887306	2023-04-25 17:01:35.047361	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2023-04-25 17:01:40.729977	2023-04-25 17:03:59.946903	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2023-04-25 17:04:08.518083	2023-04-25 18:06:18.786844	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2023-04-27 08:51:00.453186	2023-04-27 09:31:01.934302	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2023-04-27 09:31:05.8242	2023-04-27 09:34:57.902584	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2023-04-27 09:35:01.589613	2023-04-27 09:36:20.908324	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2023-04-27 09:36:24.340755	2023-04-27 09:37:42.408	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2023-04-27 09:37:45.678022	2023-04-27 09:42:27.82327	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2023-04-27 09:42:31.300644	2023-04-27 09:53:49.704875	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2023-04-27 09:53:53.048668	2023-04-27 10:07:39.073984	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2023-04-27 10:07:42.249649	2023-04-27 10:35:34.974622	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2023-04-27 10:35:39.724075	2023-04-27 11:37:13.77218	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2023-08-03 15:18:42.241604	2023-08-03 17:10:58.984465	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2023-10-11 11:56:08.258751	2023-10-11 13:33:02.682868	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2023-12-18 19:16:29.565687	2023-12-19 09:45:31.574853	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2024-04-15 14:48:35.096218	2024-04-15 15:52:11.732929	127.0.0.1	logout
gd1rldtk2m5b2p14p2b9vnnekd	ylanglais	2024-05-03 11:25:22.914429	\N	127.0.0.1	login
\.


--
-- Data for Name: log; Type: TABLE DATA; Schema: audit; Owner: ylanglais
--

COPY audit.log (tstamp, ip, login, level, msg) FROM stdin;
2023-04-25 17:00:52.245 	127.0.0.1	ylanglais	SECURITY	Attempted access to table default.param.page without due permission
2023-04-25 17:01:02.141 	127.0.0.1	ylanglais	SECURITY	Attempted access to table default.tech.dbs without due permission
2023-04-25 17:01:49.054 	127.0.0.1	ylanglais	SECURITY	Attempted access to table default.tech.dbs without due permission
2023-04-25 17:03:46.697 	127.0.0.1	ylanglais	SECURITY	Attempted access to table default.param.page_perm without due permission
2023-04-27 08:51:06.507 	127.0.0.1	ylanglais	SECURITY	Attempted access to table default.tech.dbs without due permission
2023-04-27 08:52:48.936 	127.0.0.1	ylanglais	SECURITY	Attempted access to table default.param.page_perm without due permission
2023-04-27 08:52:50.012 	127.0.0.1	ylanglais	SECURITY	Attempted access to table default.param.folder_perm without due permission
2023-04-27 08:52:51.108 	127.0.0.1	ylanglais	SECURITY	Attempted access to table default.param.folder without due permission
2023-04-27 08:52:51.753 	127.0.0.1	ylanglais	SECURITY	Attempted access to table default.param.folder_page without due permission
2023-04-27 08:52:52.258 	127.0.0.1	ylanglais	SECURITY	Attempted access to table default.param.page without due permission
2023-04-27 08:52:53.161 	127.0.0.1	ylanglais	SECURITY	Attempted access to table default.tech.role without due permission
2023-04-27 09:27:13.830 	127.0.0.1	ylanglais	SECURITY	Attempted access to table default.param.page without due permission
2023-04-27 09:31:27.437 	127.0.0.1	ylanglais	SECURITY	Attempted access to table default.param.page without due permission
2023-04-27 09:32:32.630 	127.0.0.1	ylanglais	SECURITY	Attempted access to table default.param.page without due permission
2023-04-27 09:35:15.885 	127.0.0.1	ylanglais	SECURITY	Attempted access to table default.param.page without due permission
2023-04-27 09:42:48.393 	127.0.0.1	ylanglais	SECURITY	Attempted access to table default.ref.perm without due permission
2023-04-27 09:42:49.940 	127.0.0.1	ylanglais	SECURITY	Attempted access to table default.ref.page_type without due permission
2023-04-27 09:42:50.583 	127.0.0.1	ylanglais	SECURITY	Attempted access to table m_local_pfwk.ville without due permission
2023-04-27 10:02:04.917 	127.0.0.1	ylanglais	SECURITY	Attempted access to entity Personne without due permission
2023-04-27 10:07:04.629 	127.0.0.1	ylanglais	SECURITY	Attempted access to entity Personne without due permission
2023-08-03 15:18:45.037 	127.0.0.1	ylanglais	SECURITY	Attempted access to view Personne without due permission
2023-10-11 11:56:12.089 	127.0.0.1	ylanglais	SECURITY	Attempted access to view Personne without due permission
2023-10-11 11:56:32.056 	127.0.0.1	ylanglais	SECURITY	Attempted access to view Personne without due permission
2023-12-18 19:16:34.163 	127.0.0.1	ylanglais	SECURITY	Attempted access to view Personne without due permission
\.


--
-- Data for Name: changelog; Type: TABLE DATA; Schema: db; Owner: ylanglais
--

COPY db.changelog (stamp, action, entity, request) FROM stdin;
2025-04-23 12:55:26.591	2022015.v1_v2.sql	\N	\N
2025-04-23 12:55:34.001	patch 20240704.db.changelog.action.size.sql	\N	\N
2025-04-23 12:57:12.324	20230407.stats.sql	\N	\N
2025-04-23 12:57:44.635	patch 20230411.stats-duration.sql	\N	\N
2025-04-23 13:01:26.395	patch 20230425.audit.log.sql	\N	\N
2025-04-23 13:03:48.052	patch 20230425.param.enver.sql	\N	\N
2025-04-23 13:05:46.080	patch 20230425.param.right.sql	\N	\N
\.


--
-- Data for Name: entity; Type: TABLE DATA; Schema: param; Owner: ylanglais
--

COPY param.entity (name, stated, owned, versioned, historized, audited, stamped, dsrc, tname) FROM stdin;
Personne	n	n	n	n	n	n	\N	person
Contact	n	n	n	n	n	n	default	person
\.


--
-- Data for Name: enver; Type: TABLE DATA; Schema: param; Owner: ylanglais
--

COPY param.enver (env, ver, rev) FROM stdin;
DEV	1.0	2
DEV	1.0	2
DEV	1.0	2
\.


--
-- Data for Name: field; Type: TABLE DATA; Schema: param; Owner: ylanglais
--

COPY param.field (entity, name, tab, col, rsrc, rtab, rref, rval, multiple, num) FROM stdin;
Personne	Intitulé	person	title	default	ref.title	id	value	n	0
Personne	Nom	person	last_name	\N	\N	\N	\N	n	1
Personne	Prénom	person	first_name	\N	\N	\N	\N	n	2
Personne	Genre	person	gender	default	ref.gender	id	value	n	3
\.


--
-- Data for Name: folder; Type: TABLE DATA; Schema: param; Owner: ylanglais
--

COPY param.folder (id, name, description) FROM stdin;
1	Tableau de bord	Tableau de bord
1000	Administration	Administration
1001	Compte	Paramètres du compte
900	Audit	Audit
800	Référentiel	Référentiel
10	Contacts auto	Contact default interface
\.


--
-- Data for Name: folder_page; Type: TABLE DATA; Schema: param; Owner: ylanglais
--

COPY param.folder_page (folder_id, page_id, page_order) FROM stdin;
1001	0	0
800	100	1
800	101	2
800	300	3
800	301	4
800	302	6
800	303	7
10	200	1
10	210	2
10	220	3
10	230	4
10	250	5
10	190	0
1000	1	2
1000	2	3
1000	3	4
1000	4	5
1000	5	6
1000	6	7
1000	7	8
1000	8	1
900	50	1
900	51	2
1000	52	9
\.


--
-- Data for Name: folder_perm; Type: TABLE DATA; Schema: param; Owner: ylanglais
--

COPY param.folder_perm (folder_id, role_id, perm_name) FROM stdin;
1	1000	ALL
1000	1	ALL
1001	1000	ALL
900	100	RONLY
800	1	ALL
10	0	ALL
\.


--
-- Data for Name: fragment; Type: TABLE DATA; Schema: param; Owner: ylanglais
--

COPY param.fragment (entity, type, name, fsrc, cname, jtname, ftname, finame, flname, forder) FROM stdin;
Personne	reference	Intitulé	\N	title	\N	ref.title	id	value	\N
Personne	column	Prénom	\N	first_name	\N	\N	\N	\N	\N
Personne	column	Nom	\N	last_name	\N	\N	\N	\N	\N
Personne	reference	Genre	\N	gender	\N	ref.gender	id	value	\N
Contact	reference	Titre	\N	title	\N	ref.title	id	value	\N
Contact	column	Nom	\N	last_name	\N	\N	\N	\N	\N
Contact	column	Prénom	\N	first_name	\N	\N	\N	\N	\N
Contact	reference	Genre	\N	gender	\N	ref.gender	id	value	\N
Contact	vallist	Véhicules	\N	id	\N	car	owner	cardata	\N
\.


--
-- Data for Name: glist; Type: TABLE DATA; Schema: param; Owner: ylanglais
--

COPY param.glist (user_id, role_id, provider, columns, sortby, orderby) FROM stdin;
\.


--
-- Data for Name: item; Type: TABLE DATA; Schema: param; Owner: ylanglais
--

COPY param.item (list_id, item) FROM stdin;
1	brand
1	model
\.


--
-- Data for Name: list; Type: TABLE DATA; Schema: param; Owner: ylanglais
--

COPY param.list (id, entity, dsrc, tname, name, role, "user") FROM stdin;
1	\N	\N	car	cardata	\N	\N
\.


--
-- Data for Name: page; Type: TABLE DATA; Schema: param; Owner: ylanglais
--

COPY param.page (id, name, ptype, datalink, pagefile) FROM stdin;
0	Password Change	System	\N	adm_passwd.php
2	Roles	Table	default.tech.role	\N
8	Databases	Table	default.tech.dbs	\N
50	Connexions	Table	default.audit.connection	\N
1	Users	System	\N	adm_user.php
100	Permission	Table	default.ref.perm	\N
101	Page type	Table	default.ref.page_type	\N
200	Person	Table	default.person	\N
210	Email	Table	default.email	\N
220	Phone	Table	default.phone	\N
230	Address	Table	default.address	\N
300	Villes	Table	m_local_pfwk.ville	\N
301	Pays	Table	default.ref.country	\N
302	Genre	Table	default.ref.gender	\N
303	Title	Table	default.ref.title	\N
250	Link channel	Table	default.link_channel	\N
190	Personne	View	Personne	\N
4	Folders	Table	default.param.folder	\N
3	Pages	Table	default.param.page	\N
5	Folder contents	Table	default.param.folder_page	\N
6	Folder permissions	Table	default.param.folder_perm	\N
7	Page permissions	Table	default.param.page_perm	\N
51	Log	Table	default.audit.log	
52	Droits	Table	default.param.right	
\.


--
-- Data for Name: page_perm; Type: TABLE DATA; Schema: param; Owner: ylanglais
--

COPY param.page_perm (page_id, role_id, perm_name) FROM stdin;
50	0	RONLY
\.


--
-- Data for Name: right; Type: TABLE DATA; Schema: param; Owner: ylanglais
--

COPY param."right" (type, link, role_id, user_id, perm) FROM stdin;
table	default.tech.dbs	1	\N	ALL
table	default.tech.role	1	\N	ALL
table	default.tech.user	1	\N	ALL
table	default.param.folder	1	\N	ALL
table	default.param.page	1	\N	ALL
table	default.param.page_perm	1	\N	ALL
table	default.param.folder_perm	1	\N	ALL
table	default.param.folder_page	1	\N	ALL
table	default.audit.connection	100	\N	RONLY
table	default.audit.log	100	\N	RONLY
table	default.person	1	\N	RONLY
table	default.email	1	\N	RONLY
table	default.phone	1	\N	RONLY
table	default.address	1	\N	RONLY
table	m_local_pfwk.ville	1	\N	RONLY
table	default.link_channel	1	\N	RONLY
table	default.param.right	1	\N	ALL
table	default.ref.perm	0	\N	RONLY
table	default.ref.page_type	0	\N	RONLY
table	default.ref.country	0	\N	RONLY
table	default.ref.gender	0	\N	RONLY
table	default.ref.title	0	\N	RONLY
view	Personne	100	\N	ALL
\.


--
-- Data for Name: style; Type: TABLE DATA; Schema: param; Owner: ylanglais
--

COPY param.style (key, type, value) FROM stdin;
favicon	image	images/illay.png
bg	color	white
fg	color	black
reverse_fg	color	white
light_fg	color	#4b4e55
light_bg	color	#eeeeee
normal	color	color(reverse_bg)
border	color	color(reverse_bg)
reverse_border	color	color(reverse_fg)
disabled_fg	color	#777777
disabled_bg	color	#eeeeee
selected_fg	color	red
main_bg	color	color(bg)
main_fg	color	color(fg)
button_bg_nor	color	color(normal)
button_fg_nor	color	color(reverse_fg)
button_bg_pre	color	color(hover)
button_fg_pre	color	color(normal)
main_menu_bg_nor	color	color(normal)
main_menu_fg_nor	color	color(reverse_fg)
main_menu_bg_cur	color	color(bg)
main_menu_fg_cur	color	color(normal)
main_menu_bg_pre	color	color(hover)
main_menu_fg_pre	color	color(normal)
module_menu_bg_nor	color	color(bg)
module_menu_fg_nor	color	color(normal)
module_menu_bg_cur	color	color(selected_bg)
module_menu_fg_cur	color	color(reverse_fg)
module_menu_bg_pre	color	color(hover)
module_menu_fg_pre	color	color(normal)
vector_even_fg	color	color(fg)
vector_odd_fg	color	color(fg)
vector_over_fg	color	color(reverse_fg)
vector_over_bg	color	color(normal)
vector_out_bg	color	color(bg)
vector_out_fg	color	color(fg)
text_bg	color	color(bg)
text_fg	color	color(fg)
label_bg	color	color(normal)
label_fg	color	color(reverse_fg)
ro_input_bg	color	color(light_bg)
ro_input_fg	color	color(light_fg)
div_input_bg	color	color(bg)
div_input_fg	color	color(light_fg)
input_disabled_bg	color	color(disabled_bg)
input_disabled_fg	color	color(disabled_fg)
table_border	color	color(border)
th_border	color	color(border)
th_bg 	color	color(reverse_bg)
th_fg 	color	color(reverse_fg)
td_border	color	color(border)
tr_border	color	color(border)
table_menu_border	color	color(border)
th_menu_bg	color	color(reverse_bg)
table_head_border	color	color(border)
th_head_border	color	color(reverse_border)
div_ui_sublist_fg	color	color(reverse_fg)
vector_even_bg	color	color(light_bg)
vector_odd_bg	color	color(bg)
conflict	color	red
shadow	color	rgba(50, 50, 50, .5)
selected_bg	color	orange
hover	color	orange
fontfamily	string	"Liberation sans", Arial, sans-serif
logo	image	images/ptoolbox.svg
application_title	string	ptoolbox
reverse_bg	color	#161159
lightdimed_bg	color	#fefeff
image_normal	color	color(reverse_bg)
image_pre	color	color(hover)
image_selected	color	color(selected_fg)
image_disabled	color	color(disabled_fg)
\.


--
-- Data for Name: wfaction; Type: TABLE DATA; Schema: param; Owner: ylanglais
--

COPY param.wfaction (id, workflow, role, state0, state1, action, initial, othuid, mustcomment, script) FROM stdin;
\.


--
-- Data for Name: wfcheck; Type: TABLE DATA; Schema: param; Owner: ylanglais
--

COPY param.wfcheck (version, login, id, entity_state, f_admin, f_oper, f_consult) FROM stdin;
\.


--
-- Data for Name: workflow; Type: TABLE DATA; Schema: param; Owner: ylanglais
--

COPY param.workflow (id, name, dsc, entity) FROM stdin;
\.


--
-- Data for Name: address; Type: TABLE DATA; Schema: public; Owner: ylanglais
--

COPY public.address (id, line_1, line_2, line_3, line_4, zipcode, city, country, ctime, mtime) FROM stdin;
\.


--
-- Data for Name: car; Type: TABLE DATA; Schema: public; Owner: ylanglais
--

COPY public.car (owner, brand, model) FROM stdin;
ea70ed82-15ab-45f8-bfca-a804a23a7514	Renault	Safrane
ea70ed82-15ab-45f8-bfca-a804a23a7514	Renault	twingo
8e531a08-73ff-4ed3-83ae-03a9aa1ab8e5	Peugeot	508
\.


--
-- Data for Name: email; Type: TABLE DATA; Schema: public; Owner: ylanglais
--

COPY public.email (id, email, ctime, mtime) FROM stdin;
\.


--
-- Data for Name: link_channel; Type: TABLE DATA; Schema: public; Owner: ylanglais
--

COPY public.link_channel (entity, entity_id, channel, channel_id, ctime, mtime) FROM stdin;
person	ea70ed82-15ab-45f8-bfca-a804a23a7514	phone	709ca634-20f8-4aee-a0c5-77d8dc586cba	2022-04-25 16:39:47.276527	2022-04-25 16:39:47.276527
\.


--
-- Data for Name: morning_check; Type: TABLE DATA; Schema: public; Owner: ylanglais
--

COPY public.morning_check (id, domain, categorie, item, plan, type_verif, verification) FROM stdin;
\.


--
-- Data for Name: person; Type: TABLE DATA; Schema: public; Owner: ylanglais
--

COPY public.person (id, title, first_name, last_name, maiden_name, dob, status, gender, ctime, mtime) FROM stdin;
8e531a08-73ff-4ed3-83ae-03a9aa1ab8e5	3	Jean	Dupont	\N	\N	\N	2	2022-04-19 19:01:10.969831	2022-04-19 19:01:10.969831
95844b5d-e918-4437-8062-4daf6355514b	1	Martine	Durand	\N	\N	\N	1	2022-04-19 19:01:10.969831	2022-04-19 19:01:10.969831
ea70ed82-15ab-45f8-bfca-a804a23a7514	3	Yann	Langlais	\N	1970-05-21	\N	2	2022-04-25 16:26:09.359464	2022-04-25 16:26:09.359464
3205eeed-5b46-442f-b4cf-d9565181c93a	2	Marie	Duval	Duvall	\N	\N	1	2022-04-19 19:01:10.969831	2022-04-19 19:01:10.969831
b125ad05-cef5-4c8a-ab1a-c2233ebde089	3	Jean	Dupond	\N	1970-01-01	\N	2	2022-04-19 19:01:10.969831	2022-04-19 19:01:10.969831
\.


--
-- Data for Name: phone; Type: TABLE DATA; Schema: public; Owner: ylanglais
--

COPY public.phone (id, phone, ctime, mtime) FROM stdin;
709ca634-20f8-4aee-a0c5-77d8dc586cba	+33667110710	2022-04-25 16:28:29.65753	2022-04-25 16:28:29.65753
\.


--
-- Data for Name: application; Type: TABLE DATA; Schema: ref; Owner: ylanglais
--

COPY ref.application (name, description) FROM stdin;
\.


--
-- Data for Name: check_status; Type: TABLE DATA; Schema: ref; Owner: ylanglais
--

COPY ref.check_status (name, description) FROM stdin;
Unchecked	Pas encode vérifié
N/A	Pas applicable
Ok	Pas de souci
Warning	Attention
Error	Erreur
Problem	Problème
Critical	Problème critique
None	Pas de fréquence
Daily	Quotidien
Busday	Jour ouvré
Weekly	Hebdo
Monthly	Mensuel
Quarterly	Trimestriel
Half-yearly	Semestriel
Yearly	Annuel
\.


--
-- Data for Name: country; Type: TABLE DATA; Schema: ref; Owner: ylanglais
--

COPY ref.country (id, code, code2, code3, name, cap_name, ori_name) FROM stdin;
1	4	AF	AFG	Afghanistan	AFGHANISTAN	د افغانستان اسلامي دولتدولت اسلامی افغانستان
2	710	ZA	ZAF	Afrique du Sud	AFRIQUE DU SUD	Republic of South Africa
3	248	AX	ALA	Îles Åland	ÅLAND, ÎLES	Landskapet Åland ; Ahvenanmaan maakunta ; (État libre associé d'Åland)
4	8	AL	ALB	Albanie	ALBANIE	Shqipëri ; Republika e Shqipërisë ; (République d'Albanie)
5	12	DZ	DZA	Algérie	ALGÉRIE	الجمهورية الجزائرية الديمقراطية الشعبية
6	276	DE	DEU	Allemagne	ALLEMAGNE	Bundesrepublik Deutschland
7	20	AD	AND	Andorre	ANDORRE	Principat d'Andorra
8	24	AO	AGO	Angola	ANGOLA	República de Angola
9	660	AI	AIA	Anguilla	ANGUILLA	Anguilla
10	10	AQ	ATA	Antarctique	ANTARCTIQUE	The Antarctic Treaty
11	28	AG	ATG	Antigua-et-Barbuda	ANTIGUA-ET-BARBUDA	Antigua and Barbadua
12	682	SA	SAU	Arabie saoudite	ARABIE SAOUDITE	المملكة العربية السعودية
13	32	AR	ARG	Argentine	ARGENTINE	Argentina
14	51	AM	ARM	Arménie	ARMÉNIE	Հայաստան
15	533	AW	ABW	Aruba	ARUBA	Aruba
16	36	AU	AUS	Australie	AUSTRALIE	Australia
17	40	AT	AUT	Autriche	AUTRICHE	Österreich
18	31	AZ	AZE	Azerbaïdjan	AZERBAÏDJAN	Azərbaycan Respublikası
19	44	BS	BHS	Bahamas	BAHAMAS	Commonwealth of the Bahamas
20	48	BH	BHR	Bahreïn	BAHREÏN	مملكة البحرين
21	50	BD	BGD	Bangladesh	BANGLADESH	গণপ্রজাতন্ত্রী বাংলাদেশ
22	52	BB	BRB	Barbade	BARBADE	Barbados
23	112	BY	BLR	Biélorussie	BÉLARUS	Беларусь (Belarusy)
24	56	BE	BEL	Belgique	BELGIQUE	België
25	84	BZ	BLZ	Belize	BELIZE	Belize
26	204	BJ	BEN	Bénin	BÉNIN	Bénin
27	60	BM	BMU	Bermudes	BERMUDES	Bermuda
28	64	BT	BTN	Bhoutan	BHOUTAN	འབྲུག་ཡུལ
29	68	BO	BOL	Bolivie	BOLIVIE, ÉTAT PLURINATIONAL DE	Estado Plurinacional de Bolivia
30	535	BQ	BES	Pays-Bas caribéens	BONAIRE, SAINT-EUSTACHE ET SABA	Bonaire, Sint-Eustatius, en Saba
31	70	BA	BIH	Bosnie-Herzégovine	BOSNIE-HERZÉGOVINE	Republika Bosna i Hercegovina
32	72	BW	BWA	Botswana	BOTSWANA	Republic of Botswana
33	74	BV	BVT	Île Bouvet	BOUVET, ÎLE	Bouvetøya
34	76	BR	BRA	Brésil	BRÉSIL	República Federativa do Brasil
35	96	BN	BRN	Brunei	BRUNÉI DARUSSALAM	بروني دارالسلام
36	100	BG	BGR	Bulgarie	BULGARIE	Република България
37	854	BF	BFA	Burkina Faso	BURKINA FASO	Burkina Faso
38	108	BI	BDI	Burundi	BURUNDI	République du Burundi
39	136	KY	CYM	Îles Caïmans	CAÏMANES, ÎLES	Cayman Islands
40	116	KH	KHM	Cambodge	CAMBODGE	ព្រះរាជាណាចក្រកម្ពុជា
41	120	CM	CMR	Cameroun	CAMEROUN	Cameroun
42	124	CA	CAN	Canada	CANADA	Canada
43	132	CV	CPV	Cap-Vert	CABO VERDE	Cabo Verde
44	140	CF	CAF	République centrafricaine	CENTRAFRICAINE, RÉPUBLIQUE	Ködörösêse tî Bêafrîka
45	152	CL	CHL	Chili	CHILI	Chile
46	156	CN	CHN	Chine	CHINE	中华人民共和国
47	162	CX	CXR	Île Christmas	CHRISTMAS, ÎLE	Christmas Islands
48	196	CY	CYP	Chypre	CHYPRE	Κύπρος
49	166	CC	CCK	Îles Cocos	COCOS (KEELING), ÎLES	Territory of Cocos Island
50	170	CO	COL	Colombie	COLOMBIE	Colombia
51	174	KM	COM	Comores	COMORES	جزر القُمُر
52	178	CG	COG	République du Congo	CONGO	République du Congo
53	180	CD	COD	République démocratique du Congo	CONGO, RÉPUBLIQUE DÉMOCRATIQUE DU	République démocratique du Congo
54	184	CK	COK	Îles Cook	COOK, ÎLES	Cook Islands
55	410	KR	KOR	Corée du Sud	CORÉE, RÉPUBLIQUE DE	대한민국
56	408	KP	PRK	Corée du Nord	CORÉE, RÉPUBLIQUE POPULAIRE DÉMOCRATIQUE DE	조선민주주의인민공화국
57	188	CR	CRI	Costa Rica	COSTA RICA	República de Costa Rica
58	384	CI	CIV	Côte d'Ivoire	CÔTE D'IVOIRE	Côte d'Ivoire
59	191	HR	HRV	Croatie	CROATIE	Republika Hrvatska
60	192	CU	CUB	Cuba	CUBA	República de Cuba
61	531	CW	CUW	Curaçao	CURAÇAO	Curaçao, Kòrsou
62	208	DK	DNK	Danemark	DANEMARK	Kongeriget Danmark
63	262	DJ	DJI	Djibouti	DJIBOUTI	; جمهورية جيبوتي
64	214	DO	DOM	République dominicaine	DOMINICAINE, RÉPUBLIQUE	República Dominicana
65	212	DM	DMA	Dominique	DOMINIQUE	Commonwealth of Dominica
66	818	EG	EGY	Égypte	ÉGYPTE	جمهوريّة مصر العربيّة,
67	222	SV	SLV	Salvador	EL SALVADOR	República de El Salvador
68	784	AE	ARE	Émirats arabes unis	ÉMIRATS ARABES UNIS	دولة الإمارات العربيّة المتّحدة
69	218	EC	ECU	Équateur	ÉQUATEUR	República del Ecuador
70	232	ER	ERI	Érythrée	ÉRYTHRÉE	ሃገረ ኤርትራ
71	724	ES	ESP	Espagne	ESPAGNE	España
72	233	EE	EST	Estonie	ESTONIE	Eesti
73	840	US	USA	États-Unis	ÉTATS-UNIS	United States of America
74	231	ET	ETH	Éthiopie	ÉTHIOPIE	የኢትዮጵያ ፌዴራላዊ ዲሞክራሲያዊ ሪፐብሊክ
75	238	FK	FLK	Malouines	FALKLAND, ÎLES (MALVINAS)	Falkland Islands
76	234	FO	FRO	Îles Féroé	FÉROÉ, ÎLES	Føroyar ; Færøerne
77	242	FJ	FJI	Fidji	FIDJI	फ़िजी द्वीप समूह गणराज्य
78	246	FI	FIN	Finlande	FINLANDE	Suomen Tasavalta
79	250	FR	FRA	France	FRANCE	République française
80	266	GA	GAB	Gabon	GABON	République gabonaise
81	270	GM	GMB	Gambie	GAMBIE	Republic of Gambia
82	268	GE	GEO	Géorgie	GÉORGIE	საქართველო
83	239	GS	SGS	Géorgie du Sud-et-les îles Sandwich du Sud	GÉORGIE DU SUD ET LES ÎLES SANDWICH DU SUD	Géorgie du Sud-et-les Îles Sandwich du Sud
84	288	GH	GHA	Ghana	GHANA	Republic of Ghana
85	292	GI	GIB	Gibraltar	GIBRALTAR	 
86	300	GR	GRC	Grèce	GRÈCE	Ελλάδα
87	308	GD	GRD	Grenade	GRENADE	Commonwealth of Grenada
88	304	GL	GRL	Groenland	GROENLAND	Grønland
89	312	GP	GLP	Guadeloupe	GUADELOUPE	 
90	316	GU	GUM	Guam	GUAM	Guåhån
91	320	GT	GTM	Guatemala	GUATEMALA	República de Guatemala
92	831	GG	GGY	Guernesey	GUERNESEY	Guernsey
93	324	GN	GIN	Guinée	GUINÉE	République de Guinée
94	624	GW	GNB	Guinée-Bissau	GUINÉE-BISSAU	República da Guiné-Bissau
95	226	GQ	GNQ	Guinée équatoriale	GUINÉE ÉQUATORIALE	República de Guiena ecuatorial
96	328	GY	GUY	Guyana	GUYANA	Co-Operative Republic of Guyana
97	254	GF	GUF	Guyane	GUYANE FRANÇAISE	Guyane
98	332	HT	HTI	Haïti	HAÏTI	Repiblik d'Ayiti ; République d'Haïti
99	334	HM	HMD	Îles Heard-et-MacDonald	HEARD ET MACDONALD, ÎLES	Heard Island and McDonald Islands
100	340	HN	HND	Honduras	HONDURAS	República de Honduras
101	344	HK	HKG	Hong Kong	HONG KONG	香港
102	348	HU	HUN	Hongrie	HONGRIE	Magyar
103	833	IM	IMN	Île de Man	ÎLE DE MAN	Isle of Man
104	581	UM	UMI	  Îles mineures éloignées des États-Unis	ÎLES MINEURES ÉLOIGNÉES DES ÉTATS-UNIS	United States Minor Outlying Islands
105	92	VG	VGB	Îles Vierges britanniques	ÎLES VIERGES BRITANNIQUES	British Virgin Islands
106	850	VI	VIR	Îles Vierges des États-Unis	ÎLES VIERGES DES ÉTATS-UNIS	US Virgin Islands
107	356	IN	IND	Inde	INDE	Republic of India
108	360	ID	IDN	Indonésie	INDONÉSIE	Republik Indonesia
109	364	IR	IRN	Iran	IRAN, RÉPUBLIQUE ISLAMIQUE D'	جمهوری اسلامی ايران
110	368	IQ	IRQ	Irak	IRAQ	العراق
111	372	IE	IRL	Irlande	IRLANDE	Éire
112	352	IS	ISL	Islande	ISLANDE	Ísland
113	376	IL	ISR	Israël	ISRAËL	מְדִינַת יִשְׂרָאֵל
114	380	IT	ITA	Italie	ITALIE	Italia
115	388	JM	JAM	Jamaïque	JAMAÏQUE	Jamaïca
116	392	JP	JPN	Japon	JAPON	日本国
117	832	JE	JEY	Jersey	JERSEY	Bailiwick of Jersey, Bailliage de Jersey
118	400	JO	JOR	Jordanie	JORDANIE	المملكة الأردنّيّة الهاشميّة
119	398	KZ	KAZ	Kazakhstan	KAZAKHSTAN	Қазақстан Республикасы
120	404	KE	KEN	Kenya	KENYA	Jamhuri ya Kenya
121	417	KG	KGZ	Kirghizistan	KIRGHIZISTAN	Кыргыз Республикасы
122	296	KI	KIR	Kiribati	KIRIBATI	Kiribati
123	414	KW	KWT	Koweït	KOWEÏT	دولة الكويت
124	418	LA	LAO	Laos	LAO, RÉPUBLIQUE DÉMOCRATIQUE POPULAIRE	ສາທາລະນະລັດ ປະຊາທິປະໄຕ ປະຊາຊົນລາວ
125	426	LS	LSO	Lesotho	LESOTHO	Muso oa Lesotho
126	428	LV	LVA	Lettonie	LETTONIE	Latvijas
127	422	LB	LBN	Liban	LIBAN	 الجمهوريّةاللبنانيّة
128	430	LR	LBR	Liberia	LIBÉRIA	Republic of Liberia
129	434	LY	LBY	Libye	LIBYE	دولة ليبيا
130	438	LI	LIE	Liechtenstein	LIECHTENSTEIN	Fürstentum Liechtenstein
131	440	LT	LTU	Lituanie	LITUANIE	Lietuvos Respublika
132	442	LU	LUX	Luxembourg	LUXEMBOURG	Groussherzogtum Lëtzebuerg
133	446	MO	MAC	Macao	MACAO	Região Administrativa Especial de Macau da República Popular da China
134	807	MK	MKD	Macédoine du Nord	RÉPUBLIQUE DE MACÉDOINE	Република Македонија
135	450	MG	MDG	Madagascar	MADAGASCAR	République de Madagascar ; Repoblikan'i Madagasikara
136	458	MY	MYS	Malaisie	MALAISIE	Malaysia
137	454	MW	MWI	Malawi	MALAWI	Dziko la Malaŵi
138	462	MV	MDV	Maldives	MALDIVES	ދިވެހިރާއްޖޭގެ ޖުމްހޫރިއްޔާ
139	466	ML	MLI	Mali	MALI	République du Mali
140	470	MT	MLT	Malte	MALTE	Repubblika ta' Malta
141	580	MP	MNP	Îles Mariannes du Nord	MARIANNES DU NORD, ÎLES	Commonwealth of the Northern Mariana Islands
142	504	MA	MAR	Maroc	MAROC	المملكة المغربية
143	584	MH	MHL	Îles Marshall	MARSHALL, ÎLES	Aolepān Aorōkin M̧ajeļ
144	474	MQ	MTQ	Martinique	MARTINIQUE	 
145	480	MU	MUS	Maurice	MAURICE	Mauritius
146	478	MR	MRT	Mauritanie	MAURITANIE	الجمهورية الإسلامية الموريتانية
147	175	YT	MYT	Mayotte	MAYOTTE	Mayotte
148	484	MX	MEX	Mexique	MEXIQUE	Estados Unidos Mexicanos
149	583	FM	FSM	États fédérés de Micronésie	MICRONÉSIE, ÉTATS FÉDÉRÉS DE	Federated States of Micronesia
150	498	MD	MDA	Moldavie	MOLDAVIE	Republica Moldova
151	492	MC	MCO	Monaco	MONACO	 
152	496	MN	MNG	Mongolie	MONGOLIE	Монгол Улс
153	499	ME	MNE	Monténégro	MONTÉNÉGRO	Црна Гора
154	500	MS	MSR	Montserrat	MONTSERRAT	Montserrat
155	508	MZ	MOZ	Mozambique	MOZAMBIQUE	República de Moçambique
156	104	MM	MMR	Birmanie	MYANMAR	Union of Myanmar
157	516	NA	NAM	Namibie	NAMIBIE	Namibia
158	520	NR	NRU	Nauru	NAURU	Ripublik Naoero
159	524	NP	NPL	Népal	NÉPAL	
160	558	NI	NIC	Nicaragua	NICARAGUA	Republica de Nicaragua
161	562	NE	NER	Niger	NIGER	 
162	566	NG	NGA	Nigeria	NIGÉRIA	Nigeria
163	570	NU	NIU	Niue	NIUÉ	Niue
164	574	NF	NFK	Île Norfolk	NORFOLK, ÎLE	Norfolk Island
165	578	NO	NOR	Norvège	NORVÈGE	Kongeriket Norge
166	540	NC	NCL	Nouvelle-Calédonie	NOUVELLE-CALÉDONIE	 
167	554	NZ	NZL	Nouvelle-Zélande	NOUVELLE-ZÉLANDE	New Zealand
168	86	IO	IOT	Territoire britannique de l'océan Indien	OCÉAN INDIEN, TERRITOIRE BRITANNIQUE DE L'	British Indian Ocean Territory
169	512	OM	OMN	Oman	OMAN	سلطنة عُمان
170	800	UG	UGA	Ouganda	OUGANDA	Jamhuri ya Uganda
171	860	UZ	UZB	Ouzbékistan	OUZBÉKISTAN	O'zbekiston Respublikasi
172	586	PK	PAK	Pakistan	PAKISTAN	اسلامی جمہوریت پاکستان
173	585	PW	PLW	Palaos	PALAOS	Beluu er a Belau
174	275	PS	PSE	Palestine	ÉTAT DE PALESTINE	دولة فلسطين
175	591	PA	PAN	Panama	PANAMA	República de Panamá
176	598	PG	PNG	Papouasie-Nouvelle-Guinée	PAPOUASIE-NOUVELLE-GUINÉE	l'État indépendant de Papouasie-Nouvelle-Guinée
177	600	PY	PRY	Paraguay	PARAGUAY	República del Paraguay
178	528	NL	NLD	Pays-Bas	PAYS-BAS	Nederland
179	604	PE	PER	Pérou	PÉROU	Perú
180	608	PH	PHL	Philippines	PHILIPPINES	Republika ng Pilipinas
181	612	PN	PCN	Îles Pitcairn	PITCAIRN	Pitcairn
182	616	PL	POL	Pologne	POLOGNE	Polska
183	258	PF	PYF	Polynésie française	POLYNÉSIE FRANÇAISE	Polynésie française
184	630	PR	PRI	Porto Rico	PORTO RICO	Estado Libre Asociado de Puerto Rico 
185	620	PT	PRT	Portugal	PORTUGAL	Portugal
186	634	QA	QAT	Qatar	QATAR	دولة قطر
187	638	RE	REU	La Réunion	RÉUNION	La Réunion
188	642	RO	ROU	Roumanie	ROUMANIE	România
189	826	GB	GBR	Royaume-Uni	ROYAUME-UNI	United Kingdom
190	643	RU	RUS	Russie	RUSSIE, FÉDÉRATION DE	Российская Федерация
191	646	RW	RWA	Rwanda	RWANDA	Repubulika y'u Rwanda
192	732	EH	ESH	République arabe sahraouie démocratique	SAHARA OCCIDENTAL	Western Sahara
193	652	BL	BLM	Saint-Barthélemy	SAINT-BARTHÉLEMY	 
194	659	KN	KNA	Saint-Christophe-et-Niévès	SAINT-KITTS-ET-NEVIS	Saint Kitts and Nevis
195	674	SM	SMR	Saint-Marin	SAINT-MARIN	San Marino
196	663	MF	MAF	Saint-Martin	SAINT-MARTIN (PARTIE FRANÇAISE)	 
197	534	SX	SXM	Saint-Martin	SAINT-MARTIN (PARTIE NÉERLANDAISE)	Sint Maarten
198	666	PM	SPM	Saint-Pierre-et-Miquelon	SAINT-PIERRE-ET-MIQUELON	 
199	336	VA	VAT	Saint-Siège (État de la Cité du Vatican)	SAINT-SIÈGE (ÉTAT DE LA CITÉ DU VATICAN)	Stato della Città del Vaticano
200	670	VC	VCT	Saint-Vincent-et-les-Grenadines	SAINT-VINCENT-ET-LES-GRENADINES	Saint Vincent and the Grenadines
201	654	SH	SHN	Sainte-Hélène, Ascension et Tristan da Cunha	SAINTE-HÉLÈNE, ASCENSION ET TRISTAN DA CUNHA	Saint Helena, Assunsion and Tristan da Cunha
202	662	LC	LCA	Sainte-Lucie	SAINTE-LUCIE	Commonwealth of Saint Lucia
203	90	SB	SLB	Îles Salomon	SALOMON, ÎLES	Solomons Islands
204	882	WS	WSM	Samoa	SAMOA	Malo Sa'oloto Tuto'atasi o Samoa
205	16	AS	ASM	Samoa américaines	SAMOA AMÉRICAINES	American Samoa
206	678	ST	STP	Sao Tomé-et-Principe	SAO TOMÉ-ET-PRINCIPE	República Democrática de São Tomé e Príncipe
207	686	SN	SEN	Sénégal	SÉNÉGAL	République du Sénégal
208	688	RS	SRB	Serbie	SERBIE	Република Србија
209	690	SC	SYC	Seychelles	SEYCHELLES	Repiblik Sesel
210	694	SL	SLE	Sierra Leone	SIERRA LEONE	Sierra Leone
211	702	SG	SGP	Singapour	SINGAPOUR	Republic of Singapore
212	703	SK	SVK	Slovaquie	SLOVAQUIE	Slovenská republika
213	705	SI	SVN	Slovénie	SLOVÉNIE	Republika Slovenija
214	706	SO	SOM	Somalie	SOMALIE	 جمهورية الصومال الفدرالية
215	729	SD	SDN	Soudan	SOUDAN	جمهورية السودان (Jumhuriyat al-Sudan)
216	728	SS	SSD	Soudan du Sud	SOUDAN DU SUD	Republic of South Sudan
217	144	LK	LKA	Sri Lanka	SRI LANKA	Prajatantrika Samajavadi Janarajaya ; Ilankai Sananayaka Sosolisa Kudiyarasu
218	752	SE	SWE	Suède	SUÈDE	Sverige
219	756	CH	CHE	Suisse	SUISSE	Confœderatio Helvetica
220	740	SR	SUR	Suriname	SURINAME	Republiek Suriname
221	744	SJ	SJM	Svalbard et ile Jan Mayen	SVALBARD ET ÎLE JAN MAYEN	Svalbard og Jan Mayen
222	748	SZ	SWZ	Eswatini	ESWATINI	Umbuso we Swatini
223	760	SY	SYR	Syrie	SYRIENNE, RÉPUBLIQUE ARABE	الجمهوريّة العربيّة السّوريّة
224	762	TJ	TJK	Tadjikistan	TADJIKISTAN	Ҷумҳурии Тоҷикистон
225	158	TW	TWN	Taïwan	TAÏWAN	台灣
226	834	TZ	TZA	Tanzanie	TANZANIE, RÉPUBLIQUE UNIE DE	United Republic of Tanzania
227	148	TD	TCD	Tchad	TCHAD	جمهورية تشاد
228	203	CZ	CZE	Tchéquie	TCHÉQUIE	Česká republika
229	260	TF	ATF	Terres australes et antarctiques françaises	TERRES AUSTRALES FRANÇAISES	Terres australes et antarctiques françaises
230	764	TH	THA	Thaïlande	THAÏLANDE	ราชอาณาจักรไทย
231	626	TL	TLS	Timor oriental	TIMOR-LESTE	Repúblika Demokrátika Timor Lorosa'e
232	768	TG	TGO	Togo	TOGO	République togolaise
233	772	TK	TKL	Tokelau	TOKELAU	Tokelau
234	776	TO	TON	Tonga	TONGA	Pule'anga Fakatu'i 'o Tonga
235	780	TT	TTO	Trinité-et-Tobago	TRINITÉ-ET-TOBAGO	Republic of Trinidad and Tobago
236	788	TN	TUN	Tunisie	TUNISIE	الجمهورية التونسية
237	795	TM	TKM	Turkménistan	TURKMÉNISTAN	Türkmenistan Respublikasy
238	796	TC	TCA	Îles Turques-et-Caïques	TURKS ET CAÏQUES, ÎLES	Turks-and-Caicos
239	792	TR	TUR	Turquie	TURQUIE	Türkiye Cumhuriyeti
240	798	TV	TUV	Tuvalu	TUVALU	Tuvalu
241	804	UA	UKR	Ukraine	UKRAINE	Украïна
242	858	UY	URY	Uruguay	URUGUAY	República Oriental del Uruguay
243	548	VU	VUT	Vanuatu	VANUATU	Ripablik blong Vanuatu
244	862	VE	VEN	Venezuela	VENEZUELA, RÉPUBLIQUE BOLIVARIENNE DU	República Bolivariana de Venezuela
245	704	VN	VNM	Viêt Nam	VIET NAM	Cộng Hoà Xã Hội Chủ Nghĩa Việt Nam
246	876	WF	WLF	Wallis-et-Futuna	WALLIS-ET-FUTUNA	Wallis-et-Futuna
247	887	YE	YEM	Yémen	YÉMEN	ﺍﻟﺠﻤﻬﻮﺭﯾّﺔ اليمنية
248	894	ZM	ZMB	Zambie	ZAMBIE	Republic of Zambia
249	716	ZW	ZWE	Zimbabwe	ZIMBABWE	Republic of Zimbabwe
\.


--
-- Data for Name: domain; Type: TABLE DATA; Schema: ref; Owner: ylanglais
--

COPY ref.domain (name, description) FROM stdin;
Infra	Infrastructure
Middleware	Middelware (BdD, ETL,...)
Application	Applications métier
Sicare	Application de gestion Sicare
Kélia	Application de gestion Kélia
DNA	GED
Graal	CRM
FV	Front Vie
ESPP	Espace personnel
QDD	Qualité de données
BI	BI
\.


--
-- Data for Name: etype; Type: TABLE DATA; Schema: ref; Owner: ylanglais
--

COPY ref.etype (id, name) FROM stdin;
1	table
2	view
\.


--
-- Data for Name: fragment_type; Type: TABLE DATA; Schema: ref; Owner: ylanglais
--

COPY ref.fragment_type (type, description) FROM stdin;
column	main table column
reference	1:1 simple reference from foreign table
values	1:n linked singleton
entity	1:n embedded entity
vallist	1:n linked list of values
entitylist	1:n linked list of entities
\.


--
-- Data for Name: frequency; Type: TABLE DATA; Schema: ref; Owner: ylanglais
--

COPY ref.frequency (name, description) FROM stdin;
\.


--
-- Data for Name: gender; Type: TABLE DATA; Schema: ref; Owner: ylanglais
--

COPY ref.gender (id, value, description) FROM stdin;
0	Undefined	\N
1	Female	\N
2	Male	\N
3	Other	\N
\.


--
-- Data for Name: id_type; Type: TABLE DATA; Schema: ref; Owner: ylanglais
--

COPY ref.id_type (name, description) FROM stdin;
manual	user given unique id
integer	user given unique integer id
string	user given unique string id
autoint	timestamp / epoch
autostr	MD5(timepsamp)
serial	serial id
uuid	unique uuid type 4 id
\.


--
-- Data for Name: page_type; Type: TABLE DATA; Schema: ref; Owner: ylanglais
--

COPY ref.page_type (name, description) FROM stdin;
Table	Generic page from table
View	Generic page from application view
Form	Page is a generic form
Report	Page is a gereric report
External	Page is an external link
System	Page is a system specific page
Client	Page is a client defined page
\.


--
-- Data for Name: perm; Type: TABLE DATA; Schema: ref; Owner: ylanglais
--

COPY ref.perm (name, description) FROM stdin;
SYSTEM	System defined
HIDDEN	Not to be displayed
PRIVATE	For admin only
RONLY	Read only / consult
ALL	Read and write permissions
\.


--
-- Data for Name: title; Type: TABLE DATA; Schema: ref; Owner: ylanglais
--

COPY ref.title (id, value, description) FROM stdin;
0	Undefined	\N
1	Melle	\N
2	Mme	\N
3	M	\N
4	Dr	\N
5	Me	\N
6	Pr	\N
\.


--
-- Data for Name: type_verif; Type: TABLE DATA; Schema: ref; Owner: ylanglais
--

COPY ref.type_verif (name, description) FROM stdin;
Automatique	Vérification automatique
Manuelle	Vérification manuelle
\.


--
-- Data for Name: duration; Type: TABLE DATA; Schema: stats; Owner: ylanglais
--

COPY stats.duration (key, n, min, avg, max) FROM stdin;
\.


--
-- Data for Name: dbs; Type: TABLE DATA; Schema: tech; Owner: ylanglais
--

COPY tech.dbs (dbs, drv, host, port, name, "user", pass, opts) FROM stdin;
m_local_pfwk	mysql	localhost	\N	pfwk	pfwk	pfwk_admin	\N
p_4in_rcu	pgsql	4in	5432	rcu	ylanglais	vcstHqqVWwEPg0Io	\N
p_4in_grf	pgsql	4in	5432	grf	ylanglais	vcstHqqVWwEPg0Io	\N
m_4in_aei	mysql	4in	3306	AEI	ylanglais	QjHKs6J6s	\N
m_4in_selectup	mysql	4in	3306	selectup	ylanglais	QjHKs6J6s	\N
p_local_ob2c	pgsql	localhost	5432	ob2c	ylanglais	vcstHqqVWwEPg0Io	\N
m_local_ob2c	mysql	localhost	3306	ob2c	ylanglais	QjHKs6J6s	\N
default	pgsql	localhost	5432	ptoolbox	ylanglais	vcstHqqVWwEPg0Io	\N
\.


--
-- Data for Name: role; Type: TABLE DATA; Schema: tech; Owner: ylanglais
--

COPY tech.role (id, name, description) FROM stdin;
0	any	Any role
1	admin	Administrator
10	api	Generic API role
50	local	Local authentification
100	audit	Audit user
1000	user	Any physical user
200	dev	\N
300	rec	\N
\.


--
-- Data for Name: user; Type: TABLE DATA; Schema: tech; Owner: ylanglais
--

COPY tech."user" (id, login, passwd, name, surname, mail, active, since, until) FROM stdin;
11	alupin	\N	\N	\N	\N	Y	2021-10-08	\N
12	asanantonio	\N	\N	\N	\N	Y	2021-10-08	\N
13	adupin	\N	\N	\N	\N	Y	2021-10-08	\N
14	nburma	\N	\N	\N	\N	Y	2021-10-08	\N
15	jmaigret	\N	\N	\N	\N	Y	2021-10-08	\N
16	nlefloch	\N	\N	\N	\N	Y	2021-10-08	\N
17	jjosephin	\N				Y	2021-10-08	\N
1	api	$pbkdf2-sha512$25000$nb0ylc7wGUKbA64Av5rwS8D$oy3glTZJpN4nUWq+QulBMTk3obylW+1ShDE2JVSSpI/ShHxupktDcLUG2V2tTDhX0g9ibxmHAqSWAsvAzIFGQg==	api			Y	1970-01-01	\N
0	admin	$pbkdf2-sha512$25000$oil/hKtj0veknaszUWOxPuw$TUikFyf+/PfJ3f9hQ41nuHwdIFStfe7WnpuG2apR2QWI3En52GLXrQKsbyH5C7VrElZNB6tmzZuF5WUdJzRf2g==	Administrateur			Y	1970-01-01	\N
10	ylanglais	$pbkdf2-sha512$25000$Kfn/6uVLaJeOW70hfB3O1EL$byIN3TJtf61XWWLbQSizaEzDJagyKFt2W1lsL9ErgsHUDiL7/QpWoi+GIscjEUAeT2WWjul/pTh1ZfLj5StAWg==	Yann	Langlais	ylanglais@gmail.com	Y	1970-01-01	\N
\.


--
-- Data for Name: user_role; Type: TABLE DATA; Schema: tech; Owner: ylanglais
--

COPY tech.user_role (user_id, role_id) FROM stdin;
1	10
11	1000
12	1000
13	1000
14	1000
15	1000
16	1000
17	1000
0	0
0	1
0	100
0	1000
10	0
10	1
10	50
10	100
10	1000
10	200
10	300
\.


--
-- Data for Name: wfaction; Type: TABLE DATA; Schema: tech; Owner: ylanglais
--

COPY tech.wfaction (id, workflow, role, state0, state1, action, initial, othuid, mustcomment, script) FROM stdin;
\.


--
-- Data for Name: wfcheck; Type: TABLE DATA; Schema: tech; Owner: ylanglais
--

COPY tech.wfcheck (version, login, id, entity_state, f_admin, f_oper, f_consult) FROM stdin;
\.


--
-- Data for Name: workflow; Type: TABLE DATA; Schema: tech; Owner: ylanglais
--

COPY tech.workflow (id, name, dsc, entity) FROM stdin;
\.


--
-- Name: list_id_seq; Type: SEQUENCE SET; Schema: param; Owner: ylanglais
--

SELECT pg_catalog.setval('param.list_id_seq', 1, false);


--
-- Name: morning_check_id_seq; Type: SEQUENCE SET; Schema: public; Owner: ylanglais
--

SELECT pg_catalog.setval('public.morning_check_id_seq', 1, false);


--
-- Name: action action_pkey; Type: CONSTRAINT; Schema: audit; Owner: ylanglais
--

ALTER TABLE ONLY audit.action
    ADD CONSTRAINT action_pkey PRIMARY KEY (id);


--
-- Name: connection connection_pkey; Type: CONSTRAINT; Schema: audit; Owner: ylanglais
--

ALTER TABLE ONLY audit.connection
    ADD CONSTRAINT connection_pkey PRIMARY KEY (id, login, since, ip, state);


--
-- Name: log log_pkey; Type: CONSTRAINT; Schema: audit; Owner: ylanglais
--

ALTER TABLE ONLY audit.log
    ADD CONSTRAINT log_pkey PRIMARY KEY (tstamp, ip);


--
-- Name: entity entity_pkey; Type: CONSTRAINT; Schema: param; Owner: ylanglais
--

ALTER TABLE ONLY param.entity
    ADD CONSTRAINT entity_pkey PRIMARY KEY (name);


--
-- Name: field field_pkey; Type: CONSTRAINT; Schema: param; Owner: ylanglais
--

ALTER TABLE ONLY param.field
    ADD CONSTRAINT field_pkey PRIMARY KEY (entity, name);


--
-- Name: folder folder_id_key; Type: CONSTRAINT; Schema: param; Owner: ylanglais
--

ALTER TABLE ONLY param.folder
    ADD CONSTRAINT folder_id_key UNIQUE (id);


--
-- Name: folder_page folder_page_id_key; Type: CONSTRAINT; Schema: param; Owner: ylanglais
--

ALTER TABLE ONLY param.folder_page
    ADD CONSTRAINT folder_page_id_key UNIQUE (folder_id, page_id);


--
-- Name: fragment fragment_pkey; Type: CONSTRAINT; Schema: param; Owner: ylanglais
--

ALTER TABLE ONLY param.fragment
    ADD CONSTRAINT fragment_pkey PRIMARY KEY (entity, name);


--
-- Name: item item_pkey; Type: CONSTRAINT; Schema: param; Owner: ylanglais
--

ALTER TABLE ONLY param.item
    ADD CONSTRAINT item_pkey PRIMARY KEY (list_id, item);


--
-- Name: list list_name_role_user_key; Type: CONSTRAINT; Schema: param; Owner: ylanglais
--

ALTER TABLE ONLY param.list
    ADD CONSTRAINT list_name_role_user_key UNIQUE (name, role, "user");


--
-- Name: list list_pkey; Type: CONSTRAINT; Schema: param; Owner: ylanglais
--

ALTER TABLE ONLY param.list
    ADD CONSTRAINT list_pkey PRIMARY KEY (id);


--
-- Name: page page_id_key; Type: CONSTRAINT; Schema: param; Owner: ylanglais
--

ALTER TABLE ONLY param.page
    ADD CONSTRAINT page_id_key UNIQUE (id);


--
-- Name: page_perm page_perm_id_key; Type: CONSTRAINT; Schema: param; Owner: ylanglais
--

ALTER TABLE ONLY param.page_perm
    ADD CONSTRAINT page_perm_id_key UNIQUE (page_id, role_id);


--
-- Name: fragment param_fragment_entity_type_forder_unique; Type: CONSTRAINT; Schema: param; Owner: ylanglais
--

ALTER TABLE ONLY param.fragment
    ADD CONSTRAINT param_fragment_entity_type_forder_unique UNIQUE (entity, type, forder);


--
-- Name: right right_type_link_role_id_user_id_key; Type: CONSTRAINT; Schema: param; Owner: ylanglais
--

ALTER TABLE ONLY param."right"
    ADD CONSTRAINT right_type_link_role_id_user_id_key UNIQUE (type, link, role_id, user_id);


--
-- Name: style style_key_key; Type: CONSTRAINT; Schema: param; Owner: ylanglais
--

ALTER TABLE ONLY param.style
    ADD CONSTRAINT style_key_key UNIQUE (key);


--
-- Name: wfaction wfaction_pkey; Type: CONSTRAINT; Schema: param; Owner: ylanglais
--

ALTER TABLE ONLY param.wfaction
    ADD CONSTRAINT wfaction_pkey PRIMARY KEY (id);


--
-- Name: wfcheck wfcheck_pkey; Type: CONSTRAINT; Schema: param; Owner: ylanglais
--

ALTER TABLE ONLY param.wfcheck
    ADD CONSTRAINT wfcheck_pkey PRIMARY KEY (id);


--
-- Name: workflow workflow_pkey; Type: CONSTRAINT; Schema: param; Owner: ylanglais
--

ALTER TABLE ONLY param.workflow
    ADD CONSTRAINT workflow_pkey PRIMARY KEY (id);


--
-- Name: address address_pkey; Type: CONSTRAINT; Schema: public; Owner: ylanglais
--

ALTER TABLE ONLY public.address
    ADD CONSTRAINT address_pkey PRIMARY KEY (id);


--
-- Name: car car_pkey; Type: CONSTRAINT; Schema: public; Owner: ylanglais
--

ALTER TABLE ONLY public.car
    ADD CONSTRAINT car_pkey PRIMARY KEY (brand, model);


--
-- Name: email email_pkey; Type: CONSTRAINT; Schema: public; Owner: ylanglais
--

ALTER TABLE ONLY public.email
    ADD CONSTRAINT email_pkey PRIMARY KEY (id);


--
-- Name: link_channel link_channel_pkey; Type: CONSTRAINT; Schema: public; Owner: ylanglais
--

ALTER TABLE ONLY public.link_channel
    ADD CONSTRAINT link_channel_pkey PRIMARY KEY (entity, entity_id, channel, channel_id);


--
-- Name: morning_check morning_check_pkey; Type: CONSTRAINT; Schema: public; Owner: ylanglais
--

ALTER TABLE ONLY public.morning_check
    ADD CONSTRAINT morning_check_pkey PRIMARY KEY (id);


--
-- Name: person person_pkey; Type: CONSTRAINT; Schema: public; Owner: ylanglais
--

ALTER TABLE ONLY public.person
    ADD CONSTRAINT person_pkey PRIMARY KEY (id);


--
-- Name: phone phone_pkey; Type: CONSTRAINT; Schema: public; Owner: ylanglais
--

ALTER TABLE ONLY public.phone
    ADD CONSTRAINT phone_pkey PRIMARY KEY (id);


--
-- Name: application application_pkey; Type: CONSTRAINT; Schema: ref; Owner: ylanglais
--

ALTER TABLE ONLY ref.application
    ADD CONSTRAINT application_pkey PRIMARY KEY (name);


--
-- Name: check_status check_status_pkey; Type: CONSTRAINT; Schema: ref; Owner: ylanglais
--

ALTER TABLE ONLY ref.check_status
    ADD CONSTRAINT check_status_pkey PRIMARY KEY (name);


--
-- Name: country country_pkey; Type: CONSTRAINT; Schema: ref; Owner: ylanglais
--

ALTER TABLE ONLY ref.country
    ADD CONSTRAINT country_pkey PRIMARY KEY (id);


--
-- Name: domain domain_pkey; Type: CONSTRAINT; Schema: ref; Owner: ylanglais
--

ALTER TABLE ONLY ref.domain
    ADD CONSTRAINT domain_pkey PRIMARY KEY (name);


--
-- Name: etype etype_id_key; Type: CONSTRAINT; Schema: ref; Owner: ylanglais
--

ALTER TABLE ONLY ref.etype
    ADD CONSTRAINT etype_id_key UNIQUE (id);


--
-- Name: etype etype_name_key; Type: CONSTRAINT; Schema: ref; Owner: ylanglais
--

ALTER TABLE ONLY ref.etype
    ADD CONSTRAINT etype_name_key UNIQUE (name);


--
-- Name: fragment_type fragment_type_pkey; Type: CONSTRAINT; Schema: ref; Owner: ylanglais
--

ALTER TABLE ONLY ref.fragment_type
    ADD CONSTRAINT fragment_type_pkey PRIMARY KEY (type);


--
-- Name: frequency frequency_pkey; Type: CONSTRAINT; Schema: ref; Owner: ylanglais
--

ALTER TABLE ONLY ref.frequency
    ADD CONSTRAINT frequency_pkey PRIMARY KEY (name);


--
-- Name: gender gender_pkey; Type: CONSTRAINT; Schema: ref; Owner: ylanglais
--

ALTER TABLE ONLY ref.gender
    ADD CONSTRAINT gender_pkey PRIMARY KEY (id);


--
-- Name: gender gender_value_key; Type: CONSTRAINT; Schema: ref; Owner: ylanglais
--

ALTER TABLE ONLY ref.gender
    ADD CONSTRAINT gender_value_key UNIQUE (value);


--
-- Name: id_type id_type_pkey; Type: CONSTRAINT; Schema: ref; Owner: ylanglais
--

ALTER TABLE ONLY ref.id_type
    ADD CONSTRAINT id_type_pkey PRIMARY KEY (name);


--
-- Name: page_type page_type_key; Type: CONSTRAINT; Schema: ref; Owner: ylanglais
--

ALTER TABLE ONLY ref.page_type
    ADD CONSTRAINT page_type_key PRIMARY KEY (name);


--
-- Name: perm perm_name_key; Type: CONSTRAINT; Schema: ref; Owner: ylanglais
--

ALTER TABLE ONLY ref.perm
    ADD CONSTRAINT perm_name_key UNIQUE (name);


--
-- Name: title title_pkey; Type: CONSTRAINT; Schema: ref; Owner: ylanglais
--

ALTER TABLE ONLY ref.title
    ADD CONSTRAINT title_pkey PRIMARY KEY (id);


--
-- Name: title title_value_key; Type: CONSTRAINT; Schema: ref; Owner: ylanglais
--

ALTER TABLE ONLY ref.title
    ADD CONSTRAINT title_value_key UNIQUE (value);


--
-- Name: type_verif type_verif_pkey; Type: CONSTRAINT; Schema: ref; Owner: ylanglais
--

ALTER TABLE ONLY ref.type_verif
    ADD CONSTRAINT type_verif_pkey PRIMARY KEY (name);


--
-- Name: role role_id_key; Type: CONSTRAINT; Schema: tech; Owner: ylanglais
--

ALTER TABLE ONLY tech.role
    ADD CONSTRAINT role_id_key UNIQUE (id);


--
-- Name: role role_name_key; Type: CONSTRAINT; Schema: tech; Owner: ylanglais
--

ALTER TABLE ONLY tech.role
    ADD CONSTRAINT role_name_key UNIQUE (name);


--
-- Name: role role_pkey; Type: CONSTRAINT; Schema: tech; Owner: ylanglais
--

ALTER TABLE ONLY tech.role
    ADD CONSTRAINT role_pkey PRIMARY KEY (id);


--
-- Name: dbs tech_dbs_pkey; Type: CONSTRAINT; Schema: tech; Owner: ylanglais
--

ALTER TABLE ONLY tech.dbs
    ADD CONSTRAINT tech_dbs_pkey PRIMARY KEY (dbs);


--
-- Name: user user_id_key; Type: CONSTRAINT; Schema: tech; Owner: ylanglais
--

ALTER TABLE ONLY tech."user"
    ADD CONSTRAINT user_id_key UNIQUE (id);


--
-- Name: user user_login_key; Type: CONSTRAINT; Schema: tech; Owner: ylanglais
--

ALTER TABLE ONLY tech."user"
    ADD CONSTRAINT user_login_key UNIQUE (login);


--
-- Name: user user_pkey; Type: CONSTRAINT; Schema: tech; Owner: ylanglais
--

ALTER TABLE ONLY tech."user"
    ADD CONSTRAINT user_pkey PRIMARY KEY (id);


--
-- Name: user_role user_role_pkey; Type: CONSTRAINT; Schema: tech; Owner: ylanglais
--

ALTER TABLE ONLY tech.user_role
    ADD CONSTRAINT user_role_pkey PRIMARY KEY (user_id, role_id);


--
-- Name: wfaction wfaction_pkey; Type: CONSTRAINT; Schema: tech; Owner: ylanglais
--

ALTER TABLE ONLY tech.wfaction
    ADD CONSTRAINT wfaction_pkey PRIMARY KEY (id);


--
-- Name: wfcheck wfcheck_pkey; Type: CONSTRAINT; Schema: tech; Owner: ylanglais
--

ALTER TABLE ONLY tech.wfcheck
    ADD CONSTRAINT wfcheck_pkey PRIMARY KEY (id);


--
-- Name: workflow workflow_pkey; Type: CONSTRAINT; Schema: tech; Owner: ylanglais
--

ALTER TABLE ONLY tech.workflow
    ADD CONSTRAINT workflow_pkey PRIMARY KEY (id);


--
-- Name: action action_login_fkey; Type: FK CONSTRAINT; Schema: audit; Owner: ylanglais
--

ALTER TABLE ONLY audit.action
    ADD CONSTRAINT action_login_fkey FOREIGN KEY (login) REFERENCES tech."user"(login);


--
-- Name: entity entity_dsrc_fkey; Type: FK CONSTRAINT; Schema: param; Owner: ylanglais
--

ALTER TABLE ONLY param.entity
    ADD CONSTRAINT entity_dsrc_fkey FOREIGN KEY (dsrc) REFERENCES tech.dbs(dbs);


--
-- Name: field field_entity_fkey; Type: FK CONSTRAINT; Schema: param; Owner: ylanglais
--

ALTER TABLE ONLY param.field
    ADD CONSTRAINT field_entity_fkey FOREIGN KEY (entity) REFERENCES param.entity(name);


--
-- Name: field field_rsrc_fkey; Type: FK CONSTRAINT; Schema: param; Owner: ylanglais
--

ALTER TABLE ONLY param.field
    ADD CONSTRAINT field_rsrc_fkey FOREIGN KEY (rsrc) REFERENCES tech.dbs(dbs);


--
-- Name: folder_page folder_page_folder_id_fkey; Type: FK CONSTRAINT; Schema: param; Owner: ylanglais
--

ALTER TABLE ONLY param.folder_page
    ADD CONSTRAINT folder_page_folder_id_fkey FOREIGN KEY (folder_id) REFERENCES param.folder(id);


--
-- Name: folder_page folder_page_page_id_fkey; Type: FK CONSTRAINT; Schema: param; Owner: ylanglais
--

ALTER TABLE ONLY param.folder_page
    ADD CONSTRAINT folder_page_page_id_fkey FOREIGN KEY (page_id) REFERENCES param.page(id);


--
-- Name: folder_perm folder_perm_folder_id_fkey; Type: FK CONSTRAINT; Schema: param; Owner: ylanglais
--

ALTER TABLE ONLY param.folder_perm
    ADD CONSTRAINT folder_perm_folder_id_fkey FOREIGN KEY (folder_id) REFERENCES param.folder(id);


--
-- Name: folder_perm folder_perm_perm_name_fkey; Type: FK CONSTRAINT; Schema: param; Owner: ylanglais
--

ALTER TABLE ONLY param.folder_perm
    ADD CONSTRAINT folder_perm_perm_name_fkey FOREIGN KEY (perm_name) REFERENCES ref.perm(name);


--
-- Name: folder_perm folder_perm_role_id_fkey; Type: FK CONSTRAINT; Schema: param; Owner: ylanglais
--

ALTER TABLE ONLY param.folder_perm
    ADD CONSTRAINT folder_perm_role_id_fkey FOREIGN KEY (role_id) REFERENCES tech.role(id);


--
-- Name: fragment fragment_entity_fkey; Type: FK CONSTRAINT; Schema: param; Owner: ylanglais
--

ALTER TABLE ONLY param.fragment
    ADD CONSTRAINT fragment_entity_fkey FOREIGN KEY (entity) REFERENCES param.entity(name);


--
-- Name: fragment fragment_fsrc_fkey; Type: FK CONSTRAINT; Schema: param; Owner: ylanglais
--

ALTER TABLE ONLY param.fragment
    ADD CONSTRAINT fragment_fsrc_fkey FOREIGN KEY (fsrc) REFERENCES tech.dbs(dbs);


--
-- Name: fragment fragment_type_fkey; Type: FK CONSTRAINT; Schema: param; Owner: ylanglais
--

ALTER TABLE ONLY param.fragment
    ADD CONSTRAINT fragment_type_fkey FOREIGN KEY (type) REFERENCES ref.fragment_type(type);


--
-- Name: item item_list_id_fkey; Type: FK CONSTRAINT; Schema: param; Owner: ylanglais
--

ALTER TABLE ONLY param.item
    ADD CONSTRAINT item_list_id_fkey FOREIGN KEY (list_id) REFERENCES param.list(id);


--
-- Name: list list_role_fkey; Type: FK CONSTRAINT; Schema: param; Owner: ylanglais
--

ALTER TABLE ONLY param.list
    ADD CONSTRAINT list_role_fkey FOREIGN KEY (role) REFERENCES tech.role(name);


--
-- Name: list list_user_fkey; Type: FK CONSTRAINT; Schema: param; Owner: ylanglais
--

ALTER TABLE ONLY param.list
    ADD CONSTRAINT list_user_fkey FOREIGN KEY ("user") REFERENCES tech."user"(login);


--
-- Name: page_perm page_perm_page_id_fkey; Type: FK CONSTRAINT; Schema: param; Owner: ylanglais
--

ALTER TABLE ONLY param.page_perm
    ADD CONSTRAINT page_perm_page_id_fkey FOREIGN KEY (page_id) REFERENCES param.page(id);


--
-- Name: page_perm page_perm_perm_name_fkey; Type: FK CONSTRAINT; Schema: param; Owner: ylanglais
--

ALTER TABLE ONLY param.page_perm
    ADD CONSTRAINT page_perm_perm_name_fkey FOREIGN KEY (perm_name) REFERENCES ref.perm(name);


--
-- Name: page_perm page_perm_role_id_fkey; Type: FK CONSTRAINT; Schema: param; Owner: ylanglais
--

ALTER TABLE ONLY param.page_perm
    ADD CONSTRAINT page_perm_role_id_fkey FOREIGN KEY (role_id) REFERENCES tech.role(id);


--
-- Name: fragment param_folder_page_type_fkey; Type: FK CONSTRAINT; Schema: param; Owner: ylanglais
--

ALTER TABLE ONLY param.fragment
    ADD CONSTRAINT param_folder_page_type_fkey FOREIGN KEY (type) REFERENCES ref.fragment_type(type);


--
-- Name: right right_perm_fkey; Type: FK CONSTRAINT; Schema: param; Owner: ylanglais
--

ALTER TABLE ONLY param."right"
    ADD CONSTRAINT right_perm_fkey FOREIGN KEY (perm) REFERENCES ref.perm(name);


--
-- Name: right right_role_id_fkey; Type: FK CONSTRAINT; Schema: param; Owner: ylanglais
--

ALTER TABLE ONLY param."right"
    ADD CONSTRAINT right_role_id_fkey FOREIGN KEY (role_id) REFERENCES tech.role(id);


--
-- Name: right right_type_fkey; Type: FK CONSTRAINT; Schema: param; Owner: ylanglais
--

ALTER TABLE ONLY param."right"
    ADD CONSTRAINT right_type_fkey FOREIGN KEY (type) REFERENCES ref.etype(name);


--
-- Name: right right_user_id_fkey; Type: FK CONSTRAINT; Schema: param; Owner: ylanglais
--

ALTER TABLE ONLY param."right"
    ADD CONSTRAINT right_user_id_fkey FOREIGN KEY (user_id) REFERENCES tech."user"(id);


--
-- Name: address address_country_fkey; Type: FK CONSTRAINT; Schema: public; Owner: ylanglais
--

ALTER TABLE ONLY public.address
    ADD CONSTRAINT address_country_fkey FOREIGN KEY (country) REFERENCES ref.country(id);


--
-- Name: car car_owner_fkey; Type: FK CONSTRAINT; Schema: public; Owner: ylanglais
--

ALTER TABLE ONLY public.car
    ADD CONSTRAINT car_owner_fkey FOREIGN KEY (owner) REFERENCES public.person(id);


--
-- Name: morning_check morning_check_domain_fkey; Type: FK CONSTRAINT; Schema: public; Owner: ylanglais
--

ALTER TABLE ONLY public.morning_check
    ADD CONSTRAINT morning_check_domain_fkey FOREIGN KEY (domain) REFERENCES ref.domain(name);


--
-- Name: morning_check morning_check_type_verif_fkey; Type: FK CONSTRAINT; Schema: public; Owner: ylanglais
--

ALTER TABLE ONLY public.morning_check
    ADD CONSTRAINT morning_check_type_verif_fkey FOREIGN KEY (type_verif) REFERENCES ref.type_verif(name);


--
-- Name: person person_gender_fkey; Type: FK CONSTRAINT; Schema: public; Owner: ylanglais
--

ALTER TABLE ONLY public.person
    ADD CONSTRAINT person_gender_fkey FOREIGN KEY (gender) REFERENCES ref.gender(id);


--
-- Name: person person_title_fkey; Type: FK CONSTRAINT; Schema: public; Owner: ylanglais
--

ALTER TABLE ONLY public.person
    ADD CONSTRAINT person_title_fkey FOREIGN KEY (title) REFERENCES ref.title(id);


--
-- Name: user_role user_role_role_id_fkey; Type: FK CONSTRAINT; Schema: tech; Owner: ylanglais
--

ALTER TABLE ONLY tech.user_role
    ADD CONSTRAINT user_role_role_id_fkey FOREIGN KEY (role_id) REFERENCES tech.role(id);


--
-- Name: user_role user_role_user_id_fkey; Type: FK CONSTRAINT; Schema: tech; Owner: ylanglais
--

ALTER TABLE ONLY tech.user_role
    ADD CONSTRAINT user_role_user_id_fkey FOREIGN KEY (user_id) REFERENCES tech."user"(id);


--
-- Name: SCHEMA public; Type: ACL; Schema: -; Owner: postgres
--

REVOKE USAGE ON SCHEMA public FROM PUBLIC;
GRANT ALL ON SCHEMA public TO PUBLIC;


--
-- PostgreSQL database dump complete
--

\unrestrict 4yD9DE5fHnvgsMM6ziSqbsgLFFYVUDRmBNAxde2fVXOHX28Dcj9tCiXTnH1dvxE

